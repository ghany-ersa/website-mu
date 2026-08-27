@php
    $content = $section['content'] ?? [];
    $organization = $organization ?? null;
    $stats = $content['stats'] ?? [
        ['value' => '10+', 'label' => 'Tahun Berdiri'],
        ['value' => '100+', 'label' => 'Anggota'],
        ['value' => '5+', 'label' => 'Program Aktif'],
    ];
@endphp

<section class="py-24">
    <div class="max-w-6xl mx-auto px-6 grid md:grid-cols-2 gap-16 items-center">
        <div class="reveal" style="transition-delay: 100ms">
            <span class="block w-10 h-px bg-secondary mb-6"></span>
            <h2 class="text-3xl md:text-4xl text-primary mb-6 leading-tight">
                {{ $content['title'] ?? 'Tentang Organisasi' }}
            </h2>
            <p class="text-gray-600 leading-relaxed mb-10">
                {{ $content['body'] ?? 'Deskripsi singkat sejarah, visi, dan misi organisasi.' }}
            </p>

            <div class="grid grid-cols-3 gap-6 border-t border-gray-200 pt-8">
                @foreach ($stats as $stat)
                    <div>
                        <p class="text-3xl text-secondary mb-1">{{ $stat['value'] }}</p>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">{{ $stat['label'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="reveal relative">
            <div class="absolute -inset-3 border border-primary/15 rounded-brand"></div>
            <div class="relative aspect-[4/5] rounded-brand overflow-hidden shadow-soft">
                @if (! empty($content['image']))
                    <img src="{{ $content['image'] }}" alt="{{ $content['title'] ?? '' }}" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full bg-gray-100 flex items-center justify-center text-gray-400 text-sm">Foto/Video</div>
                @endif
            </div>
        </div>
    </div>
</section>
