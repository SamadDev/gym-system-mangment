<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Equipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'equipment_code',
        'category',
        'purchase_date',
        'purchase_price',
        'condition',
        'last_maintenance_date',
        'next_maintenance_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'last_maintenance_date' => 'date',
        'next_maintenance_date' => 'date',
        'purchase_price' => 'decimal:2',
    ];

    // Relationships
    // public function maintenances(): HasMany
    // {
    //     return $this->hasMany(EquipmentMaintenance::class);
    // }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeNeedsMaintenance($query)
    {
        return $query->where('next_maintenance_date', '<=', now()->addDays(7));
    }

    public function scopeCategory($query, $category)
    {
        if (empty($category)) {
            return $query;
        }

        return $query->where('category', $category);
    }

    public function scopeCondition($query, $condition)
    {
        if (empty($condition)) {
            return $query;
        }

        return $query->where('condition', $condition);
    }

    public function scopeSearch($query, $search)
    {
        if (empty($search)) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('equipment_code', 'like', "%{$search}%")
              ->orWhere('category', 'like', "%{$search}%")
              ->orWhere('notes', 'like', "%{$search}%");
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
            'equipment_code',
            'category',
            'purchase_date',
            'purchase_price',
            'condition',
            'last_maintenance_date',
            'next_maintenance_date',
            'status',
            'notes',
            'created_at',
        ]);
    }

    /**
     * For DataTables: Load relationships
     */
    public function scopeTableRelation($query)
    {
        // return $query->withCount('maintenances')
        //     ->with(['maintenances' => function ($q) {
        //         $q->latest()->limit(1);
        //     }]);
        return $query;
    }

    /**
     * For DataTables: Apply filters
     */
    public function scopeTableFilter($query)
    {
        if (request()->has('category') && request('category') !== '') {
            $query->where('category', request('category'));
        }

        if (request()->has('condition') && request('condition') !== '') {
            $query->where('condition', request('condition'));
        }

        if (request()->has('status') && request('status') !== '') {
            $query->where('status', request('status'));
        }

        return $query;
    }

    // Accessors & Methods
    public function getAgeInYearsAttribute(): int
    {
        return $this->purchase_date ? $this->purchase_date->diffInYears(now()) : 0;
    }

    public function needsMaintenance(): bool
    {
        return $this->next_maintenance_date && 
               $this->next_maintenance_date <= now();
    }

    public function isUnderMaintenance(): bool
    {
        return $this->status === 'under_maintenance';
    }

    public function isRetired(): bool
    {
        return $this->status === 'retired';
    }
}
