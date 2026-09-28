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
        Schema::table('templates', function (Blueprint $table) {
            // Distinct from is_active: is_active gates every template surface at once
            // (organization-creation picker, template-switch picker, public catalog, home page).
            // is_public gates ONLY the public catalog (TemplateController::index()) and the home
            // page's featured grid - a template can stay pickable when creating/switching an
            // organization's template while being hidden from public browsing, e.g. a "Halaman
            // Kosong" starting point that exists only as a picker option, not a showcase piece.
            $table->boolean('is_public')->default(true)->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('templates', function (Blueprint $table) {
            $table->dropColumn('is_public');
        });
    }
};
