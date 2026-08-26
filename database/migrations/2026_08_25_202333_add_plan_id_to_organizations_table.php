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
        Schema::table('organizations', function (Blueprint $table) {
            // Nullable because existing organizations predate this column — PlanLimitService
            // treats a null plan as equivalent to the 'organization' plan (see backfill note
            // in PlanSeeder) rather than failing closed.
            $table->foreignId('plan_id')->nullable()->after('template_id')->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('plan_id');
        });
    }
};
