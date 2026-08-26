<?php

namespace App\Services;

use App\Enums\PlanChangeRequestStatus;
use App\Models\PlanChangeRequest;
use App\Models\User;

/**
 * Approving/rejecting a plan change request. organizations.plan_id is never touched by the
 * request itself (see OrganizationPlanController::store()) — only approve() flips it, which
 * is what triggers OrganizationObserver::updated() to re-sync gated sections.
 */
class PlanChangeRequestService
{
    public function approve(PlanChangeRequest $request, User $admin, ?string $note = null): void
    {
        $request->organization->update(['plan_id' => $request->requested_plan_id]);

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
