{{-- Venue-request section (key `akad-venue`, formerly `sewa-aula`): deliberately a
     WhatsApp-prefill form, not a database-backed
     submission - see Organization::seedPagesFromTemplate()/planViolations() and the tenant
     domain's read-only DB connection + no-session/no-CSRF posture (routes/web.php's
     Route::domain() group) that a real POST target would have to work around. The form fields
     are assembled into one WA message client-side, same "form that opens WhatsApp" pattern as
     formulir-kontak/standar.blade.php's fallback, so no controller/route is needed at all. --}}
@php
    $content = $section['content'] ?? [];
    $waNumber = $content['wa_number'] ?? ($organization->whatsapp ?? null);
    $waAvailable = \App\Services\WhatsAppNumber::href($waNumber) !== null;
    // Normalised rather than used as-is: `facilities` is a list, but the builder's properties
    // panel had no editor for it, so saving the section wrote whatever the generic text input
    // held - a bare string - and this section then died on foreach() with a 500. The panel now
    // has a proper list editor, but tolerate the older shapes too: a string (one-per-line, the
    // most forgiving reading of what someone typed) and a list with blank rows.
    $facilities = $content['facilities'] ?? ['Kapasitas besar', 'Pendingin ruangan', 'Sound system', 'Area parkir luas'];
    $facilities = collect(is_string($facilities) ? preg_split('/\r\n|\r|\n/', $facilities) : (array) $facilities)
        ->map(fn ($facility) => is_string($facility) ? trim($facility) : null)
        ->filter()
        ->values();
    $backgroundImage = $content['image'] ?? null;
@endphp

<section
    class="relative overflow-hidden bg-gradient-to-br from-primary to-secondary text-white py-16"
    x-data="{
        name: '',
        plannedDate: '',
        note: '',
        waNumber: {{ Js::from($waNumber) }},
        get waHref() {
            const lines = [
                'Assalamu\'alaikum, saya ingin mengAjukan Penggunaan masjid.',
                this.name ? `Nama: ${this.name}` : null,
                this.plannedDate ? `Rencana tanggal: ${this.plannedDate}` : null,
                this.note ? `Catatan: ${this.note}` : null,
            ].filter(Boolean);
            return this.waNumber ? `https://wa.me/${this.waNumber}?text=${encodeURIComponent(lines.join('\n'))}` : '#';
        },
    }"
>
    @if ($backgroundImage)
        {{-- Photo backdrop behind the same brand gradient (now semi-transparent over it),
             mirroring the nurul-huda venue page's image + gradient-overlay hero. --}}
        <img src="{{ $backgroundImage }}" alt="{{ $content['hero_title'] ?? 'Aula serbaguna masjid' }}" loading="lazy"
             class="absolute inset-0 w-full h-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-br from-primary/90 to-secondary/85"></div>
    @endif

    {{-- Two columns from `lg:` up - pitch on the left, form on the right - instead of one
         narrow max-w-3xl stack. Stacked, the form sat a full screen below the facilities it is
         meant to respond to, and the section wasted most of a desktop viewport. On mobile the
         order is unchanged: pitch first, then form. --}}
    <div class="relative max-w-6xl mx-auto px-6 grid lg:grid-cols-[1.1fr_1fr] lg:gap-12 lg:items-start">
        <div>
            <div class="reveal inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/10 backdrop-blur border border-white/20 text-xs font-semibold mb-4">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                {{ $content['availability_badge'] ?? 'Terbuka untuk Pemesanan' }}
            </div>
            {{-- font-semibold rather than extrabold, matching the headings the other sections in
                 this template settled on. --}}
            <h2 class="reveal text-2xl sm:text-4xl font-semibold tracking-tight">
                {{ $content['hero_title'] ?? 'Aula Serbaguna Masjid' }}
            </h2>
            <p class="reveal mt-3 text-white/80 max-w-xl leading-relaxed">
                {{ $content['hero_subtitle'] ?? 'Aula dengan kapasitas besar, terbuka untuk akad nikah dan kegiatan jamaah.' }}
            </p>

            @if ($facilities->isNotEmpty())
                {{-- Check-marked rows rather than centred chips: a facility is a claim about the
                     venue, and a tick reads as one. Chips also broke awkwardly once a label ran
                     longer than a word or two ("Wudhu Terpisah"), which the CMS allows. --}}
                <ul class="reveal mt-7 grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                    @foreach ($facilities as $facility)
                        <li class="flex items-center gap-2.5 bg-white/10 border border-white/15 rounded-xl px-3.5 py-2.5 text-sm font-medium">
                            <svg class="w-4 h-4 shrink-0 text-emerald-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>{{ $facility }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        {{-- lg:sticky keeps the form in view while a long facilities list scrolls past it. --}}
        <div class="reveal mt-10 lg:mt-0 lg:sticky lg:top-8 bg-white text-slate-900 rounded-2xl shadow-xl p-5 sm:p-6">
            <h3 class="font-bold text-primary">Ajukan Penggunaan</h3>
            <p class="mt-1 text-sm text-slate-500">
                Isi form berikut, Anda akan diarahkan ke WhatsApp takmir dengan pesan yang sudah terisi otomatis.
            </p>

            <div class="mt-5 space-y-4">
                <div>
                    <label class="text-xs text-slate-500 uppercase tracking-wide">Nama</label>
                    <input type="text" x-model="name" placeholder="Nama Anda"
                           class="mt-1 w-full border border-slate-200 rounded-xl px-4 py-3 text-slate-900 focus:outline-none focus:ring-2 focus:ring-secondary/40">
                </div>
                <div>
                    <label class="text-xs text-slate-500 uppercase tracking-wide">Rencana Tanggal</label>
                    <input type="date" x-model="plannedDate"
                           class="mt-1 w-full border border-slate-200 rounded-xl px-4 py-3 text-slate-900 focus:outline-none focus:ring-2 focus:ring-secondary/40">
                </div>
                <div>
                    <label class="text-xs text-slate-500 uppercase tracking-wide">Catatan (opsional)</label>
                    <textarea x-model="note" rows="3" placeholder="Perkiraan jumlah tamu, kebutuhan tambahan, dsb."
                              class="mt-1 w-full border border-slate-200 rounded-xl px-4 py-3 text-slate-900 focus:outline-none focus:ring-2 focus:ring-secondary/40"></textarea>
                </div>

                {{-- The mosque's own framing of what it asks in return. Many masjid deliberately
                     avoid rental language for a place of worship - it reads as monetising
                     religious space - and ask for a voluntary infak instead. Editable rather
                     than hardcoded because that stance varies between masjid: leaving
                     `infak_note` blank simply omits the whole block. --}}
                @if (filled($content['infak_note'] ?? null))
                    <div class="flex items-start gap-3 rounded-xl bg-secondary/5 border border-secondary/20 px-4 py-3">
                        <svg class="w-5 h-5 shrink-0 text-secondary mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-sm text-slate-600 leading-relaxed">{{ $content['infak_note'] }}</p>
                    </div>
                @endif

                @if ($waAvailable)
                    <a :href="waHref" target="_blank" rel="noopener"
                       class="w-full inline-flex items-center justify-center gap-2 bg-green-500 hover:bg-green-600 active:scale-[.98] text-white font-semibold py-4 rounded-xl shadow-lg shadow-green-500/30 transition">
                        Ajukan via WhatsApp
                    </a>
                @else
                    <button type="button" disabled
                            class="w-full bg-slate-200 text-slate-400 font-semibold py-4 rounded-xl cursor-not-allowed">
                        Ajukan via WhatsApp
                    </button>
                @endif
            </div>
        </div>
    </div>
</section>
