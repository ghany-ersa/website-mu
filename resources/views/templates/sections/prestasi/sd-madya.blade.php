{{-- Port of the "Prestasi Highlight (Bento Grid)" block on comprof-SD-Madya's index.vue - the
     homepage teaser, as opposed to prestasi/standar which is the full filterable list on the
     dedicated Prestasi page.

     Layout: a centred header, then a 3-column grid where the newest achievement fills a 2x2
     tile and the next two stack beside it. Every tile is a photo under a bottom-up gradient, so
     the text stays legible whatever the photo. The second small tile uses the secondary colour
     to break up the navy, exactly as the source alternates them.

     Auto-binds to the organization's achievements; falls back to $content['items'] in
     template-preview context - see daftar-berita.blade.php for the full rationale. --}}
@php
    $content = $section['content'] ?? [];

    $items = isset($organization)
        ? $organization->achievements()
            ->active()
            ->take(3)
            ->get()
            ->map(fn ($achievement) => [
                'title' => $achievement->title,
                'level' => $achievement->level,
                'description' => $achievement->description,
                'image' => $achievement->image,
            ])->all()
        : ($content['items'] ?? [
            ['title' => 'Borong Juara FLS3N dan O2SN', 'level' => 'Tingkat Kecamatan', 'description' => 'Keberhasilan ini menjadi bukti nyata komitmen sekolah dalam mengembangkan potensi akademik maupun non-akademik peserta didik secara seimbang.'],
            ['title' => 'Tahfidz Quran', 'description' => 'Lulusan hafal Al-Qur\'an Juz 30.'],
            ['title' => 'Ekskul Unggulan', 'description' => 'Pembinaan bakat minat yang intensif untuk hasil maksimal.'],
        ]);

    $main = $items[0] ?? null;
    $small = array_slice($items, 1, 2);
@endphp

<section class="py-14 sm:py-20 md:py-24 bg-surface-container-low">
    <div class="max-w-7xl mx-auto px-5 sm:px-6">
        <div class="text-center mb-10 sm:mb-12 md:mb-16 space-y-3 sm:space-y-4">
            @if (! empty($content['label']))
                <span class="reveal block font-headline text-secondary font-bold tracking-[0.2em] uppercase text-xs">
                    {{ $content['label'] }}
                </span>
            @endif
            <h2 class="reveal font-headline font-bold text-3xl md:text-4xl text-primary">
                {{ $content['title'] ?? 'Prestasi' }}
            </h2>
            @if (! empty($content['subtitle']))
                <p class="reveal font-body text-lg text-on-surface-variant max-w-2xl mx-auto" style="transition-delay: 80ms">
                    {{ $content['subtitle'] }}
                </p>
            @endif
        </div>

        @if (empty($items))
            <p class="text-center font-body text-on-surface-variant">Belum ada prestasi.</p>
        @else
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-6 md:auto-rows-[250px]">
                @if ($main)
                    <div class="reveal md:col-span-2 md:row-span-2 bg-primary rounded-3xl p-6 sm:p-8 flex flex-col justify-end relative overflow-hidden group min-h-[280px] sm:min-h-[340px]">
                        @if (! empty($main['image']))
                            <img src="{{ $main['image'] }}" alt="{{ $main['title'] ?? '' }}" loading="lazy"
                                 class="absolute inset-0 w-full h-full object-cover opacity-50 group-hover:scale-110 transition-transform duration-500">
                        @endif
                        <div class="absolute inset-0 bg-gradient-to-t from-primary to-transparent"></div>
                        <div class="relative z-10 space-y-4">
                            @if (! empty($main['level']))
                                <span class="inline-block px-3 py-1 rounded-full bg-secondary text-white font-headline text-[10px] font-bold uppercase tracking-wide">
                                    {{ $main['level'] }}
                                </span>
                            @endif
                            <h3 class="font-headline font-bold text-xl sm:text-2xl md:text-3xl text-white">{{ $main['title'] ?? '' }}</h3>
                            @if (! empty($main['description']))
                                <p class="font-body text-white/80 max-w-lg hidden md:block">{{ $main['description'] }}</p>
                            @endif
                        </div>
                    </div>
                @endif

                @foreach ($small as $index => $item)
                    {{-- The second tile takes the secondary wash, so the pair doesn't read as
                         one flat block of navy beside the main tile. --}}
                    @php
                        $gold = $index === 1;
                        // With a photo behind it the gradient carries the text, so white wins
                        // either way. Without one the gold tile is a flat light panel, and white
                        // on gold fails contrast - so that case flips to the dark ink instead.
                        $flat = empty($item['image']);
                        $tint = $gold ? 'from-secondary via-secondary/60 to-secondary/10' : 'from-primary via-primary/60 to-primary/10';
                        $ink = ($gold && $flat) ? 'text-primary' : 'text-white';
                        $inkMuted = ($gold && $flat) ? 'text-primary/75' : 'text-white/80';
                        $chip = ($gold && $flat) ? 'bg-primary/10' : 'bg-white/20 backdrop-blur-sm';
                    @endphp
                    <div class="reveal relative rounded-3xl overflow-hidden min-h-[190px] sm:min-h-[220px] group {{ $flat ? ($gold ? 'bg-secondary' : 'bg-primary') : '' }}"
                         style="transition-delay: {{ 80 + $index * 80 }}ms">
                        @if (! empty($item['image']))
                            <img src="{{ $item['image'] }}" alt="{{ $item['title'] ?? '' }}" loading="lazy"
                                 class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            <div class="absolute inset-0 bg-gradient-to-t {{ $tint }}"></div>
                        @endif
                        <div class="relative z-10 p-5 sm:p-6 flex flex-col justify-between h-full">
                            <div class="w-12 h-12 {{ $chip }} rounded-full flex items-center justify-center">
                                <x-tenant.icon name="{{ $gold ? 'music_note' : 'menu_book' }}" filled class="{{ $ink }}" />
                            </div>
                            <div class="mt-4">
                                <h4 class="font-headline font-bold {{ $ink }}">{{ $item['title'] ?? '' }}</h4>
                                @if (! empty($item['description']))
                                    <p class="font-body {{ $inkMuted }} text-sm">{{ $item['description'] }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
