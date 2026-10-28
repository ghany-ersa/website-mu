@php
    $content = $section['content'] ?? [];
    // Re-checked at render time, not just on save: an <iframe src="javascript:..."> runs on
    // load in this page's origin, and rows saved before the save-time check existed may still
    // hold one. Anything but a Google Maps embed URL falls back to the placeholder below.
    $mapEmbed = \App\Services\SafeUrl::mapEmbed($content['map_embed'] ?? null);
@endphp

<section class="py-16">
    <div class="max-w-6xl mx-auto px-6">
        <h2 class="reveal text-3xl font-extrabold text-primary mb-2 text-center">
            {{ $content['title'] ?? 'Lokasi' }}
        </h2>
        @if (! empty($content['address']))
            <p class="reveal text-center text-gray-500 mb-8" style="transition-delay: 60ms">{{ $content['address'] }}</p>
        @else
            <div class="mb-8"></div>
        @endif
        @if ($mapEmbed)
            <div class="reveal aspect-[21/9] rounded-brand overflow-hidden" style="transition-delay: 120ms">
                <iframe src="{{ $mapEmbed }}" class="w-full h-full border-0" loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
            </div>
        @else
            <div class="reveal aspect-[21/9] rounded-brand flex items-center justify-center text-gray-400 text-sm overflow-hidden"
                 style="transition-delay: 120ms; background-color: #F3F4F6; background-image: radial-gradient(rgba(44,54,139,0.12) 1.5px, transparent 1.5px); background-size: 18px 18px;">
                Peta lokasi
            </div>
        @endif
    </div>
</section>
