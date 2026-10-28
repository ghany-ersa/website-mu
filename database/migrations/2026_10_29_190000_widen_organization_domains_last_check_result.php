<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * last_check_result started as a 255-char string, but CustomDomainCheck's messages are full
     * sentences of advice plus the resolved IPs - the Cloudflare 52x one alone runs ~290 chars
     * and threw "Data too long" on MySQL in production (SQLite in the test suite doesn't enforce
     * the length, so tests never saw it). text, not a bigger varchar: the message is display-only
     * and never indexed or queried on.
     */
    public function up(): void
    {
        Schema::table('organization_domains', function (Blueprint $table) {
            $table->text('last_check_result')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organization_domains', function (Blueprint $table) {
            $table->string('last_check_result')->nullable()->change();
        });
    }
};
