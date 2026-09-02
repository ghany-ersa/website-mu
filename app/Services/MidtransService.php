<?php

namespace App\Services;

use App\Models\PlanChangeRequest;
use Illuminate\Support\Str;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction;

/**
 * Thin wrapper around the midtrans/midtrans-php SDK, scoped to what plan-change checkout
 * needs: creating a Snap transaction and re-verifying a webhook notification's real status
 * directly from Midtrans (never trusting the webhook payload's own status claim).
 */
class MidtransService
{
    public function __construct()
    {
        Config::$serverKey = config('billing.midtrans.server_key');
        Config::$clientKey = config('billing.midtrans.client_key');
        Config::$isProduction = (bool) config('billing.midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    /**
     * Creates a Snap transaction for the given request and stores the order id it was created
     * with, so the webhook can look the request back up by it. order_id is prefixed with the
     * request's own id but still suffixed with a random token, because Midtrans requires
     * order_id to be unique forever on the account — a plain request id would collide if the
     * same request ever needed a second Snap transaction (e.g. after the first expired).
     */
    public function createSnapTransaction(PlanChangeRequest $planChangeRequest): string
    {
        $orderId = sprintf('pcr-%d-%s', $planChangeRequest->id, Str::lower(Str::random(6)));

        $organization = $planChangeRequest->organization;
        $user = $planChangeRequest->requestedBy;

        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $planChangeRequest->gatewayAmount(),
            ],
            'customer_details' => [
                'first_name' => $user->name,
                'email' => $user->email,
            ],
            'item_details' => array_filter([
                [
                    'id' => 'plan-'.$planChangeRequest->requested_plan_id,
                    'price' => $planChangeRequest->totalPrice(),
                    'quantity' => 1,
                    'name' => Str::limit($planChangeRequest->requestedPlan->name.' — '.$organization->name, 50, ''),
                ],
                config('billing.midtrans.admin_fee') > 0 ? [
                    'id' => 'admin-fee',
                    'price' => (int) config('billing.midtrans.admin_fee'),
                    'quantity' => 1,
                    'name' => 'Biaya Admin',
                ] : null,
            ]),
            // Per-transaction redirect, so the tenant lands back on this exact organization's
            // plan page — not just the dashboard's static Finish/Unfinish/Error URL, which
            // can't carry an organization id. The plan page itself shows the real status
            // (active or still pending) regardless of which of the three Midtrans lands on,
            // since actual approval only ever happens via the webhook.
            'callbacks' => [
                'finish' => route('organizations.plan.edit', $organization),
            ],
        ];

        $redirectUrl = Snap::createTransaction($params)->redirect_url;

        $planChangeRequest->update(['midtrans_order_id' => $orderId]);

        return $redirectUrl;
    }

    /**
     * Re-fetches a transaction's status directly from Midtrans by order_id, rather than
     * trusting the transaction_status field in the webhook payload — the payload is used only
     * to look up which order_id to check.
     */
    public function fetchStatus(string $orderId): object
    {
        return Transaction::status($orderId);
    }

    /**
     * Verifies the webhook payload's signature_key: SHA512(order_id + status_code +
     * gross_amount + server_key). This is a cheap first check to reject obviously forged
     * requests before doing the (slower, network) status re-fetch — it's not a substitute for
     * fetchStatus(), since a stale but validly-signed payload could still misreport status.
     */
    public function verifySignature(array $payload): bool
    {
        if (! isset($payload['order_id'], $payload['status_code'], $payload['gross_amount'], $payload['signature_key'])) {
            return false;
        }

        $expected = hash('sha512',
            $payload['order_id'].$payload['status_code'].$payload['gross_amount'].config('billing.midtrans.server_key')
        );

        return hash_equals($expected, $payload['signature_key']);
    }
}
