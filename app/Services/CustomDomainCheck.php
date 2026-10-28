<?php

namespace App\Services;

use App\Models\OrganizationDomain;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

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
            // Capped anyway: the resolved-IP list is unbounded in principle, and a check must
            // never 500 on its own diagnostics (it did once, before the column became text).
            'last_check_result' => Str::limit($message, 1000),
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

        // 520-527 are Cloudflare's own "couldn't reach/handshake with the origin" pages, served
        // when the owner's record is proxied (orange cloud). Seen in practice as a 525 on a
        // domain that *was* correctly attached in cPanel but had no certificate yet - and it
        // can't get one while proxied, since AutoSSL's validation hits the same failing
        // handshake. The generic "wrong document root" advice below sent the admin the wrong way.
        if ($response->status() >= 520 && $response->status() <= 527 && str_contains(strtolower($response->header('Server')), 'cloudflare')) {
            return [false, 'Gagal: Cloudflare tidak bisa terhubung aman ke server (HTTP '.$response->status().'). '
                .'Ubah record domain di Cloudflare menjadi DNS only (awan abu-abu), jalankan AutoSSL di cPanel, lalu cek lagi. '
                .'Setelah aktif, proxy boleh dinyalakan kembali dengan SSL Full (strict). '.$where];
        }

        return [false, 'Gagal: domain merespons (HTTP '.$response->status().'), tetapi bukan dari aplikasi Website-mu - '
            .'pastikan domain sudah ditambahkan di cPanel dengan document root yang sama seperti wildcard subdomain. '.$where];
    }
}
