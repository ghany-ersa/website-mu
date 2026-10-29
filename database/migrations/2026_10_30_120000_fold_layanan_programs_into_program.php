<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Finishes folding `layanan` into program-unggulan. The earlier fold_layanan_section migration
 * kept services and programs as two Program pools, picked per section by a `source` field;
 * that per-widget choice was dropped as more distinction than users wanted. So here:
 *
 *  - `layanan` Program rows become `program` rows, ordered after the organization's existing
 *    programs, so one list holds both.
 *  - `source` is stripped from program-unggulan content (organization pages and template
 *    structures) - nothing reads it any more. Any `layanan` section the earlier migration
 *    somehow missed is converted too, so this stands on its own.
 *  - "Program Sosial Klinik" is removed from both clinic templates: with one list, it would
 *    render the same cards as the services grid beside it. Only templates are pruned - an
 *    organization's own pages are left as they are.
 *
 * Plain query builder so later model changes can't break replaying it. Not reversible: which
 * rows used to be `layanan` is not recorded.
 */
return new class extends Migration
{
    private const KLINIK_TEMPLATE_SLUGS = ['klinik-aisyiyah-ambulu-standar', 'klinik-aisyiyah-ambulu-eksklusif'];

    public function up(): void
    {
        $organizationIds = DB::table('programs')->where('type', 'layanan')->distinct()->pluck('organization_id');

        foreach ($organizationIds as $organizationId) {
            $nextOrder = (int) DB::table('programs')
                ->where('organization_id', $organizationId)
                ->where('type', 'program')
                ->max('order') + 1;

            DB::table('programs')
                ->where('organization_id', $organizationId)
                ->where('type', 'layanan')
                ->orderBy('order')
                ->orderBy('id')
                ->get(['id'])
                ->each(function ($program) use (&$nextOrder) {
                    DB::table('programs')->where('id', $program->id)->update(['type' => 'program', 'order' => $nextOrder++]);
                });
        }

        DB::table('organization_sections')->whereIn('key', ['layanan', 'program-unggulan'])->orderBy('id')->each(function ($section) {
            $content = json_decode($section->content ?? '[]', true) ?: [];
            unset($content['source']);

            DB::table('organization_sections')->where('id', $section->id)->update([
                'key' => 'program-unggulan',
                'content' => json_encode($content),
            ]);
        });

        DB::table('templates')->orderBy('id')->each(function ($template) {
            $structure = json_decode($template->structure ?? '', true);

            if (! is_array($structure) || ! isset($structure['pages'])) {
                return;
            }

            $original = $structure;
            $isKlinik = in_array($template->slug, self::KLINIK_TEMPLATE_SLUGS, true);

            foreach ($structure['pages'] as $pageIndex => $page) {
                $sections = [];

                foreach ($page['sections'] ?? [] as $section) {
                    $key = $section['key'] ?? null;

                    if ($key === 'layanan' || $key === 'program-unggulan') {
                        $section['key'] = 'program-unggulan';
                        unset($section['content']['source']);

                        if ($isKlinik && ($section['content']['title'] ?? null) === 'Program Sosial Klinik') {
                            continue;
                        }
                    }

                    $sections[] = $section;
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
