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
        Schema::create('organization_limit_overrides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            // Same key namespace as plan_limits.key (e.g. 'posts', 'sections_total').
            $table->string('key');
            // Null means unlimited for this tenant, overriding whatever the plan says.
            $table->unsignedInteger('max_count')->nullable();
            $table->string('note')->nullable();
            $table->timestamps();

            $table->unique(['organization_id', 'key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organization_limit_overrides');
    }
};
