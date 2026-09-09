<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Folds the 'jadwal-kajian' section key into 'agenda'.
 *
 * The two were never really different sections: both read the same `agendas` table through the
 * same CMS, exposed the same fields, and their Blade views had drifted into being byte-for-byte
 * identical - the only thing that actually differed was the wording ("Jadwal Kajian" vs "Agenda
 * Kegiatan"), which is not enough to justify a section of its own.
 *
 * Converted sections land on `standar`, not the `poster` variant that replaced the wording-only
 * one: poster is exclusive and needs agendas.poster filled in, so silently moving every existing
 * kajian section onto it would both hand a lower plan a premium layout and render a grid of
 * empty placeholders. Organizations opt into it from the builder once they upload flyers.
 *
 * Existing rows are rewritten rather than dropped, so a tenant's kajian section keeps its
 * title/limit content and its position on the page; only its key and variant change.
 *
 * Note that templates.structure is JSON stored as text, so it is rewritten with a decode/encode
 * round-trip per row rather than a blind string replace, which would also corrupt any prose
 * (descriptions, sample copy) that happens to contain the phrase.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('organization_sections')
            ->where('key', 'jadwal-kajian')
            ->update(['key' => 'agenda', 'variant' => 'standar', 'updated_at' => now()]);

        $this->rewriteTemplates('jadwal-kajian', 'standar', 'agenda', 'standar');

        DB::table('section_variants')->where('section_key', 'jadwal-kajian')->delete();

        // Only register the new variant on a database that already has a variant registry.
        // On a fresh install the table is empty at migrate time and SectionVariantSeeder is
        // what fills it - inserting here would leave a one-row table, and callers that treat a
        // non-empty table as "already seeded" (tests/TestCase.php does) would then skip that
        // seeder and run against a registry containing nothing but this row.
        if (DB::table('section_variants')->exists()) {
            DB::table('section_variants')->updateOrInsert(
                ['section_key' => 'agenda', 'variant_key' => 'poster'],
                [
                    'view' => 'templates.sections.agenda.poster',
                    'is_exclusive' => true,
                    'is_default' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            );
        }
    }

    /**
     * Splits 'jadwal-kajian' back out into its own section key.
     *
     * Converted sections are indistinguishable from agenda/standar sections that predate this
     * migration - both are agenda/standar - so only the templates are reverted, from their own
     * structure. Tenant sections stay as `agenda`, which still renders correctly; this is a
     * deliberately lossy rollback rather than one that guesses which rows to move back and
     * risks demoting a genuine agenda section.
     */
    public function down(): void
    {
        $this->rewriteTemplates('agenda', 'standar', 'jadwal-kajian', 'standar');

        DB::table('section_variants')
            ->where('section_key', 'agenda')
            ->where('variant_key', 'poster')
            ->delete();

        // Guarded for the same reason as up(): never turn an empty registry into a one-row one.
        if (DB::table('section_variants')->exists()) {
            DB::table('section_variants')->updateOrInsert(
                ['section_key' => 'jadwal-kajian', 'variant_key' => 'standar'],
                [
                    'view' => 'templates.sections.jadwal-kajian.standar',
                    'is_exclusive' => false,
                    'is_default' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            );
        }
    }

    /**
     * Rewrites every matching section entry inside each template's `structure` JSON, across all
     * of its pages. Templates predate multi-page support, so `pages` may be absent - a structure
     * without it is left untouched.
     */
    private function rewriteTemplates(string $fromKey, string $fromVariant, string $toKey, string $toVariant): void
    {
        foreach (DB::table('templates')->get() as $template) {
            $structure = json_decode($template->structure ?? '', true);

            if (! is_array($structure) || ! isset($structure['pages']) || ! is_array($structure['pages'])) {
                continue;
            }

            $changed = false;

            foreach ($structure['pages'] as $pageIndex => $page) {
                foreach ($page['sections'] ?? [] as $sectionIndex => $section) {
                    if (($section['key'] ?? null) !== $fromKey) {
                        continue;
                    }

                    // Only touch entries on the variant this migration is moving, so a template
                    // that had pinned some other agenda variant keeps it.
                    if (($section['variant'] ?? null) !== $fromVariant) {
                        continue;
                    }

                    $structure['pages'][$pageIndex]['sections'][$sectionIndex]['key'] = $toKey;
                    $structure['pages'][$pageIndex]['sections'][$sectionIndex]['variant'] = $toVariant;
                    $changed = true;
                }
            }

            if ($changed) {
                DB::table('templates')
                    ->where('id', $template->id)
                    ->update(['structure' => json_encode($structure), 'updated_at' => now()]);
            }
        }
    }
};
