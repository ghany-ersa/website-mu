<?php

// Static payment info shown on the plan-change payment screen — no payment gateway is
// integrated, so orgs transfer manually and confirm via the "Saya Sudah Bayar" button
// (see OrganizationPlanController::confirmPayment()); an admin verifies the transfer
// out of band before approving.
return [
    'bank_transfers' => [
        [
            'bank' => 'BCA',
            'account_number' => '1234567890',
            'account_name' => 'PT Website-mu Indonesia',
        ],
    ],

    'whatsapp_confirmation_number' => '628123456789',
];
