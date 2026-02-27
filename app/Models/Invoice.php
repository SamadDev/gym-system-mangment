<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'payment_id',
        'invoice_number',
        'total_amount',
        'issue_date',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'total_amount' => 'decimal:2',
    ];

    /**
     * Relationship: Invoice belongs to Member
     */
    public function member()
    {
        return $this->belongsTo(Member::class)->select('id', 'member_code', 'first_name', 'last_name', 'phone', 'email');
    }

    /**
     * Relationship: Invoice belongs to Payment
     */
    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    /**
     * Generate unique invoice number: INV-YYYY-####
     */
    public static function generateInvoiceNumber(): string
    {
        $year = date('Y');
        $lastInvoice = self::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();
        
        $number = $lastInvoice ? ((int)substr($lastInvoice->invoice_number, -4)) + 1 : 1;
        
        return 'INV-' . $year . '-' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    /**
     * For DataTables: Select columns
     */
    public static function scopeTableSelect($query)
    {
        return $query->select([
            'id',
            'member_id',
            'payment_id',
            'invoice_number',
            'total_amount',
            'issue_date',
            'created_at',
        ]);
    }

    /**
     * For DataTables: Load relationships
     */
    public static function scopeTableRelation($query)
    {
        return $query->with(['member:id,member_code,first_name,last_name', 'payment:id,payment_type,payment_method']);
    }

    /**
     * Auto-generate invoice number on creation
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($invoice) {
            if (!$invoice->invoice_number) {
                $invoice->invoice_number = self::generateInvoiceNumber();
            }
        });
    }
}

