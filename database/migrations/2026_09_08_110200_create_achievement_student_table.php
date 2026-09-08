<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Links achievements to the students who earned them.
 *
 * Many-to-many rather than a student_id column on `achievements`, because team events are real:
 * the source database's "Juara 2 Cerdas Cermat Agama Islam" is credited to three pupils, and a
 * single FK would silently drop two of them on import. The other direction matters too - a
 * pupil's profile lists every achievement they contributed to.
 *
 * No organization_id here: both sides already carry one, and the CRUD only ever offers students
 * from the same organization as the achievement.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('achievement_student', function (Blueprint $table) {
            $table->id();
            $table->foreignId('achievement_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['achievement_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('achievement_student');
    }
};
