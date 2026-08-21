<?php

namespace App\Enums;

enum OrganizationCategory: string
{
    case Persyarikatan = 'persyarikatan';
    case Ortom = 'ortom';
    case AumPendidikan = 'aum_pendidikan';
    case AumKesehatanSosial = 'aum_kesehatan_sosial';
    case Masjid = 'masjid';

    public function label(): string
    {
        return match ($this) {
            self::Persyarikatan => 'Persyarikatan',
            self::Ortom => 'Organisasi Otonom',
            self::AumPendidikan => 'AUM Pendidikan',
            self::AumKesehatanSosial => 'AUM Kesehatan dan Sosial',
            self::Masjid => 'Masjid',
        };
    }
}
