<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Backs the `testimoni` section with a CMS resource.
 *
 * That section used to be content-only: its quotes lived in the section's own `items` array, on
 * the reasoning that testimonials are written once and rarely change. In practice that made them
 * the only people-with-photos on a page that cannot be managed from the CMS - an owner had to
 * edit a repeater inside the page builder to add an alumnus, while every comparable list
 * (officers, achievements, gallery) has its own screen. It also meant the quotes could not be
 * reordered, deactivated, or carried across a template switch the way CMS records are.
 *
 * `role` is free text rather than a reference to Student: the people quoted are usually alumni or
 * parents, i.e. exactly the ones who are NOT current pupils, so pointing this at the students
 * table would fit the minority case and force a placeholder row for everyone else.
 *
 * is_active mirrors Achievement/Officer - an owner hides a quote without deleting it (a speaker
 * withdrawing consent is the obvious case, and losing the text on the way out would be worse).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->text('quote');
            $table->string('name');
            // "Alumni Tahun 2005", "Wali Murid Kelas IV" - the attribution line under the name.
            $table->string('role')->nullable();
            $table->string('photo')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();

            $table->index(['organization_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('testimonials');
    }
};
