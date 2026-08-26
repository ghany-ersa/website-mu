<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->boolean('hide_branding')->default(false)->after('is_active');
            $table->boolean('has_exclusive_templates')->default(false)->after('hide_branding');
        });
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn(['hide_branding', 'has_exclusive_templates']);
        });
    }
};
