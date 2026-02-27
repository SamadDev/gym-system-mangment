<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Permission extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'module',
        'description',
    ];

    /**
     * Scope: Group permissions by module
     */
    public function scopeGroupedByModule($query)
    {
        return $query->orderBy('module')->orderBy('name')->get()->groupBy('module');
    }

    /**
     * Scope: Filter by module
     */
    public function scopeByModule($query, $module)
    {
        return $query->where('module', $module);
    }
}
