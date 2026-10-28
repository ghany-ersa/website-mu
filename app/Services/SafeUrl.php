<?php

namespace App\Services;

/**
 * The one gate between a tenant-typed link and an href/src attribute.
 *
 * Blade's {{ }} escapes HTML, not URL schemes - `javascript:...` survives escaping untouched and
 * runs on click (in an <a href>) or on load (in an <iframe src>, where it executes in the
 * embedding page's origin). Page-builder `content` fields are free-form JSON, so unlike the CMS
 * resources (which get Laravel's `url` rule) nothing upstream had already narrowed them.
 *
 * Used twice on purpose: OrganizationSectionController::update() rejects a bad value at save
 * time so the tenant gets told, and the section partials pass every stored value through here
 * again at render time, so rows saved before this guard existed can never reach the browser.
 */
class SafeUrl
{
    /**
     * Schemes a tenant may legitimately link to from a button. Anything else (javascript:,
     * data:, vbscript:, file:, ...) is refused rather than enumerated as a blocklist - new
     * dangerous schemes don't have to be anticipated.
     */
    private const ALLOWED_SCHEMES = ['http', 'https', 'mailto', 'tel'];

    /**
     * The URL itself if it's safe to put in an href, otherwise null (the partials already treat
     * null as "render no button").
     *
     * Relative paths ("/donasi") and in-page anchors ("#kontak") are allowed, since tenants link
     * to their own pages that way; a protocol-relative "//host" is not, because it isn't what
     * anyone types for an internal link and it's an easy way to dress up an off-site URL.
     */
    public static function href(mixed $url): ?string
    {
        if (! is_string($url)) {
            return null;
        }

        $url = trim($url);

        if ($url === '') {
            return null;
        }

        // Browsers strip ASCII control characters and whitespace inside a scheme
        // ("java\tscript:"), so a value carrying any is refused outright instead of
        // trying to normalise it the same way the browser would.
        if (preg_match('/[\x00-\x20\x7F]/', $url)) {
            return null;
        }

        if (str_starts_with($url, '#') || (str_starts_with($url, '/') && ! str_starts_with($url, '//'))) {
            return $url;
        }

        $scheme = parse_url($url, PHP_URL_SCHEME);

        if (! is_string($scheme) || ! in_array(strtolower($scheme), self::ALLOWED_SCHEMES, true)) {
            return null;
        }

        return $url;
    }

    /**
     * The value if it's a Google Maps embed URL, otherwise null - for the lokasi-peta <iframe>.
     */
    public static function mapEmbed(mixed $url): ?string
    {
        return is_string($url) && GoogleMapsEmbedResolver::isEmbedUrl($url) ? $url : null;
    }
}
