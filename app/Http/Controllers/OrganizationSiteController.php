<?php

namespace App\Http\Controllers;

use App\Enums\OrganizationStatus;
use App\Models\Organization;
use Illuminate\View\View;

class OrganizationSiteController extends Controller
{
    /**
     * Render an organization's published site at its subdomain. Unauthenticated and
     * unauthorized by design — once Published, the site is public. The status filter
     * lives in the lookup query itself so a Draft organization's subdomain 404s
     * identically to one that was never claimed, instead of leaking its existence.
     */
    public function show(string $organization_slug): View
    {
        $organization = Organization::where('slug', $organization_slug)
            ->where('status', OrganizationStatus::Published)
            ->firstOrFail();

        $organization->load('pages.sections');
        $page = $organization->pages->firstWhere('is_home', true) ?? $organization->pages->first();
        abort_if($page === null, 404);

        return view('organizations.public.show', [
            'organization' => $organization,
            'page' => $page,
        ]);
    }
}
