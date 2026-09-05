{{-- Venue-rental inquiry section: deliberately a WhatsApp-prefill form, not a database-backed
     submission - see Organization::seedPagesFromTemplate()/planViolations() and the tenant
     domain's read-only DB connection + no-session/no-CSRF posture (routes/web.php's
     Route::domain() group) that a real POST target would have to work around. The form fields
     are assembled into one WA message client-side, same "form that opens WhatsApp" pattern as
     formulir-kontak/standar.blade.php's fallback, so no controller/route is needed at all. --}}
@php
    $content = $section['content'] ?? [];
    $waNumber = $content['wa_number'] ?? ($organization->whatsapp ?? null);
    $waAvailable = \App\Services\WhatsAppNumber::href($waNumber) !== null;
    $facilities = $content['facilities'] ?? ['Kapasitas besar', 'Pendingin ruangan', 'Sound system', 'Area parkir luas'];
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
                'Assalamu\'alaikum, saya ingin mengajukan sewa aula.',
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
        <img src="{{ $backgroundImage }}" alt="{{ $content['hero_title'] ?? 'Aula serbaguna' }}" loading="lazy"
             class="absolute inset-0 w-full h-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-br from-primary/90 to-secondary/85"></div>
    @endif

    <div class="relative max-w-3xl mx-auto px-6">
        <div class="reveal inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/10 backdrop-blur border border-white/20 text-xs font-semibold mb-4">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
            {{ $content['availability_badge'] ?? 'Terbuka untuk Pemesanan' }}
        </div>
        <h2 class="reveal text-2xl sm:text-4xl font-extrabold tracking-tight">
            {{ $content['hero_title'] ?? 'Sewa Aula Serbaguna' }}
        </h2>
        <p class="reveal mt-3 text-white/80 max-w-xl leading-relaxed">
            {{ $content['hero_subtitle'] ?? 'Aula dengan kapasitas besar, cocok untuk berbagai acara jamaah.' }}
        </p>

        <div class="reveal mt-6 grid grid-cols-2 sm:grid-cols-4 gap-2.5 sm:gap-3">
            @foreach ($facilities as $facility)
                <div class="bg-white/10 border border-white/15 rounded-xl p-3 text-center text-xs font-semibold">{{ $facility }}</div>
            @endforeach
        </div>

        <div class="reveal mt-10 bg-white text-slate-900 rounded-2xl shadow-xl p-5 sm:p-6">
            <h3 class="font-bold text-primary">Ajukan Rencana Sewa</h3>
            <p class="mt-1 text-sm text-slate-500">
                Isi form berikut, Anda akan diarahkan ke WhatsApp admin dengan pesan yang sudah terisi otomatis.
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
