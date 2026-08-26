<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
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
        // policy check — every OrganizationPolicy method requires membership, which an admin
        // troubleshooting or supporting a tenant otherwise wouldn't have. Runs before any
        // specific policy method, so new policies get the same bypass automatically.
        Gate::before(fn (User $user) => $user->is_admin ? true : null);
    }
}
