@php
    $content = $section['content'] ?? [];
    $doctors = $content['doctors'] ?? [
        ['name' => 'dr. Nama Dokter', 'specialty' => 'Dokter Umum', 'schedule' => 'Senin - Jumat, 08.00 - 14.00 WIB'],
    ];
@endphp

<section class="py-16">
    <div class="max-w-4xl mx-auto px-6">
        <h2 class="reveal text-3xl font-extrabold text-primary mb-10 text-center">
            {{ $content['title'] ?? 'Jadwal Praktik Dokter' }}
        </h2>
        <div class="space-y-4">
            @foreach ($doctors as $doctor)
                <div class="reveal bg-white rounded-brand p-5 flex items-center gap-5 shadow-soft"
                     style="transition-delay: {{ $loop->index * 80 }}ms">
                    <div class="w-14 h-14 shrink-0 rounded-full bg-primary/10 text-primary flex items-center justify-center text-2xl">
                        🩺
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800">{{ $doctor['name'] ?? 'dr. Nama Dokter' }}</p>
                        <p class="text-sm text-secondary font-medium">{{ $doctor['specialty'] ?? 'Dokter Umum' }}</p>
                        <p class="text-sm text-gray-500">{{ $doctor['schedule'] ?? 'Jadwal belum diatur' }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
