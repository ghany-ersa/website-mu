<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Renames the sole variant of the five premium mosque sections from 'standar' to 'nurul-huda'.
 *
 * These arrived with the Masjid Nurul Huda exclusive template and have never had a
 * non-exclusive variant, so calling them 'standar' - which everywhere else in the registry means
 * "the layout any plan may use" - misrepresented them. SectionVariantSeeder is updated in step
 * for fresh installs; existing databases need this migration for both the registry rows and the
 * organization_sections.variant values that point at them, or those sections would fall back to
 * templates/sections/_missing.blade.php once the Blade files are renamed.
 *
 * Scoped by section_key so it can't touch the unrelated 'standar' variants of other sections.
 */
return new class extends Migration
{
    private const SECTION_KEYS = [
        'fasilitas-masjid',
        'donasi-progress',
        'laporan-keuangan',
        'kalkulator-zakat',
        'sewa-aula',
    ];

    public function up(): void
    {
        $this->rename('standar', 'nurul-huda');
    }

    public function down(): void
    {
        $this->rename('nurul-huda', 'standar');
    }

    private function rename(string $from, string $to): void
    {
        // Per key rather than one bulk UPDATE with a SQL concat: `view` embeds the section key,
        // and the concat operator differs across drivers (this app runs SQLite, but the .env
        // still carries a MySQL line) - five statements is a cheap price for portability.
        foreach (self::SECTION_KEYS as $key) {
            DB::table('section_variants')
                ->where('section_key', $key)
                ->where('variant_key', $from)
                ->update([
                    'variant_key' => $to,
                    'view' => "templates.sections.{$key}.{$to}",
                    'updated_at' => now(),
                ]);
        }

        DB::table('organization_sections')
            ->whereIn('key', self::SECTION_KEYS)
            ->where('variant', $from)
            ->update(['variant' => $to, 'updated_at' => now()]);
    }
};
