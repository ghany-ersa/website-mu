@php $content = $section['content'] ?? []; @endphp

<section class="py-16">
    <div class="max-w-6xl mx-auto px-6 grid md:grid-cols-2 gap-10 items-center">
        <div class="reveal aspect-video rounded-2xl overflow-hidden shadow-soft">
            @if (! empty($content['image']))
                <img src="{{ $content['image'] }}" alt="{{ $content['title'] ?? '' }}" class="w-full h-full object-cover">
            @else
                <div class="w-full h-full bg-gray-100 flex items-center justify-center text-gray-400 text-sm">Foto/Video</div>
            @endif
        </div>
        <div class="reveal" style="transition-delay: 100ms">
            <h2 class="text-3xl font-extrabold text-primary mb-4">
                {{ $content['title'] ?? 'Tentang Organisasi' }}
            </h2>
            <p class="text-gray-600 leading-relaxed mb-6">
                {{ $content['body'] ?? 'Deskripsi singkat sejarah, visi, dan misi organisasi.' }}
            </p>

            @if (! empty($content['stats']))
                <div class="grid grid-cols-3 gap-4">
                    @foreach ($content['stats'] as $stat)
                        <div>
                            <p class="text-2xl font-extrabold text-secondary">{{ $stat['value'] }}</p>
                            <p class="text-xs text-gray-500">{{ $stat['label'] }}</p>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</section>
