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
        Schema::create('plan_limits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained()->cascadeOnDelete();
            // e.g. 'posts', 'agendas', 'sections_total' — free-form so new CMS resources
            // only need a seeded row, not a schema change.
            $table->string('key');
            // Null means unlimited for this plan/key.
            $table->unsignedInteger('max_count')->nullable();
            $table->timestamps();

            $table->unique(['plan_id', 'key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plan_limits');
    }
};
