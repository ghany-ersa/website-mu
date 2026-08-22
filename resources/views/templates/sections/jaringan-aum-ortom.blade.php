{{-- Auto-binds to the organization's affiliated AUM/Ortom entries when $organization is in
     scope (tenant page render); falls back to $content['items'] sample data in
     template-preview context — see daftar-berita.blade.php for the full rationale. --}}
@php
    $content = $section['content'] ?? [];
    $items = isset($organization)
        ? $organization->networks()->get()->map(fn ($network) => [
            'name' => $network->name,
            'type' => $network->type,
        ])
        : ($content['items'] ?? [
            ['name' => '[Nama AUM/Ortom 1]'],
            ['name' => '[Nama AUM/Ortom 2]'],
            ['name' => '[Nama AUM/Ortom 3]'],
        ]);
@endphp

<section class="py-16">
    <div class="max-w-6xl mx-auto px-6">
        <h2 class="reveal text-3xl font-extrabold text-primary mb-10 text-center">
            {{ $content['title'] ?? 'Jaringan AUM & Ortom' }}
        </h2>
        <div class="grid md:grid-cols-3 gap-4">
            @foreach ($items as $item)
                <div class="reveal border border-gray-200 rounded-brand p-4 flex items-center gap-3 transition-all duration-300 hover:border-primary/40 hover:shadow-soft hover:-translate-y-0.5"
                     style="transition-delay: {{ $loop->index * 60 }}ms">
                    <div class="w-10 h-10 rounded-brand bg-primary/10 shrink-0"></div>
                    <div>
                        <p class="font-medium text-gray-700 text-sm">{{ $item['name'] }}</p>
                        @if (! empty($item['type']))
                            <p class="text-xs text-gray-400">{{ $item['type'] }}</p>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
