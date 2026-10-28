<?php

namespace App\Services;

use App\Models\Organization;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;

/**
 * The one place that decides which host an organization's public site lives on, and builds
 * absolute URLs to it. Every link into a tenant site (section partials, detail-page back
 * links, the sitemap, TenantPageUrl) goes through here instead of calling
 * route('tenant.*', ['organization_slug' => ...]) directly.
 *
 * Why not just route(): the tenant routes are registered under the subdomain pattern, so
 * route() can only ever produce {slug}.{tenancy.domain}. Custom domains (planned) need the
 * same paths on a different host - so the path comes from the named route (relative,
 * absolute=false, keeping routes/tenant.php the single definition of every tenant path) and
 * the host comes from host(), which is the only thing that has to change to point links at
 * an organization's own domain.
 */
class TenantUrl
{
    /**
     * Absolute URL of a named tenant route for this organization, or null when tenant routes
     * aren't registered at all (TENANT_DOMAIN unset, e.g. local `php artisan serve`) - callers
     * fall back to '#' or plain text rather than a dead link.
     *
     * @param  array<string, mixed>  $parameters  Route parameters other than organization_slug.
     */
    public static function route(Organization $organization, string $name, array $parameters = []): ?string
    {
        $host = self::host($organization);

        if ($host === null || ! Route::has($name)) {
            return null;
        }

        $path = route($name, ['organization_slug' => $organization->slug, ...$parameters], false);

        // rtrim matches route()'s own output for the home route ("https://host", no trailing
        // slash), so rendered HTML stays byte-identical to what route() produced.
        return rtrim(URL::formatScheme().$host.self::portSuffix().$path, '/');
    }

    /**
     * The host this organization's public site is served on, or null when tenant routing is
     * off (TENANT_DOMAIN unset).
     */
    public static function host(Organization $organization): ?string
    {
        $domain = config('tenancy.domain');

        return $domain ? $organization->slug.'.'.$domain : null;
    }

    /**
     * Mirrors RouteUrlGenerator::addPortToDomain(), which route() applies to every domain
     * route: a non-default port on the current request (e.g. `:8000` in local dev) is carried
     * onto the tenant host, so links keep working on a dev server. Production on 80/443 gets
     * none.
     */
    private static function portSuffix(): string
    {
        $request = request();
        $port = (int) $request->getPort();

        return ($request->isSecure() && $port === 443) || (! $request->isSecure() && $port === 80)
            ? ''
            : ':'.$port;
    }
}
