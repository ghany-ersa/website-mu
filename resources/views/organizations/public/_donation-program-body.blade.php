{{--
    Body of the donation-program detail page, mirroring the nurul-huda project's
    donation-programs/show.blade.php: cover, status badge, description, a progress card with
    collected/target and a WhatsApp donate button, then the donation history.

    The donate button is only rendered for an 'active' program (see DonationProgram::status())
    and only when the organization actually has a WhatsApp number - a dead "Donasi Sekarang"
    button is worse than none.
--}}
@php
    $percent = $program->progressPercent();
    $collected = $program->collectedAmount();
    $status = $program->status();

    $statusLabel = match ($status) {
        'upcoming' => 'Akan Datang',
        'active' => 'Aktif',
        'completed' => 'Selesai',
        'expired' => 'Berakhir',
    };

    $statusClass = match ($status) {
        'upcoming' => 'bg-slate-100 text-slate-600',
        'active' => 'bg-emerald-100 text-emerald-700',
        'completed' => 'bg-sky-100 text-sky-700',
        'expired' => 'bg-red-100 text-red-700',
    };

    $donateHref = $status === 'active'
        ? \App\Services\WhatsAppNumber::href(
            $organization->whatsapp ?? null,
            'Assalamu\'alaikum, saya ingin berdonasi untuk program '.$program->name.'.'
        )
        : null;

    $donationsPage = $organization->pages->firstWhere('slug', 'donasi');
    $isPreview = request()->routeIs('organizations.preview*');

    if ($isPreview) {
        // Stay inside the preview flow rather than bouncing the owner onto the tenant
        // subdomain, which isn't routable in local dev.
        $backHref = $donationsPage
            ? route('organizations.preview.page', ['organization' => $organization, 'page' => $donationsPage->slug])
            : route('organizations.preview', $organization);
    } else {
        $backHref = $donationsPage && \Illuminate\Support\Facades\Route::has('tenant.pages.show')
            ? route('tenant.pages.show', ['organization_slug' => $organization->slug, 'page_slug' => $donationsPage->slug])
            : (\Illuminate\Support\Facades\Route::has('tenant.home')
                ? route('tenant.home', ['organization_slug' => $organization->slug])
                : '#');
    }
    $backLabel = $donationsPage ? 'Kembali ke Program Donasi' : 'Kembali ke beranda';
@endphp

<article class="py-10 bg-softBg min-h-screen">
    <div class="max-w-3xl mx-auto px-6">
        <a href="{{ $backHref }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-secondary transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
            {{ $backLabel }}
        </a>

        @if ($program->cover_photo)
            <div class="mt-5 aspect-[16/9] rounded-2xl overflow-hidden bg-slate-100">
                <img src="{{ $program->cover_photo }}" alt="{{ $program->name }}" class="w-full h-full object-cover">
            </div>
        @endif

        <span class="mt-6 inline-block text-xs font-semibold px-2.5 py-1 rounded-full {{ $statusClass }}">
            {{ $statusLabel }}
        </span>
        <h1 class="mt-2 text-2xl sm:text-3xl font-bold text-primary">{{ $program->name }}</h1>

        @if ($program->description)
            <p class="mt-4 text-slate-600 leading-relaxed">{{ $program->description }}</p>
        @endif

        <div class="mt-8 bg-white border border-slate-100 rounded-2xl shadow-sm p-5 sm:p-6">
            <div class="flex items-baseline justify-between gap-4">
                <div>
                    <p class="text-xs text-slate-500 uppercase tracking-wide">Terkumpul</p>
                    <p class="text-xl sm:text-2xl font-bold text-secondary">Rp {{ number_format($collected, 0, ',', '.') }}</p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-slate-500 uppercase tracking-wide">Target</p>
                    <p class="text-sm sm:text-base font-semibold text-slate-700">Rp {{ number_format($program->target_amount, 0, ',', '.') }}</p>
                </div>
            </div>

            <div class="mt-4 h-3 w-full bg-slate-100 rounded-full overflow-hidden">
                <div class="h-full bg-gradient-to-r from-primary to-secondary rounded-full" style="width: {{ $percent }}%"></div>
            </div>
            <div class="mt-2 flex justify-between text-xs text-slate-500">
                <span>{{ rtrim(rtrim(number_format($percent, 1, ',', '.'), '0'), ',') }}% tercapai</span>
                @if ($program->ends_at)
                    <span>Berakhir {{ $program->ends_at->translatedFormat('d M Y') }}</span>
                @endif
            </div>

            @if ($donateHref)
                <a href="{{ $donateHref }}" target="_blank" rel="noopener"
                   class="mt-5 w-full inline-flex items-center justify-center gap-2 bg-gradient-to-r from-primary to-secondary hover:opacity-95 active:scale-[.98] text-white font-semibold py-4 rounded-xl shadow-lg transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 016.364 0L12 7.636l1.318-1.318a4.5 4.5 0 116.364 6.364L12 20.364l-7.682-7.682a4.5 4.5 0 010-6.364z" />
                    </svg>
                    Donasi Sekarang
                </a>
            @endif
        </div>

        <div class="mt-10">
            <h2 class="text-lg font-bold text-primary">Riwayat Donasi</h2>

            @if ($program->transactions->isEmpty())
                <p class="mt-4 text-sm text-slate-500">Belum ada transaksi donasi untuk program ini.</p>
            @else
                <ul class="mt-4 divide-y divide-slate-100 bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden">
                    @foreach ($program->transactions->sortByDesc('donated_at') as $transaction)
                        <li class="flex items-center justify-between gap-4 px-5 py-4">
                            <div class="min-w-0">
                                <p class="font-medium text-slate-900 text-sm truncate">{{ $transaction->donor_name ?: 'Hamba Allah' }}</p>
                                <p class="text-xs text-slate-500">{{ $transaction->donated_at->translatedFormat('d M Y') }}</p>
                            </div>
                            <p class="font-semibold text-secondary text-sm whitespace-nowrap">Rp {{ number_format($transaction->amount, 0, ',', '.') }}</p>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</article>
