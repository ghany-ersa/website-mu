@php
    $content = $section['content'] ?? [];
    $times = $content['times'] ?? [
        ['label' => 'Subuh'], ['label' => 'Dzuhur'], ['label' => 'Ashar'], ['label' => 'Maghrib'], ['label' => 'Isya'],
    ];
@endphp

<section class="py-10 -mt-1">
    <div class="max-w-6xl mx-auto px-6">
        <div class="reveal bg-primary rounded-2xl p-6 shadow-float">
            <div class="flex items-center justify-between mb-4 text-white/80 text-xs">
                <span>{{ $content['title'] ?? 'Jadwal Salat Hari Ini' }}</span>
                @if (! empty($content['location']))
                    <span>{{ $content['location'] }}</span>
                @endif
            </div>
            <div class="grid grid-cols-5 gap-2 text-center text-white">
                @foreach ($times as $item)
                    <div class="transition-transform duration-300 hover:-translate-y-1">
                        <p class="text-xs uppercase text-white/70 mb-1">{{ $item['label'] }}</p>
                        <p class="font-bold">{{ $item['time'] ?? '--:--' }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
