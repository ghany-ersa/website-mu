<?php

namespace App\Services;

/**
 * Resolves which content fields a section exposes, given the variant it is currently rendered
 * with.
 *
 * config/page-builder.php's `fields` is the set EVERY variant of a section shares. A layout that
 * carries extra fields of its own declares them under `variant_fields` keyed by variant, rather
 * than widening `fields` for everybody: the hero's `sekolahmu` layout is the only two-image one
 * (`portrait` plus the highlight card over its corner), and listing those in `fields` put three
 * editors that do nothing on the other four hero variants.
 *
 * Both readers go through here so they can't disagree:
 *   - OrganizationSectionController::update() rebuilds `content` from this list and keeps
 *     nothing else, so a field missing here is silently dropped on the next save.
 *   - organizations/builder/edit.blade.php renders one editor per field in this list.
 *
 * A section with no `variant_fields` entry - which is every section but the hero today - resolves
 * to exactly its `fields`, so nothing else changes.
 */
class PageBuilderFields
{
    /**
     * @return array<int, string> The shared fields, followed by any this variant adds.
     */
    public static function for(string $sectionKey, ?string $variant): array
    {
        $section = config("page-builder.sections.{$sectionKey}");

        if (! is_array($section)) {
            return [];
        }

        $fields = $section['fields'] ?? [];

        // A null/unknown variant resolves to the shared set: better to omit an editor the
        // layout might have than to persist a field nothing renders.
        $extra = $variant === null
            ? []
            : ($section['variant_fields'][$variant] ?? []);

        return array_values(array_unique([...$fields, ...$extra]));
    }

    /**
     * Every field any variant of this section can carry.
     *
     * Used where the variant isn't known or isn't the point - e.g. checking whether a section has
     * any editable content at all. Never use it to build the list that update() persists: that
     * would let a variant keep fields a different layout owns.
     *
     * @return array<int, string>
     */
    public static function all(string $sectionKey): array
    {
        $section = config("page-builder.sections.{$sectionKey}");

        if (! is_array($section)) {
            return [];
        }

        $fields = $section['fields'] ?? [];

        foreach ($section['variant_fields'] ?? [] as $variantFields) {
            $fields = [...$fields, ...$variantFields];
        }

        return array_values(array_unique($fields));
    }
}
