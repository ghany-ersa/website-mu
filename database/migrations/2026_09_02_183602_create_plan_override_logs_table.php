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
        Schema::create('plan_override_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            // Set only when this row was created by the "retry approve" action on a stuck
            // PlanChangeRequest (see PlanChangeRequestStatus::PaymentReceivedNeedsReview) —
            // null for a direct admin plan override that bypasses PlanChangeRequest entirely.
            $table->foreignId('plan_change_request_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('admin_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('action');
            $table->foreignId('from_plan_id')->nullable()->constrained('plans')->nullOnDelete();
            $table->foreignId('to_plan_id')->nullable()->constrained('plans')->nullOnDelete();
            $table->timestamp('from_expires_at')->nullable();
            $table->timestamp('to_expires_at')->nullable();
            $table->text('note')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plan_override_logs');
    }
};
