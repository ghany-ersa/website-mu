<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PlanChangeRequestStatus;
use App\Enums\PlanOverrideAction;
use App\Http\Controllers\Controller;
use App\Models\PlanChangeRequest;
use App\Services\PlanChangeRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PlanChangeRequestController extends Controller
{
    public function index(): View
    {
        $search = trim((string) request('q'));
        $status = request('status');

        $requests = PlanChangeRequest::query()
            ->with(['organization', 'requestedPlan', 'requestedBy'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->whereHas('organization', function ($query) use ($search) {
                        $query->where('name', 'like', "%{$search}%");
                    })->orWhereHas('requestedBy', function ($query) use ($search) {
                        $query->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
                });
            })
            ->when($status, fn ($query) => $query->where('status', $status))
            ->orderByRaw("status = 'pending' desc")
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.plan-change-requests.index', [
            'requests' => $requests,
            'statuses' => PlanChangeRequestStatus::cases(),
        ]);
    }

    /**
     * Approves a request the tenant paid for by manual bank transfer (see
     * OrganizationPlanController::confirmManualPayment()). This is the only path to Approved
     * that isn't driven by Midtrans, which is why it's gated on PaymentConfirmed: the tenant's
     * claim alone never activates a plan, an admin has to verify the transfer landed first.
     * Logged to plan_override_logs like every other manual plan change.
     */
    public function approveManual(Request $request, PlanChangeRequest $planChangeRequest, PlanChangeRequestService $service): RedirectResponse
    {
        abort_unless($planChangeRequest->status === PlanChangeRequestStatus::PaymentConfirmed, 409, 'Permintaan ini tidak sedang menunggu verifikasi transfer.');

        $validated = $request->validate([
            'admin_note' => ['nullable', 'string', 'max:500'],
        ]);

        $admin = Auth::user();
        $fromPlanId = $planChangeRequest->organization->plan_id;
        $fromExpiresAt = $planChangeRequest->organization->plan_expires_at;

        $service->approve($planChangeRequest, $admin, $validated['admin_note'] ?? 'Disetujui setelah verifikasi transfer manual.');

        $planChangeRequest->organization->planOverrideLogs()->create([
            'plan_change_request_id' => $planChangeRequest->id,
            'admin_user_id' => $admin->id,
            'action' => PlanOverrideAction::ApproveManualTransfer,
            'from_plan_id' => $fromPlanId,
            'to_plan_id' => $planChangeRequest->requested_plan_id,
            'from_expires_at' => $fromExpiresAt,
            'to_expires_at' => $planChangeRequest->organization->fresh()->plan_expires_at,
            'note' => 'Disetujui manual setelah verifikasi transfer bank.',
        ]);

        return redirect()
            ->route('admin.plan-change-requests.index')
            ->with('status', 'Paket berhasil disetujui setelah verifikasi transfer.');
    }

    /**
     * Cancels a request that was never paid for - e.g. the tenant abandoned checkout and it's
     * cluttering the queue. Also covers a PaymentConfirmed request whose transfer never actually
     * landed, so an admin can clear a false claim out of the queue.
     */
    public function reject(Request $request, PlanChangeRequest $planChangeRequest, PlanChangeRequestService $service): RedirectResponse
    {
        abort_unless(in_array($planChangeRequest->status, [PlanChangeRequestStatus::Pending, PlanChangeRequestStatus::PaymentConfirmed], true), 409, 'Permintaan ini sudah diproses.');

        $validated = $request->validate([
            'admin_note' => ['nullable', 'string', 'max:500'],
        ]);

        $service->reject($planChangeRequest, Auth::user(), $validated['admin_note'] ?? null);

        return redirect()
            ->route('admin.plan-change-requests.index')
            ->with('status', 'Permintaan pergantian paket ditolak.');
    }

    /**
     * Manually retries PlanChangeRequestService::approve() for a request Midtrans already
     * settled but which failed to auto-approve from the webhook (see
     * PlanChangeRequestStatus::PaymentReceivedNeedsReview). Logged to plan_override_logs like
     * every other manual plan change, and capped at config('billing.midtrans.max_approve_attempts')
     * - beyond that the admin must use the plan-override panel instead.
     */
    public function retryApprove(PlanChangeRequest $planChangeRequest, PlanChangeRequestService $service): RedirectResponse
    {
        abort_unless($planChangeRequest->status === PlanChangeRequestStatus::PaymentReceivedNeedsReview, 409, 'Permintaan ini tidak sedang menunggu tinjauan.');
        abort_unless($planChangeRequest->canRetryApprove(), 409, 'Batas percobaan otomatis sudah tercapai. Gunakan ubah paket manual.');

        $admin = Auth::user();
        $fromPlanId = $planChangeRequest->organization->plan_id;
        $fromExpiresAt = $planChangeRequest->organization->plan_expires_at;

        $planChangeRequest->increment('approve_attempts');

        try {
            $service->approve($planChangeRequest, $admin, 'Disetujui via percobaan ulang manual setelah pembayaran Midtrans.');
            $planChangeRequest->update(['approve_error' => null]);

            $planChangeRequest->organization->planOverrideLogs()->create([
                'plan_change_request_id' => $planChangeRequest->id,
                'admin_user_id' => $admin->id,
                'action' => PlanOverrideAction::RetryApprove,
                'from_plan_id' => $fromPlanId,
                'to_plan_id' => $planChangeRequest->requested_plan_id,
                'from_expires_at' => $fromExpiresAt,
                'to_expires_at' => $planChangeRequest->organization->fresh()->plan_expires_at,
                'note' => 'Percobaan ke-'.$planChangeRequest->approve_attempts.' berhasil.',
            ]);

            return redirect()
                ->route('admin.plan-change-requests.index')
                ->with('status', 'Paket berhasil disetujui.');
        } catch (\Throwable $e) {
            $planChangeRequest->update(['approve_error' => $e->getMessage()]);

            return redirect()
                ->route('admin.plan-change-requests.index')
                ->with('warning', 'Percobaan gagal lagi: '.$e->getMessage());
        }
    }
}
