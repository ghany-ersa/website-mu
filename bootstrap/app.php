<?php

use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\SetFrameOptions;
use App\Http\Middleware\UseReadOnlyConnection;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\SubstituteBindings;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
        ]);

        // Public tenant sites (routes/web.php's Route::domain(...) group): deliberately
        // NOT the default 'web' group. Those routes are pure reads with no login and no
        // forms (see OrganizationSiteController) - StartSession/VerifyCsrfToken would
        // write to the sessions table on every single page view for no reason, which
        // also rules out ever giving that path a SELECT-only DB user. This group keeps
        // just cookie decryption (so shared cookies from the main domain don't error)
        // and route-model binding, then UseReadOnlyConnection swaps the DB connection.
        $middleware->group('tenant', [
            EncryptCookies::class,
            SubstituteBindings::class,
            SetFrameOptions::class,
            UseReadOnlyConnection::class,
        ]);

        $middleware->append(SetFrameOptions::class);

        // Midtrans calls this directly (no session, no CSRF token) - signature verification
        // in MidtransWebhookController is the actual auth for this route.
        $middleware->validateCsrfTokens(except: [
            'webhooks/midtrans',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
