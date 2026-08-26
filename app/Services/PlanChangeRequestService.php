<?php

namespace App\Services;

use App\Enums\PlanChangeRequestStatus;
use App\Models\PlanChangeRequest;
use App\Models\User;

/**
 * Approving/rejecting a plan change request. organizations.plan_id is never touched by the
 * request itself (see OrganizationPlanController::store()) — only approve() flips it.
 */
class PlanChangeRequestService
{
    public function approve(PlanChangeRequest $request, User $admin, ?string $note = null): void
    {
        // Extend from the current expiry if it's still in the future (renewing before it
        // lapses), otherwise start fresh from now — so a timely renewal doesn't lose paid-for
        // time, matching how hosting renewals work.
        $baseline = $request->organization->plan_expires_at?->isFuture()
            ? $request->organization->plan_expires_at
            : now();

        $request->organization->update([
            'plan_id' => $request->requested_plan_id,
            'plan_expires_at' => $baseline->copy()->addMonths($request->duration_months),
        ]);

        $request->update([
            'status' => PlanChangeRequestStatus::Approved,
            'reviewed_by_user_id' => $admin->id,
            'reviewed_at' => now(),
            'admin_note' => $note,
        ]);
    }

    public function reject(PlanChangeRequest $request, User $admin, ?string $note = null): void
    {
        $request->update([
            'status' => PlanChangeRequestStatus::Rejected,
            'reviewed_by_user_id' => $admin->id,
            'reviewed_at' => now(),
            'admin_note' => $note,
        ]);
    }
}
