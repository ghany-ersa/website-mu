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
        Schema::table('users', function (Blueprint $table) {
            // The person's own contact number, so CS can reach them directly (there's no email
            // service, see CLAUDE.md) - deliberately separate from organizations.whatsapp, which
            // is the org's *public* contact shown on its tenant site, and one user can manage
            // several orgs. Nullable because accounts created before this column existed have
            // none; registration requires it from here on (RegisteredUserController).
            $table->string('whatsapp', 20)->nullable()->after('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('whatsapp');
        });
    }
};
