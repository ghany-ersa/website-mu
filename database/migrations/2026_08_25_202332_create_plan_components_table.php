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
        Schema::create('plan_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained()->cascadeOnDelete();
            // Section key from config('page-builder.sections') — not a foreign key, since
            // sections are defined in config, not a database table. Only sections that are
            // restricted for a plan need a row here; any key with no row is allowed by
            // default (opt-out model), so new sections aren't accidentally locked out.
            $table->string('component_key');
            $table->boolean('is_allowed')->default(true);
            $table->timestamps();

            $table->unique(['plan_id', 'component_key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plan_components');
    }
};
