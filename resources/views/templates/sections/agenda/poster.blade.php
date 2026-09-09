{{-- Exclusive poster variant of the agenda section: a grid of the flyers an organization
     already designs for each kajian, instead of agenda/standar.blade.php's date-badge list.
     Reads the same `agendas` table, plus agendas.poster (see the add_poster_to_agendas
     migration), so switching between the two variants never loses content - a poster-less
     agenda simply falls back to the date block that standar would have shown.

     Posters are click-to-zoom via the shared x-tenant.lightbox component (same {image, caption}
     Alpine contract as galeri/standar.blade.php), because a flyer carries the details - speaker,
     agenda, contact - in the artwork itself and is unreadable at card size. --}}
@php
    $content = $section['content'] ?? [];
    // A blank/unset `limit` means "show all" rather than falling back to a default cap - both
    // Builder::take(null) and Collection::take(null) below return every item unbounded.
    $limit = filled($content['limit'] ?? null) ? (int) $content['limit'] : null;

    $items = isset($organization)
        ? $organization->agendas()->published()->take($limit)->get()->map(fn ($agenda) => [
            'title' => $agenda->title,
            'poster' => $agenda->poster,
            'date_day' => $agenda->starts_at->format('d'),
            'date_month' => $agenda->starts_at->translatedFormat('M'),
            'location' => $agenda->location,
            'time' => $agenda->starts_at->format('H:i'),
            'url' => \Illuminate\Support\Facades\Route::has('tenant.agendas.show')
                ? route('tenant.agendas.show', ['organization_slug' => $organization->slug, 'agenda' => $agenda->id])
                : null,
        ])
        : ($content['items'] ?? [
            ['title' => 'Kajian Ahad Pagi', 'poster' => null, 'date_day' => '12', 'date_month' => 'Okt', 'location' => 'Masjid', 'time' => '06:00'],
            ['title' => 'Kajian Tafsir', 'poster' => null, 'date_day' => '15', 'date_month' => 'Okt', 'location' => 'Masjid', 'time' => '18:30'],
            ['title' => 'Kajian Fikih', 'poster' => null, 'date_day' => '19', 'date_month' => 'Okt', 'location' => 'Masjid', 'time' => '16:00'],
        ]);

    $items = collect($items)->take($limit)->values();

    $photos = $items->map(fn ($item) => [
        'image' => $item['poster'] ?? null,
        'caption' => $item['title'] ?? null,
    ])->values();
@endphp

<section class="py-16 bg-softBg" x-data="{ lightboxOpen: false, activeIndex: 0, photos: {{ Js::from($photos) }} }">
    <div class="max-w-6xl mx-auto px-6">
        <h2 class="reveal text-3xl font-extrabold text-primary">
            {{ $content['title'] ?? 'Jadwal Kajian' }}
        </h2>
        @if (! empty($content['subtitle']))
            <p class="reveal mt-2 text-gray-600">{{ $content['subtitle'] }}</p>
        @endif

        @if ($items->isEmpty())
            <p class="mt-8 text-gray-500">Belum ada kajian terjadwal.</p>
        @else
            <div class="mt-10 grid grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-6">
                @foreach ($items as $item)
                    <article class="reveal bg-white rounded-brand overflow-hidden shadow-soft transition-all duration-300 hover:shadow-float hover:-translate-y-0.5 flex flex-col"
                        style="transition-delay: {{ $loop->index * 80 }}ms">
                        @if (! empty($item['poster']))
                            <button type="button"
                                @click="activeIndex = {{ $loop->index }}; lightboxOpen = true"
                                class="aspect-[3/4] bg-gray-100 overflow-hidden block w-full cursor-zoom-in">
                                <img src="{{ $item['poster'] }}" alt="Poster {{ $item['title'] }}" loading="lazy"
                                    class="w-full h-full object-cover object-center hover:scale-105 transition duration-500">
                            </button>
                        @else
                            {{-- No flyer uploaded: keep the card the same shape and show the date
                                 block standar would have used, so a half-filled CMS still reads
                                 as a deliberate grid rather than a broken one. --}}
                            <div class="aspect-[3/4] bg-secondary/10 flex flex-col items-center justify-center text-secondary">
                                <span class="text-4xl font-extrabold leading-none">{{ $item['date_day'] ?? '--' }}</span>
                                <span class="text-xs uppercase tracking-widest mt-1">{{ $item['date_month'] ?? '' }}</span>
                            </div>
                        @endif

                        <div class="p-4 flex-1 flex flex-col">
                            <h3 class="font-semibold text-gray-800 leading-snug">
                                @if ($item['url'] ?? null)
                                    <a href="{{ $item['url'] }}" class="hover:text-primary transition">{{ $item['title'] }}</a>
                                @else
                                    {{ $item['title'] }}
                                @endif
                            </h3>
                            <p class="mt-1.5 text-xs text-gray-500">
                                {{ $item['date_day'] ?? '' }} {{ $item['date_month'] ?? '' }}
                                @if (! empty($item['time'])) &middot; {{ $item['time'] }} @endif
                            </p>
                            @if (! empty($item['location']))
                                <p class="text-xs text-gray-500">{{ $item['location'] }}</p>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>

    <x-tenant.lightbox />
</section>
