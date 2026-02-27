<?php

namespace App\Http\Controllers;

use App\Constants\DateTimeConstants;
use App\Http\Requests\StorePaymentRequest;
use App\Models\Payment;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use DataTables;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    use HttpResponses;

    /**
     * Get DataTables data for payments listing
     */
    public function data(Request $request)
    {
        $payments = Payment::tableSelect()->tableRelation()->tableFilter();

        return DataTables::eloquent($payments)
            ->editColumn('member_name', function ($data) {
                return $data->member ? $data->member->full_name : 'N/A';
            })
            ->editColumn('payment_type', function ($data) {
                return $data->payment_type->label();
            })
            ->editColumn('payment_method', function ($data) {
                return $data->payment_method->label();
            })
            ->editColumn('amount', function ($data) {
                return number_format($data->amount, 2) . ' IQD';
            })
            ->editColumn('payment_date', function ($data) {
                return $data->payment_date?->format(DateTimeConstants::DISPLAY_DATE_FORMAT);
            })
            ->editColumn('created_at', function ($data) {
                return $data->created_at?->timezone(DateTimeConstants::TIMEZONE)
                    ->format(DateTimeConstants::DISPLAY_DATETIME_FORMAT);
            })
            ->only(['id', 'reference_number', 'member_name', 'payment_type', 'payment_method', 'amount', 'payment_date', 'created_at'])
            ->make(true);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!auth()->user()->can('view_payments')) {
            abort(403, 'Unauthorized action.');
        }

        return view('app');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePaymentRequest $request)
    {
        try {
            DB::beginTransaction();

            $payment = Payment::create($request->validated());

            DB::commit();

            return $this->success($payment, 'Payment created successfully', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->exception($e);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Payment $payment)
    {
        if (!auth()->user()->can('view_payments')) {
            abort(403, 'Unauthorized action.');
        }

        $payment->load(['member', 'membership.membershipPlan', 'invoice']);

        return $this->success($payment, 'Payment retrieved successfully');
    }

    /**
     * Get payment statistics
     */
    public function stats(Request $request)
    {
        try {
            $startDate = $request->input('start_date', now()->startOfMonth());
            $endDate = $request->input('end_date', now()->endOfMonth());

            $stats = [
                'total_payments' => Payment::whereBetween('payment_date', [$startDate, $endDate])->count(),
                'total_amount' => Payment::whereBetween('payment_date', [$startDate, $endDate])->sum('amount'),
                'by_method' => Payment::whereBetween('payment_date', [$startDate, $endDate])
                    ->selectRaw('payment_method, COUNT(*) as count, SUM(amount) as total')
                    ->groupBy('payment_method')
                    ->get(),
                'by_type' => Payment::whereBetween('payment_date', [$startDate, $endDate])
                    ->selectRaw('payment_type, COUNT(*) as count, SUM(amount) as total')
                    ->groupBy('payment_type')
                    ->get(),
                'recent_payments' => Payment::with(['member', 'membership'])
                    ->whereBetween('payment_date', [$startDate, $endDate])
                    ->latest()
                    ->limit(10)
                    ->get(),
            ];

            return $this->success($stats, 'Payment statistics retrieved successfully');
        } catch (\Exception $e) {
            return $this->exception($e);
        }
    }
}
