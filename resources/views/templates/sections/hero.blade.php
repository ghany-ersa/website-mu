@php $content = $section['content'] ?? []; @endphp

<section class="relative overflow-hidden bg-softBg">
    <div class="absolute -top-24 -left-24 w-72 h-72 rounded-full bg-primary/10 blur-3xl animate-blob"></div>
    <div class="absolute -bottom-24 -right-24 w-72 h-72 rounded-full bg-secondary/10 blur-3xl animate-blob" style="animation-delay: 2s"></div>

    <div class="relative max-w-6xl mx-auto px-6 py-20 grid md:grid-cols-2 gap-12 items-center">
        <div>
            @if (! empty($content['badge']))
                <span class="reveal inline-block px-3 py-1 rounded-full bg-primary/10 text-primary text-xs font-semibold mb-4">
                    {{ $content['badge'] }}
                </span>
            @endif

            <h1 class="reveal text-4xl md:text-5xl font-extrabold text-primary leading-tight mb-4" style="transition-delay: 80ms">
                {{ $content['headline'] ?? 'Headline Utama' }}
            </h1>

            <p class="reveal max-w-xl text-lg text-gray-600 mb-8" style="transition-delay: 160ms">
                {{ $content['subheadline'] ?? 'Subheadline yang menjelaskan organisasi secara singkat.' }}
            </p>

            <div class="reveal flex flex-wrap gap-3" style="transition-delay: 240ms">
                @if (! empty($content['cta_label']))
                    <button class="px-6 py-3 rounded-full bg-secondary text-white font-semibold shadow-float transition-transform duration-200 hover:scale-105">
                        {{ $content['cta_label'] }}
                    </button>
                @endif
                @if (! empty($content['cta_secondary_label']))
                    <button class="px-6 py-3 rounded-full border border-gray-300 text-gray-700 font-semibold transition-colors duration-200 hover:border-primary hover:text-primary">
                        {{ $content['cta_secondary_label'] }}
                    </button>
                @endif
            </div>
        </div>

        <div class="reveal relative" style="transition-delay: 120ms">
            <div class="animate-float rounded-brand overflow-hidden shadow-float">
                @if (! empty($content['image']))
                    <img src="{{ $content['image'] }}" alt="{{ $content['headline'] ?? '' }}" class="w-full aspect-[4/3] object-cover">
                @else
                    <div class="w-full aspect-[4/3] bg-gray-100 flex items-center justify-center text-gray-400 text-sm">Gambar</div>
                @endif
            </div>
        </div>
    </div>
</section>
