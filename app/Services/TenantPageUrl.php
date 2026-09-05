<?php

namespace App\Services;

use App\Models\Organization;
use App\Models\OrganizationPage;
use Illuminate\Support\Facades\Route;

/**
 * Resolves the URL of one of an organization's builder pages, for links rendered inside
 * section partials (header/footer nav).
 *
 * The same partial renders in three contexts and each needs a different URL:
 *
 *  - Public tenant site: the page's own subdomain URL (tenant.home / tenant.pages.show).
 *  - Owner preview / builder on the main app domain: the equivalent preview route, because
 *    wildcard subdomains aren't routable under `php artisan serve`, so a tenant URL would be
 *    a dead link for anyone checking their site locally.
 *  - Template preview (no organization saved yet): no URL at all - the caller drops the item.
 */
class TenantPageUrl
{
    public static function for(?Organization $organization, ?OrganizationPage $page): ?string
    {
        if (! $organization || ! $page || ! $organization->exists) {
            return null;
        }

        if (request()->routeIs('organizations.preview*', 'organizations.builder*')) {
            return $page->is_home
                ? route('organizations.preview', $organization)
                : route('organizations.preview.page', ['organization' => $organization, 'page' => $page->slug]);
        }

        if (! Route::has('tenant.home')) {
            return null;
        }

        return $page->is_home
            ? route('tenant.home', ['organization_slug' => $organization->slug])
            : route('tenant.pages.show', ['organization_slug' => $organization->slug, 'page_slug' => $page->slug]);
    }
}
