<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Students - a school's pupils, shown as an achievement showcase rather than a directory: the
 * public `/siswa/{slug}` page pairs a pupil's profile with a timeline of everything they've won
 * (see OrganizationSiteController::student).
 *
 * Deliberately its own table rather than a generalisation of an existing one. It was tempting
 * to fold this into a shared "members/jamaah" entity, but almost nothing here (grade, quote,
 * hobbies, enrolled/graduated year) means anything for a mosque congregation or a branch's
 * membership - it would be a school table wearing a general name. Types that don't need it
 * simply never get the section, exactly like MasjidFacility or FinancialReport.
 *
 * slug is unique per organization, not globally, and generated in the controller like every
 * other slugged model here.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('slug');
            $table->string('name');
            $table->string('grade')->nullable();
            $table->string('enrolled_year')->nullable();
            $table->string('graduated_year')->nullable();
            $table->text('bio')->nullable();
            $table->string('quote')->nullable();
            // List of short interest labels, rendered as chips on the profile.
            $table->json('hobbies')->nullable();
            $table->string('photo')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();

            $table->unique(['organization_id', 'slug']);
            $table->index(['organization_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
