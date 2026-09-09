<?php

namespace App\Services;

use App\Models\SectionVariant;
use Illuminate\Support\Collection;

/**
 * Resolves a section key to the Blade view that should render it, backed by the
 * section_variants table (App\Models\SectionVariant) - not config/page-builder.php, which no
 * longer carries variant data. A key with no rows in that table resolves straight to its single
 * flat file (templates.sections.{key}), unaffected by any of this.
 *
 * For a key that does have rows, the layout to use is picked in order:
 *   1. An explicit $variant (an OrganizationSection's own `variant` column, or a template
 *      structure entry's `variant` key), if it names a variant the table actually has.
 *   2. Whichever row is flagged `is_default` for that key.
 */
class SectionVariantResolver
{
    public static function resolve(string $key, ?string $variant = null): string
    {
        return self::resolveFrom(self::variantsFor($key), $key, $variant);
    }

    /**
     * Resolves every (key, variant) pair in one pass against a single preloaded set of rows,
     * instead of resolve() 's one query per call - for a page with many sections
     * (organizations/pages/_render.blade.php), that's the difference between one
     * section_variants query for the whole page and one per section. Callers fetch $allRows
     * themselves (e.g. via variantsForKeys()) so this stays a pure in-memory lookup.
     *
     * @param  Collection<int, SectionVariant>  $allRows
     */
    public static function resolveFrom(Collection $allRows, string $key, ?string $variant = null): string
    {
        $rows = $allRows->where('section_key', $key);

        if ($rows->isEmpty()) {
            return "templates.sections.{$key}";
        }

        $default = $rows->firstWhere('is_default', true) ?? $rows->first();
        $resolved = $variant !== null ? $rows->firstWhere('variant_key', $variant) : null;

        return ($resolved ?? $default)->view;
    }

    /**
     * All variant rows for several section keys in one query - see resolveFrom().
     *
     * @param  iterable<string>  $keys
     * @return Collection<int, SectionVariant>
     */
    public static function variantsForKeys(iterable $keys): Collection
    {
        $keys = collect($keys)->unique()->values();

        if ($keys->isEmpty()) {
            return collect();
        }

        return SectionVariant::whereIn('section_key', $keys)->get();
    }

    /**
     * Whether picking $variant for $key requires Organization::canUseExclusiveTemplates() —
     * read from that variant's own `is_exclusive` column, never inferred from its name. Enforced
     * in OrganizationSectionController::update(), which is the authority: the builder's dropdown
     * (organizations/builder/edit.blade.php) lists an unauthorized variant as a disabled option
     * rather than hiding it, so the plan's value stays visible, and a crafted POST naming one is
     * rejected there — it falls back to the section's existing variant.
     */
    public static function isExclusive(string $key, string $variant): bool
    {
        return (bool) self::variantsFor($key)->firstWhere('variant_key', $variant)?->is_exclusive;
    }

    /**
     * All variant rows for a section key. Deliberately NOT cached in a static property across
     * calls - a static cache would outlive a single HTTP request only by accident (it happens to
     * get a fresh PHP process per-request in production, but not across tests in one PHPUnit
     * process, where a stale cached row silently outlives the RefreshDatabase reset that's
     * supposed to invalidate it - this bit a toggle-then-verify test before this comment was
     * written). The query itself is a cheap indexed lookup against a small table.
     *
     * @return Collection<int, SectionVariant>
     */
    public static function variantsFor(string $key): Collection
    {
        return SectionVariant::where('section_key', $key)->get();
    }
}
