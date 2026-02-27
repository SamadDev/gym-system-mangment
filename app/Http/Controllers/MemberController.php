<?php

namespace App\Http\Controllers;

use App\Constants\DateTimeConstants;
use App\Http\Requests\StoreMemberRequest;
use App\Http\Requests\UpdateMemberRequest;
use App\Models\Member;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MemberController extends Controller
{
    use HttpResponses;

    /**
     * Get DataTables data for members listing
     */
    public function data(Request $request)
    {
        $members = Member::tableSelect()
            ->tableRelation()
            ->tableFilter();

        $search = $request->input('search.value');

        if ($search) {
            $members->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('member_code', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        return DataTables::eloquent($members)
            ->skipTotalRecords()
            ->filterColumn('full_name', function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('first_name', 'like', "%{$keyword}%")
                      ->orWhere('last_name', 'like', "%{$keyword}%");
                });
            })
            ->addColumn('full_name', function ($data) {
                return $data->full_name;
            })
            ->editColumn('gender', function ($data) {
                return $data->gender->value;
            })
            ->editColumn('status', function ($data) {
                return $data->status->value;
            })
            ->addColumn('membership', function ($data) {
                if ($data->currentMembership) {
                    return $data->currentMembership->membershipPlan->name;
                }
                return 'No Active Membership';
            })
            ->editColumn('photo', function ($data) {
                return $data->photo ? asset('storage/' . $data->photo) : null;
            })
            ->editColumn('created_at', function ($data) {
                return $data->created_at?->timezone(DateTimeConstants::TIMEZONE)
                    ->format(DateTimeConstants::DISPLAY_DATETIME_FORMAT);
            })
            ->rawColumns(['full_name', 'membership'])
            ->make(true);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!auth()->user()->can('members.view')) {
            abort(403, 'Unauthorized action.');
        }

        return view('app');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!auth()->user()->can('add_member')) {
            abort(403, 'Unauthorized action.');
        }

        return view('members.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMemberRequest $request)
    {
        $data = $request->validated();

        try {
            DB::beginTransaction();

            // Handle photo upload
            if ($request->hasFile('photo')) {
                $data['photo'] = $request->file('photo')->store('members/photos', 'public');
            }

            $member = Member::create($data);

            DB::commit();

            return $this->success($member, 'Member created successfully', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->exception($e);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id, Request $request)
    {
        if (!auth()->user()->can('view_members')) {
            abort(403, 'Unauthorized action.');
        }

        $member = Member::select([
            'id', 'member_code', 'first_name', 'last_name', 'phone', 'email',
            'address', 'date_of_birth', 'gender', 'photo', 'emergency_contact',
            'emergency_phone', 'blood_type', 'status', 'created_at'
        ])
            ->with([
                'currentMembership.membershipPlan',
                'memberships' => function ($q) {
                    $q->latest()->limit(5);
                },
                'attendance' => function ($q) {
                    $q->latest()->limit(10);
                },
                'payments' => function ($q) {
                    $q->latest()->limit(10);
                }
            ])
            ->where('id', $id)
            ->firstOrFail();

        return $this->success($member, 'Member retrieved successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        if (!auth()->user()->can('edit_member')) {
            abort(403, 'Unauthorized action.');
        }

        $member = Member::findOrFail($id);
        return view('members.edit', compact('member'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMemberRequest $request, $id)
    {
        $data = $request->validated();

        try {
            DB::beginTransaction();

            $member = Member::findOrFail($id);

            // Handle photo upload
            if ($request->hasFile('photo')) {
                // Delete old photo
                if ($member->photo) {
                    Storage::disk('public')->delete($member->photo);
                }
                $data['photo'] = $request->file('photo')->store('members/photos', 'public');
            }

            $member->update($data);

            DB::commit();

            return $this->success($member, 'Member updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->exception($e);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        if (!auth()->user()->can('delete_member')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $member = Member::findOrFail($id);
            
            // Check if member has active memberships
            if ($member->hasActiveMembership()) {
                return $this->error(null, 'Cannot delete member with active membership', 400);
            }

            // Delete photo
            if ($member->photo) {
                Storage::disk('public')->delete($member->photo);
            }

            $member->delete();

            return $this->success(null, 'Member deleted successfully');
        } catch (\Exception $e) {
            return $this->exception($e);
        }
    }

    /**
     * Get member statistics
     */
    public function stats()
    {
        if (!auth()->user()->can('view_members')) {
            abort(403, 'Unauthorized action.');
        }

        $stats = [
            'total_members' => Member::count(),
            'active_members' => Member::active()->count(),
            'members_with_active_membership' => Member::whereHas('currentMembership')->count(),
            'members_expiring_soon' => Member::expiringSoon(7)->count(),
        ];

        return $this->success($stats, 'Member statistics retrieved successfully');
    }
}
