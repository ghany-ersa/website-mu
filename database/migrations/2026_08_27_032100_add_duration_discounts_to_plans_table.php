<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->unsignedTinyInteger('discount_percent_6')->default(0)->after('price_monthly');
            $table->unsignedTinyInteger('discount_percent_12')->default(0)->after('discount_percent_6');
        });
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn(['discount_percent_6', 'discount_percent_12']);
        });
    }
};
