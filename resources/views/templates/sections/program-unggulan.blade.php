{{-- Auto-binds to the organization's programs when $organization is in scope (tenant page
     render); falls back to $content['items'] sample data in template-preview context —
     see daftar-berita.blade.php for the full rationale. Reused by layanan.blade.php with
     $programType = 'layanan' to pull from the same Program table's "layanan" pool instead. --}}
@php
    $content = $section['content'] ?? [];
    $dummyItems = ($programType ?? 'program') === 'layanan'
        ? [
            ['title' => 'Layanan Konsultasi', 'description' => 'Konsultasi dan pendampingan bagi masyarakat.', 'icon' => '🗣️'],
            ['title' => 'Layanan Administrasi', 'description' => 'Pengurusan surat dan dokumen organisasi.', 'icon' => '📄'],
            ['title' => 'Layanan Sosial', 'description' => 'Bantuan dan pemberdayaan bagi warga kurang mampu.', 'icon' => '❤️'],
        ]
        : [
            ['title' => 'Program Unggulan 1', 'description' => 'Deskripsi singkat program unggulan pertama.', 'icon' => '⭐'],
            ['title' => 'Program Unggulan 2', 'description' => 'Deskripsi singkat program unggulan kedua.', 'icon' => '🎯'],
            ['title' => 'Program Unggulan 3', 'description' => 'Deskripsi singkat program unggulan ketiga.', 'icon' => '🚀'],
        ];
    $items = isset($organization)
        ? $organization->programs()->ofType($programType ?? 'program')->get()->map(fn ($program) => [
            'title' => $program->title,
            'description' => $program->description,
            'icon' => $program->icon,
        ])
        : ($content['items'] ?? $dummyItems);

    // See hero.blade.php's header comment for the shared $isExclusive resolution rationale.
    $isExclusive = isset($template)
        ? ($template->structure['exclusive'] ?? false)
        : ($organization->template?->structure['exclusive'] ?? false);
@endphp

@if ($isExclusive)
    <section class="py-24 bg-softBg">
        <div class="max-w-6xl mx-auto px-6">
            <div class="reveal text-center mb-16">
                <span class="block w-10 h-px bg-secondary mx-auto mb-6"></span>
                <h2 class="text-3xl md:text-4xl text-primary">
                    {{ $content['title'] ?? 'Program Unggulan' }}
                </h2>
            </div>
            <div class="grid md:grid-cols-3 gap-x-10 gap-y-12">
                @foreach ($items as $item)
                    @php $item = is_array($item) ? $item : ['title' => $item]; @endphp
                    <div class="reveal group" style="transition-delay: {{ $loop->index * 100 }}ms">
                        <div class="flex items-center gap-3 mb-3">
                            <span class="text-2xl">{{ $item['icon'] ?? $loop->iteration }}</span>
                            <span class="flex-1 h-px bg-gray-200 group-hover:bg-secondary transition-colors duration-300"></span>
                        </div>
                        <p class="font-semibold text-primary mb-1.5">{{ $item['title'] }}</p>
                        @if (! empty($item['description']))
                            <p class="text-sm text-gray-500 leading-relaxed">{{ $item['description'] }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@else
    <section class="py-16 bg-softBg">
        <div class="max-w-6xl mx-auto px-6">
            <h2 class="reveal text-3xl font-extrabold text-primary mb-10 text-center">
                {{ $content['title'] ?? 'Program Unggulan' }}
            </h2>
            <div class="grid md:grid-cols-3 gap-6">
                @foreach ($items as $item)
                    @php $item = is_array($item) ? $item : ['title' => $item]; @endphp
                    <div class="reveal group bg-white rounded-brand p-6 shadow-soft transition-all duration-300 hover:-translate-y-1.5 hover:shadow-float"
                         style="transition-delay: {{ $loop->index * 100 }}ms">
                        <div class="w-11 h-11 rounded-brand bg-secondary/10 text-secondary flex items-center justify-center font-bold mb-4 text-lg transition-transform duration-300 group-hover:scale-110">
                            {{ $item['icon'] ?? $loop->iteration }}
                        </div>
                        <p class="font-semibold text-gray-800 mb-1">{{ $item['title'] }}</p>
                        @if (! empty($item['description']))
                            <p class="text-sm text-gray-500">{{ $item['description'] }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
