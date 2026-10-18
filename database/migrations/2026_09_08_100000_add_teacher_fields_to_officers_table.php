<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Widens `officers` so it can back a school's teaching staff as well as a branch's board of
 * officers - the "Teacher" entity of the standalone comprof-SD-Madya project maps onto this
 * table rather than getting one of its own, since both are "a person with a titled role at the
 * organization, shown with a photo" and Officer::role already carries "Kepala Sekolah" or
 * "Guru Kelas 6A" as happily as "Ketua".
 *
 * Every column here is nullable (is_active defaults true), so the four existing templates that
 * render `struktur-pengurus` keep working untouched and existing rows stay valid. The
 * `sekolahmu` variant is the one that actually reads subject/bio.
 *
 * slug is unique per organization, not globally - two schools may both have a "budi-santoso".
 * It is generated in the controller (see OrganizationPostController::uniqueSlug), matching how
 * every other slugged model here works, rather than in the model's boot().
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('officers', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('organization_id');
            $table->string('subject')->nullable()->after('role');
            $table->text('bio')->nullable()->after('subject');
            $table->string('email')->nullable()->after('photo');
            $table->string('phone')->nullable()->after('email');
            $table->boolean('is_active')->default(true)->after('phone');

            $table->unique(['organization_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::table('officers', function (Blueprint $table) {
            $table->dropUnique(['organization_id', 'slug']);
            $table->dropColumn(['slug', 'subject', 'bio', 'email', 'phone', 'is_active']);
        });
    }
};
