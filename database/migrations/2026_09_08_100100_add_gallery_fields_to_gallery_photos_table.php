<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Widens `gallery_photos` for the SD-Madya gallery: a titled, categorised photo that can be
 * filtered on the public page, rather than just an image with a caption.
 *
 * `caption` is kept as-is and stays the short overlay text; `title` is the heading shown on
 * hover in the filtered bento grid, and `description` the longer blurb. All nullable, so the
 * existing `galeri` sections and rows are unaffected.
 *
 * `category` is a plain string rather than a DB enum - SQLite (dev) has no native enum and
 * Laravel emulates it as a check constraint that later migrations cannot easily alter.
 * Allowed values are validated in the controller instead: Kegiatan, Prestasi, Fasilitas,
 * Lainnya (mirroring comprof-SD-Madya's Activity/Achievement/Facility/Other).
 *
 * Deliberately NOT ported: the source project's galleries.activity_id FK. It only ever fed the
 * /activities/{slug} API endpoint, which no page in the site renders.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gallery_photos', function (Blueprint $table) {
            $table->string('title')->nullable()->after('url');
            $table->text('description')->nullable()->after('caption');
            $table->string('category')->nullable()->after('description');
            $table->boolean('is_active')->default(true)->after('category');
        });
    }

    public function down(): void
    {
        Schema::table('gallery_photos', function (Blueprint $table) {
            $table->dropColumn(['title', 'description', 'category', 'is_active']);
        });
    }
};
