<?php

namespace App\Enums;

enum PlanChangeRequestStatus: string
{
    case Pending = 'pending';
    case PaymentConfirmed = 'payment_confirmed';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu Pembayaran',
            self::PaymentConfirmed => 'Menunggu Verifikasi Pembayaran',
            self::Approved => 'Disetujui',
            self::Rejected => 'Ditolak',
        };
    }
}
