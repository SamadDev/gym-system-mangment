<?php

namespace App\Http\Controllers;

use App\Constants\DateTimeConstants;
use App\Http\Requests\StoreExpenseRequest;
use App\Http\Requests\UpdateExpenseRequest;
use App\Models\Expense;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ExpenseController extends Controller
{
    use HttpResponses;

    /**
     * Get DataTables data for expenses listing
     */
    public function data(Request $request)
    {
        $expenses = Expense::tableSelect()->tableRelation()->tableFilter();

        return DataTables::eloquent($expenses)
            ->editColumn('user_name', function ($data) {
                return $data->user ? $data->user->name : 'N/A';
            })
            ->editColumn('amount', function ($data) {
                return number_format($data->amount, 2) . ' IQD';
            })
            ->editColumn('expense_date', function ($data) {
                return $data->expense_date?->format(DateTimeConstants::DISPLAY_DATE_FORMAT);
            })
            ->editColumn('created_at', function ($data) {
                return $data->created_at?->timezone(DateTimeConstants::TIMEZONE)
                    ->format(DateTimeConstants::DISPLAY_DATETIME_FORMAT);
            })
            ->only(['id', 'expense_code', 'category', 'description', 'amount', 'expense_date', 'payment_method', 'user_name', 'created_at'])
            ->make(true);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!auth()->user()->can('view_expenses')) {
            abort(403, 'Unauthorized action.');
        }

        return view('app');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreExpenseRequest $request)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();
            $data['created_by'] = auth()->id();

            // Handle attachment upload
            if ($request->hasFile('attachment')) {
                $data['attachment'] = $request->file('attachment')->store('expenses/attachments', 'public');
            }

            $expense = Expense::create($data);

            DB::commit();

            return $this->success($expense, 'Expense created successfully', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->exception($e);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Expense $expense)
    {
        if (!auth()->user()->can('view_expenses')) {
            abort(403, 'Unauthorized action.');
        }

        $expense->load('user');

        return $this->success($expense, 'Expense retrieved successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateExpenseRequest $request, Expense $expense)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();

            // Handle attachment upload
            if ($request->hasFile('attachment')) {
                // Delete old attachment
                if ($expense->attachment) {
                    Storage::disk('public')->delete($expense->attachment);
                }
                $data['attachment'] = $request->file('attachment')->store('expenses/attachments', 'public');
            }

            $expense->update($data);

            DB::commit();

            return $this->success($expense, 'Expense updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->exception($e);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Expense $expense)
    {
        if (!auth()->user()->can('delete_expense')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            DB::beginTransaction();

            // Delete attachment if exists
            if ($expense->attachment) {
                Storage::disk('public')->delete($expense->attachment);
            }

            $expense->delete();

            DB::commit();

            return $this->success(null, 'Expense deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->exception($e);
        }
    }

    /**
     * Get expense statistics
     */
    public function stats(Request $request)
    {
        try {
            $startDate = $request->input('start_date', now()->startOfMonth());
            $endDate = $request->input('end_date', now()->endOfMonth());

            $stats = [
                'total_expenses' => Expense::whereBetween('expense_date', [$startDate, $endDate])->count(),
                'total_amount' => Expense::whereBetween('expense_date', [$startDate, $endDate])->sum('amount'),
                'by_category' => Expense::whereBetween('expense_date', [$startDate, $endDate])
                    ->selectRaw('category, COUNT(*) as count, SUM(amount) as total')
                    ->groupBy('category')
                    ->get(),
                'by_payment_method' => Expense::whereBetween('expense_date', [$startDate, $endDate])
                    ->selectRaw('payment_method, COUNT(*) as count, SUM(amount) as total')
                    ->groupBy('payment_method')
                    ->get(),
                'monthly_trend' => Expense::selectRaw('DATE_FORMAT(expense_date, "%Y-%m") as month, SUM(amount) as total')
                    ->whereBetween('expense_date', [$startDate, $endDate])
                    ->groupBy('month')
                    ->orderBy('month')
                    ->get(),
                'recent_expenses' => Expense::with('user')
                    ->whereBetween('expense_date', [$startDate, $endDate])
                    ->latest()
                    ->limit(10)
                    ->get(),
            ];

            return $this->success($stats, 'Expense statistics retrieved successfully');
        } catch (\Exception $e) {
            return $this->exception($e);
        }
    }
}
