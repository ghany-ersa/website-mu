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

];
