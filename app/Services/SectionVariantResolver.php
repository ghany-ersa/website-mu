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
        $rows = self::variantsFor($key);

        if ($rows->isEmpty()) {
            return "templates.sections.{$key}";
        }

        $default = $rows->firstWhere('is_default', true) ?? $rows->first();
        $resolved = $variant !== null ? $rows->firstWhere('variant_key', $variant) : null;

        return ($resolved ?? $default)->view;
    }

    /**
     * Whether picking $variant for $key requires Organization::canUseExclusiveTemplates() —
     * read from that variant's own `is_exclusive` column, never inferred from its name. Enforced
     * in OrganizationSectionController::update() and mirrored in the builder's own dropdown
     * (organizations/builder/edit.blade.php) so an unauthorized option is never even offered.
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
