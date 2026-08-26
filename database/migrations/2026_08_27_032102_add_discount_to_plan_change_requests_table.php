<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plan_change_requests', function (Blueprint $table) {
            $table->foreignId('discount_code_id')->nullable()->after('duration_months')->constrained()->nullOnDelete();
            // Snapshot of the voucher's rupiah value at request time, so editing/deleting the
            // code later doesn't change the amount already agreed to on a past request.
            $table->unsignedInteger('discount_amount')->default(0)->after('discount_code_id');
        });
    }

    public function down(): void
    {
        Schema::table('plan_change_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('discount_code_id');
            $table->dropColumn('discount_amount');
        });
    }
};
