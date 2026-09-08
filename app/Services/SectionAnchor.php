<?php

namespace App\Services;

class SectionAnchor
{
    /**
     * Anchor id for a rendered section wrapper.
     *
     * Two flavours exist because sections are rendered from two different sources:
     *
     *  - Organization sites (organizations/pages/_render.blade.php) render persisted
     *    OrganizationSection rows, so the wrapper is keyed by the row's primary key.
     *  - Template previews (templates/preview.blade.php) render sections straight out of
     *    the template's `structure` JSON, where no such id exists - those are keyed by the
     *    section key instead.
     *
     * A "scroll" CTA stores whichever identifier its authoring context had: the builder's
     * dropdown saves an OrganizationSection id, while template JSON is authored with
     * section keys. Both must resolve, so this returns the anchor for either form.
     */
    public static function id(int|string $identifier): string
    {
        return 'section-'.$identifier;
    }

    /**
     * Href for a "scroll" CTA target, or null when nothing was chosen.
     *
     * On a template preview a numeric target is meaningless - those ids belong to another
     * organization's rows and match no element on the page - so it is dropped rather than
     * rendered as a link that silently scrolls nowhere.
     */
    public static function href(mixed $target, bool $isPreview = false): ?string
    {
        if (blank($target)) {
            return null;
        }

        $target = (string) $target;

        if ($isPreview && ctype_digit($target)) {
            return null;
        }

        return '#'.self::id($target);
    }
}
