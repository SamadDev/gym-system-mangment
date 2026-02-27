<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'permissions',
        'description',
    ];

    protected $casts = [
        'permissions' => 'array',
    ];

    /**
     * Relationship: Role has many Users
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Check if role has a specific permission
     */
    public function hasPermission(string $permission): bool
    {
        $permissions = $this->permissions ?? [];
        return in_array($permission, $permissions);
    }

    /**
     * Get count of active users with this role
     */
    public function getActiveUsersCountAttribute(): int
    {
        return $this->users()->where('status', true)->count();
    }

    /**
     * Scope: Search roles by name or description
     */
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
     * For DataTables: Select columns
     */
    public static function scopeTableSelect($query)
    {
        return $query->select(['id', 'name', 'description', 'created_at']);
    }
}
