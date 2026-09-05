{{-- Auto-binds to the organization's donation programs (organizations.donations.* CMS,
     collected amount computed live from donation_transactions) when $organization is in
     scope; falls back to $content['items'] sample data in template-preview context - see
     struktur-pengurus/standar.blade.php for the pattern. --}}
@php
    $content = $section['content'] ?? [];
    $limit = (int) ($content['limit'] ?? 3);

    // Currently-running programs first, then largest target - so a section with a small
    // `limit` (e.g. the home page's 3) leads with the flagship appeal people are most likely
    // to give to, rather than whichever rows the database happened to return first, which
    // would let an already-expired program crowd out the active headline one.
    $items = isset($organization)
        ? $organization->donationPrograms()
            ->get()
            ->sortBy(fn ($program) => [
                $program->ends_at !== null && $program->ends_at->isPast() ? 1 : 0,
                -$program->target_amount,
            ])
            ->values()
            ->map(fn ($program) => [
                'name' => $program->name,
                'cover_photo' => $program->cover_photo,
                'target_amount' => $program->target_amount,
                'collected_amount' => $program->collectedAmount(),
                'percent' => $program->progressPercent(),
                'status' => $program->status(),
                // On the real tenant site this links to the program's own subdomain URL; when
                // the same section is rendered from the main app domain (owner preview, or
                // local dev where wildcard subdomains aren't routable) it points at the
                // equivalent preview route instead, so the card is never a dead end.
                'url' => request()->routeIs('organizations.preview*', 'organizations.builder*')
                    ? route('organizations.preview.donation', ['organization' => $organization, 'program' => $program])
                    : (\Illuminate\Support\Facades\Route::has('tenant.donations.show')
                        ? route('tenant.donations.show', ['organization_slug' => $organization->slug, 'program_slug' => $program->slug])
                        : null),
            ])
        : ($content['items'] ?? [
            ['name' => 'Wakaf Pembangunan Masjid', 'cover_photo' => null, 'target_amount' => 100_000_000, 'collected_amount' => 68_000_000, 'percent' => 68, 'status' => 'active'],
        ]);

    $items = collect($items)->take($limit);
@endphp

<section class="py-16">
    <div class="max-w-5xl mx-auto px-6">
        <div class="text-center">
            <h2 class="reveal text-2xl sm:text-3xl font-bold text-primary">
                {{ $content['title'] ?? 'Program Donasi Aktif' }}
            </h2>
            @if (! empty($content['subtitle']))
                <p class="reveal mt-4 text-slate-600 leading-relaxed max-w-2xl mx-auto">{{ $content['subtitle'] }}</p>
            @endif
        </div>

        @if ($items->isEmpty())
            <p class="mt-8 text-center text-slate-500">Belum ada program donasi.</p>
        @else
            <div class="mt-8 grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($items as $item)
                    <{{ ($item['url'] ?? null) ? 'a' : 'div' }} @if ($item['url'] ?? null) href="{{ $item['url'] }}" @endif
                        class="reveal bg-white rounded-2xl shadow-sm hover:shadow-md transition overflow-hidden border border-slate-100 flex flex-col" style="transition-delay: {{ $loop->index * 80 }}ms">
                        @if (! empty($item['cover_photo']))
                            <div class="aspect-[16/9] bg-slate-100 overflow-hidden">
                                <img src="{{ $item['cover_photo'] }}" alt="{{ $item['name'] }}" loading="lazy"
                                     class="w-full h-full object-cover object-center hover:scale-105 transition duration-500">
                            </div>
                        @endif
                        <div class="p-5 flex-1 flex flex-col">
                            @php
                                $status = $item['status'] ?? 'active';
                                [$statusLabel, $statusClass] = match ($status) {
                                    'upcoming' => ['Akan Datang', 'bg-slate-100 text-slate-600'],
                                    'completed' => ['Selesai', 'bg-sky-100 text-sky-700'],
                                    'expired' => ['Berakhir', 'bg-red-100 text-red-700'],
                                    default => ['Aktif', 'bg-emerald-100 text-emerald-700'],
                                };
                            @endphp
                            <span class="inline-block self-start text-xs font-semibold px-2.5 py-1 rounded-full {{ $statusClass }}">
                                {{ $statusLabel }}
                            </span>
                            <h3 class="mt-2 font-semibold text-slate-900">{{ $item['name'] }}</h3>
                            <div class="mt-3 h-2 w-full bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-primary to-secondary rounded-full" style="width: {{ $item['percent'] }}%"></div>
                            </div>
                            <div class="mt-2 flex justify-between gap-2 text-xs text-slate-500">
                                <span>Rp {{ number_format($item['collected_amount'], 0, ',', '.') }}</span>
                                <span class="text-right">{{ rtrim(rtrim(number_format($item['percent'], 1, ',', '.'), '0'), ',') }}% dari Rp {{ number_format($item['target_amount'], 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </{{ ($item['url'] ?? null) ? 'a' : 'div' }}>
                @endforeach
            </div>
        @endif
    </div>
</section>
