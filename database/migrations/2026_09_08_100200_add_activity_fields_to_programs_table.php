<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Widens `programs` so it can also back a school's activities ("kegiatan") - the Activity
 * entity of comprof-SD-Madya. No new table: `programs` already carries a `type` discriminator
 * (program / layanan, see OrganizationProgramController::type), and an activity is the same
 * shape - a titled, described, illustrated thing the organization runs. It becomes a third
 * type, `kegiatan`.
 *
 * `icon` (an emoji) already exists and stays the fallback when no image is set; `image` is the
 * photo the sekolahmu activity cards use. `category` groups them on the public page
 * (Religious/Extracurricular/... in the source project); `date` is optional since a recurring
 * habit like Sholat Dhuha has no single date.
 *
 * All nullable, so existing program/layanan rows and the four templates rendering
 * `program-unggulan` are untouched. `category` is a plain string, not a DB enum - SQLite has
 * no native enum and Laravel emulates it as a check constraint that is painful to alter later;
 * allowed values live in Program::CATEGORIES and are enforced by the controller.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->string('image')->nullable()->after('icon');
            $table->string('category')->nullable()->after('image');
            $table->date('date')->nullable()->after('category');
        });
    }

    public function down(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->dropColumn(['image', 'category', 'date']);
        });
    }
};
