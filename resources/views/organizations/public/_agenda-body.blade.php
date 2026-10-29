@php
    // See _donation-program-body.blade.php's matching comment for why this isn't just
    // TenantUrl::route(..., 'tenant.home') unconditionally - that only resolves a real, published,
    // non-sandbox organization's subdomain, which 404s (or isn't even registered) for both the
    // owner-preview and template-catalog-preview contexts this partial also renders in.
    if (request()->routeIs('templates.preview*')) {
        $backHref = route('templates.preview', $organization->template);
    } elseif (request()->routeIs('organizations.preview*')) {
        $backHref = route('organizations.preview', $organization);
    } else {
        $backHref = \App\Services\TenantUrl::route($organization, 'tenant.home') ?? '#';
    }
@endphp
<article class="py-16 bg-softBg">
    <div class="max-w-3xl mx-auto px-6">
        <a href="{{ $backHref }}" class="text-sm text-secondary font-semibold hover:underline">&larr; Kembali ke beranda</a>

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
                @if ($agenda->repeats_weekly)
                    <span class="text-[10px] uppercase">Setiap</span>
                    <span class="text-sm font-extrabold mt-0.5">{{ $agenda->dayName() }}</span>
                @else
                    <span class="text-xl font-extrabold">{{ $agenda->starts_at->format('d') }}</span>
                    <span class="text-xs uppercase">{{ $agenda->starts_at->translatedFormat('M') }}</span>
                @endif
            </div>
            <div class="flex-1">
                <h1 class="text-2xl font-extrabold text-primary mb-1">{{ $agenda->title }}</h1>
                @if ($agenda->speaker)
                    <p class="text-sm text-gray-600">Narasumber: <span class="font-semibold">{{ $agenda->speaker }}</span></p>
                @endif
                <p class="text-sm text-gray-500">
                    {{ $agenda->scheduleLabel() }}
                    @if ($agenda->location)
                        &middot; {{ $agenda->location }}
                    @endif
                </p>

                @if ($agenda->description)
                    <div class="prose max-w-none text-gray-700 leading-relaxed mb-4 mt-4">
                        {!! $agenda->description !!}
                    </div>
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
