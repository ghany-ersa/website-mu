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

<section class="py-24">
    <div class="max-w-6xl mx-auto px-6">
        <div class="reveal mb-16">
            <span class="block w-10 h-px bg-secondary mb-6"></span>
            <h2 class="text-3xl md:text-4xl text-primary">
                {{ $content['title'] ?? 'Berita Terbaru' }}
            </h2>
        </div>
        @php $featured = $items->first() ?? ($items[0] ?? null); @endphp
        <div class="grid md:grid-cols-[1.4fr_1fr] gap-12">
            @if ($featured)
                <article class="reveal group">
                    <a href="{{ $featured['url'] ?? '#' }}">
                        <div class="aspect-[16/10] overflow-hidden bg-gray-100 rounded-brand mb-6">
                            @if (! empty($featured['image']))
                                <img src="{{ $featured['image'] }}" alt="{{ $featured['title'] ?? '' }}"
                                     class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-400 text-sm">Gambar</div>
                            @endif
                        </div>
                        <div class="flex items-center gap-2 mb-2">
                            <span class="text-xs text-secondary font-semibold uppercase tracking-wide">{{ $featured['category'] ?? 'Kategori' }}</span>
                            @if (! empty($featured['date']))
                                <span class="text-xs text-gray-400">&middot; {{ $featured['date'] }}</span>
                            @endif
                        </div>
                        <h3 class="text-2xl text-primary leading-snug mb-3 transition-colors group-hover:text-secondary">
                            {{ $featured['title'] ?? 'Judul berita contoh' }}
                        </h3>
                        <p class="text-gray-500 leading-relaxed">
                            {{ $featured['excerpt'] ?? 'Ringkasan singkat berita akan tampil di sini.' }}
                        </p>
                    </a>
                </article>
            @endif

            <div class="flex flex-col divide-y divide-gray-200">
                @foreach ($items->skip(1) as $item)
                    <article class="reveal group py-6 first:pt-0" style="transition-delay: {{ $loop->index * 80 }}ms">
                        <a href="{{ $item['url'] ?? '#' }}" class="flex gap-4">
                            <div class="w-20 h-20 shrink-0 overflow-hidden bg-gray-100 rounded-brand">
                                @if (! empty($item['image']))
                                    <img src="{{ $item['image'] }}" alt="{{ $item['title'] ?? '' }}" class="w-full h-full object-cover">
                                @endif
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-xs text-secondary font-semibold uppercase tracking-wide">{{ $item['category'] ?? 'Kategori' }}</span>
                                    @if (! empty($item['date']))
                                        <span class="text-xs text-gray-400">&middot; {{ $item['date'] }}</span>
                                    @endif
                                </div>
                                <h3 class="font-semibold text-primary leading-snug transition-colors group-hover:text-secondary line-clamp-2">
                                    {{ $item['title'] ?? 'Judul berita contoh '.($loop->index + 2) }}
                                </h3>
                            </div>
                        </a>
                    </article>
                @endforeach
            </div>
        </div>
    </div>
</section>
