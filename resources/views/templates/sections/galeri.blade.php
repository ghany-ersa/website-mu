{{--
    Auto-binds to the organization's gallery photos (GalleryPhoto, ordered by `order`)
    when $organization is in scope — i.e. when rendered as a tenant page
    (organizations/pages/_render.blade.php). Falls back to $content['items'] sample data
    in template-preview context (templates/preview.blade.php), which has no organization yet.
--}}
@php
    $content = $section['content'] ?? [];
    $limit = $content['limit'] ?? 8;
    $items = isset($organization)
        ? $organization->photos()->take($limit)->get()->map(fn ($photo) => [
            'image' => $photo->url,
            'caption' => $photo->caption,
        ])
        : ($content['items'] ?? array_fill(0, 4, []));
@endphp

<section class="py-16 bg-softBg">
    <div class="max-w-6xl mx-auto px-6">
        <h2 class="reveal text-3xl font-extrabold text-primary mb-8 text-center">
            {{ $content['title'] ?? 'Galeri' }}
        </h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach ($items as $item)
                <div class="reveal group aspect-square rounded-xl overflow-hidden bg-gray-100 relative"
                     style="transition-delay: {{ $loop->index * 80 }}ms">
                    @php $image = is_array($item) ? ($item['image'] ?? null) : $item; @endphp
                    @if ($image)
                        <img src="{{ $image }}" alt="{{ is_array($item) ? ($item['caption'] ?? 'Galeri '.$loop->iteration) : 'Galeri '.$loop->iteration }}"
                             class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                        @if (is_array($item) && ! empty($item['caption']))
                            <span class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/60 to-transparent text-white text-xs px-2 py-1.5 truncate">
                                {{ $item['caption'] }}
                            </span>
                        @endif
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</section>
