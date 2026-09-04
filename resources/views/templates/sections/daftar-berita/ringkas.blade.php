{{--
    Auto-binds to the organization's published posts (PRD §12: "Berita Terbaru otomatis
    menampilkan post terbaru yang sudah diterbitkan") when $organization is in scope —
    i.e. when rendered as a tenant page (organizations/pages/_render.blade.php). Falls
    back to $content['items'] sample data in template-preview context (templates/preview.blade.php),
    which has no organization yet.

    Dense thumbnail-list treatment (no excerpt, no featured item) modeled on muhammadiyah.or.id's
    "Kabar Persyarikatan" block - every item renders the same compact row, unlike
    daftar-berita/portal.blade.php which singles out the first item as a large featured card.
--}}
@php
    $content = $section['content'] ?? [];
    $limit = $content['limit'] ?? 6;
    $categoryFilter = $content['category_filter'] ?? null;
    $items = isset($organization)
        ? $organization->posts()->published()
            ->when($categoryFilter, fn ($query) => $query->where('category', $categoryFilter))
            ->take($limit)->get()->map(fn ($post) => [
            'title' => $post->title,
            'image' => $post->image,
            'category' => $post->category,
            'date' => $post->published_at?->translatedFormat('d M Y'),
            'excerpt' => \Illuminate\Support\Str::limit(strip_tags($post->body), 140),
            'url' => \Illuminate\Support\Facades\Route::has('tenant.posts.show')
                ? route('tenant.posts.show', ['organization_slug' => $organization->slug, 'post_slug' => $post->slug])
                : '#',
        ])
        : collect($content['items'] ?? array_fill(0, $limit, []));
@endphp

<section class="py-14">
    <div class="max-w-6xl mx-auto px-6">
        <h2 class="reveal text-2xl font-extrabold text-primary mb-6 pb-3 border-b-2 border-secondary inline-block">
            {{ $content['title'] ?? 'Berita Terbaru' }}
        </h2>

        <div class="grid md:grid-cols-2 md:gap-x-8">
            @foreach ($items as $item)
                <article class="reveal group py-3.5 border-b border-gray-200" style="transition-delay: {{ $loop->index * 50 }}ms">
                    <a href="{{ $item['url'] ?? '#' }}" class="flex items-center gap-4">
                        <div class="w-24 h-16 shrink-0 overflow-hidden bg-gray-100 rounded-brand">
                            @if (! empty($item['image']))
                                <img src="{{ $item['image'] }}" alt="{{ $item['title'] ?? '' }}" class="w-full h-full object-cover">
                            @endif
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 mb-0.5">
                                <span class="text-[11px] text-secondary font-bold uppercase tracking-wide">{{ $item['category'] ?? 'Kategori' }}</span>
                                @if (! empty($item['date']))
                                    <span class="text-[11px] text-gray-400">&middot; {{ $item['date'] }}</span>
                                @endif
                            </div>
                            <h3 class="text-sm font-semibold text-gray-800 leading-snug transition-colors group-hover:text-primary line-clamp-2">
                                {{ $item['title'] ?? 'Judul berita contoh '.$loop->iteration }}
                            </h3>
                        </div>
                    </a>
                </article>
            @endforeach
        </div>
    </div>
</section>
