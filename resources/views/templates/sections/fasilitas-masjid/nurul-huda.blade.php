{{-- Auto-binds to the organization's facilities (organizations.facilities.* CMS) when
     $organization is in scope; falls back to $content['items'] sample data in
     template-preview context - see struktur-pengurus/standar.blade.php for the pattern.

     Photos are click-to-zoom via the shared x-tenant.lightbox component (same {image,
     caption} Alpine contract as galeri/standar.blade.php), matching the nurul-huda site
     where facility photos open full-size rather than being decoration only. --}}
@php
    $content = $section['content'] ?? [];
    // A blank/unset `limit` means "show all" rather than falling back to a default cap -
    // Collection::take(null) returns every item.
    $limit = filled($content['limit'] ?? null) ? (int) $content['limit'] : null;

    $items = isset($organization)
        ? $organization->facilities()->get()->map(fn ($facility) => [
            'name' => $facility->name,
            'photo' => $facility->photo,
            'description' => $facility->description,
        ])
        : ($content['items'] ?? [
            ['name' => 'Ruang Sholat Utama', 'photo' => null, 'description' => null],
            ['name' => 'Tempat Wudhu', 'photo' => null, 'description' => null],
            ['name' => 'Area Parkir', 'photo' => null, 'description' => null],
        ]);

    $items = collect($items)->take($limit)->values();

    $photos = $items->map(fn ($item) => [
        'image' => $item['photo'] ?? null,
        'caption' => $item['name'] ?? null,
    ])->values();
@endphp

<section class="py-16 bg-slate-50" x-data="{ lightboxOpen: false, activeIndex: 0, photos: {{ Js::from($photos) }} }">
    <div class="max-w-5xl mx-auto px-6">
        <div class="reveal text-center">
            <span class="text-secondary font-semibold text-xs uppercase tracking-widest">Fasilitas</span>
            <h2 class="mt-2 text-2xl sm:text-3xl font-semibold tracking-tight text-primary">
                {{ $content['title'] ?? 'Fasilitas Masjid' }}
            </h2>
            @if (! empty($content['subtitle']))
                <p class="mt-3 text-slate-600 leading-relaxed max-w-xl mx-auto">{{ $content['subtitle'] }}</p>
            @endif
        </div>

        <div class="mt-10 grid grid-cols-2 gap-3 sm:gap-4 sm:grid-cols-4">
            @foreach ($items as $item)
                <article class="reveal group relative aspect-square overflow-hidden rounded-2xl bg-slate-200"
                         style="transition-delay: {{ min($loop->index, 6) * 60 }}ms">
                    @if (! empty($item['photo']))
                        <button type="button"
                                @click="activeIndex = {{ $loop->index }}; lightboxOpen = true"
                                class="absolute inset-0 w-full h-full cursor-zoom-in"
                                aria-label="Perbesar foto {{ $item['name'] }}">
                            <img src="{{ $item['photo'] }}" alt="{{ $item['name'] }}" loading="lazy"
                                 class="w-full h-full object-cover object-center transition duration-700 group-hover:scale-105">
                        </button>
                    @endif

                    {{-- pointer-events-none so the gradient never swallows the zoom button's
                         click; the caption is decoration layered over that full-bleed trigger. --}}
                    <div class="pointer-events-none absolute inset-x-0 bottom-0 p-3
                                bg-gradient-to-t from-slate-900/85 via-slate-900/35 to-transparent">
                        <h3 class="text-white text-sm font-medium leading-snug drop-shadow-sm">
                            {{ $item['name'] }}
                        </h3>
                        @if (! empty($item['description']))
                            <p class="mt-1 text-xs text-white/80 leading-relaxed line-clamp-2">{{ $item['description'] }}</p>
                        @endif
                    </div>

                    {{-- Zoom affordance: without it a photo that opens a lightbox looks inert.
                         Hidden from assistive tech - the button above already announces it. --}}
                    @if (! empty($item['photo']))
                        <span aria-hidden="true"
                              class="pointer-events-none absolute top-2.5 right-2.5 w-7 h-7 rounded-full bg-white/15 backdrop-blur
                                     border border-white/25 text-white flex items-center justify-center
                                     opacity-0 transition group-hover:opacity-100">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 18a7 7 0 110-14 7 7 0 010 14zM11 8v6M8 11h6" />
                            </svg>
                        </span>
                    @endif
                </article>
            @endforeach
        </div>
    </div>

    <x-tenant.lightbox />
</section>
