@php $content = $section['content'] ?? []; @endphp

<section class="py-16 bg-softBg">
    <div class="max-w-xl mx-auto px-6 text-center">
        <h2 class="reveal text-3xl font-extrabold text-primary mb-2">
            {{ $content['title'] ?? 'Hubungi Kami' }}
        </h2>
        @if (! empty($content['subtitle']))
            <p class="reveal text-gray-500 mb-8" style="transition-delay: 60ms">{{ $content['subtitle'] }}</p>
        @else
            <div class="mb-8"></div>
        @endif
        <div class="reveal space-y-4 text-left" style="transition-delay: 120ms">
            <input type="text" disabled placeholder="Nama"
                   class="w-full h-11 rounded-lg bg-white border border-gray-200 px-4 text-sm text-gray-400 transition-colors focus-within:border-primary">
            <input type="text" disabled placeholder="Email"
                   class="w-full h-11 rounded-lg bg-white border border-gray-200 px-4 text-sm text-gray-400">
            <textarea disabled placeholder="Pesan" rows="4"
                      class="w-full rounded-lg bg-white border border-gray-200 px-4 py-3 text-sm text-gray-400"></textarea>
            <button class="w-full py-3 rounded-full bg-primary text-white font-semibold transition-transform duration-200 hover:scale-[1.02]">
                Kirim Pesan
            </button>
        </div>
    </div>
</section>
