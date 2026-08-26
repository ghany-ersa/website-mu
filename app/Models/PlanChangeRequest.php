<?php

namespace App\Models;

use App\Enums\PlanChangeRequestStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['organization_id', 'requested_plan_id', 'duration_months', 'payment_confirmed_at', 'requested_by_user_id', 'status', 'reviewed_by_user_id', 'reviewed_at', 'admin_note'])]
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
     * Total amount the requester owes for the chosen duration — the figure an admin matches
     * against the payment they're confirming before approving.
     */
    public function totalPrice(): int
    {
        return $this->requestedPlan->price_monthly * $this->duration_months;
    }
}
