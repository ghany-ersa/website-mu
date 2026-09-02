<?php

namespace App\Models;

use App\Enums\PlanChangeRequestStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'organization_id', 'requested_plan_id', 'duration_months', 'discount_code_id', 'discount_amount',
    'limits_snapshot', 'payment_confirmed_at', 'requested_by_user_id', 'status', 'reviewed_by_user_id',
    'reviewed_at', 'admin_note', 'midtrans_order_id', 'midtrans_transaction_id', 'midtrans_payment_type',
    'midtrans_status', 'midtrans_paid_at', 'approve_error', 'approve_attempts',
])]
class PlanChangeRequest extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => PlanChangeRequestStatus::class,
            'reviewed_at' => 'datetime',
            'payment_confirmed_at' => 'datetime',
            'limits_snapshot' => 'array',
            'midtrans_paid_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Organization, $this>
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * @return BelongsTo<Plan, $this>
     */
    public function requestedPlan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'requested_plan_id');
    }

    /**
     * @return BelongsTo<DiscountCode, $this>
     */
    public function discountCode(): BelongsTo
    {
        return $this->belongsTo(DiscountCode::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by_user_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }

    /**
     * Price for the chosen plan/duration before any voucher discount is applied.
     */
    public function subtotal(): int
    {
        return $this->requestedPlan->priceForDuration($this->duration_months);
    }

    /**
     * Total amount the requester owes for the chosen duration, before the Midtrans surcharge.
     */
    public function totalPrice(): int
    {
        return max(0, $this->subtotal() - $this->discount_amount);
    }

    /**
     * The exact gross_amount sent to Midtrans and expected back on the settlement webhook —
     * totalPrice() plus the flat gateway surcharge (see config('billing.midtrans.admin_fee')).
     */
    public function gatewayAmount(): int
    {
        return $this->totalPrice() + (int) config('billing.midtrans.admin_fee', 0);
    }

    /**
     * Whether an admin may still retry PlanChangeRequestService::approve() from the admin
     * panel for a settled-but-unapproved request, per config('billing.midtrans.max_approve_attempts').
     */
    public function canRetryApprove(): bool
    {
        return $this->approve_attempts < (int) config('billing.midtrans.max_approve_attempts', 3);
    }
}
