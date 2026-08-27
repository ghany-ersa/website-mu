{{-- Auto-binds to the organization's published agenda items (soonest/most recent first,
     regardless of date) when $organization is in scope (tenant page render); falls back to
     $content['items'] sample data in template-preview context — see daftar-berita.blade.php
     for the full rationale. --}}
@php
    $content = $section['content'] ?? [];
    $limit = $content['limit'] ?? 3;
    $items = isset($organization)
        ? $organization->agendas()->published()->take($limit)->get()->map(fn ($agenda) => [
            'title' => $agenda->title,
            'date_day' => $agenda->starts_at->format('d'),
            'date_month' => $agenda->starts_at->translatedFormat('M'),
            'date_year' => $agenda->starts_at->format('Y'),
            'location' => $agenda->location,
            'time' => $agenda->starts_at->format('H:i'),
            'url' => \Illuminate\Support\Facades\Route::has('tenant.agendas.show')
                ? route('tenant.agendas.show', ['organization_slug' => $organization->slug, 'agenda' => $agenda->id])
                : '#',
        ])
        : ($content['items'] ?? array_fill(0, $limit, []));
@endphp

<section class="py-16 bg-softBg">
    <div class="max-w-6xl mx-auto px-6">
        <h2 class="reveal text-3xl font-extrabold text-primary mb-10">
            {{ $content['title'] ?? 'Agenda Kegiatan' }}
        </h2>
        <div class="space-y-4">
            @foreach ($items as $item)
                <a href="{{ $item['url'] ?? '#' }}"
                   class="reveal bg-white rounded-brand p-5 flex items-center gap-5 shadow-soft transition-all duration-300 hover:shadow-float hover:-translate-y-0.5"
                   style="transition-delay: {{ $loop->index * 80 }}ms">
                    <div class="w-14 h-14 shrink-0 rounded-brand bg-secondary text-white flex flex-col items-center justify-center leading-none">
                        <span class="text-lg font-extrabold">{{ $item['date_day'] ?? (10 + $loop->iteration) }}</span>
                        <span class="text-[10px] uppercase">{{ $item['date_month'] ?? 'Bulan' }}</span>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800">{{ $item['title'] ?? 'Agenda contoh '.$loop->iteration }}</p>
                        <p class="text-sm text-gray-500">
                            @if (! empty($item['date_year'])) {{ $item['date_year'] }} &middot; @endif
                            {{ $item['location'] ?? 'Lokasi kegiatan' }}
                            @if (! empty($item['time'])) &middot; {{ $item['time'] }} @endif
                        </p>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</section>
