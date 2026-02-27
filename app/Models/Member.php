<?php

namespace App\Models;

use App\Enums\Gender;
use App\Enums\MemberStatus;
use App\Enums\BloodType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Member extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_code',
        'first_name',
        'last_name',
        'phone',
        'email',
        'address',
        'date_of_birth',
        'gender',
        'photo',
        'emergency_contact',
        'emergency_phone',
        'blood_type',
        'status',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'gender' => Gender::class,
        'status' => MemberStatus::class,
        'blood_type' => BloodType::class,
    ];

    // Relationships
    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class);
    }

    public function currentMembership()
    {
        return $this->hasOne(Membership::class)
            ->where('status', 'active')
            ->where('end_date', '>=', now())
            ->latest();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function attendance(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', MemberStatus::ACTIVE);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('first_name', 'like', "%{$search}%")
              ->orWhere('last_name', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%")
              ->orWhere('member_code', 'like', "%{$search}%");
        });
    }

    // Accessors
    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function hasActiveMembership(): bool
    {
        return $this->memberships()
            ->where('status', 'active')
            ->where('end_date', '>=', now())
            ->exists();
    }

    /**
     * For DataTables: Select columns for table view
     */
    public function scopeTableSelect($query)
    {
        return $query->select([
            'id',
            'member_code',
            'first_name',
            'last_name',
            'phone',
            'email',
            'gender',
            'status',
            'photo',
            'address',
            'date_of_birth',
            'emergency_contact',
            'emergency_phone',
            'blood_type',
            'created_at',
        ]);
    }

    /**
     * For DataTables: Load relationships
     */
    public function scopeTableRelation($query)
    {
        return $query->with([
            'currentMembership.membershipPlan:id,name,duration_days',
        ]);
    }

    /**
     * For DataTables: Apply filters
     */
    public function scopeTableFilter($query)
    {
        $filters = request('filters', []);

        $status = $filters['status'] ?? request('status');
        if ($status) {
            $query->where('status', $status);
        }

        $gender = $filters['gender'] ?? request('gender');
        if ($gender) {
            $query->where('gender', $gender);
        }

        $membershipStatus = $filters['membership_status'] ?? request('membership_status');
        if ($membershipStatus === 'active') {
            $query->whereHas('memberships', function ($q) {
                $q->where('status', 'active')->where('end_date', '>=', now());
            });
        } elseif ($membershipStatus === 'inactive') {
            $query->whereDoesntHave('memberships', function ($q) {
                $q->where('status', 'active')->where('end_date', '>=', now());
            });
        }

        return $query;
    }

    // Accessors
    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo ? asset('storage/' . $this->photo) : null;
    }

    // Auto-generate member code on creation
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($member) {
            if (!$member->member_code) {
                $member->member_code = self::generateMemberCode();
            }
        });
    }

    /**
     * Generate unique member code: MEM-YYYY-####
     */
    private static function generateMemberCode(): string
    {
        $year = date('Y');
        $lastMember = self::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();
        
        $number = $lastMember ? ((int)substr($lastMember->member_code, -4)) + 1 : 1;
        
        return 'MEM-' . $year . '-' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }
}

