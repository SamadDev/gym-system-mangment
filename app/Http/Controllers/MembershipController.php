<?php

namespace App\Http\Controllers;

use App\Constants\DateTimeConstants;
use App\Http\Requests\StoreMembershipRequest;
use App\Http\Requests\UpdateMembershipRequest;
use App\Models\Membership;
use App\Models\MembershipPlan;
use App\Models\Member;
use App\Models\Payment;
use App\Models\Invoice;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use DataTables;
use Illuminate\Support\Facades\DB;

class MembershipController extends Controller
{
    use HttpResponses;

    /**
     * Get DataTables data for memberships listing
     */
    public function data(Request $request)
    {
        $memberships = Membership::select([
            'id', 'member_id', 'membership_plan_id', 'start_date', 
            'end_date', 'price', 'status', 'created_at'
        ])
        ->with([
            'member:id,member_code,first_name,last_name,phone',
            'membershipPlan:id,name,duration_months'
        ])
        ->when(request('status'), function ($q, $status) {
            $q->where('status', $status);
        })
        ->when(request('member_id'), function ($q, $memberId) {
            $q->where('member_id', $memberId);
        });

        return DataTables::eloquent($memberships)
            ->editColumn('member', function ($data) {
                return $data->member->full_name . ' (' . $data->member->member_code . ')';
            })
            ->editColumn('plan', function ($data) {
                return $data->membershipPlan->name;
            })
            ->editColumn('price', function ($data) {
                return number_format($data->price, 0) . ' IQD';
            })
            ->editColumn('status', function ($data) {
                return ucfirst($data->status);
            })
            ->editColumn('start_date', function ($data) {
                return $data->start_date->format(DateTimeConstants::DISPLAY_DATE_FORMAT);
            })
            ->editColumn('end_date', function ($data) {
                return $data->end_date->format(DateTimeConstants::DISPLAY_DATE_FORMAT);
            })
            ->editColumn('created_at', function ($data) {
                return $data->created_at?->timezone(DateTimeConstants::TIMEZONE)
                    ->format(DateTimeConstants::DISPLAY_DATETIME_FORMAT);
            })
            ->only(['id', 'member', 'plan', 'start_date', 'end_date', 'price', 'status', 'created_at'])
            ->make(true);
    }

    /**
     * Display a listing of memberships
     */
    public function index()
    {
        if (!auth()->user()->can('view_memberships')) {
            abort(403, 'Unauthorized action.');
        }

        return view('memberships.index');
    }

    /**
     * Show the form for creating a new membership
     */
    public function create()
    {
        if (!auth()->user()->can('add_membership')) {
            abort(403, 'Unauthorized action.');
        }

        return view('memberships.create');
    }

    /**
     * Store a newly created membership with payment and invoice
     */
    public function store(StoreMembershipRequest $request)
    {
        $data = $request->validated();

        try {
            DB::beginTransaction();

            // Get member and plan
            $member = Member::findOrFail($data['member_id']);
            $plan = MembershipPlan::findOrFail($data['membership_plan_id']);

            // Calculate price based on gender
            $price = $member->gender->value === 'male' ? $plan->price_male : $plan->price_female;

            // Create membership
            $membership = Membership::create([
                'member_id' => $member->id,
                'membership_plan_id' => $plan->id,
                'start_date' => $data['start_date'],
                'end_date' => now()->parse($data['start_date'])->addMonths($plan->duration_months),
                'price' => $price,
                'status' => 'active',
            ]);

            // Create payment record
            $payment = Payment::create([
                'member_id' => $member->id,
                'membership_id' => $membership->id,
                'amount' => $price,
                'payment_method' => $data['payment_method'],
                'payment_type' => 'membership',
                'payment_date' => now(),
                'reference_number' => Payment::generateReferenceNumber(),
                'notes' => $data['notes'] ?? null,
            ]);

            // Create invoice
            $invoice = Invoice::create([
                'member_id' => $member->id,
                'payment_id' => $payment->id,
                'invoice_number' => Invoice::generateInvoiceNumber(),
                'total_amount' => $price,
                'issue_date' => now(),
            ]);

            DB::commit();

            return $this->success([
                'membership' => $membership,
                'payment' => $payment,
                'invoice' => $invoice,
            ], 'Membership created successfully', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->exception($e);
        }
    }

    /**
     * Display the specified membership
     */
    public function show($id)
    {
        if (!auth()->user()->can('view_memberships')) {
            abort(403, 'Unauthorized action.');
        }

        $membership = Membership::with([
            'member',
            'membershipPlan',
            'payments',
            'invoices'
        ])->findOrFail($id);

        return $this->success($membership, 'Membership retrieved successfully');
    }

    /**
     * Update the specified membership
     */
    public function update(UpdateMembershipRequest $request, $id)
    {
        $data = $request->validated();

        try {
            $membership = Membership::findOrFail($id);
            $membership->update($data);

            return $this->success($membership, 'Membership updated successfully');
        } catch (\Exception $e) {
            return $this->exception($e);
        }
    }

    /**
     * Cancel/expire membership
     */
    public function cancel($id)
    {
        if (!auth()->user()->can('edit_membership')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $membership = Membership::findOrFail($id);
            $membership->update(['status' => 'expired']);

            return $this->success($membership, 'Membership cancelled successfully');
        } catch (\Exception $e) {
            return $this->exception($e);
        }
    }

    /**
     * Renew membership
     */
    public function renew(Request $request, $id)
    {
        if (!auth()->user()->can('add_membership')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            DB::beginTransaction();

            $oldMembership = Membership::with('member', 'membershipPlan')->findOrFail($id);
            
            // Mark old membership as expired
            $oldMembership->update(['status' => 'expired']);

            // Create new membership
            $plan = $oldMembership->membershipPlan;
            $member = $oldMembership->member;
            $price = $member->gender->value === 'male' ? $plan->price_male : $plan->price_female;

            $newMembership = Membership::create([
                'member_id' => $member->id,
                'membership_plan_id' => $plan->id,
                'start_date' => now(),
                'end_date' => now()->addMonths($plan->duration_months),
                'price' => $price,
                'status' => 'active',
            ]);

            // Create payment and invoice if payment_method provided
            if ($request->has('payment_method')) {
                $payment = Payment::create([
                    'member_id' => $member->id,
                    'membership_id' => $newMembership->id,
                    'amount' => $price,
                    'payment_method' => $request->payment_method,
                    'payment_type' => 'membership',
                    'payment_date' => now(),
                    'reference_number' => Payment::generateReferenceNumber(),
                ]);

                $invoice = Invoice::create([
                    'member_id' => $member->id,
                    'payment_id' => $payment->id,
                    'invoice_number' => Invoice::generateInvoiceNumber(),
                    'total_amount' => $price,
                    'issue_date' => now(),
                ]);
            }

            DB::commit();

            return $this->success($newMembership, 'Membership renewed successfully', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->exception($e);
        }
    }
}
