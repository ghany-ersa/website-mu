<?php

use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\SetFrameOptions;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

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

        $middleware->append(SetFrameOptions::class);

        // Midtrans calls this directly (no session, no CSRF token) — signature verification
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
