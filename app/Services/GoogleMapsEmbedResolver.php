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
 */
class GoogleMapsEmbedResolver
{
    public static function resolve(?string $url): ?string
    {
        $url = trim((string) $url);

        if ($url === '') {
            return null;
        }

        if (str_contains($url, '/maps/embed')) {
            return $url;
        }

        if (! str_contains($url, 'google.com/maps') && ! str_contains($url, 'goo.gl')) {
            return null;
        }

        $resolved = self::followRedirects($url) ?? $url;

        $query = self::extractQuery($resolved);

        if ($query === null) {
            return null;
        }

        return self::embedUrlFor($query);
    }

    /**
     * maps.app.goo.gl links resolve via HTTP redirect to the full google.com/maps/place/...
     * URL that actually carries the coordinates/place name we need.
     */
    private static function followRedirects(string $url): ?string
    {
        try {
            $response = Http::withUserAgent('Mozilla/5.0 (compatible; website-mu/1.0)')
                ->timeout(5)
                ->get($url);

            return (string) $response->effectiveUri();
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
