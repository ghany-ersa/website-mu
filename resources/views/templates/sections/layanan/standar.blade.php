{{-- Auto-binds to the organization's layanan-type programs when $organization is in scope
     (tenant page render); falls back to $content['items'] sample data in template-preview
     context - see daftar-berita.blade.php for the full rationale. --}}
@php
    $content = $section['content'] ?? [];
    $dummyItems = [
        ['title' => 'Layanan Konsultasi', 'description' => 'Konsultasi dan pendampingan bagi masyarakat.', 'icon' => '🗣️'],
        ['title' => 'Layanan Administrasi', 'description' => 'Pengurusan surat dan dokumen organisasi.', 'icon' => '📄'],
        ['title' => 'Layanan Sosial', 'description' => 'Bantuan dan pemberdayaan bagi warga kurang mampu.', 'icon' => '❤️'],
    ];
    $items = isset($organization)
        ? $organization->programs()->ofType('layanan')->get()->map(fn ($program) => [
            'title' => $program->title,
            'description' => $program->description,
            'icon' => $program->icon,
        ])
        : ($content['items'] ?? $dummyItems);
@endphp

<section class="py-16 bg-softBg">
    <div class="max-w-6xl mx-auto px-6">
        <h2 class="reveal text-3xl font-extrabold text-primary mb-10 text-center">
            {{ $content['title'] ?? 'Layanan' }}
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
