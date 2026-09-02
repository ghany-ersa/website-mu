<?php

namespace App\Models;

use App\Enums\PlanOverrideAction;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['organization_id', 'plan_change_request_id', 'admin_user_id', 'action', 'from_plan_id', 'to_plan_id', 'from_expires_at', 'to_expires_at', 'note'])]
class PlanOverrideLog extends Model
{
    public $timestamps = false;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'action' => PlanOverrideAction::class,
            'from_expires_at' => 'datetime',
            'to_expires_at' => 'datetime',
            'created_at' => 'datetime',
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
     * @return BelongsTo<PlanChangeRequest, $this>
     */
    public function planChangeRequest(): BelongsTo
    {
        return $this->belongsTo(PlanChangeRequest::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_user_id');
    }

    /**
     * @return BelongsTo<Plan, $this>
     */
    public function fromPlan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'from_plan_id');
    }

    /**
     * @return BelongsTo<Plan, $this>
     */
    public function toPlan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'to_plan_id');
    }
}
