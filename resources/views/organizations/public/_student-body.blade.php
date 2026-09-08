{{--
    Body of the pupil profile page, ported from comprof-SD-Madya's app/pages/siswa/[slug].vue:
    a dark hero with the pupil's avatar and pull quote, a four-tile stat strip, an about/interests
    block, and their achievements grouped by year.

    Achievements arrive already filtered to active and sorted newest-first by the controller.
--}}
@php
    $achievements = $student->achievements;

    // Highest scope reached, by position in Achievement::LEVELS (ordered narrow -> wide). Rows
    // with an unrecognised or missing level simply don't win the comparison.
    $highest = $achievements
        ->map(fn ($achievement) => $achievement->level)
        ->filter()
        ->sortByDesc(fn (string $level) => array_search($level, \App\Models\Achievement::LEVELS, true))
        ->first();

    $byYear = $achievements->groupBy(fn ($achievement) => $achievement->year ?: 'Tahun lain');

    $stats = [
        ['value' => $achievements->count(), 'label' => 'Total Prestasi'],
        ['value' => $achievements->where('category', 'Akademik')->count(), 'label' => 'Akademik'],
        ['value' => $achievements->where('category', 'Olahraga')->count(), 'label' => 'Olahraga'],
        ['value' => $highest ?: '-', 'label' => 'Pencapaian Tertinggi'],
    ];
@endphp

<article>
    <section class="relative px-6 py-16 md:py-24 bg-primary text-white overflow-hidden">
        <div class="absolute inset-0 opacity-10" aria-hidden="true">
            <div class="absolute top-0 right-0 w-96 h-96 bg-white rounded-full blur-[100px] -mr-48 -mt-48"></div>
            <div class="absolute bottom-0 left-0 w-96 h-96 bg-secondary rounded-full blur-[100px] -ml-48 -mb-48"></div>
        </div>

        <div class="max-w-5xl mx-auto relative z-10">
            <a href="{{ \App\Services\TenantPageUrl::for($organization, $organization->pages->firstWhere('is_home', true)) ?? '/' }}"
               class="inline-flex items-center gap-2 font-headline text-xs font-bold text-white/70 hover:text-white mb-8 transition-colors">
                <x-tenant.icon name="arrow_back" class="text-sm" />
                Kembali ke Beranda
            </a>

            <div class="flex flex-col md:flex-row gap-8 md:gap-12 items-start md:items-center">
                <div class="shrink-0">
                    @if ($student->photo)
                        <div class="w-32 h-32 md:w-40 md:h-40 rounded-2xl overflow-hidden border-4 border-white/20 shadow-ambient">
                            <img src="{{ $student->photo }}" alt="{{ $student->name }}" class="w-full h-full object-cover">
                        </div>
                    @else
                        <div class="w-32 h-32 md:w-40 md:h-40 rounded-2xl bg-secondary text-white flex items-center justify-center font-headline text-5xl md:text-6xl font-extrabold border-4 border-white/20 shadow-ambient">
                            {{ $student->initials() }}
                        </div>
                    @endif
                </div>

                <div class="flex-1 min-w-0">
                    <span class="inline-block px-3 py-1 rounded-full bg-white/15 text-white text-xs font-semibold font-headline mb-4">Profil Siswa</span>
                    <h1 class="font-headline text-3xl md:text-5xl font-extrabold tracking-tight mb-3 leading-tight">{{ $student->name }}</h1>
                    @if ($student->grade)
                        <p class="font-headline text-sm font-bold text-white/70 uppercase tracking-widest mb-6">{{ $student->grade }}</p>
                    @endif
                    @if ($student->quote)
                        <p class="font-body text-lg md:text-xl text-white/80 leading-relaxed italic border-l-2 border-white/40 pl-4">
                            "{{ $student->quote }}"
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <section class="max-w-5xl mx-auto px-6 py-12 md:py-16">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6 mb-12">
            @foreach ($stats as $stat)
                <div class="bg-surface-container-low rounded-2xl p-6 border border-outline-variant/30">
                    <div class="font-headline {{ is_int($stat['value']) ? 'text-3xl' : 'text-base' }} font-bold text-primary mb-1">{{ $stat['value'] }}</div>
                    <div class="font-headline text-xs uppercase tracking-widest text-on-surface-variant">{{ $stat['label'] }}</div>
                </div>
            @endforeach
        </div>

        @if ($student->bio || ! empty($student->hobbies))
            <div class="grid md:grid-cols-3 gap-8 mb-12">
                @if ($student->bio)
                    <div class="md:col-span-2">
                        <h2 class="font-headline text-xl font-bold text-primary mb-3">Tentang</h2>
                        <p class="font-body text-base text-on-surface leading-relaxed">{{ $student->bio }}</p>
                    </div>
                @endif
                @if (! empty($student->hobbies))
                    <div>
                        <h2 class="font-headline text-xl font-bold text-primary mb-3">Minat</h2>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($student->hobbies as $hobby)
                                <span class="px-3 py-1 rounded-full bg-secondary/15 text-secondary font-headline text-xs font-bold">{{ $hobby }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </section>

    <section class="max-w-5xl mx-auto px-6 pb-20">
        <h2 class="font-headline text-2xl md:text-3xl font-bold text-primary mb-2 leading-tight">Riwayat Prestasi</h2>
        <p class="font-body text-on-surface-variant mb-8">Daftar lengkap prestasi yang pernah diraih.</p>

        @forelse ($byYear as $year => $items)
            <div class="flex flex-col md:flex-row gap-6 mb-10">
                <div class="md:w-32 shrink-0">
                    <div class="font-headline text-2xl md:text-3xl font-bold text-primary">{{ $year }}</div>
                    <div class="font-headline text-xs uppercase tracking-widest text-on-surface-variant">{{ $items->count() }} prestasi</div>
                </div>

                <div class="flex-1 flex flex-col gap-4">
                    @foreach ($items as $achievement)
                        <div class="bg-surface-container-low rounded-2xl p-6 border border-outline-variant/30 flex flex-col sm:flex-row gap-4">
                            @if ($achievement->image)
                                <div class="w-full sm:w-32 h-32 rounded-xl overflow-hidden shrink-0">
                                    <img src="{{ $achievement->image }}" alt="{{ $achievement->title }}" loading="lazy" class="w-full h-full object-cover">
                                </div>
                            @endif
                            <div class="flex-1 flex flex-col gap-2 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    @if ($achievement->level)
                                        <span class="px-3 py-1 rounded-full bg-secondary/15 text-secondary font-headline text-[10px] font-bold uppercase tracking-wide">{{ $achievement->level }}</span>
                                    @endif
                                    @if ($achievement->category)
                                        <span class="font-headline text-[10px] font-bold uppercase tracking-widest text-on-surface-variant">{{ $achievement->category }}</span>
                                    @endif
                                </div>
                                <h3 class="font-headline text-lg font-bold text-primary leading-snug">{{ $achievement->title }}</h3>
                                @if ($achievement->description)
                                    <p class="font-body text-sm text-on-surface-variant leading-relaxed">{{ $achievement->description }}</p>
                                @endif
                                @if ($achievement->rank)
                                    <div class="flex items-center gap-2 mt-1">
                                        <x-tenant.icon name="emoji_events" class="text-sm text-on-surface-variant" />
                                        <span class="font-headline text-xs font-bold text-on-surface-variant">{{ $achievement->rank }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="text-center py-16 flex flex-col items-center gap-3">
                <div class="w-16 h-16 rounded-full bg-surface-container-high flex items-center justify-center">
                    <x-tenant.icon name="emoji_events" class="text-3xl text-on-surface-variant" />
                </div>
                <p class="font-body text-on-surface-variant">Belum ada prestasi tercatat.</p>
            </div>
        @endforelse
    </section>
</article>
