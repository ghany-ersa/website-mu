<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Achievements - competition results a school puts on its wall of fame, filtered by category
 * on the public `prestasi` section.
 *
 * `year` is a string, not a date or integer: the source data records only the year, and some
 * entries are a span ("2024/2025"). It sorts correctly as a string for four-digit years and is
 * only ever displayed, never computed with.
 *
 * category/level are plain strings validated against Achievement::CATEGORIES / ::LEVELS rather
 * than DB enums - SQLite (dev) emulates enums as check constraints that later migrations can't
 * easily alter, and these lists are likely to grow.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('category')->nullable();
            $table->string('level')->nullable();
            // Placing, e.g. "Juara 1" - separate from title because the cards show it as its
            // own labelled line, and because two schools word their titles differently.
            $table->string('rank')->nullable();
            $table->string('year')->nullable();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();

            $table->index(['organization_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('achievements');
    }
};
