<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Post::excerpt is dropped now that body is edited via the Tiptap rich text editor and public
 * post cards/meta descriptions derive their summary from body (strip_tags + Str::limit)
 * instead - see resources/views/templates/sections/daftar-berita/*.blade.php and
 * resources/views/organizations/public/post.blade.php.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('excerpt');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->text('excerpt')->nullable();
        });
    }
};
