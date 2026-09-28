<?php

namespace App\Enums;

enum PlanOverrideAction: string
{
    case OverridePlan = 'override_plan';
    case RetryApprove = 'retry_approve';
    // An admin verified a manual bank transfer and approved the request by hand - the fallback
    // path used when Midtrans is unavailable (see Admin\PlanChangeRequestController::approveManual()).
    case ApproveManualTransfer = 'approve_manual_transfer';

    public function label(): string
    {
        return match ($this) {
            self::OverridePlan => 'Ubah Paket Manual',
            self::RetryApprove => 'Coba Lagi Persetujuan',
            self::ApproveManualTransfer => 'Persetujuan Transfer Manual',
        };
    }
}
