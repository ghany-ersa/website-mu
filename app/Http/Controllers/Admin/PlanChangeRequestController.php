<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PlanChangeRequestStatus;
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

    public function approve(Request $request, PlanChangeRequest $planChangeRequest, PlanChangeRequestService $service): RedirectResponse
    {
        abort_unless($this->isActionable($planChangeRequest), 409, 'Permintaan ini sudah diproses.');

        $validated = $request->validate([
            'admin_note' => ['nullable', 'string', 'max:500'],
        ]);

        $service->approve($planChangeRequest, Auth::user(), $validated['admin_note'] ?? null);

        return redirect()
            ->route('admin.plan-change-requests.index')
            ->with('status', 'Paket berhasil disetujui dan diaktifkan.');
    }

    public function reject(Request $request, PlanChangeRequest $planChangeRequest, PlanChangeRequestService $service): RedirectResponse
    {
        abort_unless($this->isActionable($planChangeRequest), 409, 'Permintaan ini sudah diproses.');

        $validated = $request->validate([
            'admin_note' => ['nullable', 'string', 'max:500'],
        ]);

        $service->reject($planChangeRequest, Auth::user(), $validated['admin_note'] ?? null);

        return redirect()
            ->route('admin.plan-change-requests.index')
            ->with('status', 'Permintaan pergantian paket ditolak.');
    }

    /**
     * Approve/reject are allowed from Pending (admin verified payment out of band without
     * waiting for the org to click "Saya Sudah Bayar") or PaymentConfirmed (the normal path).
     */
    private function isActionable(PlanChangeRequest $planChangeRequest): bool
    {
        return in_array($planChangeRequest->status, [
            PlanChangeRequestStatus::Pending,
            PlanChangeRequestStatus::PaymentConfirmed,
        ], true);
    }
}
