{{-- Default rendering for a school's activities: the plain card grid every organization gets,
     matching program-unggulan/standar's look. The `sd-madya` variant is the bento version.

     Auto-binds to the organization's `kegiatan`-type programs when $organization is in scope
     (tenant page render); falls back to $content['items'] in template-preview context - see
     daftar-berita.blade.php for the full rationale. --}}
@php
    $content = $section['content'] ?? [];

    $items = isset($organization)
        ? $organization->programs()->ofType('kegiatan')->get()->map(fn ($program) => [
            'title' => $program->title,
            'description' => $program->description,
            'icon' => $program->icon,
            'image' => $program->image,
            'category' => $program->category,
        ])->all()
        : ($content['items'] ?? [
            ['title' => 'Sholat Dhuha', 'description' => 'Dilaksanakan setiap pagi sebelum pembelajaran dimulai.', 'icon' => '🕌'],
            ['title' => 'Tahfidz Quran', 'description' => 'Program hafalan Al-Quran sesuai perkembangan usia anak.', 'icon' => '📖'],
            ['title' => 'Hizbul Wathan', 'description' => 'Kepanduan untuk karakter mandiri dan peduli lingkungan.', 'icon' => '🏕️'],
        ]);
@endphp

<section class="py-16">
    <div class="max-w-6xl mx-auto px-5 sm:px-6">
        <h2 class="reveal text-3xl font-extrabold text-primary mb-10 text-center">
            {{ $content['title'] ?? 'Kegiatan' }}
        </h2>

        @if (empty($items))
            <p class="text-center text-gray-500">Belum ada kegiatan.</p>
        @else
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($items as $index => $item)
                    <div class="reveal bg-white rounded-brand shadow-soft overflow-hidden flex flex-col"
                         style="transition-delay: {{ min($index * 60, 240) }}ms">
                        @if (! empty($item['image']))
                            <img src="{{ $item['image'] }}" alt="{{ $item['title'] ?? '' }}" loading="lazy"
                                 class="w-full aspect-video object-cover">
                        @endif
                        <div class="p-6">
                            @if (! empty($item['icon']))
                                <span class="block text-3xl mb-3">{{ $item['icon'] }}</span>
                            @endif
                            @if (! empty($item['category']))
                                <span class="block text-[10px] font-bold uppercase tracking-widest text-secondary mb-1">{{ $item['category'] }}</span>
                            @endif
                            <h3 class="font-bold text-lg text-primary mb-2">{{ $item['title'] ?? '' }}</h3>
                            @if (! empty($item['description']))
                                <p class="text-gray-600 text-sm leading-relaxed">{{ $item['description'] }}</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
