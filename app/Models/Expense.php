<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'expense_category_id',
        'amount',
        'expense_date',
        'description',
        'payment_method',
        'receipt_image',
        'approved_by',
        'created_by',
        'status',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'amount' => 'decimal:2',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    public function category()
    {
        return $this->belongsTo(\Illuminate\Database\Eloquent\Model::class, 'expense_category_id');
    }

    // Scopes
    public function scopeCategory($query, $category)
    {
        if (empty($category)) {
            return $query;
        }

        return $query->where('category', $category);
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('expense_date', [$startDate, $endDate]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('expense_date', now()->month)
            ->whereYear('expense_date', now()->year);
    }

    public function scopeThisYear($query)
    {
        return $query->whereYear('expense_date', now()->year);
    }

    public function scopeSearch($query, $search)
    {
        if (empty($search)) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('expense_code', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%")
              ->orWhere('category', 'like', "%{$search}%")
              ->orWhere('reference_number', 'like', "%{$search}%");
        });
    }

    /**
     * For DataTables: Select columns for table view
     */
    public function scopeTableSelect($query)
    {
        return $query->select([
            'id',
            'expense_code',
            'category',
            'description',
            'amount',
            'expense_date',
            'payment_method',
            'reference_number',
            'created_by',
            'attachment',
            'created_at',
        ]);
    }

    /**
     * For DataTables: Load relationships
     */
    public function scopeTableRelation($query)
    {
        return $query->with(['user:id,name,email']);
    }

    /**
     * For DataTables: Apply filters
     */
    public function scopeTableFilter($query)
    {
        if (request()->has('category') && request('category') !== '') {
            $query->where('category', request('category'));
        }

        if (request()->has('payment_method') && request('payment_method') !== '') {
            $query->where('payment_method', request('payment_method'));
        }

        if (request()->has('date_from') && request('date_from') !== '') {
            $query->whereDate('expense_date', '>=', request('date_from'));
        }

        if (request()->has('date_to') && request('date_to') !== '') {
            $query->whereDate('expense_date', '<=', request('date_to'));
        }

        if (request()->has('amount_min') && request('amount_min') !== '') {
            $query->where('amount', '>=', request('amount_min'));
        }

        if (request()->has('amount_max') && request('amount_max') !== '') {
            $query->where('amount', '<=', request('amount_max'));
        }

        return $query;
    }

    // Accessors
    public function getFormattedAmountAttribute(): string
    {
        return number_format((float) $this->amount, 2) . ' IQD';
    }
}
