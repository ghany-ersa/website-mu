<?php

namespace App\Support;

/**
 * Cloudflare's published edge ranges (https://www.cloudflare.com/ips-v4 and /ips-v6, fetched
 * 2026-10-29), trusted as proxies in bootstrap/app.php.
 *
 * website-mu.id is proxied through Cloudflare, so without this every request's $request->ip()
 * is a Cloudflare edge address - and every IP-keyed limit collapses onto a handful of shared
 * IPs: register's throttle:5,1 becomes "5 sign-ups per minute for everyone", and the login
 * lockout (email|IP) hits unrelated people behind the same edge.
 *
 * A hardcoded list rather than fetching it at runtime: it changes very rarely, a fetch would
 * add a network dependency to boot, and a stale list fails safe (an edge IP not listed just
 * isn't trusted, so ip() reports that edge IP - today's behaviour - instead of trusting a
 * spoofable header). Re-check the two URLs above occasionally.
 *
 * Loopback is trusted too: if the host puts a local reverse proxy in front of Apache, the
 * request arrives from 127.0.0.1 with Cloudflare's address already appended to
 * X-Forwarded-For, and the real client is only recoverable by trusting both hops.
 */
final class Cloudflare
{
    public const TRUSTED_PROXIES = [
        '127.0.0.1',
        '::1',
        '173.245.48.0/20',
        '103.21.244.0/22',
        '103.22.200.0/22',
        '103.31.4.0/22',
        '141.101.64.0/18',
        '108.162.192.0/18',
        '190.93.240.0/20',
        '188.114.96.0/20',
        '197.234.240.0/22',
        '198.41.128.0/17',
        '162.158.0.0/15',
        '104.16.0.0/13',
        '104.24.0.0/14',
        '172.64.0.0/13',
        '131.0.72.0/22',
        '2400:cb00::/32',
        '2606:4700::/32',
        '2803:f800::/32',
        '2405:b500::/32',
        '2405:8100::/32',
        '2a06:98c0::/29',
        '2c0f:f248::/32',
    ];
}
