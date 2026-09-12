<?php

// Manual bank transfer is always offered as a way to pay for a plan change; `manual_transfer.only`
// decides whether it's the *only* way.
//
// - only = false (normal): Midtrans Snap is the primary action - see
//   OrganizationPlanController::store()/pay() (creates the Snap transaction) and
//   MidtransWebhookController (settles the PlanChangeRequest automatically on payment). The
//   manual option sits alongside it for tenants who hit trouble with Snap.
// - only = true (Midtrans down): no Snap transaction is ever created, so an outage or a broken
//   notification URL can't strand a tenant mid-checkout. The plan page sends them straight to
//   the transfer instructions.
//
// Either way the manual path is deliberately *not* self-service: confirmManualPayment() moves a
// request to PaymentConfirmed ("tenant claims to have paid") and never to Approved. An admin
// verifies the transfer landed and approves it from admin/plan-change-requests, so a tenant
// self-declaring payment can't activate a plan on its own.
return [
    'manual_transfer' => [
        'only' => (bool) env('BILLING_MANUAL_TRANSFER', false),
        'bank_name' => env('BILLING_MANUAL_BANK_NAME', 'Bank Jago'),
        'account_number' => env('BILLING_MANUAL_ACCOUNT_NUMBER', '103836081300'),
        'account_holder' => env('BILLING_MANUAL_ACCOUNT_HOLDER', 'Ghany Abdillah Ersa'),
        // Digits only, in international format without the leading "+" - used to build the
        // wa.me confirmation link the tenant taps after transferring.
        'whatsapp' => env('BILLING_MANUAL_WHATSAPP', '6282164028264'),
    ],

    'midtrans' => [
        'merchant_id' => env('MIDTRANS_MERCHANT_ID'),
        'client_key' => env('MIDTRANS_CLIENT_KEY'),
        'server_key' => env('MIDTRANS_SERVER_KEY'),
        'is_production' => env('MIDTRANS_IS_PRODUCTION', false),

        // Flat rupiah surcharge added on top of PlanChangeRequest::totalPrice() to cover
        // Midtrans' processing fee, passed on to the tenant. 0 until the real per-method fee
        // schedule from the Midtrans dashboard is known - see PlanChangeRequest::gatewayAmount().
        'admin_fee' => (int) env('MIDTRANS_ADMIN_FEE', 0),

        // How many times an admin may retry PlanChangeRequestService::approve() from the admin
        // panel after a settled payment fails to auto-approve (see
        // PlanChangeRequestStatus::PaymentReceivedNeedsReview). Beyond this, retrying is hidden
        // and the admin must fall back to the plan-override panel instead.
        'max_approve_attempts' => 3,
    ],
];
