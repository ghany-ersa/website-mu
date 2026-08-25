@php $content = $section['content'] ?? []; @endphp

<section class="relative overflow-hidden py-16 bg-primary">
    <div class="absolute -top-16 -right-16 w-64 h-64 rounded-full bg-white/5 animate-blob"></div>
    <div class="absolute -bottom-20 -left-10 w-56 h-56 rounded-full bg-white/5 animate-blob" style="animation-delay: 3s"></div>

    <div class="relative max-w-6xl mx-auto px-6 text-center">
        <h2 class="reveal text-3xl font-extrabold text-white mb-3">
            {{ $content['title'] ?? 'Mari Bergabung' }}
        </h2>
        <p class="reveal text-white/80 max-w-xl mx-auto mb-6" style="transition-delay: 80ms">
            {{ $content['subtitle'] ?? 'Ajakan singkat untuk bergabung atau berpartisipasi bersama kami.' }}
        </p>
        <button class="reveal px-6 py-3 rounded-brand bg-white text-primary font-semibold transition-transform duration-200 hover:scale-105"
                style="transition-delay: 160ms">
            {{ $content['cta_label'] ?? 'Selengkapnya' }}
        </button>
    </div>
</section>
