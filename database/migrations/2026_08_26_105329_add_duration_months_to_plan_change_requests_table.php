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
            // Default only exists to satisfy SQLite's NOT NULL-with-existing-rows restriction;
            // every row created going forward always sets this explicitly (see
            // OrganizationPlanController::store()).
            $table->unsignedTinyInteger('duration_months')->default(3)->after('requested_plan_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plan_change_requests', function (Blueprint $table) {
            $table->dropColumn('duration_months');
        });
    }
};
