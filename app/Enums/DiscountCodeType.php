<?php

namespace App\Enums;

enum DiscountCodeType: string
{
    case Percent = 'percent';
    case Fixed = 'fixed';

    public function label(): string
    {
        return match ($this) {
            self::Percent => 'Persentase',
            self::Fixed => 'Nominal Tetap',
        };
    }
}
