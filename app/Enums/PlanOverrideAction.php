<?php

namespace App\Enums;

enum PlanOverrideAction: string
{
    case OverridePlan = 'override_plan';
    case RetryApprove = 'retry_approve';

    public function label(): string
    {
        return match ($this) {
            self::OverridePlan => 'Ubah Paket Manual',
            self::RetryApprove => 'Coba Lagi Persetujuan',
        };
    }
}
