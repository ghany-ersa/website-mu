{{--
    Auto-binds to the organization's gallery photos (GalleryPhoto, ordered by `order`)
    when $organization is in scope - i.e. when rendered as a tenant page
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
        : ($content['items'] ?? array_fill(0, 4, ['caption' => 'Foto kegiatan']));

    // Normalized once here (rather than inline per-item below) so the lightbox's JS array
    // and the grid's rendering both agree on the same [{image, caption}, ...] shape regardless
    // of whether an item arrived as a plain image-URL string (older sample data) or an array.
    $photos = collect($items)->map(function ($item) {
        return is_array($item)
            ? ['image' => $item['image'] ?? null, 'caption' => $item['caption'] ?? null]
            : ['image' => $item, 'caption' => null];
    })->values();
@endphp

<section class="py-16 bg-softBg" x-data="{ lightboxOpen: false, activeIndex: 0, photos: {{ Js::from($photos) }} }">
    <div class="max-w-6xl mx-auto px-6">
        <h2 class="reveal text-3xl font-extrabold text-primary mb-8 text-center">
            {{ $content['title'] ?? 'Galeri' }}
        </h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach ($photos as $photo)
                <button type="button"
                        @click="activeIndex = {{ $loop->index }}; lightboxOpen = true"
                        {{ $photo['image'] ? '' : 'disabled' }}
                        class="reveal group aspect-square rounded-brand overflow-hidden bg-gray-100 relative ring-2 ring-transparent transition-shadow duration-300 hover:ring-secondary text-left disabled:cursor-default"
                        style="transition-delay: {{ $loop->index * 80 }}ms">
                    @if ($photo['image'])
                        <img src="{{ $photo['image'] }}" alt="{{ $photo['caption'] ?? 'Galeri '.$loop->iteration }}"
                             class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                        @if (! empty($photo['caption']))
                            <span class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/60 to-transparent text-white text-xs px-2 py-1.5 truncate">
                                {{ $photo['caption'] }}
                            </span>
                        @endif
                    @else
                        <div class="w-full h-full flex items-center justify-center text-gray-400 text-sm">
                            {{ $photo['caption'] ?? 'Foto' }}
                        </div>
                    @endif
                </button>
            @endforeach
        </div>
    </div>

    <x-tenant.lightbox />
</section>
