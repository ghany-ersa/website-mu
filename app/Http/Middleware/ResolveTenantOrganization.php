<?php

namespace App\Http\Middleware;

use App\Enums\OrganizationStatus;
use App\Models\Organization;
use App\Models\OrganizationDomain;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves which organization a public tenant request is for, once, before any controller
 * runs - so OrganizationSiteController never sees how the tenant was identified, only the
 * Organization itself (read back via ResolveTenantOrganization::from()).
 *
 * Two ways in, matching the two route groups in routes/web.php that include routes/tenant.php:
 *
 *  - {organization_slug} subdomain: looked up by slug. If that organization has an activated
 *    custom domain, the request is 301'd there instead - one canonical host, so search engines
 *    never see the same site twice and old subdomain links/QR codes keep working.
 *  - {custom_host}: looked up by hostname among *activated* domains only, accepting the www/
 *    non-www counterpart too and 301'ing it to the registered form. A domain that's
 *    registered but not yet activated 404s, since an admin activates only after the hosting
 *    side (alias + SSL) is confirmed working.
 *
 * The published + non-sandbox filter lives in the lookup query itself so a Draft or sandbox
 * organization 404s identically to one that was never claimed, instead of leaking its
 * existence. Otherwise deliberately bare: on a TenantPageCache hit nothing else about the
 * organization is used - see OrganizationSiteController::loadForRender(). customDomain is the
 * one exception, eager-loaded because the redirect check (and TenantUrl, on a miss) needs it.
 *
 * Stored in request attributes rather than swapped in as a route parameter: Laravel passes
 * route parameters to controller actions positionally, so a model set via setParameter()
 * lands *after* {announcement}/{agenda} and arrives in the wrong argument. The host parameter
 * is forgotten for the same reason - left in, it would still be passed as the first argument.
 *
 * Runs inside the 'tenant' middleware group after UseReadOnlyConnection (bootstrap/app.php),
 * so the lookup already goes through the SELECT-only connection.
 */
class ResolveTenantOrganization
{
    private const ATTRIBUTE = 'tenant.organization';

    public function handle(Request $request, Closure $next): Response
    {
        $route = $request->route();

        if ($route->hasParameter('custom_host')) {
            return $this->viaCustomDomain($request, $route, $next);
        }

        $organization = $this->published()
            ->where('slug', $route->parameter('organization_slug'))
            ->firstOrFail();

        if ($hostname = $organization->activeCustomHostname()) {
            return redirect()->away($request->getScheme().'://'.$hostname.$request->getRequestUri(), 301);
        }

        $route->forgetParameter('organization_slug');
        $request->attributes->set(self::ATTRIBUTE, $organization);

        return $next($request);
    }

    public static function from(Request $request): Organization
    {
        return $request->attributes->get(self::ATTRIBUTE);
    }

    private function viaCustomDomain(Request $request, Route $route, Closure $next): Response
    {
        $host = strtolower($request->getHost());

        $domain = OrganizationDomain::query()
            ->where('is_active', true)
            ->whereIn('hostname', [$host, OrganizationDomain::wwwCounterpart($host)])
            ->first();

        $organization = $domain ? $this->published()->whereKey($domain->organization_id)->first() : null;

        abort_if($organization === null, 404);

        if ($domain->hostname !== $host) {
            return redirect()->away($request->getScheme().'://'.$domain->hostname.$request->getRequestUri(), 301);
        }

        $route->forgetParameter('custom_host');
        $request->attributes->set(self::ATTRIBUTE, $organization);

        return $next($request);
    }

    /**
     * @return Builder<Organization>
     */
    private function published(): Builder
    {
        return Organization::excludingSandbox()
            ->with('customDomain')
            ->where('status', OrganizationStatus::Published);
    }
}
