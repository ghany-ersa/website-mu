<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('plan_change_requests', function (Blueprint $table) {
            // The actual rupiah amount settled for this request - frozen at approval time so it
            // stays accurate even if Plan::price_monthly changes later (unlike
            // PlanChangeRequest::totalPrice()/gatewayAmount(), which recompute live against the
            // *current* plan price - see those methods' doc comments). Sourced from Midtrans's
            // own verified gross_amount for the Snap path (MidtransWebhookController), or from
            // gatewayAmount() at the moment an admin verifies a manual bank transfer
            // (Admin\PlanChangeRequestController::approveManual()) - there is no independent
            // confirmation of the transferred amount beyond the admin's own verification there.
            $table->unsignedInteger('amount_paid')->nullable()->after('discount_amount');

            // The plan period this specific request activated, so a request keeps its own
            // history even after a later request moves organizations.plan_expires_at forward
            // again. active_from mirrors PlanChangeRequestService::approve()'s $baseline (now,
            // or the organization's still-future plan_expires_at for a timely same-plan
            // renewal); active_until is active_from plus duration_months, matching what actually
            // got written to organizations.plan_expires_at at that moment.
            $table->timestamp('active_from')->nullable()->after('amount_paid');
            $table->timestamp('active_until')->nullable()->after('active_from');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plan_change_requests', function (Blueprint $table) {
            $table->dropColumn(['amount_paid', 'active_from', 'active_until']);
        });
    }
};
