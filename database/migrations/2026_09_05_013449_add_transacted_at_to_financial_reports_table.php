<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Nullable on purpose: period_month/period_year stay the source of truth for grouping, and
     * rows that predate this column (or that genuinely are a whole-month recap, like the seeded
     * "Infak Jum'at" totals) have no single date to point at. A date is recorded when the entry
     * really is one dated transaction.
     */
    public function up(): void
    {
        Schema::table('financial_reports', function (Blueprint $table) {
            $table->date('transacted_at')->nullable()->after('period_year');
        });
    }

    public function down(): void
    {
        Schema::table('financial_reports', function (Blueprint $table) {
            $table->dropColumn('transacted_at');
        });
    }
};
