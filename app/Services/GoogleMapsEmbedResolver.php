<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Turns whatever Google Maps URL a user pastes (a short maps.app.goo.gl link, a full
 * google.com/maps/place/... link, or an already-resolved embed URL) into a keyless
 * https://www.google.com/maps/embed?... URL safe to use as an iframe src.
 *
 * No Google Maps API key is configured for this project, so this relies on following
 * Google's own redirect chain and reading the resulting URL's `@lat,lng` (or place name)
 * rather than calling the Places/Geocoding API.
 *
 * Every host check here goes through parse_url() against an exact allowlist, never a substring
 * match. The old `str_contains($url, '/maps/embed')` / `str_contains($url, 'goo.gl')` checks let
 * `javascript:alert(1)//maps/embed` straight into an <iframe src> (stored XSS) and made the
 * server fetch any URL that merely mentioned goo.gl, e.g. `http://169.254.169.254/?goo.gl`
 * (SSRF) - see the 2026-10-28 security audit.
 */
class GoogleMapsEmbedResolver
{
    /**
     * Hosts whose /maps/embed URL may be rendered as an iframe src.
     */
    private const EMBED_HOSTS = ['www.google.com', 'google.com', 'maps.google.com'];

    /**
     * Hosts a pasted link may come from - and therefore the only hosts the server will ever
     * send a request to while following a short link.
     */
    private const INPUT_HOSTS = ['www.google.com', 'google.com', 'maps.google.com', 'goo.gl', 'maps.app.goo.gl'];

    private const MAX_REDIRECTS = 5;

    public static function resolve(?string $url): ?string
    {
        $url = trim((string) $url);

        if ($url === '') {
            return null;
        }

        if (self::isEmbedUrl($url)) {
            return $url;
        }

        if (! self::hasAllowedHost($url, self::INPUT_HOSTS)) {
            return null;
        }

        // A full /maps/place/... link already carries what we need - only a short link (or a
        // link with nothing extractable) is worth a network round trip.
        $query = self::extractQuery($url);

        if ($query === null) {
            $resolved = self::followRedirects($url);
            $query = $resolved === null ? null : self::extractQuery($resolved);
        }

        return $query === null ? null : self::embedUrlFor($query);
    }

    /**
     * Whether $url is an https Google Maps embed URL - the only shape that may become an
     * iframe src. Also used at render time (SafeUrl::mapEmbed()) so rows stored before this
     * check existed can't reach the browser either.
     */
    public static function isEmbedUrl(string $url): bool
    {
        if (preg_match('/[\x00-\x20\x7F]/', $url)) {
            return false;
        }

        $parts = parse_url($url);

        return is_array($parts)
            && strtolower($parts['scheme'] ?? '') === 'https'
            && in_array(strtolower($parts['host'] ?? ''), self::EMBED_HOSTS, true)
            && ! isset($parts['user'], $parts['pass'])
            && ! isset($parts['port'])
            && str_starts_with($parts['path'] ?? '', '/maps/embed');
    }

    /**
     * @param  array<int, string>  $hosts
     */
    private static function hasAllowedHost(string $url, array $hosts): bool
    {
        if (preg_match('/[\x00-\x20\x7F]/', $url)) {
            return false;
        }

        $parts = parse_url($url);

        return is_array($parts)
            && in_array(strtolower($parts['scheme'] ?? ''), ['http', 'https'], true)
            && in_array(strtolower($parts['host'] ?? ''), $hosts, true)
            && ! isset($parts['user'])
            && ! isset($parts['port']);
    }

    /**
     * maps.app.goo.gl links resolve via HTTP redirect to the full google.com/maps/place/...
     * URL that actually carries the coordinates/place name we need.
     *
     * Redirects are followed by hand rather than by the HTTP client, so every hop's target is
     * re-checked against INPUT_HOSTS before it's requested - otherwise an allowed first URL
     * that redirects somewhere internal would still make the server fetch it.
     */
    private static function followRedirects(string $url): ?string
    {
        try {
            for ($hop = 0; $hop <= self::MAX_REDIRECTS; $hop++) {
                if (! self::hasAllowedHost($url, self::INPUT_HOSTS)) {
                    return null;
                }

                $response = Http::withUserAgent('Mozilla/5.0 (compatible; website-mu/1.0)')
                    ->withoutRedirecting()
                    ->timeout(5)
                    ->get($url);

                $location = $response->header('Location');

                if (! $response->redirect() || $location === '') {
                    return $url;
                }

                $url = str_starts_with($location, '/')
                    ? parse_url($url, PHP_URL_SCHEME).'://'.parse_url($url, PHP_URL_HOST).$location
                    : $location;
            }

            return null;
        } catch (\Throwable $e) {
            Log::warning('Failed to resolve Google Maps share link', ['url' => $url, 'error' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * Prefers exact `@lat,lng` coordinates when present, falling back to the place name
     * segment of a /maps/place/ URL so a text-only query still gets a usable pin.
     */
    private static function extractQuery(string $url): ?string
    {
        if (preg_match('/@(-?\d+\.\d+),(-?\d+\.\d+)/', $url, $matches)) {
            return $matches[1].','.$matches[2];
        }

        if (preg_match('#/maps/place/([^/@]+)#', $url, $matches)) {
            return rawurldecode(str_replace('+', ' ', $matches[1]));
        }

        return null;
    }

    private static function embedUrlFor(string $query): string
    {
        return 'https://www.google.com/maps/embed?origin=mfe&pb=!1m2!2m1!1s'.rawurlencode($query);
    }
}
