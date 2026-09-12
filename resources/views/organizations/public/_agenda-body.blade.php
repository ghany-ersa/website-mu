<article class="py-16 bg-softBg">
    <div class="max-w-3xl mx-auto px-6">
        <a href="{{ route('tenant.home', ['organization_slug' => $organization->slug]) }}" class="text-sm text-secondary font-semibold hover:underline">&larr; Kembali ke beranda</a>

        {{-- The flyer, when there is one: on a shared kajian link the poster is what people
             recognise, and it usually carries details the fields don't (speaker, rundown).
             Sits above the card rather than inside it so it can run full width on mobile. --}}
        {{-- No max-w cap and no fixed aspect: agenda posters are usually landscape, and the old
             max-w-md was sized for a portrait flyer, which left a wide poster shrunk into a
             narrow column. Letting it fill the article's own max-w-3xl and keeping the image's
             natural ratio (h-auto, no object-cover) means neither orientation is cropped or
             letterboxed - the flyer's own text stays readable either way. --}}
        @if ($agenda->poster)
            <img src="{{ $agenda->poster }}" alt="Poster {{ $agenda->title }}"
                 class="mt-6 w-full h-auto rounded-2xl shadow-soft">
        @endif

        <div class="bg-white rounded-2xl p-6 flex items-start gap-5 shadow-soft mt-6">
            <div class="w-16 h-16 shrink-0 rounded-xl bg-primary text-white flex flex-col items-center justify-center leading-none">
                <span class="text-xl font-extrabold">{{ $agenda->starts_at->format('d') }}</span>
                <span class="text-xs uppercase">{{ $agenda->starts_at->translatedFormat('M') }}</span>
            </div>
            <div class="flex-1">
                <h1 class="text-2xl font-extrabold text-primary mb-1">{{ $agenda->title }}</h1>
                <p class="text-sm text-gray-500 mb-4">
                    {{ $agenda->starts_at->translatedFormat('d M Y, H:i') }}
                    @if ($agenda->location)
                        &middot; {{ $agenda->location }}
                    @endif
                </p>

                @if ($agenda->description)
                    <div class="prose max-w-none text-gray-700 leading-relaxed mb-4">
                        {!! $agenda->description !!}
                    </div>
                @endif

                @if ($agenda->contact_person)
                    <p class="text-sm text-gray-600">Kontak: {{ $agenda->contact_person }}</p>
                @endif

                @if ($agenda->registration_url)
                    <a href="{{ $agenda->registration_url }}" target="_blank" rel="noopener"
                       class="inline-block mt-4 px-5 py-2.5 rounded-xl bg-primary text-white font-semibold text-sm hover:opacity-90 transition">
                        Daftar Sekarang
                    </a>
                @endif
            </div>
        </div>
    </div>
</article>
