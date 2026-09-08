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

<section class="py-14 sm:py-20 md:py-24 bg-surface">
    <div class="max-w-7xl mx-auto px-5 sm:px-6">
        <div class="mb-10 sm:mb-12 md:mb-16 text-center md:text-left max-w-2xl">
            @if (! empty($content['label']))
                <span class="reveal inline-block font-headline text-secondary font-bold tracking-[0.2em] uppercase text-xs mb-4">{{ $content['label'] }}</span>
            @endif
            <h2 class="reveal font-headline font-extrabold text-3xl md:text-5xl text-primary tracking-tight leading-tight mb-4">
                {{ $content['title'] ?? 'Tim Pengajar' }}
            </h2>
            @if (! empty($content['subtitle']))
                <p class="reveal font-body text-lg md:text-xl text-on-surface-variant leading-relaxed italic" style="transition-delay: 80ms">
                    "{{ $content['subtitle'] }}"
                </p>
            @endif
        </div>

        @if (empty($people))
            <p class="text-center font-body text-on-surface-variant">Belum ada data pengajar.</p>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-6 md:gap-8">
                @if ($head)
                    <div class="reveal lg:col-span-3 grid md:grid-cols-5 bg-surface-container-lowest rounded-2xl overflow-hidden shadow-ambient border border-outline-variant/20">
                        <div class="md:col-span-2 relative h-72 md:h-auto overflow-hidden">
                            @if (! empty($head['photo']))
                                <img src="{{ $head['photo'] }}" alt="{{ $head['name'] ?? '' }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full min-h-[18rem] bg-surface-container-high flex items-center justify-center">
                                    <x-tenant.icon name="person" class="text-6xl text-on-surface-variant" />
                                </div>
                            @endif
                            <div class="absolute inset-0 bg-gradient-to-t from-primary/50 to-transparent"></div>
                        </div>
                        <div class="md:col-span-3 p-6 sm:p-8 md:p-12 flex flex-col justify-center">
                            @if (! empty($head['role']))
                                <span class="font-headline text-secondary font-bold text-xs uppercase tracking-widest mb-2">{{ $head['role'] }}</span>
                            @endif
                            <h3 class="font-headline font-bold text-2xl md:text-3xl text-primary mb-4 italic">{{ $head['name'] ?? '' }}</h3>
                            <div class="w-12 h-1 bg-secondary mb-6"></div>
                            @if (! empty($head['bio']))
                                <p class="font-body text-lg md:text-xl text-on-surface italic leading-relaxed">"{{ $head['bio'] }}"</p>
                            @endif
                        </div>
                    </div>
                @endif

                @foreach ($rest as $index => $person)
                    <div class="reveal bg-surface-container-lowest rounded-2xl overflow-hidden shadow-ambient flex flex-col"
                         style="transition-delay: {{ min($index * 60, 240) }}ms">
                        <div class="h-60 relative overflow-hidden group bg-surface-container-high">
                            @if (! empty($person['photo']))
                                {{-- Desaturated until hover, as in the source: it keeps a grid of
                                     photos from many different cameras looking like one set. --}}
                                <img src="{{ $person['photo'] }}" alt="{{ $person['name'] ?? '' }}" loading="lazy"
                                     class="w-full h-full object-cover grayscale group-hover:grayscale-0 transition-all duration-500">
                            @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <x-tenant.icon name="person" class="text-5xl text-on-surface-variant" />
                                </div>
                            @endif
                            @if (! empty($person['subject']))
                                <span class="absolute top-4 right-4 bg-secondary px-3 py-1 rounded-full text-white font-headline font-bold text-[10px] uppercase tracking-wide">
                                    {{ $person['subject'] }}
                                </span>
                            @endif
                        </div>
                        <div class="p-5 sm:p-6 flex flex-col grow">
                            <h4 class="font-headline font-bold text-xl text-primary mb-1">{{ $person['name'] ?? '' }}</h4>
                            @if (! empty($person['role']))
                                <p class="font-headline text-xs font-medium text-on-surface-variant mb-4">{{ $person['role'] }}</p>
                            @endif
                            @if (! empty($person['bio']))
                                <p class="font-body italic text-on-surface leading-relaxed mt-auto border-l-2 border-secondary/40 pl-4">
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
