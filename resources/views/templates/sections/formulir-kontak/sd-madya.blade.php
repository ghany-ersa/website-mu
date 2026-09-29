{{-- Port of comprof-SD-Madya kontak.vue: contact details and opening hours on the left, an
     enrolment form on the right. Like the source, the form doesn't post anywhere - it composes
     a pre-filled WhatsApp message and opens it, which is how these schools actually field
     enquiries and means no submission can be silently lost in an inbox nobody reads.

     Extra content keys over `standar`: `address`, `hours` (a list of {day, time} rows),
     `form_title`/`form_subtitle`, and `grades` (the options in the class dropdown). --}}
@php
    $content = $section['content'] ?? [];
    $waNumber = $content['wa_number'] ?? ($organization->whatsapp ?? null);
    $orgName = $template->structure['sample_org_name'] ?? null
        ?? $organization->name ?? null
        ?? '[Nama Organisasi]';
    $waDigits = \App\Services\WhatsAppNumber::normalize($waNumber);

    $address = $content['address'] ?? ($organization->address ?? null);
    $hours = $content['hours'] ?? [];
    $grades = $content['grades'] ?? ['Kelas 1', 'Kelas 2', 'Kelas 3', 'Kelas 4', 'Kelas 5', 'Kelas 6'];
@endphp

<section class="section-y bg-surface">
    <div class="shell grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-10">
        <aside class="lg:col-span-4 space-y-6">
            <div class="space-y-6">
                <h2 class="reveal font-headline font-bold t-h3 sm:text-2xl text-primary flex items-center gap-2">
                    <svg class="w-6 h-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.5 12a1.5 1.5 0 1 0 3 0 1.5 1.5 0 0 0-3 0Zm5 0a1.5 1.5 0 1 0 3 0 1.5 1.5 0 0 0-3 0Zm-10 0a1.5 1.5 0 1 0 3 0 1.5 1.5 0 0 0-3 0ZM12 3a9 9 0 0 0-9 9c0 1.6.44 3.1 1.2 4.4L3 21l4.8-1.2A9 9 0 1 0 12 3Z" />
                    </svg>
                    {{ $content['title'] ?? 'Hubungi Kami' }}
                </h2>

                @if ($address)
                    <div class="reveal flex gap-4">
                        <div class="w-9 h-9 rounded-xl bg-secondary/15 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4 text-secondary" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M12 2a7 7 0 0 0-7 7c0 5.25 7 13 7 13s7-7.75 7-13a7 7 0 0 0-7-7Zm0 9.5A2.5 2.5 0 1 1 12 6.5a2.5 2.5 0 0 1 0 5Z" />
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="font-headline font-bold text-sm text-primary mb-0.5">Alamat</p>
                            <p class="font-body text-on-surface-variant text-sm leading-relaxed">{{ $address }}</p>
                        </div>
                    </div>
                @endif

                @if ($waNumber)
                    <div class="reveal flex gap-4" style="transition-delay: 60ms">
                        <div class="w-9 h-9 rounded-xl bg-secondary/15 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4 text-secondary" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M6.6 10.8c1.4 2.8 3.8 5.1 6.6 6.6l2.2-2.2c.3-.3.7-.4 1-.2 1.1.4 2.3.6 3.6.6.6 0 1 .4 1 1V20c0 .6-.4 1-1 1-9.4 0-17-7.6-17-17 0-.6.4-1 1-1h3.5c.6 0 1 .4 1 1 0 1.3.2 2.5.6 3.6.1.4 0 .8-.2 1L6.6 10.8Z" />
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="font-headline font-bold text-sm text-primary mb-0.5">WhatsApp</p>
                            <p class="font-body text-on-surface-variant text-sm">{{ $waNumber }}</p>
                        </div>
                    </div>
                @endif
            </div>

            @if ($hours)
                <div class="reveal bg-primary p-5 sm:p-6 rounded-2xl text-white relative overflow-hidden" style="transition-delay: 120ms">
                    <svg class="absolute -right-4 -bottom-4 w-32 h-32 text-white/5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20Zm.75 5v5.3l4.2 2.5-.75 1.2-4.95-3V7h1.5Z" />
                    </svg>
                    <h3 class="font-headline font-bold text-base mb-4 relative z-10">{{ $content['hours_title'] ?? 'Jam Operasional' }}</h3>
                    <ul class="space-y-3 relative z-10 text-sm">
                        @foreach ($hours as $row)
                            <li class="flex justify-between gap-4 border-b border-white/10 pb-2 last:border-0">
                                <span class="font-body">{{ $row['day'] ?? '' }}</span>
                                <span class="font-headline font-bold text-white/80">{{ $row['time'] ?? '' }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </aside>

        <div class="lg:col-span-8 bg-surface-container-lowest p-5 sm:p-7 md:p-9 rounded-2xl shadow-ambient">
            <div class="mb-7">
                <h3 class="reveal font-headline font-extrabold t-h3 sm:text-2xl text-primary mb-2">
                    {{ $content['form_title'] ?? 'Formulir Pendaftaran' }}
                </h3>
                <p class="reveal font-body text-on-surface-variant t-body" style="transition-delay: 60ms">
                    {{ $content['form_subtitle'] ?? 'Lengkapi data di bawah ini. Tim kami akan menghubungi Anda melalui WhatsApp.' }}
                </p>
            </div>

            @if ($waDigits)
                {{-- Composed and opened client-side: no server round-trip, and the parent keeps
                     their own copy of the message in their WhatsApp history. --}}
                <form class="space-y-4"
                      x-data="{
                        student: '',
                        guardian: '',
                        grade: '',
                        submit() {
                            const message = `Assalamu'alaikum, saya ${this.guardian} wali dari ananda ${this.student}. `
                                + `Saya ingin mendaftarkan anak saya di {{ addslashes($orgName) }} untuk ${this.grade}. `
                                + `Apakah pendaftaran masih dibuka?`;
                            window.open('https://wa.me/{{ $waDigits }}?text=' + encodeURIComponent(message), '_blank', 'noopener');
                        },
                      }"
                      @submit.prevent="submit()">
                    <div>
                        <label for="sd-madya-student" class="block font-headline font-bold text-xs text-primary uppercase tracking-wider mb-2">Nama Lengkap Siswa</label>
                        <input id="sd-madya-student" type="text" x-model="student" required placeholder="Sesuai akta kelahiran"
                               class="w-full bg-surface-container-high border-none rounded-xl px-4 py-3 font-body text-[15px] focus:bg-white focus:ring-2 focus:ring-primary outline-none transition-all">
                    </div>

                    <div>
                        <label for="sd-madya-guardian" class="block font-headline font-bold text-xs text-primary uppercase tracking-wider mb-2">Nama Orang Tua / Wali</label>
                        <input id="sd-madya-guardian" type="text" x-model="guardian" required placeholder="Nama lengkap wali"
                               class="w-full bg-surface-container-high border-none rounded-xl px-4 py-3 font-body text-[15px] focus:bg-white focus:ring-2 focus:ring-primary outline-none transition-all">
                    </div>

                    <div>
                        <label for="sd-madya-grade" class="block font-headline font-bold text-xs text-primary uppercase tracking-wider mb-2">Mendaftar untuk</label>
                        <div class="relative">
                            <select id="sd-madya-grade" x-model="grade" required
                                    class="w-full bg-surface-container-high border-none rounded-xl px-4 py-3 font-body text-[15px] focus:bg-white focus:ring-2 focus:ring-primary outline-none transition-all appearance-none cursor-pointer">
                                <option value="" disabled>Pilih kelas</option>
                                @foreach ($grades as $grade)
                                    <option value="{{ is_array($grade) ? ($grade['label'] ?? '') : $grade }}">{{ is_array($grade) ? ($grade['label'] ?? '') : $grade }}</option>
                                @endforeach
                            </select>
                            <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-on-surface-variant">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" />
                                </svg>
                            </span>
                        </div>
                    </div>

                    <div class="pt-3 flex flex-col sm:flex-row items-center justify-between gap-3">
                        <p class="font-body text-sm text-on-surface-variant italic order-2 sm:order-1">Anda akan diarahkan ke WhatsApp.</p>
                        <button type="submit"
                                class="order-1 sm:order-2 w-full sm:w-auto px-7 py-3 bg-primary text-white font-headline font-bold rounded-xl shadow-ambient-lg hover:-translate-y-0.5 transition-all flex items-center justify-center gap-3 group">
                            {{ $content['cta_label'] ?? 'Daftar Sekarang' }}
                            <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-7 7 7-7 7" />
                            </svg>
                        </button>
                    </div>
                </form>
            @else
                {{-- No number configured: a form that goes nowhere is worse than an honest note. --}}
                <p class="font-body text-on-surface-variant rounded-xl border border-dashed border-outline-variant px-6 py-10 text-center">
                    Nomor WhatsApp belum diatur, sehingga formulir pendaftaran belum bisa digunakan.
                </p>
            @endif
        </div>
    </div>
</section>
