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
    <div class="shell">
        <h2 class="reveal font-headline t-h2 font-extrabold text-primary mb-8 text-center">
            {{ $content['title'] ?? 'Kegiatan' }}
        </h2>

        @if (empty($items))
            <p class="text-center text-gray-500">Belum ada kegiatan.</p>
        @else
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
                @foreach ($items as $index => $item)
                    <div class="reveal bg-white rounded-brand shadow-soft overflow-hidden flex flex-col"
                         style="transition-delay: {{ min($index * 60, 240) }}ms">
                        @if (! empty($item['image']))
                            <img src="{{ $item['image'] }}" alt="{{ $item['title'] ?? '' }}" loading="lazy"
                                 class="w-full aspect-video object-cover">
                        @endif
                        <div class="p-5">
                            @if (! empty($item['icon']))
                                <span class="block text-2xl mb-2.5">{{ $item['icon'] }}</span>
                            @endif
                            @if (! empty($item['category']))
                                <span class="block text-[10px] font-bold uppercase tracking-widest text-secondary mb-1">{{ $item['category'] }}</span>
                            @endif
                            <h3 class="font-headline font-bold text-[15px] text-primary mb-1.5">{{ $item['title'] ?? '' }}</h3>
                            @if (! empty($item['description']))
                                <p class="text-gray-600 text-[13px] leading-relaxed">{{ $item['description'] }}</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
