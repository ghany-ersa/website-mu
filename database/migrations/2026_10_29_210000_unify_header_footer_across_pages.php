<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Header and footer became `shared` sections (config/page-builder.php): one setting per site,
 * mirrored onto every page's row on save. Rows saved before that may already differ page to
 * page, so this makes each organization's copies match once, taking the home page's as the
 * source - the page owners see first and most likely the one they actually edited. An
 * organization with no home page falls back to its lowest-ordered page.
 *
 * Plain query builder rather than models, so a later change to OrganizationSection (or its
 * cache-invalidation hook) can't break replaying this migration.
 *
 * Not reversible: the per-page differences it overwrites are not recorded anywhere.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['header', 'footer'] as $key) {
            $organizationIds = DB::table('organization_pages')->distinct()->pluck('organization_id');

            foreach ($organizationIds as $organizationId) {
                $source = DB::table('organization_sections')
                    ->join('organization_pages', 'organization_pages.id', '=', 'organization_sections.organization_page_id')
                    ->where('organization_pages.organization_id', $organizationId)
                    ->where('organization_sections.key', $key)
                    ->orderByDesc('organization_pages.is_home')
                    ->orderBy('organization_pages.order')
                    ->orderBy('organization_pages.id')
                    ->select('organization_sections.*')
                    ->first();

                if ($source === null) {
                    continue;
                }

                $pageIds = DB::table('organization_pages')->where('organization_id', $organizationId)->pluck('id');

                DB::table('organization_sections')
                    ->whereIn('organization_page_id', $pageIds)
                    ->where('key', $key)
                    ->where('id', '!=', $source->id)
                    ->update([
                        'content' => $source->content,
                        'variant' => $source->variant,
                        'is_visible' => $source->is_visible,
                        'updated_at' => now(),
                    ]);
            }
        }
    }

    public function down(): void
    {
        //
    }
};
