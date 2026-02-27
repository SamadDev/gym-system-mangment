<?php

namespace App\Models;

use App\Enums\MembershipStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Membership extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'membership_plan_id',
        'start_date',
        'end_date',
        'amount_paid',
        'status',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'status' => MembershipStatus::class,
        'amount_paid' => 'decimal:2',
    ];

    // Relationships
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function membershipPlan(): BelongsTo
    {
        return $this->belongsTo(MembershipPlan::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', MembershipStatus::ACTIVE)
            ->where('end_date', '>=', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('status', MembershipStatus::EXPIRED)
            ->orWhere('end_date', '<', now());
    }

    public function scopeExpiringSoon($query, $days = 7)
    {
        return $query->where('status', MembershipStatus::ACTIVE)
            ->whereBetween('end_date', [now(), now()->addDays($days)]);
    }

    public function scopeSearch($query, $search)
    {
        if (empty($search)) {
            return $query;
        }

        return $query->whereHas('member', function ($q) use ($search) {
            $q->where('first_name', 'like', "%{$search}%")
              ->orWhere('last_name', 'like', "%{$search}%")
              ->orWhere('member_code', 'like', "%{$search}%");
        });
    }

    /**
     * For DataTables: Select columns for table view
     */
    public function scopeTableSelect($query)
    {
        return $query->select([
            'id',
            'member_id',
            'membership_plan_id',
            'start_date',
            'end_date',
            'amount_paid',
            'status',
            'created_at',
        ]);
    }

    /**
     * For DataTables: Load relationships
     */
    public function scopeTableRelation($query)
    {
        return $query->with([
            'member:id,member_code,first_name,last_name,phone',
            'membershipPlan:id,name,duration_days',
        ]);
    }

    /**
     * For DataTables: Apply filters
     */
    public function scopeTableFilter($query)
    {
        if (request()->has('status') && request('status') !== '') {
            $query->where('status', request('status'));
        }

        if (request()->has('membership_plan_id') && request('membership_plan_id') !== '') {
            $query->where('membership_plan_id', request('membership_plan_id'));
        }

        if (request()->has('start_date') && request('start_date') !== '') {
            $query->whereDate('start_date', '>=', request('start_date'));
        }

        if (request()->has('end_date') && request('end_date') !== '') {
            $query->whereDate('end_date', '<=', request('end_date'));
        }

        return $query;
    }

    // Accessors
    public function getDaysRemainingAttribute(): int
    {
        if ($this->status === MembershipStatus::EXPIRED || $this->end_date < now()) {
            return 0;
        }

        return now()->diffInDays($this->end_date, false);
    }

    public function isExpired(): bool
    {
        return $this->status === MembershipStatus::EXPIRED || $this->end_date < now();
    }

    public function isActive(): bool
    {
        return $this->status === MembershipStatus::ACTIVE && $this->end_date >= now();
    }
}
