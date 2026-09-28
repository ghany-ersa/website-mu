{{-- Port of comprof-SD-Madya prestasi.vue: an optional stats strip, then a filter rail and a
     search box over a grid of achievement cards, each linking to the profile of whoever earned
     it. Filtering and searching happen client-side in Alpine - the whole list is already in the
     page, so this stays instant and needs no request per keystroke.

     Auto-binds to the organization's achievements when $organization is in scope (tenant page
     render); falls back to $content['items'] in template-preview context - see
     daftar-berita.blade.php for the full rationale. --}}
@php
    $content = $section['content'] ?? [];
    $limit = $content['limit'] ?? null;
    $stats = $content['stats'] ?? [];

    $items = isset($organization)
        ? $organization->achievements()
            ->active()
            ->with('students')
            ->when($limit, fn ($query) => $query->limit((int) $limit))
            ->get()
            ->map(fn ($achievement) => [
                'title' => $achievement->title,
                'category' => $achievement->category,
                'level' => $achievement->level,
                'rank' => $achievement->rank,
                'year' => $achievement->year,
                'description' => $achievement->description,
                'image' => $achievement->image,
                'students' => $achievement->students->map(fn ($student) => [
                    'name' => $student->name,
                    'url' => \App\Services\TenantPageUrl::student($organization, $student->slug),
                ])->all(),
            ])->all()
        : ($content['items'] ?? [
            ['title' => 'Juara 1 Olimpiade Matematika', 'category' => 'Akademik', 'level' => 'Tingkat Kabupaten', 'rank' => 'Juara 1', 'year' => '2026', 'description' => 'Meraih medali emas pada olimpiade tingkat kabupaten.', 'students' => []],
            ['title' => 'Juara 2 Turnamen Bulu Tangkis', 'category' => 'Olahraga', 'level' => 'Tingkat Kecamatan', 'rank' => 'Juara 2', 'year' => '2026', 'description' => 'Runner-up pada turnamen antar sekolah dasar.', 'students' => []],
            ['title' => 'Juara 1 Lomba Tari Tradisional', 'category' => 'Seni', 'level' => 'Tingkat Kabupaten', 'rank' => 'Juara 1', 'year' => '2025', 'description' => 'Menampilkan tari tradisional pada festival seni pelajar.', 'students' => []],
        ]);

    // Only offer a filter for categories that actually appear - an empty "Seni" tab on a school
    // that has never entered an art competition is just a dead end.
    $categories = collect($items)->pluck('category')->filter()->unique()->values()->all();
@endphp

<section class="section-y bg-surface"
         x-data="{
            filter: 'Semua',
            q: '',
            matches(el) {
                const category = el.dataset.category || '';
                const haystack = (el.dataset.search || '').toLowerCase();
                return (this.filter === 'Semua' || category === this.filter)
                    && (this.q === '' || haystack.includes(this.q.toLowerCase()));
            },
            get shown() {
                return this.$refs.grid ? [...this.$refs.grid.children].filter((el) => this.matches(el)).length : 0;
            },
         }">
    <div class="shell">
        @if (! empty($content['label']))
            <span class="reveal block font-headline text-secondary font-bold tracking-[0.2em] uppercase text-[11px] mb-3">{{ $content['label'] }}</span>
        @endif

        <h2 class="reveal font-headline t-h2 font-extrabold text-primary mb-2.5">
            {{ $content['title'] ?? 'Prestasi' }}
        </h2>

        @if (! empty($content['subtitle']))
            <p class="reveal font-body t-body text-on-surface-variant measure mb-8">{{ $content['subtitle'] }}</p>
        @endif

        @if ($stats)
            <div class="reveal grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4 mb-10">
                @foreach ($stats as $stat)
                    <div class="bg-primary rounded-2xl p-5 text-center">
                        <p class="font-headline text-xl md:text-2xl font-extrabold text-white">{{ $stat['value'] ?? '' }}</p>
                        <p class="font-body text-xs text-white/70 mt-1">{{ $stat['label'] ?? '' }}</p>
                    </div>
                @endforeach
            </div>
        @endif

        @if (empty($items))
            <p class="text-center font-body text-on-surface-variant">Belum ada prestasi.</p>
        @else
            @if (count($categories) > 1)
                <div class="reveal flex flex-col gap-4 mb-8">
                    <div class="flex gap-2 overflow-x-auto hide-scrollbar -mx-5 px-5 sm:-mx-6 sm:px-6 md:mx-0 md:px-0">
                        @foreach (array_merge(['Semua'], $categories) as $category)
                            <button type="button" @click="filter = {{ Js::from($category) }}"
                                    :class="filter === {{ Js::from($category) }}
                                        ? 'bg-primary text-white'
                                        : 'bg-surface-container-high text-on-surface-variant hover:bg-surface-container-highest'"
                                    class="px-4 py-2 rounded-full font-headline text-xs font-bold whitespace-nowrap transition-colors">
                                {{ $category }}
                            </button>
                        @endforeach
                    </div>

                    <div class="relative max-w-md">
                        <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-on-surface-variant pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 18a7 7 0 110-14 7 7 0 010 14z" />
                        </svg>
                        <input type="search" x-model="q" placeholder="Cari prestasi atau nama siswa…"
                               class="w-full pl-12 pr-4 py-3 rounded-full bg-surface-container-high font-body text-sm text-on-surface placeholder:text-on-surface-variant focus:outline-none focus:ring-2 focus:ring-primary transition">
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5" x-ref="grid">
                @foreach ($items as $index => $item)
                    <div x-show="matches($el)"
                         data-category="{{ $item['category'] ?? '' }}"
                         data-search="{{ collect([$item['title'] ?? '', $item['description'] ?? '', $item['rank'] ?? '', $item['year'] ?? ''])->merge(collect($item['students'] ?? [])->pluck('name'))->filter()->implode(' ') }}"
                         class="reveal bg-surface-container-lowest rounded-2xl overflow-hidden flex flex-col border border-outline-variant/30 hover:shadow-ambient transition-shadow"
                         style="transition-delay: {{ min($index * 40, 200) }}ms">
                        @if (! empty($item['image']))
                            <div class="h-36 overflow-hidden shrink-0">
                                <img src="{{ $item['image'] }}" alt="{{ $item['title'] ?? '' }}" loading="lazy" class="w-full h-full object-cover">
                            </div>
                        @endif

                        <div class="p-5 flex flex-col gap-3 flex-1">
                            <div class="flex justify-between items-start gap-2">
                                @if (! empty($item['level']))
                                    <span class="inline-block px-3 py-1 rounded-full bg-secondary/15 text-secondary font-headline text-[10px] font-bold uppercase tracking-wide">
                                        {{ $item['level'] }}
                                    </span>
                                @endif
                                @if (! empty($item['year']))
                                    <span class="font-headline text-xs font-bold text-on-surface-variant shrink-0">{{ $item['year'] }}</span>
                                @endif
                            </div>

                            <div class="flex items-start gap-3">
                                <div class="w-9 h-9 rounded-xl bg-secondary/15 flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5 text-secondary" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M18 4V2H6v2H2v3a5 5 0 0 0 4.1 4.92A6 6 0 0 0 11 15.91V18H7v2h10v-2h-4v-2.09a6 6 0 0 0 4.9-3.99A5 5 0 0 0 22 7V4h-4ZM4 7V6h2v3.83A3 3 0 0 1 4 7Zm16 0a3 3 0 0 1-2 2.83V6h2v1Z" />
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    @if (! empty($item['category']))
                                        <span class="block font-headline text-[10px] font-bold uppercase tracking-widest text-secondary">{{ $item['category'] }}</span>
                                    @endif
                                    <h3 class="font-headline text-[15px] font-bold text-primary leading-snug mt-0.5">{{ $item['title'] ?? '' }}</h3>
                                </div>
                            </div>

                            @if (! empty($item['description']))
                                <p class="font-body text-[13px] text-on-surface-variant leading-relaxed">{{ $item['description'] }}</p>
                            @endif

                            @if (! empty($item['students']))
                                <div class="mt-auto pt-4 border-t border-outline-variant/40 flex items-center gap-2 flex-wrap">
                                    <svg class="w-3.5 h-3.5 text-on-surface-variant shrink-0" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M12 12a5 5 0 1 0 0-10 5 5 0 0 0 0 10Zm0 2c-4.42 0-8 2.24-8 5v1h16v-1c0-2.76-3.58-5-8-5Z" />
                                    </svg>
                                    @foreach ($item['students'] as $student)
                                        @if (! empty($student['url']))
                                            <a href="{{ $student['url'] }}" class="font-headline text-xs font-bold text-primary hover:underline">{{ $student['name'] }}</a>
                                        @else
                                            <span class="font-headline text-xs font-bold text-on-surface-variant">{{ $student['name'] }}</span>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Only reachable once a filter or query is active, since the grid is non-empty. --}}
            <div x-show="shown === 0" x-cloak class="text-center py-16 flex flex-col items-center gap-3">
                <div class="w-16 h-16 rounded-full bg-surface-container-high flex items-center justify-center">
                    <svg class="w-8 h-8 text-on-surface-variant" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 18a7 7 0 1 1 0-14 7 7 0 0 1 0 14ZM8.5 8.5l5 5m0-5-5 5" />
                    </svg>
                </div>
                <p class="font-body text-on-surface-variant">Tidak ada prestasi yang cocok.</p>
                <button type="button" @click="filter = 'Semua'; q = ''"
                        class="mt-1 px-5 py-2 rounded-full bg-primary text-white font-headline text-sm font-bold">
                    Reset Pencarian
                </button>
            </div>
        @endif
    </div>
</section>
