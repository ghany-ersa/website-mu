<?php

namespace App\Enums;

enum OrganizationCategory: string
{
    case Persyarikatan = 'persyarikatan';
    case Ortom = 'ortom';
    case Aum = 'aum';

    public function label(): string
    {
        return match ($this) {
            self::Persyarikatan => 'Persyarikatan',
            self::Ortom => 'Organisasi Otonom',
            self::Aum => 'Amal Usaha Muhammadiyah',
        };
    }
}
