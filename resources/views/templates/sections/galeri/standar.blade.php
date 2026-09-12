{{--
    Auto-binds to the organization's gallery photos (GalleryPhoto, ordered by `order`)
    when $organization is in scope - i.e. when rendered as a tenant page
    (organizations/pages/_render.blade.php). Falls back to $content['items'] sample data
    in template-preview context (templates/preview.blade.php), which has no organization yet.

    `limit` doubles as the initial page size: when there are more photos than that, a "Muat
    Lebih Banyak" button fetches the next batch from OrganizationSiteController::
    loadMoreGaleri() - only on a real tenant page, since the template-preview/sample-items
    branch has no live query to page through (same split as daftar-berita's ringkas/standar
    variants - see those partials for the fuller rationale, including why $hasMore is derived
    from a `take($limit + 1)` lookahead row instead of a separate count() query).

    Unlike daftar-berita, the grid itself is driven by Alpine's `photos` array (x-for), not a
    server-rendered @foreach: the lightbox component addresses photos by array index
    (activeIndex), so a newly loaded batch has to land in that same reactive array - appending
    raw HTML next to it would leave the lightbox's index arithmetic out of sync with what's
    actually in the DOM.
--}}
@php
    $content = $section['content'] ?? [];
    // A blank/unset `limit` means "show all" rather than falling back to a default cap - null
    // is the right value for that on the query branch (Builder::take(null) omits the SQL LIMIT
    // clause entirely, i.e. no "load more" button since every photo already rendered), and the
    // sample-items branch below has no query to defer to, so it keeps its own default.
    $limit = filled($content['limit'] ?? null) ? (int) $content['limit'] : null;
    $items = isset($organization)
        ? $organization->photos()->take($limit === null ? null : $limit + 1)->get()->map(fn ($photo) => [
            'image' => $photo->url,
            'caption' => $photo->caption,
        ])
        // array_fill's placeholder-card count only needs a number when there's neither a real
        // `items` sample list nor a `limit` to size it by - 4 keeps that specific edge case's
        // look unchanged; it never caps a real (or genuinely limitless) `items` list.
        : array_slice($content['items'] ?? array_fill(0, $limit ?? 4, ['caption' => 'Foto kegiatan']), 0, $limit);

    // Normalized once here (rather than inline per-item below) so the lightbox's JS array
    // and the grid's rendering both agree on the same [{image, caption}, ...] shape regardless
    // of whether an item arrived as a plain image-URL string (older sample data) or an array.
    $photos = collect($items)->map(function ($item) {
        return is_array($item)
            ? ['image' => $item['image'] ?? null, 'caption' => $item['caption'] ?? null]
            : ['image' => $item, 'caption' => null];
    })->values();

    $hasMore = isset($organization) && $limit !== null && $photos->count() > $limit;
    $photos = $hasMore ? $photos->take($limit)->values() : $photos;
@endphp

<section class="py-16 bg-softBg"
    x-data="{
        lightboxOpen: false,
        activeIndex: 0,
        photos: {{ Js::from($photos) }},
        loading: false,
        offset: {{ $photos->count() }},
        hasMore: {{ Js::from($hasMore) }},
        async loadMore() {
            if (this.loading || ! this.hasMore) return;
            this.loading = true;
            try {
                @if (isset($organization))
                    const url = new URL(@js(route('tenant.galleries.load-more', ['organization_slug' => $organization->slug])));
                    url.searchParams.set('offset', this.offset);
                    url.searchParams.set('limit', @js($limit));
                    const response = await fetch(url);
                    const data = await response.json();
                    this.photos.push(...data.photos);
                    this.offset = data.nextOffset;
                    this.hasMore = data.hasMore;
                @endif
            } finally {
                this.loading = false;
            }
        },
    }">
    <div class="max-w-6xl mx-auto px-6">
        {{-- Heading matches fasilitas-masjid/nurul-huda.blade.php: an eyebrow over a
             font-semibold title rather than the old font-extrabold text-3xl, which read as
             shouty beside the light body copy and made the two photo sections on this page
             look like they came from different templates. --}}
        <div class="reveal text-center mb-8">
            <span class="text-secondary font-semibold text-xs uppercase tracking-widest">Galeri</span>
            <h2 class="mt-2 text-2xl sm:text-3xl font-semibold tracking-tight text-primary">
                {{ $content['title'] ?? 'Galeri' }}
            </h2>
            @if (! empty($content['subtitle']))
                <p class="mt-3 text-slate-600 leading-relaxed max-w-xl mx-auto">{{ $content['subtitle'] }}</p>
            @endif
        </div>

        {{-- grid-cols-2 on mobile stays (a 4-up row would render thumbnails too small to read
             on a phone), but the caption moves from a truncated one-liner to a two-line clamp
             over a taller gradient, so a real caption like "Kegiatan Kajian Guru Besar Ramadhan
             2026" is legible instead of cut after a few words. --}}
        <div class="grid grid-cols-2 gap-3 sm:gap-4 md:grid-cols-3 lg:grid-cols-4">
            <template x-for="(photo, index) in photos" :key="index">
                <button type="button"
                        @click="activeIndex = index; lightboxOpen = true"
                        :disabled="!photo.image"
                        class="reveal reveal-visible group aspect-square rounded-2xl overflow-hidden bg-slate-200 relative text-left disabled:cursor-default cursor-zoom-in">
                    <template x-if="photo.image">
                        <div>
                            <img :src="photo.image" :alt="photo.caption ?? ''" loading="lazy"
                                 class="w-full h-full object-cover object-center transition duration-700 group-hover:scale-105">
                            <div x-show="photo.caption"
                                 class="pointer-events-none absolute inset-x-0 bottom-0 p-3 bg-gradient-to-t from-slate-900/85 via-slate-900/35 to-transparent">
                                <span x-text="photo.caption"
                                      class="block text-white text-sm font-medium leading-snug drop-shadow-sm line-clamp-2"></span>
                            </div>
                            {{-- Same zoom affordance as the facilities tiles, so a photo that
                                 opens a lightbox doesn't look inert on hover. --}}
                            <span aria-hidden="true"
                                  class="pointer-events-none absolute top-2.5 right-2.5 w-7 h-7 rounded-full bg-white/15 backdrop-blur
                                         border border-white/25 text-white flex items-center justify-center
                                         opacity-0 transition group-hover:opacity-100">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 18a7 7 0 110-14 7 7 0 010 14zM11 8v6M8 11h6" />
                                </svg>
                            </span>
                        </div>
                    </template>
                    <template x-if="!photo.image">
                        <div class="w-full h-full flex items-center justify-center text-slate-400 text-sm px-3 text-center" x-text="photo.caption || 'Foto'"></div>
                    </template>
                </button>
            </template>
        </div>

        <div class="mt-8 flex justify-center" x-show="hasMore">
            <button type="button" @click="loadMore()" :disabled="loading"
                    class="px-6 py-3 rounded-brand border border-primary text-primary text-sm font-semibold transition-colors hover:bg-primary hover:text-white disabled:opacity-50">
                <span x-show="!loading">Muat Lebih Banyak</span>
                <span x-show="loading">Memuat...</span>
            </button>
        </div>
    </div>

    <x-tenant.lightbox />
</section>
