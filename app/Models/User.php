<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'phone',
        'photo',
        'language',
        'status',
        'last_login',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login' => 'datetime',
            'password' => 'hashed',
            'status' => 'boolean',
        ];
    }

    /**
     * Relationship: User belongs to a Role
     */
    public function role()
    {
        return $this->belongsTo(Role::class)->select('id', 'name', 'permissions');
    }

    /**
     * Check if user has a specific permission
     */
    public function hasPermission(string $permission): bool
    {
        if (!$this->role) {
            return false;
        }

        $permissions = $this->role->permissions ?? [];
        
        if (is_string($permissions)) {
            $permissions = json_decode($permissions, true) ?? [];
        }

        // Check for wildcard permission (Super Admin)
        if (in_array('*', $permissions)) {
            return true;
        }

        return in_array($permission, $permissions);
    }

    /**
     * Check if user can perform an action
     */
    public function can($abilities, $arguments = []): bool
    {
        if (is_array($abilities)) {
            foreach ($abilities as $ability) {
                if ($this->hasPermission($ability)) {
                    return true;
                }
            }
            return false;
        }

        return $this->hasPermission($abilities);
    }

    /**
     * Get user's full name with status indicator
     */
    public function getFullNameAttribute(): string
    {
        return $this->name . ($this->status ? '' : ' (Inactive)');
    }

    /**
     * Scope: Filter only active users
     */
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    /**
     * Scope: Search users by name, email, or phone
     */
    public function scopeSearch($query, $search)
    {
        if (empty($search)) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%");
        });
    }

    /**
     * For DataTables: Select columns for table view
     */
    public static function scopeTableSelect($query)
    {
        return $query->select([
            'id',
            'name',
            'email',
            'phone',
            'role_id',
            'status',
            'last_login',
            'created_at',
        ]);
    }

    /**
     * For DataTables: Load relationships
     */
    public static function scopeTableRelation($query)
    {
        return $query->with('role:id,name');
    }

    /**
     * For DataTables: Apply filters
     */
    public static function scopeTableFilter($query)
    {
        if (request()->has('status') && request('status') !== '') {
            $query->where('status', request('status'));
        }

        if (request()->has('role_id') && request('role_id')) {
            $query->where('role_id', request('role_id'));
        }

        return $query;
    }
}
