{{-- Admin-only custom domain panel - see Admin\OrganizationDomainController for the
     register -> check -> activate flow and why tenants can't do this themselves. --}}
@php
    $domain = $organization->customDomain;
    $featureOn = config('tenancy.custom_domains');
    $slotsFull = $customDomainSlots !== null && $customDomainsUsed >= $customDomainSlots;
    $serverIp = request()->server('SERVER_ADDR');
@endphp

<div id="custom-domain" class="bg-white rounded-2xl shadow-soft p-6 mb-6">
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2 mb-4">
        <div>
            <h2 class="font-bold text-gray-800">Custom Domain</h2>
            <p class="text-xs text-gray-400 mt-0.5">
                Tayangkan situs di domain milik organisasi (mis. pcmambulu.or.id). Setelah aktif, subdomain lama
                otomatis dialihkan ke domain ini.
            </p>
        </div>
        <p class="shrink-0 text-xs text-gray-500">
            Slot hosting terpakai:
            <span class="font-semibold {{ $slotsFull ? 'text-red-500' : 'text-gray-800' }}">
                {{ $customDomainsUsed }}{{ $customDomainSlots !== null ? ' / '.$customDomainSlots : '' }}
            </span>
        </p>
    </div>

    @unless ($featureOn)
        <div class="mb-4 rounded-lg bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 text-sm">
            Fitur custom domain belum dinyalakan di server (<code>CUSTOM_DOMAINS_ENABLED=false</code>). Domain bisa
            didaftarkan, tetapi Cek Koneksi akan gagal dan domain belum dilayani sampai fitur dinyalakan.
        </div>
    @endunless

    @if ($errors->domain->any())
        <div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-600 px-4 py-3 text-sm">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->domain->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($domain)
        <div class="rounded-xl border {{ $domain->is_active ? 'border-secondary/30 bg-secondary/5' : 'border-gray-200 bg-gray-50' }} p-4 mb-4">
            <div class="flex flex-wrap items-center gap-2 mb-1">
                <p class="font-semibold text-gray-800 break-all">{{ $domain->hostname }}</p>
                @if ($domain->is_active)
                    <span class="px-2 py-1 rounded-full bg-secondary/10 text-secondary text-xs font-semibold">Aktif</span>
                @else
                    <span class="px-2 py-1 rounded-full bg-gray-200 text-gray-600 text-xs font-semibold">Belum aktif</span>
                @endif
            </div>
            @if ($domain->is_active && $domain->activated_at)
                <p class="text-xs text-gray-500">
                    Diaktifkan {{ $domain->activated_at->translatedFormat('d M Y, H:i') }}
                    @if ($domain->activatedBy) oleh {{ $domain->activatedBy->name }} @endif
                </p>
            @endif
            @if ($domain->last_checked_at)
                <p class="text-xs mt-2 {{ $domain->last_check_ok ? 'text-secondary' : 'text-red-500' }}">
                    {{ $domain->last_check_result }}
                    <span class="text-gray-400">({{ $domain->last_checked_at->translatedFormat('d M Y, H:i') }})</span>
                </p>
            @else
                <p class="text-xs text-gray-400 mt-2">Belum pernah dicek.</p>
            @endif

            <div class="flex flex-wrap gap-2 mt-4">
                <form action="{{ route('admin.organizations.domain.check', $organization) }}" method="POST">
                    @csrf
                    <button type="submit" class="px-4 py-2 rounded-full border border-gray-200 bg-white text-xs font-semibold text-gray-700 hover:border-primary/40 hover:text-primary cursor-pointer">
                        Cek Koneksi
                    </button>
                </form>

                @if ($domain->is_active)
                    <form action="{{ route('admin.organizations.domain.deactivate', $organization) }}" method="POST"
                          x-data @submit.prevent="if (await confirmAction('Nonaktifkan custom domain? Situs akan kembali tayang di subdomain.', { danger: false })) $el.submit()">
                        @csrf
                        <button type="submit" class="px-4 py-2 rounded-full border border-gray-200 bg-white text-xs font-semibold text-gray-700 hover:border-primary/40 hover:text-primary cursor-pointer">
                            Nonaktifkan
                        </button>
                    </form>
                @else
                    <form action="{{ route('admin.organizations.domain.activate', $organization) }}" method="POST"
                          x-data="{ host: @js($domain->hostname) }" @submit.prevent="if (await confirmAction('Aktifkan ' + host + '? Subdomain lama akan dialihkan ke domain ini.', { danger: false })) $el.submit()">
                        @csrf
                        <button type="submit" @disabled(! $domain->last_check_ok)
                                class="px-4 py-2 rounded-full bg-primary text-white text-xs font-semibold hover:bg-primary/90 cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed">
                            Aktifkan
                        </button>
                    </form>
                @endif

                <form action="{{ route('admin.organizations.domain.destroy', $organization) }}" method="POST"
                      x-data="{ host: @js($domain->hostname) }" @submit.prevent="if (await confirmAction('Lepas ' + host + ' dari organisasi ini?')) $el.submit()">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 rounded-full text-xs font-semibold text-red-500 hover:bg-red-50 cursor-pointer">
                        Lepas Domain
                    </button>
                </form>
            </div>
        </div>
    @endif

    <form action="{{ route('admin.organizations.domain.update', $organization) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="grid sm:grid-cols-2 gap-4 mb-4">
            <div>
                <label for="hostname" class="block text-sm font-semibold text-gray-700 mb-1">Domain</label>
                <input type="text" name="hostname" id="hostname" required placeholder="pcmambulu.or.id"
                       value="{{ old('hostname', $domain?->hostname) }}" autocomplete="off" autocapitalize="none"
                       class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
                <p class="text-xs text-gray-400 mt-1">Mengganti domain akan menonaktifkannya sampai dicek ulang.</p>
            </div>
            <div>
                <label for="domain_notes" class="block text-sm font-semibold text-gray-700 mb-1">Catatan (opsional)</label>
                <input type="text" name="notes" id="domain_notes" placeholder="mis. Alias cPanel ditambahkan 29 Okt"
                       value="{{ old('notes', $domain?->notes) }}"
                       class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
            </div>
        </div>
        <button type="submit" @disabled(! $domain && $slotsFull)
                class="px-5 py-2.5 rounded-full bg-primary text-white text-sm font-semibold hover:bg-primary/90 transition-colors cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed">
            {{ $domain ? 'Simpan Domain' : 'Daftarkan Domain' }}
        </button>
    </form>

    <details class="mt-5 text-sm text-gray-600">
        <summary class="cursor-pointer font-semibold text-gray-700">Langkah pemasangan</summary>
        <ol class="list-decimal list-inside space-y-2 mt-3 text-xs sm:text-sm">
            <li>
                Pemilik domain menambahkan record DNS: <code>A @</code> ke IP server
                @if ($serverIp) (terdeteksi: <code>{{ $serverIp }}</code>, cocokkan dengan "Shared IP Address" di cPanel) @endif
                dan <code>CNAME www</code> ke domain tersebut. Jika DNS domain dikelola di Cloudflare, set keduanya
                <strong>DNS only (awan abu-abu)</strong> sampai domain aktif - jika di-proxy, AutoSSL gagal dan Cek Koneksi berakhir HTTP 525.
            </li>
            <li>
                Di cPanel &rarr; Domains &rarr; Create A New Domain: masukkan domain, lalu arahkan Document Root ke folder yang
                <strong>sama persis dengan <code>*.{{ config('tenancy.domain') }}</code></strong> (folder <code>public/</code> aplikasi).
                Cocokkan di daftar Domains; jika sama dengan domain utama, cukup centang <em>Share document root</em>.
            </li>
            <li>Di cPanel &rarr; SSL/TLS Status: jalankan <em>Run AutoSSL</em> untuk domain dan www-nya.</li>
            <li>Daftarkan domain di form ini, klik <strong>Cek Koneksi</strong> sampai berhasil, lalu <strong>Aktifkan</strong>.</li>
        </ol>
    </details>
</div>
