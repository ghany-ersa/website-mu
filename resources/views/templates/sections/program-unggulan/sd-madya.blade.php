{{-- Port of comprof-SD-Madya kegiatan.vue's "Ekstrakurikuler" bento: a grid where the first
     card is wide, the second tall, and the rest fill in around them, each alternating between
     a light card, a solid brand panel, and an accent panel.

     Auto-binds to the organization's programs like program-unggulan/standar, but carries the
     photo and category the `kegiatan` type adds (see the programs table migration). Falls back
     to $content['items'] in template-preview context, where no organization exists. --}}
@php
    $content = $section['content'] ?? [];
    $type = $programType ?? 'program';

    $items = isset($organization)
        ? $organization->programs()->ofType($type)->get()->map(fn ($program) => [
            'title' => $program->title,
            'description' => $program->description,
            'icon' => $program->icon,
            'image' => $program->image,
            'category' => $program->category,
        ])->all()
        : ($content['items'] ?? [
            ['title' => 'Futsal', 'description' => 'Melatih kerjasama tim, fisik, dan sportivitas siswa.', 'icon' => '⚽'],
            ['title' => 'Tapak Suci', 'description' => 'Seni bela diri khas Muhammadiyah untuk kemandirian diri.', 'icon' => '🥋'],
            ['title' => 'Hizbul Wathan', 'description' => 'Kepanduan untuk karakter mandiri dan peduli lingkungan.', 'icon' => '🏕️'],
            ['title' => 'Marching Band', 'description' => 'Harmonisasi nada dan langkah dalam balutan kedisiplinan.', 'icon' => '🥁'],
        ]);

    // The bento rhythm from the source: wide, tall, small, wide, then repeat. Cards cycle
    // through three treatments so no two neighbours look alike.
    $spans = ['md:col-span-2', 'md:col-span-1 md:row-span-2', 'md:col-span-1', 'md:col-span-2'];
    $tones = ['light', 'primary', 'accent'];
@endphp

<section class="py-14 sm:py-20 md:py-24 bg-surface-container-low">
    <div class="max-w-7xl mx-auto px-5 sm:px-6">
        <div class="text-center mb-10 sm:mb-12 md:mb-16">
            <h2 class="reveal font-headline text-2xl md:text-3xl font-extrabold text-primary uppercase tracking-[0.2em] mb-4">
                {{ $content['title'] ?? 'Program Unggulan' }}
            </h2>
            <div class="h-1 w-20 bg-secondary mx-auto"></div>
            @if (! empty($content['subtitle']))
                <p class="reveal font-body text-lg text-on-surface-variant max-w-2xl mx-auto mt-6">{{ $content['subtitle'] }}</p>
            @endif
        </div>

        @if (empty($items))
            <p class="text-center font-body text-on-surface-variant">Belum ada program.</p>
        @else
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 md:auto-rows-[280px]">
                @foreach ($items as $index => $item)
                    @php
                        $span = $spans[$index % count($spans)];
                        $tone = $tones[$index % count($tones)];
                        $card = match ($tone) {
                            'primary' => 'bg-primary text-white',
                            'accent' => 'bg-secondary text-white',
                            default => 'bg-surface-container-lowest',
                        };
                        $muted = $tone === 'light' ? 'text-on-surface-variant' : 'text-white/80';
                        $heading = $tone === 'light' ? 'text-primary' : 'text-white';
                        $tall = str_contains($span, 'row-span-2');
                    @endphp
                    <div class="reveal {{ $span }} {{ $card }} rounded-2xl p-5 sm:p-6 md:p-8 overflow-hidden group flex {{ $tall ? 'flex-col justify-between' : 'items-center gap-4 sm:gap-6' }}"
                         style="transition-delay: {{ min($index * 60, 240) }}ms">
                        <div class="{{ $tall ? '' : 'flex-1 min-w-0' }}">
                            @if (! empty($item['icon']))
                                <span class="block text-3xl mb-3">{{ $item['icon'] }}</span>
                            @endif
                            @if (! empty($item['category']))
                                <span class="block font-headline text-[10px] font-bold uppercase tracking-widest {{ $muted }} mb-1">{{ $item['category'] }}</span>
                            @endif
                            <h3 class="font-headline font-bold text-xl md:text-2xl {{ $heading }} mb-2">{{ $item['title'] ?? '' }}</h3>
                            @if (! empty($item['description']))
                                <p class="font-body {{ $muted }} text-sm md:text-base">{{ $item['description'] }}</p>
                            @endif
                        </div>

                        @if (! empty($item['image']))
                            @if ($tall)
                                <img src="{{ $item['image'] }}" alt="{{ $item['title'] ?? '' }}" loading="lazy"
                                     class="mt-6 rounded-xl h-40 w-full object-cover shadow-lg opacity-80 group-hover:opacity-100 transition-opacity">
                            @else
                                {{-- Hidden on the narrowest screens: at that width the circle
                                     would squeeze the text it is meant to accompany. --}}
                                <img src="{{ $item['image'] }}" alt="{{ $item['title'] ?? '' }}" loading="lazy"
                                     class="hidden sm:block w-28 h-28 rounded-full object-cover border-4 border-white shadow-xl shrink-0">
                            @endif
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
