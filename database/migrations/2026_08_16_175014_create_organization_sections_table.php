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
        Schema::create('organization_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_page_id')->constrained()->cascadeOnDelete();
            $table->string('key');
            $table->string('variant')->nullable();
            // Free-form to match the existing template.structure content shape and to stay
            // forward-compatible with a future CMS binding (e.g. {"source": "cms", ...})
            // without a schema change.
            $table->json('content');
            $table->unsignedInteger('order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organization_sections');
    }
};
