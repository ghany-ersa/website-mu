<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds the agenda poster - the flyer masjid share for a kajian, which the `poster` variant of
 * the `agenda` section renders as the card's image.
 *
 * A media-library URL stored as a string, matching every other image field in the CMS
 * (masjid_facilities.photo, donation_programs.cover_photo) rather than a separate attachment
 * table - see the x-form.image-picker component both ends use.
 *
 * The column is on `agendas` itself, not tied to the variant that displays it: an organization
 * that uploads posters then switches variants keeps them, and any future variant can show them.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agendas', function (Blueprint $table) {
            $table->string('poster')->nullable()->after('title');
        });
    }

    public function down(): void
    {
        Schema::table('agendas', function (Blueprint $table) {
            $table->dropColumn('poster');
        });
    }
};
