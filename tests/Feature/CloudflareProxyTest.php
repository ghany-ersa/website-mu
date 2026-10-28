<?php

namespace Tests\Feature;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Pins the trusted-proxy setup in bootstrap/app.php (see App\Support\Cloudflare): the client
 * IP comes from X-Forwarded-For only when the request really arrived from Cloudflare, and a
 * forwarded host is never honoured.
 */
class CloudflareProxyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::get('/_test/client', fn (Request $request) => [
            'ip' => $request->ip(),
            'host' => $request->getHost(),
            'secure' => $request->isSecure(),
        ]);
    }

    public function test_the_client_ip_and_scheme_come_from_cloudflare_headers(): void
    {
        $this->withServerVariables(['REMOTE_ADDR' => '172.68.10.20'])
            ->withHeaders(['X-Forwarded-For' => '203.0.113.9', 'X-Forwarded-Proto' => 'https'])
            ->getJson('/_test/client')
            ->assertJson(['ip' => '203.0.113.9', 'secure' => true]);
    }

    public function test_a_local_reverse_proxy_in_front_of_the_app_is_seen_through(): void
    {
        $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->withHeaders(['X-Forwarded-For' => '203.0.113.9, 172.68.10.20'])
            ->getJson('/_test/client')
            ->assertJson(['ip' => '203.0.113.9']);
    }

    public function test_forwarded_headers_from_anyone_else_are_ignored(): void
    {
        $this->withServerVariables(['REMOTE_ADDR' => '198.51.100.7'])
            ->withHeaders(['X-Forwarded-For' => '203.0.113.9'])
            ->getJson('/_test/client')
            ->assertJson(['ip' => '198.51.100.7']);
    }

    public function test_a_forwarded_host_is_never_trusted(): void
    {
        $this->withServerVariables(['REMOTE_ADDR' => '172.68.10.20'])
            ->withHeaders(['X-Forwarded-Host' => 'pcm-ambulu.website-mu.test'])
            ->getJson('http://localhost/_test/client')
            ->assertJson(['host' => 'localhost']);
    }
}
