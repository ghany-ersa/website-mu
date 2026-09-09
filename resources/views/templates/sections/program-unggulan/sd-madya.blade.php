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

<section class="section-y bg-surface-container-low">
    <div class="shell shell-wide">
        <div class="text-center mb-8 sm:mb-10">
            <h2 class="reveal font-headline t-h2 font-extrabold text-primary uppercase tracking-[0.15em] mb-3">
                {{ $content['title'] ?? 'Program Unggulan' }}
            </h2>
            <div class="h-0.5 w-14 bg-secondary mx-auto"></div>
            @if (! empty($content['subtitle']))
                <p class="reveal font-body t-body text-on-surface-variant measure mx-auto mt-4">{{ $content['subtitle'] }}</p>
            @endif
        </div>

        @if (empty($items))
            <p class="text-center font-body text-on-surface-variant">Belum ada program.</p>
        @else
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 md:auto-rows-[235px]">
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
                    <div class="reveal {{ $span }} {{ $card }} rounded-2xl p-5 sm:p-6 overflow-hidden group flex {{ $tall ? 'flex-col justify-between' : 'items-center gap-4 sm:gap-6' }}"
                         style="transition-delay: {{ min($index * 60, 240) }}ms">
                        <div class="{{ $tall ? '' : 'flex-1 min-w-0' }}">
                            @if (! empty($item['icon']))
                                <span class="block text-2xl mb-2.5">{{ $item['icon'] }}</span>
                            @endif
                            @if (! empty($item['category']))
                                <span class="block font-headline text-[10px] font-bold uppercase tracking-widest {{ $muted }} mb-1">{{ $item['category'] }}</span>
                            @endif
                            <h3 class="font-headline font-bold t-h3 {{ $heading }} mb-1.5">{{ $item['title'] ?? '' }}</h3>
                            @if (! empty($item['description']))
                                <p class="font-body {{ $muted }} text-[13px] leading-relaxed">{{ $item['description'] }}</p>
                            @endif
                        </div>

                        @if (! empty($item['image']))
                            @if ($tall)
                                <img src="{{ $item['image'] }}" alt="{{ $item['title'] ?? '' }}" loading="lazy"
                                     class="mt-5 rounded-xl h-32 w-full object-cover shadow-lg opacity-80 group-hover:opacity-100 transition-opacity">
                            @else
                                {{-- Hidden on the narrowest screens: at that width the circle
                                     would squeeze the text it is meant to accompany. --}}
                                <img src="{{ $item['image'] }}" alt="{{ $item['title'] ?? '' }}" loading="lazy"
                                     class="hidden sm:block w-24 h-24 rounded-full object-cover border-4 border-white shadow-xl shrink-0">
                            @endif
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
