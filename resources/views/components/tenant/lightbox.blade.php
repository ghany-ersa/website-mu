{{--
    Click-to-preview lightbox for tenant-facing photo galleries: overlay with the full-size
    photo, caption, prev/next, and close - keyboard-accessible (Escape closes, arrow keys
    navigate) so it isn't a hover-only or mouse-only affordance.

    Usage: wrap the trigger buttons in an element with `x-data="..."` that exposes
    `lightboxOpen`, `activeIndex`, and a `photos` array of {image, caption} - this component
    only renders the overlay markup, driven by that same Alpine scope (place it inside the
    same x-data element as the triggers). See templates/sections/galeri/standar.blade.php for
    a full example of the trigger side.
--}}
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

    <template x-if="photos.length > 1">
        <button type="button" @click.stop="activeIndex = (activeIndex - 1 + photos.length) % photos.length" aria-label="Foto sebelumnya"
                class="absolute left-2 sm:left-6 top-1/2 -translate-y-1/2 w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-white/10 text-white flex items-center justify-center hover:bg-white/20 transition-colors z-10">
            <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
            </svg>
        </button>
    </template>

    <template x-if="photos.length > 1">
        <button type="button" @click.stop="activeIndex = (activeIndex + 1) % photos.length" aria-label="Foto berikutnya"
                class="absolute right-2 sm:right-6 top-1/2 -translate-y-1/2 w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-white/10 text-white flex items-center justify-center hover:bg-white/20 transition-colors z-10">
            <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
            </svg>
        </button>
    </template>

    <div class="relative max-w-4xl max-h-[85vh] flex flex-col items-center" @click.stop>
        <img :src="photos[activeIndex]?.image" :alt="photos[activeIndex]?.caption ?? ''" class="max-w-full max-h-[75vh] object-contain rounded-brand">
        <p x-show="photos[activeIndex]?.caption" x-text="photos[activeIndex]?.caption" class="text-white/80 text-sm mt-4 text-center"></p>
    </div>
</div>
