<?php

namespace App\Models;

use App\Enums\PlanChangeRequestStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['organization_id', 'requested_plan_id', 'duration_months', 'discount_code_id', 'discount_amount', 'limits_snapshot', 'payment_confirmed_at', 'requested_by_user_id', 'status', 'reviewed_by_user_id', 'reviewed_at', 'admin_note'])]
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
     * Total amount the requester owes for the chosen duration — the figure an admin matches
     * against the payment they're confirming before approving.
     */
    public function totalPrice(): int
    {
        return max(0, $this->subtotal() - $this->discount_amount);
    }
}
