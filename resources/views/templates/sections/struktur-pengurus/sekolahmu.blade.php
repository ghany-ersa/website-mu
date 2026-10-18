{{-- Port of comprof-SD-Madya pengajar.vue: the head teacher gets a full-width feature card
     (portrait beside their name and a pull quote), everyone else a photo card with their
     subject on a chip and their bio as a quote.

     Auto-binds to the organization's officers like struktur-pengurus/standar, but reads the
     school-facing columns (subject, bio) added to that table. The head is whoever's role
     mentions "kepala" - matching how the source picks its principal - falling back to the
     first person in order when no role says so, so the layout never loses its lead card. --}}
@php
    $content = $section['content'] ?? [];

    $people = isset($organization)
        ? $organization->officers()->active()->get()->map(fn ($officer) => [
            'name' => $officer->name,
            'role' => $officer->role,
            'subject' => $officer->subject,
            'bio' => $officer->bio,
            'photo' => $officer->photo,
        ])->all()
        : ($content['items'] ?? [
            ['name' => '[Nama Kepala Sekolah]', 'role' => 'Kepala Sekolah', 'bio' => 'Pendidikan bukan sekadar mengisi wadah, melainkan menyalakan api semangat keimanan dan ilmu pengetahuan.'],
            ['name' => '[Nama Guru]', 'role' => 'Wali Kelas 6A', 'subject' => 'Matematika', 'bio' => 'Membangun logika berpikir yang kuat sejak dini.'],
            ['name' => '[Nama Guru]', 'role' => 'Guru Sains', 'subject' => 'IPA', 'bio' => 'Eksplorasi alam adalah kelas yang tidak berdinding.'],
            ['name' => '[Nama Guru]', 'role' => 'Guru Al-Islam', 'subject' => 'Al-Islam', 'bio' => 'Ilmu tanpa adab terputus dari tujuannya.'],
        ]);

    $headIndex = collect($people)->search(fn ($person) => str_contains(mb_strtolower($person['role'] ?? ''), 'kepala'));
    $head = $headIndex === false ? ($people[0] ?? null) : $people[$headIndex];
    $rest = collect($people)->reject(fn ($person, $index) => $index === ($headIndex === false ? 0 : $headIndex))->values();
@endphp

<section class="section-y bg-surface">
    <div class="shell">
        <div class="mb-8 sm:mb-10 text-center md:text-left max-w-2xl">
            @if (! empty($content['label']))
                <span class="reveal inline-block font-headline text-secondary font-bold tracking-[0.2em] uppercase text-[11px] mb-3">{{ $content['label'] }}</span>
            @endif
            <h2 class="reveal font-headline font-extrabold t-h2 text-primary mb-3">
                {{ $content['title'] ?? 'Tim Pengajar' }}
            </h2>
            @if (! empty($content['subtitle']))
                <p class="reveal font-body t-body text-on-surface-variant italic measure" style="transition-delay: 80ms">
                    "{{ $content['subtitle'] }}"
                </p>
            @endif
        </div>

        @if (empty($people))
            <p class="text-center font-body text-on-surface-variant">Belum ada data pengajar.</p>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
                @if ($head)
                    <div class="reveal lg:col-span-3 grid md:grid-cols-5 bg-surface-container-lowest rounded-2xl overflow-hidden shadow-ambient border border-outline-variant/20">
                        <div class="md:col-span-2 relative h-60 md:h-auto overflow-hidden">
                            @if (! empty($head['photo']))
                                <img src="{{ $head['photo'] }}" alt="{{ $head['name'] ?? '' }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full min-h-[18rem] bg-surface-container-high flex items-center justify-center">
                                    <svg class="w-14 h-14 text-on-surface-variant" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M12 12a5 5 0 1 0 0-10 5 5 0 0 0 0 10Zm0 2c-4.42 0-8 2.24-8 5v1h16v-1c0-2.76-3.58-5-8-5Z" />
                                    </svg>
                                </div>
                            @endif
                            <div class="absolute inset-0 bg-gradient-to-t from-primary/50 to-transparent"></div>
                        </div>
                        <div class="md:col-span-3 p-5 sm:p-7 md:p-9 flex flex-col justify-center">
                            @if (! empty($head['role']))
                                <span class="font-headline text-secondary font-bold text-xs uppercase tracking-widest mb-2">{{ $head['role'] }}</span>
                            @endif
                            <h3 class="font-headline font-bold t-h3 sm:text-2xl text-primary mb-3 italic">{{ $head['name'] ?? '' }}</h3>
                            <div class="w-10 h-0.5 bg-secondary mb-4"></div>
                            @if (! empty($head['bio']))
                                <p class="font-body t-body text-on-surface italic">"{{ $head['bio'] }}"</p>
                            @endif
                        </div>
                    </div>
                @endif

                @foreach ($rest as $index => $person)
                    <div class="reveal bg-surface-container-lowest rounded-2xl overflow-hidden shadow-ambient flex flex-col"
                         style="transition-delay: {{ min($index * 60, 240) }}ms">
                        <div class="aspect-[4/3] relative overflow-hidden group bg-surface-container-high">
                            @if (! empty($person['photo']))
                                {{-- Desaturated until hover, as in the source: it keeps a grid of
                                     photos from many different cameras looking like one set. --}}
                                <img src="{{ $person['photo'] }}" alt="{{ $person['name'] ?? '' }}" loading="lazy"
                                     class="w-full h-full object-cover grayscale group-hover:grayscale-0 transition-all duration-500">
                            @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <svg class="w-9 h-9 text-on-surface-variant" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M12 12a5 5 0 1 0 0-10 5 5 0 0 0 0 10Zm0 2c-4.42 0-8 2.24-8 5v1h16v-1c0-2.76-3.58-5-8-5Z" />
                                    </svg>
                                </div>
                            @endif
                            @if (! empty($person['subject']))
                                <span class="absolute top-4 right-4 bg-secondary px-3 py-1 rounded-full text-white font-headline font-bold text-[10px] uppercase tracking-wide">
                                    {{ $person['subject'] }}
                                </span>
                            @endif
                        </div>
                        <div class="p-4 sm:p-5 flex flex-col grow">
                            <h4 class="font-headline font-bold text-[15px] text-primary mb-0.5">{{ $person['name'] ?? '' }}</h4>
                            @if (! empty($person['role']))
                                <p class="font-headline text-xs font-medium text-on-surface-variant mb-3">{{ $person['role'] }}</p>
                            @endif
                            @if (! empty($person['bio']))
                                <p class="font-body italic text-[13px] text-on-surface leading-relaxed mt-auto border-l-2 border-secondary/40 pl-3">
                                    "{{ $person['bio'] }}"
                                </p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
