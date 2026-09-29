{{-- Port of comprof-SD-Madya galeri.vue: an optional video-profile poster that swaps to an
     embedded player on click, a sticky category rail, and a bento grid whose first tile is
     twice the size. Clicking a photo opens the shared tenant lightbox.

     Auto-binds to the organization's gallery photos when $organization is in scope; falls back
     to $content['items'] in template-preview context - see galeri/standar.blade.php.

     Extra content keys over `standar`: `label`, `subtitle`, `video_url` (a YouTube watch/share
     URL) and `video_poster`/`video_title`/`video_subtitle` for the poster overlaying it. --}}
@php
    $content = $section['content'] ?? [];
    $limit = $content['limit'] ?? 12;

    $items = isset($organization)
        ? $organization->photos()->active()->take($limit)->get()->map(fn ($photo) => [
            'image' => $photo->url,
            'title' => $photo->title,
            'caption' => $photo->caption,
            'category' => $photo->category,
        ])->all()
        : ($content['items'] ?? [
            ['title' => 'Kegiatan Belajar', 'category' => 'Kegiatan'],
            ['title' => 'Wisuda Tahfidz', 'category' => 'Prestasi'],
            ['title' => 'Ruang Kelas', 'category' => 'Fasilitas'],
            ['title' => 'Upacara Bendera', 'category' => 'Kegiatan'],
            ['title' => 'Perpustakaan', 'category' => 'Fasilitas'],
        ]);

    $photos = collect($items)->map(fn ($item) => is_array($item)
        ? ['image' => $item['image'] ?? null, 'caption' => $item['title'] ?? $item['caption'] ?? null, 'category' => $item['category'] ?? null]
        : ['image' => $item, 'caption' => null, 'category' => null]
    )->values();

    $categories = $photos->pluck('category')->filter()->unique()->values()->all();

    // Accepts a full watch URL, a share link, or a bare id, and normalises to the embed form -
    // pasting the URL straight from the browser is what an owner will actually do.
    $videoId = null;
    if (! empty($content['video_url'])) {
        $url = $content['video_url'];
        $videoId = match (true) {
            (bool) preg_match('#(?:youtube\.com/(?:watch\?v=|embed/)|youtu\.be/)([\w-]{11})#', $url, $m) => $m[1],
            (bool) preg_match('#^[\w-]{11}$#', $url) => $url,
            default => null,
        };
    }
@endphp

<section class="section-y bg-surface"
         x-data="{
            lightboxOpen: false,
            activeIndex: 0,
            showVideo: false,
            filter: 'Semua',
            photos: {{ Js::from($photos->map(fn ($photo) => ['image' => $photo['image'], 'caption' => $photo['caption']])) }},
         }">
    <div class="shell shell-wide">
        <div class="flex flex-col gap-3 mb-8 sm:mb-10">
            @if (! empty($content['label']))
                <span class="reveal font-headline text-secondary font-bold tracking-[0.2em] uppercase text-[11px]">{{ $content['label'] }}</span>
            @endif
            <h2 class="reveal font-headline t-h2 font-extrabold text-primary max-w-3xl">
                {{ $content['title'] ?? 'Galeri' }}
            </h2>
            @if (! empty($content['subtitle']))
                <p class="reveal font-body t-body text-on-surface-variant measure" style="transition-delay: 80ms">
                    {{ $content['subtitle'] }}
                </p>
            @endif
        </div>

        @if ($videoId)
            <div class="reveal relative aspect-video rounded-2xl overflow-hidden shadow-ambient-lg bg-primary mb-8 sm:mb-12 group">
                <template x-if="!showVideo">
                    <div class="absolute inset-0">
                        @if (! empty($content['video_poster']))
                            <img src="{{ $content['video_poster'] }}" alt="" aria-hidden="true" loading="lazy"
                                 class="w-full h-full object-cover opacity-60 group-hover:scale-105 transition-transform duration-700">
                        @endif
                        <div class="absolute inset-0 flex flex-col items-center justify-center text-center p-8 bg-gradient-to-t from-primary/80 to-transparent">
                            {{-- The iframe is only created on click, so the page doesn't pull
                                 YouTube's player on every visit. --}}
                            <button type="button" @click="showVideo = true"
                                    class="w-14 h-14 sm:w-16 sm:h-16 md:w-20 md:h-20 rounded-full bg-secondary text-white flex items-center justify-center shadow-2xl hover:scale-110 active:scale-95 transition-all mb-6">
                                <svg class="w-7 h-7 md:w-9 md:h-9" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M8 5v14l11-7L8 5Z" />
                                </svg>
                            </button>
                            <h3 class="font-headline t-h3 sm:text-xl md:text-2xl font-bold text-white mb-1.5">{{ $content['video_title'] ?? 'Video Profil' }}</h3>
                            @if (! empty($content['video_subtitle']))
                                <p class="font-body text-white/80 italic text-sm">{{ $content['video_subtitle'] }}</p>
                            @endif
                        </div>
                    </div>
                </template>
                <template x-if="showVideo">
                    <iframe class="w-full h-full" src="https://www.youtube.com/embed/{{ $videoId }}?autoplay=1"
                            title="{{ $content['video_title'] ?? 'Video Profil' }}" frameborder="0" allowfullscreen
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe>
                </template>
            </div>
        @endif

        @if ($photos->isEmpty())
            <p class="text-center font-body text-on-surface-variant">Belum ada foto.</p>
        @else
            @if (count($categories) > 1)
                <div class="reveal flex gap-3 overflow-x-auto hide-scrollbar -mx-5 px-5 sm:-mx-6 sm:px-6 md:mx-0 md:px-0 mb-8">
                    @foreach (array_merge(['Semua'], $categories) as $category)
                        <button type="button" @click="filter = {{ Js::from($category) }}"
                                :class="filter === {{ Js::from($category) }}
                                    ? 'bg-primary text-white'
                                    : 'bg-surface-container-high text-primary hover:bg-surface-container-highest'"
                                class="px-4 py-1.5 rounded-full font-headline text-[13px] font-bold whitespace-nowrap transition-colors">
                            {{ $category }}
                        </button>
                    @endforeach
                </div>
            @endif

            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4 auto-rows-[120px] sm:auto-rows-[150px] md:auto-rows-[185px]">
                @foreach ($photos as $index => $photo)
                    <button type="button"
                            @click="activeIndex = {{ $index }}; lightboxOpen = true"
                            x-show="filter === 'Semua' || filter === {{ Js::from($photo['category'] ?? '') }}"
                            class="reveal {{ $index === 0 ? 'col-span-2 row-span-2' : 'col-span-1 row-span-1' }} rounded-2xl overflow-hidden relative group bg-surface-container-high text-left"
                            style="transition-delay: {{ min($index * 40, 200) }}ms">
                        @if (! empty($photo['image']))
                            <img src="{{ $photo['image'] }}" alt="{{ $photo['caption'] ?? '' }}" loading="lazy"
                                 class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        @else
                            <span class="absolute inset-0 flex items-center justify-center">
                                <svg class="w-9 h-9 text-on-surface-variant" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5V6a1.5 1.5 0 0 1 1.5-1.5h15A1.5 1.5 0 0 1 21 6v12a1.5 1.5 0 0 1-1.5 1.5h-15A1.5 1.5 0 0 1 3 18v-1.5Zm0 0 5.5-5a1.5 1.5 0 0 1 2.1 0l1.4 1.4a1.5 1.5 0 0 0 2.1 0L18 9.5 21 12.5M9.75 9a1.25 1.25 0 1 1-2.5 0 1.25 1.25 0 0 1 2.5 0Z" />
                                </svg>
                            </span>
                        @endif

                        @if (! empty($photo['caption']) || ! empty($photo['category']))
                            <span class="absolute inset-0 bg-gradient-to-t from-primary/90 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex flex-col justify-end p-3 md:p-4">
                                @if (! empty($photo['category']))
                                    <span class="font-headline text-[10px] text-white/80 font-bold tracking-widest uppercase">{{ $photo['category'] }}</span>
                                @endif
                                @if (! empty($photo['caption']))
                                    <span class="font-headline text-[13px] md:text-base text-white font-bold">{{ $photo['caption'] }}</span>
                                @endif
                            </span>
                        @endif
                    </button>
                @endforeach
            </div>
        @endif
    </div>

    <x-tenant.lightbox />
</section>
