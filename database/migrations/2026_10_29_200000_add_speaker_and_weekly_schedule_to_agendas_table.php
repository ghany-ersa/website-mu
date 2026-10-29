<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Agendas in practice are mostly kajian rutin - "Kajian Tafsir setiap Kamis ba'da Maghrib" -
 * which the one-date-only form forced tenants to re-enter every week. And the field the public
 * page actually needs for a kajian is who is speaking, not who to call.
 *
 * `repeats_weekly` reuses `starts_at` for the weekday rather than adding a day column: for a
 * weekly agenda only starts_at's weekday carries meaning (Agenda::nextOccurrence() derives the
 * actual date), so ordering, the existing index and every reader of starts_at keep working
 * without a nullable date to guard against.
 *
 * Its time is free text in `time_label`, not starts_at's clock time: kajian are scheduled as
 * "Ba'da Maghrib" or "05.30 - selesai" far more often than as an exact HH:MM, and a time
 * picker can't hold either.
 *
 * `contact_person` is no longer read or written, but deliberately NOT dropped here: tenants
 * have typed real names and phone numbers into it, and dropping a column is the one step that
 * can't be undone. Drop it in a later migration once nobody wants that data back.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agendas', function (Blueprint $table) {
            $table->boolean('repeats_weekly')->default(false)->after('starts_at');
            $table->string('time_label')->nullable()->after('repeats_weekly');
            $table->string('speaker')->nullable()->after('location');
        });
    }

    public function down(): void
    {
        Schema::table('agendas', function (Blueprint $table) {
            $table->dropColumn(['repeats_weekly', 'time_label', 'speaker']);
        });
    }
};
