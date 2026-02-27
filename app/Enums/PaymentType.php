<?php

namespace App\Enums;

enum PaymentType: string
{
    case MEMBERSHIP = 'membership';
    case PERSONAL_TRAINING = 'personal_training';
    case MARKET_SALE = 'market_sale';

    public function label(): string
    {
        return match($this) {
            self::MEMBERSHIP => 'Membership',
            self::PERSONAL_TRAINING => 'Personal Training',
            self::MARKET_SALE => 'Market Sale',
        };
    }
}
