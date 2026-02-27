<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Trainer extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'phone',
        'email',
        'certifications',
        'hire_date',
        'salary',
        'commission_rate',
        'photo',
        'status',
    ];

    protected $casts = [
        'hire_date' => 'date',
        'salary' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'certifications' => 'array',
    ];

    // Relationships
    // public function classes(): HasMany
    // {
    //     return $this->hasMany(TrainerClass::class);
    // }

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
            $q->where('first_name', 'like', "%{$search}%")
              ->orWhere('last_name', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%");
        });
    }

    /**
     * For DataTables: Select columns for table view
     */
    public function scopeTableSelect($query)
    {
        return $query->select([
            'id',
            'first_name',
            'last_name',
            'phone',
            'email',
            'certifications',
            'hire_date',
            'salary',
            'commission_rate',
            'photo',
            'status',
            'created_at',
        ]);
    }

    /**
     * For DataTables: Load relationships
     */
    public function scopeTableRelation($query)
    {
        // return $query->withCount('classes');
        return $query;
    }

    /**
     * For DataTables: Apply filters
     */
    public function scopeTableFilter($query)
    {
        if (request()->has('status') && request('status') !== '') {
            $query->where('status', request('status'));
        }

        return $query;
    }

    // Accessors
    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getYearsOfServiceAttribute(): int
    {
        return $this->hire_date ? $this->hire_date->diffInYears(now()) : 0;
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo ? asset('storage/' . $this->photo) : null;
    }
}
