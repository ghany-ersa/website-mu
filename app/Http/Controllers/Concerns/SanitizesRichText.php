<?php

namespace App\Http\Controllers\Concerns;

use Mews\Purifier\Facades\Purifier;

/**
 * Sanitizes HTML coming from the Tiptap rich text editor (Post::body, Announcement::body,
 * Agenda::description) before it's persisted, using the 'cms_richtext' HTMLPurifier profile
 * (config/purifier.php) — a whitelist of formatting tags only, no script/style/img/on*
 * attributes. Called once at the controller boundary, right before create()/update(), so
 * every render of these fields afterwards can trust the stored HTML is already safe.
 */
trait SanitizesRichText
{
    protected function sanitizeRichText(?string $html): ?string
    {
        if ($html === null || trim($html) === '') {
            return null;
        }

        return Purifier::clean($html, 'cms_richtext');
    }
}
