<?php

namespace App\Enums;

enum OrganizationCategory: string
{
    case Persyarikatan = 'persyarikatan';
    case AumPendidikan = 'aum_pendidikan';
    case AumKesehatan = 'aum_kesehatan';
    case Media = 'media';
    case Masjid = 'masjid';

    public function label(): string
    {
        return match ($this) {
            self::Persyarikatan => 'Persyarikatan',
            self::AumPendidikan => 'AUM Pendidikan',
            self::AumKesehatan => 'AUM Kesehatan dan Sosial',
            self::Media => 'Media',
            self::Masjid => 'Masjid',
        };
    }
}
