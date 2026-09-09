{{--
    Auto-binds to the organization's published posts (PRD §12: "Berita Terbaru otomatis
    menampilkan post terbaru yang sudah diterbitkan") when $organization is in scope —
    i.e. when rendered as a tenant page (organizations/pages/_render.blade.php). Falls
    back to $content['items'] sample data in template-preview context (templates/preview.blade.php),
    which has no organization yet.

    Even grid of same-sized cards - no featured item singled out, unlike modern.blade.php. Shows
    the full excerpt (no line-clamp) when a post/sample item has one - for items with none, the
    excerpt line simply doesn't render, so this serves both a plain "image + title" look and a
    longer-form "image + title + excerpt" look without a separate mode flag.

    `limit` doubles as the initial page size: when there are more matching posts than that, a
    "Muat Lebih Banyak" button fetches the next batch from OrganizationSiteController::
    loadMoreBerita() and appends it client-side via the Alpine component below - only on a real
    tenant page, since the template-preview/sample-items branch has no live query to page
    through. Alpine (window.Alpine, resources/js/app.js) already loads on every tenant page, so
    this needs no extra Vite entry or global script - the x-data scope is per-section by
    construction, which is also what keeps two daftar-berita sections on one page (see
    SuaraMuhammadiyahAmbuluTemplateSeeder's `berita` page) from ever fetching into each other.

    $hasMore is derived by fetching one extra row (`take($limit + 1)`) rather than a separate
    `count()` query - a COUNT over every matching post would grow with the table just to answer
    a yes/no question this render only needs once, whereas the +1 row rides along on the exact
    same indexed, LIMITed SELECT this section already runs (see loadMoreBerita()'s matching
    comment for why its own batches use the same trick).
--}}
@php
    $content = $section['content'] ?? [];
    // A blank/unset `limit` means "show all" rather than falling back to a default cap - null
    // is the right value for that on the query branch (Builder::take(null) omits the SQL LIMIT
    // clause entirely, i.e. no "load more" button since every matching post already rendered),
    // and the sample-items branch below has no query to defer to, so it keeps its own default.
    $limit = filled($content['limit'] ?? null) ? (int) $content['limit'] : null;
    $categoryFilter = $content['category_filter'] ?? null;
    $items = isset($organization)
        ? $organization->posts()->published()
            ->when($categoryFilter, fn ($query) => $query->where('category', $categoryFilter))
            ->take($limit === null ? null : $limit + 1)->get()->map(fn ($post) => [
            'title' => $post->title,
            'image' => $post->image,
            'category' => $post->category,
            'date' => $post->published_at?->translatedFormat('d M Y'),
            'excerpt' => \Illuminate\Support\Str::limit(strip_tags($post->body), 140),
            'url' => \Illuminate\Support\Facades\Route::has('tenant.posts.show')
                ? route('tenant.posts.show', ['organization_slug' => $organization->slug, 'post_slug' => $post->slug])
                : '#',
        ])
        // array_fill's placeholder-card count only needs a number when there's neither a real
        // `items` sample list nor a `limit` to size it by - 3 keeps that specific edge case's
        // look unchanged; it never caps a real (or genuinely limitless) `items` list.
        : collect($content['items'] ?? array_fill(0, $limit ?? 3, []))->take($limit);
    $hasMore = isset($organization) && $limit !== null && $items->count() > $limit;
    $items = $hasMore ? $items->take($limit) : $items;
@endphp

<section class="py-14 bg-softBg"
    @if ($hasMore)
        x-data="{
            loading: false,
            offset: {{ $items->count() }},
            hasMore: true,
            async loadMore() {
                if (this.loading || ! this.hasMore) return;
                this.loading = true;
                try {
                    const url = new URL(@js(route('tenant.posts.load-more', ['organization_slug' => $organization->slug])));
                    url.searchParams.set('variant', 'standar');
                    url.searchParams.set('offset', this.offset);
                    url.searchParams.set('limit', @js($limit));
                    @if ($categoryFilter) url.searchParams.set('category_filter', @js($categoryFilter)); @endif
                    const response = await fetch(url);
                    const data = await response.json();
                    this.$refs.list.insertAdjacentHTML('beforeend', data.html);
                    window.observeRevealElements?.();
                    this.offset = data.nextOffset;
                    this.hasMore = data.hasMore;
                } finally {
                    this.loading = false;
                }
            },
        }"
    @endif
>
    <div class="max-w-6xl mx-auto px-6">
        <h2 class="reveal text-2xl font-extrabold text-primary mb-8 pb-3 border-b-2 border-secondary inline-block">
            {{ $content['title'] ?? 'Berita Terbaru' }}
        </h2>
        <div class="grid md:grid-cols-3 gap-5" @if ($hasMore) x-ref="list" @endif>
            @include('templates.sections.daftar-berita._items-standar', ['items' => $items, 'startIndex' => 0])
        </div>

        @if ($hasMore)
            <div class="mt-8 flex justify-center" x-show="hasMore">
                <button type="button" @click="loadMore()" :disabled="loading"
                        class="px-6 py-3 rounded-brand border border-primary text-primary text-sm font-semibold transition-colors hover:bg-primary hover:text-white disabled:opacity-50">
                    <span x-show="!loading">Muat Lebih Banyak</span>
                    <span x-show="loading">Memuat...</span>
                </button>
            </div>
        @endif
    </div>
</section>
