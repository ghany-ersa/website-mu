<?php

namespace App\Services;

/**
 * Resolves a section key to the Blade view that should render it. A key with no `variants`
 * entry in config/page-builder.php resolves straight to its single flat file
 * (templates.sections.{key}), unaffected by any of this.
 *
 * For a key that does have `variants`, the layout to use is picked in order:
 *   1. An explicit $variant (an OrganizationSection's own `variant` column, or a template
 *      structure entry's `variant` key), if it names a variant the registry actually has.
 *   2. The registry's own `default_variant` for that key.
 */
class SectionVariantResolver
{
    public static function resolve(string $key, ?string $variant = null): string
    {
        $variants = config("page-builder.sections.{$key}.variants");

        if (! $variants) {
            return "templates.sections.{$key}";
        }

        $default = config("page-builder.sections.{$key}.default_variant", 'standar');

        $resolved = $variant ?? $default;

        return $variants[$resolved] ?? $variants[$default] ?? array_values($variants)[0];
    }
}
