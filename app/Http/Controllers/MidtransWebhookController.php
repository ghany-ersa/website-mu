<?php

namespace App\Http\Controllers;

use App\Enums\PlanChangeRequestStatus;
use App\Models\PlanChangeRequest;
use App\Services\MidtransService;
use App\Services\PlanChangeRequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Public endpoint Midtrans calls whenever a Snap transaction's status changes. Not behind
 * `auth` - Midtrans, not a logged-in tenant, is the caller - so it's protected by signature
 * verification plus a live status re-fetch instead of a session/CSRF check. Always responds
 * 200 once a payload is handled (even for a status we ignore) since Midtrans retries with
 * backoff on anything else, and duplicate notifications are expected and must be safe to
 * re-process (see PlanChangeRequestService::approve()'s Approved-state guard).
 */
class MidtransWebhookController extends Controller
{
    public function __invoke(Request $request, MidtransService $midtrans, PlanChangeRequestService $planChangeRequestService): JsonResponse
    {
        $payload = $request->all();

        if (! $midtrans->verifySignature($payload)) {
            Log::warning('Midtrans webhook: invalid signature', ['order_id' => $payload['order_id'] ?? null]);

            return response()->json(['message' => 'invalid signature'], 403);
        }

        $orderId = $payload['order_id'] ?? null;
        $planChangeRequest = PlanChangeRequest::where('midtrans_order_id', $orderId)->first();

        if (! $planChangeRequest) {
            Log::warning('Midtrans webhook: no matching PlanChangeRequest', ['order_id' => $orderId]);

            return response()->json(['message' => 'not found'], 404);
        }

        // Never trust the payload's own transaction_status - re-fetch the authoritative status
        // directly from Midtrans by order_id.
        $status = $midtrans->fetchStatus($orderId);

        if ((int) $status->gross_amount !== $planChangeRequest->gatewayAmount()) {
            Log::error('Midtrans webhook: gross_amount mismatch', [
                'order_id' => $orderId,
                'expected' => $planChangeRequest->gatewayAmount(),
                'received' => $status->gross_amount,
            ]);

            return response()->json(['message' => 'amount mismatch, not processed']);
        }

        $planChangeRequest->update([
            'midtrans_transaction_id' => $status->transaction_id,
            'midtrans_payment_type' => $status->payment_type,
            'midtrans_status' => $status->transaction_status,
        ]);

        match ($status->transaction_status) {
            'settlement', 'capture' => $this->handleSettlement($planChangeRequest, $planChangeRequestService),
            'expire', 'cancel' => $planChangeRequest->update(['status' => PlanChangeRequestStatus::Expired]),
            'deny' => $planChangeRequest->update([
                'status' => PlanChangeRequestStatus::Rejected,
                'admin_note' => 'Ditolak oleh Midtrans.',
            ]),
            default => null, // e.g. "pending" - midtrans_status above already reflects it.
        };

        return response()->json(['message' => 'ok']);
    }

    private function handleSettlement(PlanChangeRequest $planChangeRequest, PlanChangeRequestService $service): void
    {
        if ($planChangeRequest->status === PlanChangeRequestStatus::Approved) {
            return;
        }

        $planChangeRequest->update(['midtrans_paid_at' => now()]);
        $planChangeRequest->increment('approve_attempts');

        try {
            $service->approve($planChangeRequest, note: 'Disetujui otomatis oleh sistem setelah pembayaran Midtrans.');
            $planChangeRequest->update(['approve_error' => null]);
        } catch (\Throwable $e) {
            Log::error('Midtrans webhook: approve() failed after settlement', [
                'plan_change_request_id' => $planChangeRequest->id,
                'error' => $e->getMessage(),
            ]);

            $planChangeRequest->update([
                'status' => PlanChangeRequestStatus::PaymentReceivedNeedsReview,
                'approve_error' => $e->getMessage(),
            ]);
        }
    }
}
