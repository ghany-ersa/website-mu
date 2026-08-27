@php $content = $section['content'] ?? []; @endphp

<section class="py-24 bg-softBg">
    <div class="max-w-4xl mx-auto px-6 text-center">
        <div class="reveal w-14 h-14 rounded-full bg-white shadow-soft flex items-center justify-center mx-auto mb-8 overflow-hidden">
            @if (! empty($content['photo']))
                <img src="{{ $content['photo'] }}" alt="{{ $content['nama'] ?? '' }}" class="w-full h-full object-cover">
            @else
                <span class="text-secondary text-2xl leading-none">&ldquo;</span>
            @endif
        </div>
        <p class="reveal text-xl md:text-2xl text-gray-700 leading-relaxed mb-8" style="transition-delay: 80ms">
            &ldquo;{{ $content['sambutan'] ?? 'Sambutan singkat dari ketua organisasi.' }}&rdquo;
        </p>
        <div class="reveal flex items-center justify-center gap-3" style="transition-delay: 160ms">
            <span class="w-8 h-px bg-secondary"></span>
            <div class="text-left">
                <p class="font-semibold text-primary">{{ $content['nama'] ?? '[Nama Ketua]' }}</p>
                <p class="text-sm text-gray-500">{{ $content['jabatan'] ?? 'Ketua Organisasi' }}</p>
            </div>
        </div>
    </div>
</section>
