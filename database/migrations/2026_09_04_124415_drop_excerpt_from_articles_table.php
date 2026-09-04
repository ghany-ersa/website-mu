<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Article::excerpt is dropped in favor of deriving the summary from body (strip_tags +
 * Str::limit) for article cards and meta descriptions - see resources/views/articles/*.blade.php
 * and resources/views/welcome.blade.php - matching how Post::excerpt was dropped earlier.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn('excerpt');
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->text('excerpt')->nullable();
        });
    }
};
