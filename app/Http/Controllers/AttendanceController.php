<?php

namespace App\Http\Controllers;

use App\Constants\DateTimeConstants;
use App\Models\Attendance;
use App\Models\Member;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use DataTables;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    use HttpResponses;

    /**
     * Get DataTables data for attendance listing
     */
    public function data(Request $request)
    {
        $attendance = Attendance::select([
            'id', 'member_id', 'check_in_time', 'check_out_time', 'created_at'
        ])
        ->with('member:id,member_code,first_name,last_name')
        ->when(request('member_id'), function ($q, $memberId) {
            $q->where('member_id', $memberId);
        })
        ->when(request('date_from'), function ($q, $dateFrom) {
            $q->whereDate('check_in_time', '>=', $dateFrom);
        })
        ->when(request('date_to'), function ($q, $dateTo) {
            $q->whereDate('check_in_time', '<=', $dateTo);
        });

        return DataTables::eloquent($attendance)
            ->editColumn('member', function ($data) {
                return $data->member->full_name . ' (' . $data->member->member_code . ')';
            })
            ->editColumn('check_in_time', function ($data) {
                return $data->check_in_time->timezone(DateTimeConstants::TIMEZONE)
                    ->format(DateTimeConstants::DISPLAY_DATETIME_FORMAT);
            })
            ->editColumn('check_out_time', function ($data) {
                return $data->check_out_time 
                    ? $data->check_out_time->timezone(DateTimeConstants::TIMEZONE)
                        ->format(DateTimeConstants::DISPLAY_DATETIME_FORMAT)
                    : 'Still in gym';
            })
            ->addColumn('duration', function ($data) {
                if (!$data->check_out_time) {
                    return 'In progress';
                }
                $minutes = $data->check_in_time->diffInMinutes($data->check_out_time);
                $hours = floor($minutes / 60);
                $mins = $minutes % 60;
                return $hours . 'h ' . $mins . 'm';
            })
            ->only(['id', 'member', 'check_in_time', 'check_out_time', 'duration'])
            ->make(true);
    }

    /**
     * Display a listing of attendance records
     */
    public function index()
    {
        if (!auth()->user()->can('view_attendance')) {
            abort(403, 'Unauthorized action.');
        }

        return view('app');
    }

    /**
     * Check-in a member
     */
    public function checkIn(Request $request)
    {
        if (!auth()->user()->can('add_attendance')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'member_id' => 'required|exists:members,id',
        ]);

        try {
            $member = Member::with('currentMembership')->findOrFail($request->member_id);

            // Check if member has active membership
            if (!$member->hasActiveMembership()) {
                return $this->error(null, 'Member does not have an active membership. Please renew membership first.', 403);
            }

            // Check if member is already checked in
            $existingCheckIn = Attendance::where('member_id', $member->id)
                ->whereNull('check_out_time')
                ->first();

            if ($existingCheckIn) {
                return $this->error(null, 'Member is already checked in.', 400);
            }

            // Create attendance record
            $attendance = Attendance::create([
                'member_id' => $member->id,
                'check_in_time' => now(),
            ]);

            return $this->success($attendance, 'Member checked in successfully', 201);
        } catch (\Exception $e) {
            return $this->exception($e);
        }
    }

    /**
     * Check-out a member
     */
    public function checkOut(Request $request)
    {
        if (!auth()->user()->can('edit_attendance')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'member_id' => 'required|exists:members,id',
        ]);

        try {
            $attendance = Attendance::where('member_id', $request->member_id)
                ->whereNull('check_out_time')
                ->firstOrFail();

            $attendance->update([
                'check_out_time' => now(),
            ]);

            return $this->success($attendance, 'Member checked out successfully');
        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                return $this->error(null, 'No active check-in found for this member.', 404);
            }
            return $this->exception($e);
        }
    }

    /**
     * Get member's attendance history
     */
    public function memberHistory($memberId)
    {
        if (!auth()->user()->can('view_attendance')) {
            abort(403, 'Unauthorized action.');
        }

        $attendance = Attendance::where('member_id', $memberId)
            ->orderBy('check_in_time', 'desc')
            ->limit(50)
            ->get();

        return $this->success($attendance, 'Attendance history retrieved successfully');
    }

    /**
     * Get today's attendance stats
     */
    public function todayStats()
    {
        if (!auth()->user()->can('view_attendance')) {
            abort(403, 'Unauthorized action.');
        }

        $today = now()->startOfDay();

        $stats = [
            'total_today' => Attendance::where('check_in_time', '>=', $today)->count(),
            'currently_in_gym' => Attendance::whereNull('check_out_time')
                ->where('check_in_time', '>=', $today)->count(),
            'checked_out' => Attendance::whereNotNull('check_out_time')
                ->where('check_in_time', '>=', $today)->count(),
            'peak_hour' => $this->getPeakHour($today),
        ];

        return $this->success($stats, 'Today\'s attendance stats retrieved successfully');
    }

    /**
     * Get peak hour for attendance
     */
    private function getPeakHour($date)
    {
        $peakData = Attendance::select(DB::raw('HOUR(check_in_time) as hour, COUNT(*) as count'))
            ->where('check_in_time', '>=', $date)
            ->groupBy('hour')
            ->orderBy('count', 'desc')
            ->first();

        return $peakData ? $peakData->hour . ':00' : null;
    }
}
