<?php

namespace App\Services;

use App\Models\Organization;
use App\Models\OrganizationPage;

/**
 * Resolves the URL of one of an organization's builder pages, for links rendered inside
 * section partials (header/footer nav).
 *
 * The same partial renders in three contexts and each needs a different URL:
 *
 *  - Public tenant site: the page's own tenant URL (tenant.home / tenant.pages.show), built
 *    through TenantUrl so it follows whichever host the organization is served on.
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

        return $page->is_home
            ? TenantUrl::route($organization, 'tenant.home')
            : TenantUrl::route($organization, 'tenant.pages.show', ['page_slug' => $page->slug]);
    }

    /**
     * URL of a pupil's public profile, or null when there isn't one to link to - which is the
     * case in template preview (no organization yet) and whenever tenant routes aren't
     * registered at all, i.e. local `php artisan serve` with no wildcard subdomain configured.
     * Callers render plain text instead of a dead link.
     */
    public static function student(?Organization $organization, string $slug): ?string
    {
        if (! $organization || ! $organization->exists) {
            return null;
        }

        return TenantUrl::route($organization, 'tenant.students.show', ['student_slug' => $slug]);
    }
}
