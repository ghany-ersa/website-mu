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
        Schema::table('section_variants', function (Blueprint $table) {
            // Lets an admin finish/test a new layout (via the read-only preview route) before
            // offering it to organizations - distinct from is_exclusive (a plan gate on an
            // already-offered variant) and from config/page-builder.php's section-level `hidden`
            // flag (hides a whole section type, code-only, no admin UI). Defaults true so every
            // existing row stays offered with no backfill needed.
            $table->boolean('is_visible')->default(true)->after('is_exclusive');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('section_variants', function (Blueprint $table) {
            $table->dropColumn('is_visible');
        });
    }
};
