<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Tenant Base Domain
    |--------------------------------------------------------------------------
    |
    | The bare host suffix (no scheme) that organization subdomains are served
    | under, e.g. "website-mu.id" so an organization with slug "pcm-ambulu" is
    | reachable at "pcm-ambulu.website-mu.id". Left blank, the tenant routing
    | group in routes/web.php is never registered - safe default for local
    | `php artisan serve`, which has no wildcard subdomain to route.
    |
    */

    'domain' => env('TENANT_DOMAIN'),

    /*
    |--------------------------------------------------------------------------
    | Custom Domains
    |--------------------------------------------------------------------------
    |
    | Lets an organization's site be served on a domain it already owns (e.g.
    | "pcmambulu.or.id"), attached by an admin - see OrganizationDomain. Off by
    | default so deploying the code changes nothing until it's switched on: while
    | off, the catch-any-host route group in routes/web.php isn't registered and
    | every organization keeps its subdomain, even one with an activated domain.
    | Requires `domain` above to be set as well.
    |
    | `slots` mirrors how many extra domains the hosting package can attach (the
    | cPanel Alias/Addon Domain quota) - the admin panel refuses to register more.
    | Null means no limit is enforced.
    |
    */

    'custom_domains' => (bool) env('CUSTOM_DOMAINS_ENABLED', false),

    'custom_domain_slots' => env('CUSTOM_DOMAIN_SLOTS') !== null ? (int) env('CUSTOM_DOMAIN_SLOTS') : null,

];
