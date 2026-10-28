<?php

namespace App\Services;

use App\Models\OrganizationDomain;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

/**
 * The admin's "Cek koneksi" for a custom domain: fetches
 * https://{hostname}/.well-known/website-mu-check and expects this app's own token for that
 * hostname back.
 *
 * One HTTPS round-trip proves everything activation depends on at once - DNS points at this
 * server, the hosting has the domain attached to this app's document root (cPanel alias), and
 * a valid certificate is installed (AutoSSL) - which is why there's no separate DNS lookup
 * gate. The token is an HMAC of the hostname under APP_KEY: another server can't answer
 * correctly, and a domain wired to a different app (or cPanel's default page) fails cleanly.
 * The resolved IPs are only gathered to make a failure message actionable.
 */
class CustomDomainCheck
{
    public static function token(string $hostname): string
    {
        return hash_hmac('sha256', 'custom-domain-check|'.strtolower($hostname), (string) config('app.key'));
    }

    /**
     * Runs the check and records its outcome on the domain (last_check_*), which is what
     * activation is gated on.
     */
    public function run(OrganizationDomain $domain): bool
    {
        [$ok, $message] = $this->probe($domain->hostname);

        $domain->update([
            'last_check_ok' => $ok,
            'last_check_result' => $message,
            'last_checked_at' => now(),
        ]);

        return $ok;
    }

    /**
     * @return array{0: bool, 1: string}
     */
    private function probe(string $hostname): array
    {
        $resolved = gethostbynamel($hostname) ?: [];
        $where = $resolved ? 'DNS mengarah ke '.implode(', ', $resolved).'.' : 'DNS belum mengarah ke mana pun.';

        try {
            $response = Http::timeout(10)->withoutRedirecting()
                ->get('https://'.$hostname.'/.well-known/website-mu-check');
        } catch (ConnectionException $e) {
            $reason = str_contains(strtolower($e->getMessage()), 'ssl') || str_contains(strtolower($e->getMessage()), 'certificate')
                ? 'sertifikat SSL belum terpasang/valid (jalankan AutoSSL di cPanel).'
                : 'server tidak bisa dihubungi.';

            return [false, 'Gagal: '.$reason.' '.$where];
        }

        if ($response->successful() && hash_equals(self::token($hostname), trim($response->body()))) {
            return [true, 'Berhasil: domain sudah terhubung ke Website-mu dengan HTTPS.'];
        }

        return [false, 'Gagal: domain merespons (HTTP '.$response->status().'), tetapi bukan dari aplikasi Website-mu - '
            .'pastikan domain sudah ditambahkan di cPanel dengan document root yang sama seperti wildcard subdomain. '.$where];
    }
}
