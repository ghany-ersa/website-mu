<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Renames the `sewa-aula` section (and its page slug) to `akad-venue`.
 *
 * Many masjid deliberately avoid rental wording for a place of worship - "sewa" frames the use
 * of religious space as a commercial transaction - and ask for a voluntary infak instead. The
 * user-facing copy moved to that framing, and the internal key followed so the two don't drift.
 *
 * Also seeds the new `infak_note` content field on existing sections, since that is what now
 * carries the mosque's stance. Only sections that don't already have one are touched, so a
 * re-run can't overwrite wording a takmir has since written themselves.
 */
return new class extends Migration
{
    private const DEFAULT_INFAK_NOTE = 'Tidak ada tarif sewa, jamaah dipersilakan berinfak semampunya.';

    public function up(): void
    {
        DB::table('section_variants')
            ->where('section_key', 'sewa-aula')
            ->update([
                'section_key' => 'akad-venue',
                'view' => 'templates.sections.akad-venue.nurul-huda',
                'updated_at' => now(),
            ]);

        foreach (DB::table('organization_sections')->where('key', 'sewa-aula')->get() as $section) {
            $content = json_decode($section->content ?? '', true);
            $content = is_array($content) ? $content : [];

            if (! array_key_exists('infak_note', $content)) {
                $content['infak_note'] = self::DEFAULT_INFAK_NOTE;
            }

            DB::table('organization_sections')->where('id', $section->id)->update([
                'key' => 'akad-venue',
                'content' => json_encode($content),
                'updated_at' => now(),
            ]);
        }

        DB::table('organization_pages')
            ->where('slug', 'sewa-aula')
            ->update(['slug' => 'akad-venue', 'updated_at' => now()]);

        $this->rewriteTemplates('sewa-aula', 'akad-venue', true);
    }

    public function down(): void
    {
        DB::table('section_variants')
            ->where('section_key', 'akad-venue')
            ->update([
                'section_key' => 'sewa-aula',
                'view' => 'templates.sections.sewa-aula.nurul-huda',
                'updated_at' => now(),
            ]);

        DB::table('organization_sections')
            ->where('key', 'akad-venue')
            ->update(['key' => 'sewa-aula', 'updated_at' => now()]);

        DB::table('organization_pages')
            ->where('slug', 'akad-venue')
            ->update(['slug' => 'sewa-aula', 'updated_at' => now()]);

        $this->rewriteTemplates('akad-venue', 'sewa-aula', false);
    }

    /**
     * Rewrites the section key and page slug inside each template's `structure` JSON.
     *
     * Decoded and re-encoded per row rather than a blind string replace: the same templates'
     * prose (descriptions, sample copy) mentions the word, and a textual swap would corrupt it.
     */
    private function rewriteTemplates(string $from, string $to, bool $seedInfakNote): void
    {
        foreach (DB::table('templates')->get() as $template) {
            $structure = json_decode($template->structure ?? '', true);

            if (! is_array($structure) || ! is_array($structure['pages'] ?? null)) {
                continue;
            }

            $changed = false;

            foreach ($structure['pages'] as $pageIndex => $page) {
                if (($page['slug'] ?? null) === $from) {
                    $structure['pages'][$pageIndex]['slug'] = $to;
                    $changed = true;
                }

                foreach ($page['sections'] ?? [] as $sectionIndex => $section) {
                    if (($section['key'] ?? null) !== $from) {
                        continue;
                    }

                    $structure['pages'][$pageIndex]['sections'][$sectionIndex]['key'] = $to;

                    if ($seedInfakNote && ! isset($section['content']['infak_note'])) {
                        $structure['pages'][$pageIndex]['sections'][$sectionIndex]['content']['infak_note'] = self::DEFAULT_INFAK_NOTE;
                    }

                    $changed = true;
                }
            }

            if ($changed) {
                DB::table('templates')->where('id', $template->id)->update([
                    'structure' => json_encode($structure),
                    'updated_at' => now(),
                ]);
            }
        }
    }
};
