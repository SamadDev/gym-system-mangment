<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\PaymentType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'membership_id',
        'amount',
        'payment_method',
        'payment_type',
        'payment_date',
        'reference_number',
        'notes',
    ];

    protected $casts = [
        'payment_date' => 'datetime',
        'payment_method' => PaymentMethod::class,
        'payment_type' => PaymentType::class,
        'amount' => 'decimal:2',
    ];

    /**
     * Relationship: Payment belongs to Member
     */
    public function member()
    {
        return $this->belongsTo(Member::class)->select('id', 'member_code', 'first_name', 'last_name', 'phone');
    }

    /**
     * Relationship: Payment may belong to Membership
     */
    public function membership()
    {
        return $this->belongsTo(Membership::class);
    }

    /**
     * Relationship: Payment has one Invoice
     */
    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    /**
     * Generate unique reference number: PAY-YYYY-MM-####
     */
    public static function generateReferenceNumber(): string
    {
        $year = date('Y');
        $month = date('m');
        $lastPayment = self::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderBy('id', 'desc')
            ->first();
        
        $number = $lastPayment ? ((int)substr($lastPayment->reference_number, -4)) + 1 : 1;
        
        return 'PAY-' . $year . '-' . $month . '-' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    /**
     * For DataTables: Select columns
     */
    public static function scopeTableSelect($query)
    {
        return $query->select([
            'id',
            'member_id',
            'membership_id',
            'amount',
            'payment_method',
            'payment_type',
            'payment_date',
            'reference_number',
            'created_at',
        ]);
    }

    /**
     * For DataTables: Load relationships
     */
    public static function scopeTableRelation($query)
    {
        return $query->with(['member:id,member_code,first_name,last_name', 'membership.membershipPlan:id,name']);
    }

    /**
     * For DataTables: Apply filters
     */
    public static function scopeTableFilter($query)
    {
        if (request()->has('payment_type') && request('payment_type') !== '') {
            $query->where('payment_type', request('payment_type'));
        }

        if (request()->has('payment_method') && request('payment_method') !== '') {
            $query->where('payment_method', request('payment_method'));
        }

        if (request()->has('date_from')) {
            $query->whereDate('payment_date', '>=', request('date_from'));
        }

        if (request()->has('date_to')) {
            $query->whereDate('payment_date', '<=', request('date_to'));
        }

        return $query;
    }

    /**
     * Auto-generate reference number on creation
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($payment) {
            if (!$payment->reference_number) {
                $payment->reference_number = self::generateReferenceNumber();
            }
        });
    }
}

