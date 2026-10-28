<?php

namespace App\Http\Middleware;

use App\Enums\OrganizationStatus;
use App\Models\Organization;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves which organization a public tenant request is for, once, before any controller
 * runs - so OrganizationSiteController never sees how the tenant was identified, only the
 * Organization itself (read back via ResolveTenantOrganization::from()).
 *
 * Today the only identifier is the {organization_slug} subdomain parameter. The point of
 * pulling this out of the controller is the planned custom-domain support: a request on an
 * organization's own domain carries no slug at all, just a Host header, and resolving it here
 * means every tenant route works on either host without each controller action branching.
 *
 * The published + non-sandbox filter lives in the lookup query itself so a Draft or sandbox
 * organization 404s identically to one that was never claimed, instead of leaking its
 * existence. Deliberately bare (no eager loading): on a TenantPageCache hit nothing else
 * about the organization is used at all - see OrganizationSiteController::loadForRender().
 *
 * Stored in request attributes rather than swapped in as a route parameter: Laravel passes
 * route parameters to controller actions positionally, so a model set via setParameter()
 * lands *after* {announcement}/{agenda} and arrives in the wrong argument. The slug parameter
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

        $organization = Organization::excludingSandbox()
            ->where('slug', $route->parameter('organization_slug'))
            ->where('status', OrganizationStatus::Published)
            ->firstOrFail();

        $route->forgetParameter('organization_slug');
        $request->attributes->set(self::ATTRIBUTE, $organization);

        return $next($request);
    }

    public static function from(Request $request): Organization
    {
        return $request->attributes->get(self::ATTRIBUTE);
    }
}
