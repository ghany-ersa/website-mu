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
            $table->string('midtrans_order_id')->nullable()->unique()->after('discount_amount');
            $table->string('midtrans_transaction_id')->nullable()->after('midtrans_order_id');
            $table->string('midtrans_payment_type')->nullable()->after('midtrans_transaction_id');
            // Raw transaction_status from the latest webhook (settlement, pending, expire, ...),
            // kept separate from the internal `status` enum for audit/debugging.
            $table->string('midtrans_status')->nullable()->after('midtrans_payment_type');
            $table->timestamp('midtrans_paid_at')->nullable()->after('midtrans_status');
            // Exception message from the last failed PlanChangeRequestService::approve() call
            // after a settled payment — shown to the admin next to the "Coba Lagi" button.
            $table->text('approve_error')->nullable()->after('midtrans_paid_at');
            $table->unsignedTinyInteger('approve_attempts')->default(0)->after('approve_error');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plan_change_requests', function (Blueprint $table) {
            $table->dropColumn([
                'midtrans_order_id',
                'midtrans_transaction_id',
                'midtrans_payment_type',
                'midtrans_status',
                'midtrans_paid_at',
                'approve_error',
                'approve_attempts',
            ]);
        });
    }
};
