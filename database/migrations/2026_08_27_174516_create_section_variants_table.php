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
        Schema::create('section_variants', function (Blueprint $table) {
            $table->id();
            $table->string('section_key');
            $table->string('variant_key');
            $table->string('view');
            $table->boolean('is_exclusive')->default(false);
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->unique(['section_key', 'variant_key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('section_variants');
    }
};
