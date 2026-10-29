<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Folds the `layanan` section into `program-unggulan` (see that registry entry in
 * config/page-builder.php): every `layanan` section - on organizations' pages and inside
 * templates' structure JSON - becomes a `program-unggulan` section with `source: layanan`, so it
 * keeps showing the same `layanan` Program rows. layanan only ever had a `standar` variant, and
 * program-unggulan/standar renders the identical card grid, so the variant carries over as is.
 *
 * Also drops "Program Sosial Klinik" from the Rumah Sehat template (klinik-aisyiyah-ambulu-
 * standar), mirroring its seeder: once both grids were the same widget, the second one on the
 * same home page read as a duplicate. Organizations already built from that template keep
 * theirs - this edits the template, not anyone's site.
 *
 * Plain query builder rather than models, so later model changes can't break replaying this.
 * Not reversed: `down()` would have to guess which program-unggulan sections used to be layanan.
 */
return new class extends Migration
{
    private const RUMAH_SEHAT_SLUG = 'klinik-aisyiyah-ambulu-standar';

    public function up(): void
    {
        DB::table('organization_sections')->where('key', 'layanan')->orderBy('id')->each(function ($section) {
            $content = json_decode($section->content ?? '[]', true) ?: [];

            DB::table('organization_sections')->where('id', $section->id)->update([
                'key' => 'program-unggulan',
                'content' => json_encode([...$content, 'source' => 'layanan']),
            ]);
        });

        DB::table('templates')->orderBy('id')->each(function ($template) {
            $structure = json_decode($template->structure ?? '', true);

            if (! is_array($structure) || ! isset($structure['pages'])) {
                return;
            }

            $original = $structure;

            foreach ($structure['pages'] as $pageIndex => $page) {
                $sections = [];

                foreach ($page['sections'] ?? [] as $section) {
                    if (($section['key'] ?? null) === 'layanan') {
                        $section['key'] = 'program-unggulan';
                        $section['content'] = [...($section['content'] ?? []), 'source' => 'layanan'];
                    }

                    $isRumahSehatProgramSosial = $template->slug === self::RUMAH_SEHAT_SLUG
                        && ($section['key'] ?? null) === 'program-unggulan'
                        && ($section['content']['source'] ?? 'program') === 'program'
                        && ($section['content']['title'] ?? null) === 'Program Sosial Klinik';

                    if (! $isRumahSehatProgramSosial) {
                        $sections[] = $section;
                    }
                }

                $structure['pages'][$pageIndex]['sections'] = $sections;
            }

            if ($structure !== $original) {
                DB::table('templates')->where('id', $template->id)->update(['structure' => json_encode($structure)]);
            }
        });

        DB::table('section_variants')->where('section_key', 'layanan')->delete();
    }

    public function down(): void
    {
        //
    }
};
