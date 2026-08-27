{{--
    Auto-binds to the organization's published posts (PRD §12: "Berita Terbaru otomatis
    menampilkan post terbaru yang sudah diterbitkan") when $organization is in scope —
    i.e. when rendered as a tenant page (organizations/pages/_render.blade.php). Falls
    back to $content['items'] sample data in template-preview context (templates/preview.blade.php),
    which has no organization yet.

    Even grid of same-sized cards — no featured item singled out, unlike sorotan.blade.php.
    Shared by the 'standar' and 'mozaik' registry keys (see config/page-builder.php) — 'standar'
    has no view file of its own, it's just an alias onto this one. Shows the full excerpt (no
    line-clamp) when a post/sample item has one — for items with none, the excerpt line simply
    doesn't render, so this serves both a plain "image + title" look and a longer-form
    "image + title + excerpt" look without a separate mode flag.
--}}
@php
    $content = $section['content'] ?? [];
    $limit = $content['limit'] ?? 3;
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

<section class="py-14 bg-softBg">
    <div class="max-w-6xl mx-auto px-6">
        <h2 class="reveal text-2xl font-extrabold text-primary mb-8 pb-3 border-b-2 border-secondary inline-block">
            {{ $content['title'] ?? 'Berita Terbaru' }}
        </h2>
        <div class="grid md:grid-cols-3 gap-5">
            @foreach ($items as $item)
                <article class="reveal group bg-white rounded-brand overflow-hidden shadow-soft transition-all duration-300 hover:-translate-y-1 hover:shadow-float"
                          style="transition-delay: {{ $loop->index * 80 }}ms">
                    <a href="{{ $item['url'] ?? '#' }}" class="contents">
                        <div class="aspect-[16/10] overflow-hidden bg-gray-100">
                            @if (! empty($item['image']))
                                <img src="{{ $item['image'] }}" alt="{{ $item['title'] ?? '' }}"
                                     class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-400 text-sm">Gambar</div>
                            @endif
                        </div>
                        <div class="p-4">
                            <span class="text-[11px] text-secondary font-bold uppercase tracking-wide">{{ $item['category'] ?? 'Kategori' }}</span>
                            <h3 class="text-sm font-semibold text-gray-800 leading-snug mt-1 transition-colors group-hover:text-primary">
                                {{ $item['title'] ?? 'Judul berita contoh '.$loop->iteration }}
                            </h3>
                            @if (! empty($item['date']))
                                <span class="text-xs text-gray-400 mt-2 block">{{ $item['date'] }}</span>
                            @endif
                        </div>
                    </a>
                </article>
            @endforeach
        </div>
    </div>
</section>
