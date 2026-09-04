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

    {{-- Click-to-preview lightbox: overlay with the full-size photo, caption, prev/next, and
         close - keyboard-accessible (Escape closes, arrow keys navigate) so it isn't a
         hover-only or mouse-only affordance. --}}
    <div x-show="lightboxOpen"
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @keydown.window.escape="lightboxOpen = false"
         @keydown.window.arrow-right="activeIndex = (activeIndex + 1) % photos.length"
         @keydown.window.arrow-left="activeIndex = (activeIndex - 1 + photos.length) % photos.length"
         class="fixed inset-0 z-50 bg-black/90 flex items-center justify-center p-4 sm:p-8"
         role="dialog"
         aria-modal="true">
        <div class="absolute inset-0" @click="lightboxOpen = false"></div>

        <button type="button" @click="lightboxOpen = false" aria-label="Tutup"
                class="absolute top-4 right-4 sm:top-6 sm:right-6 w-10 h-10 rounded-full bg-white/10 text-white flex items-center justify-center hover:bg-white/20 transition-colors z-10">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        <button type="button" @click.stop="activeIndex = (activeIndex - 1 + photos.length) % photos.length" aria-label="Foto sebelumnya"
                class="absolute left-2 sm:left-6 top-1/2 -translate-y-1/2 w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-white/10 text-white flex items-center justify-center hover:bg-white/20 transition-colors z-10">
            <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
            </svg>
        </button>

        <button type="button" @click.stop="activeIndex = (activeIndex + 1) % photos.length" aria-label="Foto berikutnya"
                class="absolute right-2 sm:right-6 top-1/2 -translate-y-1/2 w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-white/10 text-white flex items-center justify-center hover:bg-white/20 transition-colors z-10">
            <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
            </svg>
        </button>

        <div class="relative max-w-4xl max-h-[85vh] flex flex-col items-center" @click.stop>
            <img :src="photos[activeIndex]?.image" :alt="photos[activeIndex]?.caption ?? ''" class="max-w-full max-h-[75vh] object-contain rounded-brand">
            <p x-show="photos[activeIndex]?.caption" x-text="photos[activeIndex]?.caption" class="text-white/80 text-sm mt-4 text-center"></p>
        </div>
    </div>
</section>
