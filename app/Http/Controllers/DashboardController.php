<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Membership;
use App\Models\Payment;
use App\Models\Expense;
use App\Models\Attendance;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    use HttpResponses;

    /**
     * Display the dashboard
     */
    public function index()
    {
        return view('app');
    }

    /**
     * Get dashboard statistics
     */
    public function data(Request $request)
    {
        // if (!auth()->user()->can('view_dashboard')) {
        //     abort(403, 'Unauthorized action.');
        // }

        $cacheTtlSeconds = 60; // Short-lived cache for dashboard

        $today = now()->startOfDay();
        $thisWeek = now()->startOfWeek();
        $thisMonth = now()->startOfMonth();
        $lastMonth = now()->copy()->subMonth()->startOfMonth();

        $stats = Cache::remember('dashboard:gym_stats', $cacheTtlSeconds, function () use ($today, $thisWeek, $thisMonth, $lastMonth) {
            return [
                // Members
                'total_members' => Member::count(),
                'members_today' => Member::where('created_at', '>=', $today)->count(),
                'members_this_week' => Member::where('created_at', '>=', $thisWeek)->count(),
                'members_this_month' => Member::where('created_at', '>=', $thisMonth)->count(),
                'members_growth' => $this->calculateGrowth(
                    Member::where('created_at', '>=', $thisMonth)->count(),
                    Member::whereBetween('created_at', [$lastMonth, $thisMonth])->count()
                ),
                'active_members' => Member::active()->count(),

                // Memberships
                'total_memberships' => Membership::count(),
                'active_memberships' => Membership::where('status', 'active')
                    ->where('end_date', '>=', now())
                    ->count(),
                'expiring_soon' => Membership::where('status', 'active')
                    ->whereBetween('end_date', [now(), now()->addDays(7)])
                    ->count(),
                'expired_this_week' => Membership::whereBetween('end_date', [now()->subDays(7), now()])
                    ->count(),

                // Revenue (Subscription payments)
                'total_revenue' => Payment::where('payment_type', 'membership')->sum('amount'),
                'revenue_today' => Payment::where('payment_type', 'membership')
                    ->where('created_at', '>=', $today)->sum('amount'),
                'revenue_this_week' => Payment::where('payment_type', 'membership')
                    ->where('created_at', '>=', $thisWeek)->sum('amount'),
                'revenue_this_month' => Payment::where('payment_type', 'membership')
                    ->where('created_at', '>=', $thisMonth)->sum('amount'),
                'revenue_growth' => $this->calculateGrowth(
                    Payment::where('payment_type', 'membership')
                        ->where('created_at', '>=', $thisMonth)->sum('amount'),
                    Payment::where('payment_type', 'membership')
                        ->whereBetween('created_at', [$lastMonth, $thisMonth])->sum('amount')
                ),

                // Market Sales
                'market_sales_total' => Payment::where('payment_type', 'market_sale')->sum('amount'),
                'market_sales_today' => Payment::where('payment_type', 'market_sale')
                    ->where('created_at', '>=', $today)->sum('amount'),
                'market_sales_this_month' => Payment::where('payment_type', 'market_sale')
                    ->where('created_at', '>=', $thisMonth)->sum('amount'),

                // Expenses
                'total_expenses' => Expense::sum('amount'),
                'expenses_today' => Expense::where('created_at', '>=', $today)->sum('amount'),
                'expenses_this_month' => Expense::where('created_at', '>=', $thisMonth)->sum('amount'),
                'expenses_growth' => $this->calculateGrowth(
                    Expense::where('created_at', '>=', $thisMonth)->sum('amount'),
                    Expense::whereBetween('created_at', [$lastMonth, $thisMonth])->sum('amount')
                ),

                // Attendance
                'attendance_today' => Attendance::where('check_in_time', '>=', $today)->count(),
                'attendance_this_week' => Attendance::where('check_in_time', '>=', $thisWeek)->count(),
                'attendance_this_month' => Attendance::where('check_in_time', '>=', $thisMonth)->count(),
            ];
        });

        // Recent members
        $recentMembers = Cache::remember('dashboard:recent_members', $cacheTtlSeconds, function () {
            return Member::select('id', 'member_code', 'first_name', 'last_name', 'phone', 'status', 'created_at')
                ->with('currentMembership.membershipPlan:id,name')
                ->latest()
                ->limit(5)
                ->get()
                ->map(function ($member) {
                    return [
                        'id' => $member->id,
                        'member_code' => $member->member_code,
                        'full_name' => $member->full_name,
                        'phone' => $member->phone,
                        'status' => $member->status->label(),
                        'membership' => $member->currentMembership?->membershipPlan->name ?? 'No Active Membership',
                        'created_at' => $member->created_at->format('d/m/Y H:i'),
                    ];
                });
        });

        // Expiring memberships
        $expiringMemberships = Membership::where('status', 'active')
            ->whereBetween('end_date', [now(), now()->addDays(7)])
            ->with('member:id,first_name,last_name,phone', 'membershipPlan:id,name')
            ->orderBy('end_date', 'asc')
            ->limit(10)
            ->get()
            ->map(function ($membership) {
                return [
                    'id' => $membership->id,
                    'member_name' => $membership->member->full_name,
                    'member_phone' => $membership->member->phone,
                    'plan_name' => $membership->membershipPlan->name,
                    'end_date' => $membership->end_date->format('d/m/Y'),
                    'days_left' => now()->diffInDays($membership->end_date),
                ];
            });

        return $this->success([
            'stats' => $stats,
            'recent_members' => $recentMembers,
            'expiring_memberships' => $expiringMemberships,
        ], 'Dashboard data retrieved successfully');
    }

    /**
     * Calculate growth percentage
     */
    private function calculateGrowth($current, $previous): float
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }
        
        return round((($current - $previous) / $previous) * 100, 2);
    }
}
