<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\OrganizationDomain;
use App\Services\CustomDomainCheck;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * The admin-only "Custom Domain" panel on an organization's admin page. Admin-only by
 * decision, not by omission: the hosting allows a limited number of attached domains
 * (tenancy.custom_domain_slots) and each one needs manual hosting work (cPanel alias +
 * AutoSSL) before it can serve anything, so tenants request a domain and an admin attaches it.
 *
 * The flow is register -> check -> activate, and activation is the only step with public
 * effect (the site moves hosts, the subdomain starts redirecting). It is refused until the
 * latest connection check for the *current* hostname passed - re-registering a different
 * hostname wipes the previous result - so an admin can't move a live site onto a domain that
 * doesn't answer yet.
 */
class OrganizationDomainController extends Controller
{
    public function update(Request $request, Organization $organization): RedirectResponse
    {
        $request->merge(['hostname' => OrganizationDomain::normalize((string) $request->input('hostname'))]);
        $domain = $organization->customDomain;

        $validated = $request->validateWithBag('domain', [
            'hostname' => ['required', 'string', 'max:253', $this->hostnameRule($domain)],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $slots = config('tenancy.custom_domain_slots');
        if ($domain === null && $slots !== null && OrganizationDomain::count() >= $slots) {
            return back()->withInput()->withErrors(['hostname' => "Kuota custom domain hosting sudah penuh ({$slots} slot). Lepas domain organisasi lain atau naikkan CUSTOM_DOMAIN_SLOTS setelah menambah kuota hosting."], 'domain');
        }

        $attributes = ['hostname' => $validated['hostname'], 'notes' => $validated['notes'] ?? null];

        if ($domain?->hostname !== $validated['hostname']) {
            $attributes += [
                'is_active' => false,
                'last_check_ok' => null,
                'last_check_result' => null,
                'last_checked_at' => null,
                'activated_by' => null,
                'activated_at' => null,
            ];
        }

        $organization->customDomain()->updateOrCreate([], $attributes);

        return $this->back($organization, 'Custom domain disimpan. Tambahkan domain di cPanel, lalu jalankan Cek Koneksi.');
    }

    public function check(Organization $organization, CustomDomainCheck $check): RedirectResponse
    {
        $domain = $organization->customDomain;
        abort_if($domain === null, 404);

        $check->run($domain);

        return $this->back($organization, $domain->last_check_result);
    }

    public function activate(Organization $organization): RedirectResponse
    {
        $domain = $organization->customDomain;
        abort_if($domain === null, 404);

        if (! $domain->last_check_ok) {
            return back()->withErrors(['hostname' => 'Jalankan Cek Koneksi sampai berhasil sebelum mengaktifkan domain.'], 'domain');
        }

        $domain->update(['is_active' => true, 'activated_by' => Auth::id(), 'activated_at' => now()]);

        return $this->back($organization, "Custom domain {$domain->hostname} aktif. Subdomain lama kini dialihkan ke domain ini.");
    }

    public function deactivate(Organization $organization): RedirectResponse
    {
        $domain = $organization->customDomain;
        abort_if($domain === null, 404);

        $domain->update(['is_active' => false]);

        return $this->back($organization, 'Custom domain dinonaktifkan. Situs kembali tayang di subdomain.');
    }

    public function destroy(Organization $organization): RedirectResponse
    {
        $organization->customDomain?->delete();

        return $this->back($organization, 'Custom domain dilepas. Jangan lupa hapus juga domainnya dari cPanel agar slot hosting kembali.');
    }

    private function back(Organization $organization, string $status): RedirectResponse
    {
        return redirect()->route('admin.organizations.show', $organization)->with('status', $status);
    }

    /**
     * Shape, platform-host and uniqueness checks on the already-normalized hostname. The
     * www/non-www counterpart counts as taken too, because ResolveTenantOrganization serves
     * both variants of a registered hostname.
     */
    private function hostnameRule(?OrganizationDomain $current): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail) use ($current): void {
            if (! preg_match('/^(?=.{1,253}$)(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z][a-z0-9-]{1,62}$/', $value)) {
                $fail('Format domain tidak valid. Contoh: pcmambulu.or.id');

                return;
            }

            $platformHosts = array_filter([config('tenancy.domain'), parse_url(config('app.url'), PHP_URL_HOST)]);
            foreach ($platformHosts as $platformHost) {
                if ($value === $platformHost || str_ends_with($value, '.'.$platformHost)) {
                    $fail('Domain platform tidak bisa dipakai sebagai custom domain.');

                    return;
                }
            }

            $taken = OrganizationDomain::query()
                ->whereIn('hostname', [$value, OrganizationDomain::wwwCounterpart($value)])
                ->when($current, fn ($query) => $query->whereKeyNot($current->getKey()))
                ->exists();

            if ($taken) {
                $fail('Domain ini sudah dipakai organisasi lain.');
            }
        };
    }
}
