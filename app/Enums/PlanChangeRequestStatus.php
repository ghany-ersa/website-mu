<?php

namespace App\Enums;

enum PlanChangeRequestStatus: string
{
    case Pending = 'pending';
    // Legacy: set by the old manual "Saya Sudah Bayar" flow, retired now that payment goes
    // entirely through Midtrans. Kept so historical rows still resolve to a valid case.
    case PaymentConfirmed = 'payment_confirmed';
    case Approved = 'approved';
    case Rejected = 'rejected';
    // Midtrans reported settlement but PlanChangeRequestService::approve() threw - admin must
    // retry it manually from the admin panel (see approve_error/approve_attempts).
    case PaymentReceivedNeedsReview = 'payment_received_needs_review';
    // The Snap transaction expired or was cancelled before payment.
    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu Pembayaran',
            self::PaymentConfirmed => 'Menunggu Verifikasi Pembayaran',
            self::Approved => 'Disetujui',
            self::Rejected => 'Ditolak',
            self::PaymentReceivedNeedsReview => 'Perlu Ditinjau Admin',
            self::Expired => 'Kedaluwarsa',
        };
    }
}
