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
        <h2 class="reveal text-3xl font-extrabold text-primary mb-8 text-center">
            {{ $content['title'] ?? 'Galeri' }}
        </h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <template x-for="(photo, index) in photos" :key="index">
                <button type="button"
                        @click="activeIndex = index; lightboxOpen = true"
                        :disabled="!photo.image"
                        class="reveal reveal-visible group aspect-square rounded-brand overflow-hidden bg-gray-100 relative ring-2 ring-transparent transition-shadow duration-300 hover:ring-secondary text-left disabled:cursor-default">
                    <template x-if="photo.image">
                        <div>
                            <img :src="photo.image" :alt="photo.caption ?? ''" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                            <span x-show="photo.caption" x-text="photo.caption"
                                  class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/60 to-transparent text-white text-xs px-2 py-1.5 truncate"></span>
                        </div>
                    </template>
                    <template x-if="!photo.image">
                        <div class="w-full h-full flex items-center justify-center text-gray-400 text-sm" x-text="photo.caption || 'Foto'"></div>
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
