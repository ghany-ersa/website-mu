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
            // key => max_count (or null) for every plan_limits row, captured from the
            // requested plan at the moment this request is approved — see
            // PlanChangeRequestService::approve(). Null on requests that predate this column
            // (rejected/pending requests, or ones approved before this feature existed);
            // PlanLimitService falls back to the plan's current live limits for those.
            $table->json('limits_snapshot')->nullable()->after('duration_months');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plan_change_requests', function (Blueprint $table) {
            $table->dropColumn('limits_snapshot');
        });
    }
};
