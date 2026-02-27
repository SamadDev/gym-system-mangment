<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Payment;
use App\Models\Expense;
use App\Models\Attendance;
use App\Models\Membership;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    use HttpResponses;

    /**
     * Get revenue report
     */
    public function revenue(Request $request)
    {
        try {
            $startDate = $request->input('start_date', Carbon::now()->startOfMonth());
            $endDate = $request->input('end_date', Carbon::now()->endOfMonth());

            // Total revenue from payments
            $totalRevenue = Payment::whereBetween('payment_date', [$startDate, $endDate])
                ->sum('amount');

            // Total expenses
            $totalExpenses = Expense::whereBetween('expense_date', [$startDate, $endDate])
                ->sum('amount');

            // Net profit
            $netProfit = $totalRevenue - $totalExpenses;

            // Revenue by payment type
            $revenueByType = Payment::whereBetween('payment_date', [$startDate, $endDate])
                ->select('payment_type', DB::raw('SUM(amount) as total'))
                ->groupBy('payment_type')
                ->get();

            // Revenue by payment method
            $revenueByMethod = Payment::whereBetween('payment_date', [$startDate, $endDate])
                ->select('payment_method', DB::raw('SUM(amount) as total'))
                ->groupBy('payment_method')
                ->get();

            // Daily revenue trend
            $dailyRevenue = Payment::whereBetween('payment_date', [$startDate, $endDate])
                ->select(DB::raw('DATE(payment_date) as date'), DB::raw('SUM(amount) as total'))
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            return $this->success([
                'summary' => [
                    'total_revenue' => $totalRevenue,
                    'total_expenses' => $totalExpenses,
                    'net_profit' => $netProfit,
                ],
                'revenue_by_type' => $revenueByType,
                'revenue_by_method' => $revenueByMethod,
                'daily_revenue' => $dailyRevenue,
            ]);
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), 500);
        }
    }

    /**
     * Get members report
     */
    public function members(Request $request)
    {
        try {
            // Total members
            $totalMembers = Member::count();
            $activeMembers = Member::whereHas('memberships', function($q) {
                $q->where('status', 'active');
            })->count();
            $inactiveMembers = $totalMembers - $activeMembers;

            // New members this month
            $newMembersThisMonth = Member::whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year)
                ->count();

            // Members by gender
            $membersByGender = Member::select('gender', DB::raw('COUNT(*) as count'))
                ->groupBy('gender')
                ->get();

            // Members by blood type
            $membersByBloodType = Member::select('blood_type', DB::raw('COUNT(*) as count'))
                ->whereNotNull('blood_type')
                ->groupBy('blood_type')
                ->get();

            // Monthly registration trend
            $registrationTrend = Member::select(
                    DB::raw('YEAR(created_at) as year'),
                    DB::raw('MONTH(created_at) as month'),
                    DB::raw('COUNT(*) as count')
                )
                ->where('created_at', '>=', Carbon::now()->subMonths(12))
                ->groupBy('year', 'month')
                ->orderBy('year')
                ->orderBy('month')
                ->get();

            return $this->success([
                'summary' => [
                    'total_members' => $totalMembers,
                    'active_members' => $activeMembers,
                    'inactive_members' => $inactiveMembers,
                    'new_members_this_month' => $newMembersThisMonth,
                ],
                'members_by_gender' => $membersByGender,
                'members_by_blood_type' => $membersByBloodType,
                'registration_trend' => $registrationTrend,
            ]);
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), 500);
        }
    }

    /**
     * Get attendance report
     */
    public function attendance(Request $request)
    {
        try {
            $startDate = $request->input('start_date', Carbon::now()->startOfMonth());
            $endDate = $request->input('end_date', Carbon::now()->endOfMonth());

            // Total check-ins
            $totalCheckIns = Attendance::whereBetween('check_in_time', [$startDate, $endDate])
                ->count();

            // Average daily attendance
            $days = Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1;
            $avgDailyAttendance = round($totalCheckIns / $days, 2);

            // Peak hours
            $peakHours = Attendance::whereBetween('check_in_time', [$startDate, $endDate])
                ->select(DB::raw('HOUR(check_in_time) as hour'), DB::raw('COUNT(*) as count'))
                ->groupBy('hour')
                ->orderBy('count', 'desc')
                ->limit(5)
                ->get();

            // Daily attendance trend
            $dailyAttendance = Attendance::whereBetween('check_in_time', [$startDate, $endDate])
                ->select(DB::raw('DATE(check_in_time) as date'), DB::raw('COUNT(*) as count'))
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            // Top attending members
            $topMembers = Attendance::whereBetween('check_in_time', [$startDate, $endDate])
                ->select('member_id', DB::raw('COUNT(*) as visits'))
                ->with('member:id,member_code,first_name,last_name')
                ->groupBy('member_id')
                ->orderBy('visits', 'desc')
                ->limit(10)
                ->get();

            return $this->success([
                'summary' => [
                    'total_check_ins' => $totalCheckIns,
                    'average_daily_attendance' => $avgDailyAttendance,
                ],
                'peak_hours' => $peakHours,
                'daily_attendance' => $dailyAttendance,
                'top_members' => $topMembers,
            ]);
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), 500);
        }
    }

    /**
     * Get expenses report
     */
    public function expenses(Request $request)
    {
        try {
            $startDate = $request->input('start_date', Carbon::now()->startOfMonth());
            $endDate = $request->input('end_date', Carbon::now()->endOfMonth());

            // Total expenses
            $totalExpenses = Expense::whereBetween('expense_date', [$startDate, $endDate])
                ->sum('amount');

            // Expenses by category
            $expensesByCategory = Expense::whereBetween('expense_date', [$startDate, $endDate])
                ->select('category', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
                ->groupBy('category')
                ->get();

            // Daily expenses trend
            $dailyExpenses = Expense::whereBetween('expense_date', [$startDate, $endDate])
                ->select(DB::raw('DATE(expense_date) as date'), DB::raw('SUM(amount) as total'))
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            // Recent expenses
            $recentExpenses = Expense::whereBetween('expense_date', [$startDate, $endDate])
                ->orderBy('expense_date', 'desc')
                ->limit(20)
                ->get();

            return $this->success([
                'summary' => [
                    'total_expenses' => $totalExpenses,
                ],
                'expenses_by_category' => $expensesByCategory,
                'daily_expenses' => $dailyExpenses,
                'recent_expenses' => $recentExpenses,
            ]);
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), 500);
        }
    }
}
