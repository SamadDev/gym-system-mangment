<?php

namespace App\Http\Controllers;

use App\Constants\DateTimeConstants;
use App\Models\Invoice;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use DataTables;

class InvoiceController extends Controller
{
    use HttpResponses;

    /**
     * Get DataTables data for invoices listing
     */
    public function data(Request $request)
    {
        $invoices = Invoice::tableSelect()->tableRelation()->tableFilter();

        return DataTables::eloquent($invoices)
            ->editColumn('member_name', function ($data) {
                return $data->member ? $data->member->full_name : 'N/A';
            })
            ->editColumn('payment_reference', function ($data) {
                return $data->payment ? $data->payment->reference_number : 'N/A';
            })
            ->editColumn('total_amount', function ($data) {
                return number_format($data->total_amount, 2) . ' IQD';
            })
            ->editColumn('invoice_date', function ($data) {
                return $data->invoice_date?->format(DateTimeConstants::DISPLAY_DATE_FORMAT);
            })
            ->editColumn('created_at', function ($data) {
                return $data->created_at?->timezone(DateTimeConstants::TIMEZONE)
                    ->format(DateTimeConstants::DISPLAY_DATETIME_FORMAT);
            })
            ->only(['id', 'invoice_number', 'member_name', 'payment_reference', 'total_amount', 'invoice_date', 'created_at'])
            ->make(true);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!auth()->user()->can('view_invoices')) {
            abort(403, 'Unauthorized action.');
        }

        return view('invoices.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Invoice $invoice)
    {
        if (!auth()->user()->can('view_invoices')) {
            abort(403, 'Unauthorized action.');
        }

        $invoice->load(['member', 'payment.membership.membershipPlan']);

        return $this->success($invoice, 'Invoice retrieved successfully');
    }

    /**
     * Print invoice
     */
    public function print(Invoice $invoice)
    {
        if (!auth()->user()->can('view_invoices')) {
            abort(403, 'Unauthorized action.');
        }

        $invoice->load(['member', 'payment.membership.membershipPlan']);

        return view('invoices.print', compact('invoice'));
    }

    /**
     * Get invoice statistics
     */
    public function stats(Request $request)
    {
        try {
            $startDate = $request->input('start_date', now()->startOfMonth());
            $endDate = $request->input('end_date', now()->endOfMonth());

            $stats = [
                'total_invoices' => Invoice::whereBetween('invoice_date', [$startDate, $endDate])->count(),
                'total_amount' => Invoice::whereBetween('invoice_date', [$startDate, $endDate])->sum('total_amount'),
                'invoices_by_month' => Invoice::selectRaw('DATE_FORMAT(invoice_date, "%Y-%m") as month, COUNT(*) as count, SUM(total_amount) as total')
                    ->whereBetween('invoice_date', [$startDate, $endDate])
                    ->groupBy('month')
                    ->orderBy('month')
                    ->get(),
                'recent_invoices' => Invoice::with(['member', 'payment'])
                    ->whereBetween('invoice_date', [$startDate, $endDate])
                    ->latest()
                    ->limit(10)
                    ->get(),
            ];

            return $this->success($stats, 'Invoice statistics retrieved successfully');
        } catch (\Exception $e) {
            return $this->exception($e);
        }
    }
}
