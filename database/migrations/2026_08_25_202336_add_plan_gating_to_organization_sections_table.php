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
        Schema::table('organization_sections', function (Blueprint $table) {
            // Independent from is_visible (the user's manual show/hide toggle): this flag is
            // only ever set by the system when a plan change makes the section's key no longer
            // allowed, and cleared again on upgrade — without touching is_visible, so a section
            // the user deliberately hid stays hidden after an upgrade instead of reappearing.
            $table->boolean('hidden_by_plan')->default(false)->after('is_visible');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organization_sections', function (Blueprint $table) {
            $table->dropColumn('hidden_by_plan');
        });
    }
};
