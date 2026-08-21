@php $content = $section['content'] ?? []; @endphp

<section class="py-16">
    <div class="max-w-6xl mx-auto px-6 grid md:grid-cols-3 gap-10 items-center">
        <div class="reveal aspect-square rounded-2xl overflow-hidden shadow-soft">
            @if (! empty($content['photo']))
                <img src="{{ $content['photo'] }}" alt="{{ $content['nama'] ?? '' }}" class="w-full h-full object-cover">
            @else
                <div class="w-full h-full bg-gray-100 flex items-center justify-center text-gray-400 text-sm">Foto</div>
            @endif
        </div>
        <div class="reveal md:col-span-2" style="transition-delay: 100ms">
            <p class="text-secondary font-semibold mb-2">Sambutan</p>
            <h2 class="text-2xl md:text-3xl font-extrabold text-primary mb-2">
                {{ $content['nama'] ?? '[Nama Ketua]' }}
            </h2>
            <p class="text-sm text-gray-500 mb-4">{{ $content['jabatan'] ?? 'Ketua Organisasi' }}</p>
            <p class="text-gray-600 leading-relaxed">
                {{ $content['sambutan'] ?? 'Sambutan singkat dari ketua organisasi.' }}
            </p>
        </div>
    </div>
</section>
