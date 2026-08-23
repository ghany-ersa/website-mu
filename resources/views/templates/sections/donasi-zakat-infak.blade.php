@php $content = $section['content'] ?? []; @endphp

<section class="py-16">
    <div class="max-w-4xl mx-auto px-6">
        <div class="reveal grid md:grid-cols-2 items-center bg-secondary/10 border border-secondary/30 rounded-brand overflow-hidden">
            @if (! empty($content['image']))
                <img src="{{ $content['image'] }}" alt="{{ $content['title'] ?? '' }}" class="w-full h-full object-cover aspect-video md:aspect-auto">
            @endif
            <div class="p-8 text-center md:text-left">
                <h2 class="text-2xl font-extrabold text-secondary mb-3">
                    {{ $content['title'] ?? 'Donasi, Zakat, dan Infak' }}
                </h2>
                <p class="text-gray-600 mb-6">
                    {{ $content['body'] ?? 'Salurkan donasi melalui QRIS atau tautan Lazismu.' }}
                </p>
                <button class="px-6 py-3 rounded-brand bg-secondary text-white font-semibold transition-transform duration-200 hover:scale-105">
                    Donasi Sekarang
                </button>
            </div>
        </div>
    </div>
</section>
