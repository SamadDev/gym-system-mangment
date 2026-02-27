<?php

namespace App\Models;

use App\Constants\DateTimeConstants;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasFactory;
    
    protected $table = 'attendance';
    public $timestamps = false;
    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    protected $fillable = [
        'member_id',
        'check_in_time',
        'check_out_time',
        'entry_method',
        'notes',
    ];

    protected $casts = [
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime',
    ];

    // Relationships
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    // Scopes
    public function scopeToday($query)
    {
        return $query->whereDate('check_in_time', now()->format('Y-m-d'));
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('check_in_time', [$startDate, $endDate]);
    }

    public function scopeCheckedOut($query)
    {
        return $query->whereNotNull('check_out');
    }

    public function scopeStillInGym($query)
    {
        return $query->whereNull('check_out');
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
            'check_in_time',
            'check_out_time',
            'entry_method',
            'notes',
            'created_at',
        ]);
    }

    /**
     * For DataTables: Load relationships
     */
    public function scopeTableRelation($query)
    {
        return $query->with([
            'member:id,member_code,first_name,last_name,photo',
        ]);
    }

    /**
     * For DataTables: Apply filters
     */
    public function scopeTableFilter($query)
    {
        if (request()->has('date') && request('date') !== '') {
            $query->whereDate('check_in', request('date'));
        }

        if (request()->has('date_from') && request('date_from') !== '') {
            $query->whereDate('check_in', '>=', request('date_from'));
        }

        if (request()->has('date_to') && request('date_to') !== '') {
            $query->whereDate('check_in', '<=', request('date_to'));
        }

        if (request()->has('status')) {
            if (request('status') === 'checked_in') {
                $query->whereNull('check_out');
            } elseif (request('status') === 'checked_out') {
                $query->whereNotNull('check_out');
            }
        }

        return $query;
    }

    // Accessors & Methods
    public function getFormattedCheckInAttribute(): string
    {
        return $this->check_in?->format(DateTimeConstants::DISPLAY_DATETIME_FORMAT) ?? 'N/A';
    }

    public function getFormattedCheckOutAttribute(): string
    {
        return $this->check_out?->format(DateTimeConstants::DISPLAY_DATETIME_FORMAT) ?? 'Still in gym';
    }

    public function calculateDuration(): void
    {
        if ($this->check_out && $this->check_in) {
            $this->duration_minutes = $this->check_in->diffInMinutes($this->check_out);
            $this->save();
        }
    }

    public function getFormattedDurationAttribute(): string
    {
        if (!$this->duration_minutes) {
            return 'N/A';
        }

        $hours = floor($this->duration_minutes / 60);
        $minutes = $this->duration_minutes % 60;

        return $hours > 0 ? "{$hours}h {$minutes}m" : "{$minutes}m";
    }

    public function isStillInGym(): bool
    {
        return $this->check_out === null;
    }
}
