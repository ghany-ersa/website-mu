<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Baseline security headers for every response - the app and the public tenant sites alike
 * (it's in both the global stack and the 'tenant' group, see bootstrap/app.php).
 *
 * The CSP is deliberately narrow: it does NOT restrict script-src. The builder, Alpine
 * components and JSON-LD blocks all rely on inline scripts (and Alpine's expression
 * evaluator needs 'unsafe-eval'), so a script-src policy would have to allow both and would
 * then stop nothing - including `javascript:` URLs, which script-src governs. What it does
 * restrict costs nothing to keep working: plugins, <base> hijacking, which origins may be
 * framed (only the builder's own canvas, Google Maps and YouTube are ever embedded), and who
 * may frame us. The real defence against tenant-supplied URLs is SafeUrl at save and render.
 */
class SetSecurityHeaders
{
    private const CONTENT_SECURITY_POLICY = "object-src 'none'; "
        ."base-uri 'self'; "
        ."frame-src 'self' https://www.google.com https://maps.google.com https://www.youtube.com https://www.youtube-nocookie.com; "
        ."frame-ancestors 'self'";

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Content-Security-Policy', self::CONTENT_SECURITY_POLICY);

        // Only over HTTPS in production: sending HSTS from a local http:// dev server would pin
        // the browser to https for localhost. No includeSubDomains - whether every tenant
        // subdomain is reliably served over TLS is a deployment fact this code can't vouch for.
        if ($request->isSecure() && app()->isProduction()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000');
        }

        return $response;
    }
}
