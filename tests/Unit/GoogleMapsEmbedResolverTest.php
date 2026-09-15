<?php

namespace Tests\Unit;

use App\Services\GoogleMapsEmbedResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Whatever this returns ends up as an <iframe src> on a public tenant page, and its input is a
 * URL a tenant pasted - so the property that matters most is what it *refuses*. Anything that
 * isn't recognisably a Google Maps URL must come back null (the partial then renders no iframe
 * at all) rather than being passed through for the browser to load.
 *
 * The short-link branch performs an HTTP redirect follow, faked here: the branch being tested is
 * how the resolved URL is parsed, not Google's redirect behaviour.
 */
class GoogleMapsEmbedResolverTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_blank_or_null_url_resolves_to_null(): void
    {
        $this->assertNull(GoogleMapsEmbedResolver::resolve(null));
        $this->assertNull(GoogleMapsEmbedResolver::resolve(''));
        $this->assertNull(GoogleMapsEmbedResolver::resolve('   '));
    }

    /**
     * An already-embeddable URL is handed back untouched - re-resolving it would cost an HTTP
     * round trip for no gain.
     */
    public function test_an_already_embeddable_url_passes_through_unchanged(): void
    {
        $embed = 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3';

        $this->assertSame($embed, GoogleMapsEmbedResolver::resolve($embed));
    }

    /**
     * The guard that keeps arbitrary user input out of an iframe src.
     */
    public function test_a_non_google_maps_url_is_refused(): void
    {
        $this->assertNull(GoogleMapsEmbedResolver::resolve('https://example.test/peta'));
        $this->assertNull(GoogleMapsEmbedResolver::resolve('https://openstreetmap.org/#map=15/-8.3/113.6'));
        $this->assertNull(GoogleMapsEmbedResolver::resolve('javascript:alert(1)'));
        $this->assertNull(GoogleMapsEmbedResolver::resolve('Masjid Nurul Huda Ambulu'));
    }

    public function test_coordinates_are_extracted_from_a_place_url(): void
    {
        $resolved = GoogleMapsEmbedResolver::resolve(
            'https://www.google.com/maps/place/Masjid+Nurul+Huda/@-8.3456789,113.6054321,17z'
        );

        $this->assertNotNull($resolved);
        $this->assertStringStartsWith('https://www.google.com/maps/embed?', $resolved);
        $this->assertStringContainsString(rawurlencode('-8.3456789,113.6054321'), $resolved);
    }

    /**
     * Without an @lat,lng the place name is used instead, so a text-only link still produces a
     * usable pin rather than nothing.
     */
    public function test_a_place_name_is_used_when_no_coordinates_are_present(): void
    {
        $resolved = GoogleMapsEmbedResolver::resolve('https://www.google.com/maps/place/Masjid+Nurul+Huda+Ambulu');

        $this->assertNotNull($resolved);
        $this->assertStringContainsString(rawurlencode('Masjid Nurul Huda Ambulu'), $resolved);
    }

    public function test_a_google_maps_url_with_nothing_extractable_resolves_to_null(): void
    {
        Http::fake(['*' => Http::response('', 200)]);

        $this->assertNull(GoogleMapsEmbedResolver::resolve('https://www.google.com/maps'));
    }

    /**
     * A short link carries no coordinates itself - they only appear on the URL Google redirects
     * to. Under Http::fake() there is no real redirect, so effectiveUri() stays the short link
     * and nothing is extractable. What this pins down is that the branch fails closed: null,
     * not an exception and not the bare short link handed to an iframe.
     *
     * The successful-redirect case isn't reachable without a real HTTP round trip to Google,
     * so it is deliberately left to manual verification rather than faked into something that
     * only tests the fake.
     */
    public function test_a_short_link_that_cannot_be_resolved_fails_closed(): void
    {
        Http::fake(['*' => Http::response('', 200)]);

        $this->assertNull(GoogleMapsEmbedResolver::resolve('https://maps.app.goo.gl/abc123'));
    }

    /**
     * A network failure while following a short link must degrade to null, not bubble a 500 up
     * through whatever page was being saved.
     */
    public function test_a_failed_redirect_lookup_degrades_to_null(): void
    {
        Http::fake(fn () => throw new \RuntimeException('network down'));

        $this->assertNull(GoogleMapsEmbedResolver::resolve('https://maps.app.goo.gl/abc123'));
    }

    public function test_the_embed_url_is_percent_encoded(): void
    {
        $resolved = GoogleMapsEmbedResolver::resolve('https://www.google.com/maps/place/Jl.+dr.+Soetomo+No.+15');

        $this->assertNotNull($resolved);
        // A raw space would break the iframe src attribute.
        $this->assertStringNotContainsString(' ', $resolved);
    }
}
