<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * Switches the default DB connection to 'mysql_readonly' (config/database.php) for the
 * request, so the public tenant site - which never needs to write anything, see
 * OrganizationSiteController - runs against a MySQL user granted SELECT only. Applied to
 * the tenant domain route group (routes/web.php) via the 'tenant' middleware group
 * (bootstrap/app.php), which also drops session/CSRF middleware so nothing on this path
 * attempts a write in the first place.
 *
 * No-op when DB_READONLY_USERNAME isn't configured (e.g. local sqlite dev), so this never
 * breaks environments that haven't set up the read-only MySQL user yet.
 */
class UseReadOnlyConnection
{
    public function handle(Request $request, Closure $next): Response
    {
        if (config('database.connections.mysql_readonly.username') && config('database.default') !== 'sqlite') {
            DB::setDefaultConnection('mysql_readonly');
        }

        return $next($request);
    }
}
