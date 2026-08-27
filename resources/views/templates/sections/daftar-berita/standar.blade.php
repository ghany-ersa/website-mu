{{--
    Auto-binds to the organization's published posts (PRD §12: "Berita Terbaru otomatis
    menampilkan post terbaru yang sudah diterbitkan") when $organization is in scope —
    i.e. when rendered as a tenant page (organizations/pages/_render.blade.php). Falls
    back to $content['items'] sample data in template-preview context (templates/preview.blade.php),
    which has no organization yet.
--}}
@php
    $content = $section['content'] ?? [];
    $limit = $content['limit'] ?? 3;
    $items = isset($organization)
        ? $organization->posts()->published()->take($limit)->get()->map(fn ($post) => [
            'title' => $post->title,
            'image' => $post->image,
            'category' => $post->category,
            'date' => $post->published_at?->translatedFormat('d M Y'),
            'excerpt' => $post->excerpt,
            'url' => \Illuminate\Support\Facades\Route::has('tenant.posts.show')
                ? route('tenant.posts.show', ['organization_slug' => $organization->slug, 'post_slug' => $post->slug])
                : '#',
        ])
        : collect($content['items'] ?? array_fill(0, $limit, []));
@endphp

<section class="py-16">
    <div class="max-w-6xl mx-auto px-6">
        <h2 class="reveal text-3xl font-extrabold text-primary mb-10">
            {{ $content['title'] ?? 'Berita Terbaru' }}
        </h2>
        <div class="grid md:grid-cols-3 gap-6">
            @foreach ($items as $item)
                <article class="reveal group rounded-brand overflow-hidden shadow-soft transition-all duration-300 hover:-translate-y-1.5 hover:shadow-float"
                          style="transition-delay: {{ $loop->index * 100 }}ms">
                    <a href="{{ $item['url'] ?? '#' }}" class="contents">
                        <div class="aspect-video overflow-hidden bg-gray-100">
                            @if (! empty($item['image']))
                                <img src="{{ $item['image'] }}" alt="{{ $item['title'] ?? '' }}"
                                     class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-400 text-sm">Gambar</div>
                            @endif
                        </div>
                        <div class="p-5">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-xs text-secondary font-semibold">{{ $item['category'] ?? 'Kategori' }}</span>
                                @if (! empty($item['date']))
                                    <span class="text-xs text-gray-400">&middot; {{ $item['date'] }}</span>
                                @endif
                            </div>
                            <h3 class="font-bold text-gray-800 transition-colors group-hover:text-primary">
                                {{ $item['title'] ?? 'Judul berita contoh '.$loop->iteration }}
                            </h3>
                            <p class="text-sm text-gray-500 mt-2">
                                {{ $item['excerpt'] ?? 'Ringkasan singkat berita akan tampil di sini.' }}
                            </p>
                        </div>
                    </a>
                </article>
            @endforeach
        </div>
    </div>
</section>
