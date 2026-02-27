<?php

namespace App\Enums;

enum MembershipStatus: string
{
    case ACTIVE = 'active';
    case EXPIRED = 'expired';

    public function label(): string
    {
        return match($this) {
            self::ACTIVE => 'Active',
            self::EXPIRED => 'Expired',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::ACTIVE => 'bg-green-100 text-green-800',
            self::EXPIRED => 'bg-red-100 text-red-800',
        };
    }
}
