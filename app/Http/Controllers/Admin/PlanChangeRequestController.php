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
        $requests = PlanChangeRequest::with(['organization', 'requestedPlan', 'requestedBy'])
            ->orderByRaw("status = 'pending' desc")
            ->latest()
            ->get();

        return view('admin.plan-change-requests.index', [
            'requests' => $requests,
        ]);
    }

    public function approve(Request $request, PlanChangeRequest $planChangeRequest, PlanChangeRequestService $service): RedirectResponse
    {
        abort_unless($planChangeRequest->status === PlanChangeRequestStatus::Pending, 409, 'Permintaan ini sudah diproses.');

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
        abort_unless($planChangeRequest->status === PlanChangeRequestStatus::Pending, 409, 'Permintaan ini sudah diproses.');

        $validated = $request->validate([
            'admin_note' => ['nullable', 'string', 'max:500'],
        ]);

        $service->reject($planChangeRequest, Auth::user(), $validated['admin_note'] ?? null);

        return redirect()
            ->route('admin.plan-change-requests.index')
            ->with('status', 'Permintaan pergantian paket ditolak.');
    }
}
