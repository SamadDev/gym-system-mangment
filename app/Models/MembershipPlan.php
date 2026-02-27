<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MembershipPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'duration_days',
        'price_male',
        'price_female',
        'class_limit',
        'personal_training_included',
        'status',
        'features',
    ];

    protected $casts = [
        'features' => 'array',
        'personal_training_included' => 'boolean',
        'price_male' => 'decimal:2',
        'price_female' => 'decimal:2',
        'class_limit' => 'integer',
    ];

    // Relationships
    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeSearch($query, $search)
    {
        if (empty($search)) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    /**
     * For DataTables: Select columns for table view
     */
    public function scopeTableSelect($query)
    {
        return $query->select([
            'id',
            'name',
            'description',
            'duration_days',
            'price_male',
            'price_female',
            'class_limit',
            'personal_training_included',
            'status',
            'created_at',
        ]);
    }

    /**
     * For DataTables: Load relationships
     */
    public function scopeTableRelation($query)
    {
        return $query->withCount('memberships');
    }

    /**
     * For DataTables: Apply filters
     */
    public function scopeTableFilter($query)
    {
        if (request()->has('status') && request('status') !== '') {
            $query->where('status', request('status'));
        }

        if (request()->has('duration_days') && request('duration_days') !== '') {
            $query->where('duration_days', request('duration_days'));
        }

        return $query;
    }

    // Accessors
    public function getPriceForGender(string $gender): float
    {
        return (float) ($gender === 'male' ? $this->price_male : $this->price_female);
    }

    public function getDurationInMonthsAttribute(): int
    {
        return (int) ceil($this->duration_days / 30);
    }
}
