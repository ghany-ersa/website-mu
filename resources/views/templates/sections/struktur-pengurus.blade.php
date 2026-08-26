{{-- Auto-binds to the organization's officers when $organization is in scope (tenant page
     render); falls back to $content['items'] sample data in template-preview context —
     see daftar-berita.blade.php for the full rationale. --}}
@php
    $content = $section['content'] ?? [];
    $items = isset($organization)
        ? $organization->officers()->get()->map(fn ($officer) => [
            'name' => $officer->name,
            'role' => $officer->role,
            'photo' => $officer->photo,
        ])
        : ($content['items'] ?? [
            ['name' => '[Nama]', 'role' => 'Ketua'],
            ['name' => '[Nama]', 'role' => 'Sekretaris'],
            ['name' => '[Nama]', 'role' => 'Bendahara'],
            ['name' => '[Nama]', 'role' => 'Anggota'],
        ]);

    // See hero.blade.php's header comment for the shared $isExclusive resolution rationale.
    $isExclusive = isset($template)
        ? ($template->structure['exclusive'] ?? false)
        : ($organization->template?->structure['exclusive'] ?? false);
@endphp

@if ($isExclusive)
    <section class="py-24">
        <div class="max-w-6xl mx-auto px-6">
            <div class="reveal text-center mb-16">
                <span class="block w-10 h-px bg-secondary mx-auto mb-6"></span>
                <h2 class="text-3xl md:text-4xl text-primary">
                    {{ $content['title'] ?? 'Struktur Pengurus' }}
                </h2>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-x-8 gap-y-12">
                @foreach ($items as $item)
                    <div class="reveal text-center" style="transition-delay: {{ $loop->index * 80 }}ms">
                        <div class="aspect-square rounded-brand overflow-hidden bg-gray-100 mb-4 border-b-2 border-secondary">
                            @if (! empty($item['photo']))
                                <img src="{{ $item['photo'] }}" alt="{{ $item['name'] }}" class="w-full h-full object-cover">
                            @endif
                        </div>
                        <p class="font-semibold text-primary text-sm">{{ $item['name'] }}</p>
                        <p class="text-xs text-secondary uppercase tracking-wide mt-1">{{ $item['role'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@else
    <section class="py-16 bg-softBg">
        <div class="max-w-6xl mx-auto px-6">
            <h2 class="reveal text-3xl font-extrabold text-primary mb-10 text-center">
                {{ $content['title'] ?? 'Struktur Pengurus' }}
            </h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                @foreach ($items as $item)
                    <div class="reveal group text-center" style="transition-delay: {{ $loop->index * 100 }}ms">
                        <div class="aspect-square rounded-brand overflow-hidden bg-gray-100 mb-3 ring-2 ring-transparent transition-all duration-300 group-hover:-translate-y-1 group-hover:shadow-float group-hover:ring-secondary">
                            @if (! empty($item['photo']))
                                <img src="{{ $item['photo'] }}" alt="{{ $item['name'] }}" class="w-full h-full object-cover">
                            @endif
                        </div>
                        <p class="font-semibold text-gray-800 text-sm">{{ $item['name'] }}</p>
                        <p class="text-xs text-gray-500">{{ $item['role'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
