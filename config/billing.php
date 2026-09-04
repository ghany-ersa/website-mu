<?php

// Payment for plan changes goes entirely through Midtrans Snap - see
// OrganizationPlanController::store() (creates the Snap transaction) and
// MidtransWebhookController (settles the PlanChangeRequest on payment). There is no manual
// bank-transfer fallback; an admin who needs to bypass payment entirely uses the plan-override
// panel on the admin organization detail page instead (Admin\OrganizationController::overridePlan()).
return [
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
