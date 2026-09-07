<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Platform admins (is_admin, gated to /admin/* via EnsureUserIsAdmin) bypass every
        // policy check - every OrganizationPolicy method requires membership, which an admin
        // troubleshooting or supporting a tenant otherwise wouldn't have. Runs before any
        // specific policy method, so new policies get the same bypass automatically.
        Gate::before(fn (User $user) => $user->is_admin ? true : null);

        // Without this, url()/route() generate http:// links whenever a TLS-terminating
        // proxy (e.g. Cloudflare) forwards requests to the app over plain HTTP - causing
        // canonical tags, sitemap entries, and the /use redirect target to disagree with
        // the https:// URL Google actually requested, which reads to it as a duplicate/
        // mismatched canonical.
        if ($this->app->isProduction()) {
            URL::forceScheme('https');
        }
    }
}
