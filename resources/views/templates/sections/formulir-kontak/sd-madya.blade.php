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

<section class="py-14 sm:py-20 md:py-24 bg-surface">
    <div class="max-w-7xl mx-auto px-5 sm:px-6 grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12">
        <aside class="lg:col-span-4 space-y-8">
            <div class="space-y-6">
                <h2 class="reveal font-headline font-bold text-2xl md:text-3xl text-primary flex items-center gap-2">
                    <x-tenant.icon name="contact_support" />
                    {{ $content['title'] ?? 'Hubungi Kami' }}
                </h2>

                @if ($address)
                    <div class="reveal flex gap-4">
                        <div class="w-10 h-10 rounded-xl bg-secondary/15 flex items-center justify-center shrink-0">
                            <x-tenant.icon name="location_on" class="text-secondary" />
                        </div>
                        <div class="min-w-0">
                            <p class="font-headline font-bold text-sm text-primary mb-0.5">Alamat</p>
                            <p class="font-body text-on-surface-variant text-sm leading-relaxed">{{ $address }}</p>
                        </div>
                    </div>
                @endif

                @if ($waNumber)
                    <div class="reveal flex gap-4" style="transition-delay: 60ms">
                        <div class="w-10 h-10 rounded-xl bg-secondary/15 flex items-center justify-center shrink-0">
                            <x-tenant.icon name="call" class="text-secondary" />
                        </div>
                        <div class="min-w-0">
                            <p class="font-headline font-bold text-sm text-primary mb-0.5">WhatsApp</p>
                            <p class="font-body text-on-surface-variant text-sm">{{ $waNumber }}</p>
                        </div>
                    </div>
                @endif
            </div>

            @if ($hours)
                <div class="reveal bg-primary p-6 sm:p-8 rounded-2xl text-white relative overflow-hidden" style="transition-delay: 120ms">
                    <x-tenant.icon name="schedule" class="absolute -right-4 -bottom-4 text-white/5 text-9xl" />
                    <h3 class="font-headline font-bold text-xl mb-6 relative z-10">{{ $content['hours_title'] ?? 'Jam Operasional' }}</h3>
                    <ul class="space-y-4 relative z-10">
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

        <div class="lg:col-span-8 bg-surface-container-lowest p-6 sm:p-8 md:p-12 rounded-2xl shadow-ambient">
            <div class="mb-10">
                <h3 class="reveal font-headline font-extrabold text-2xl md:text-3xl text-primary mb-3">
                    {{ $content['form_title'] ?? 'Formulir Pendaftaran' }}
                </h3>
                <p class="reveal font-body text-on-surface-variant text-lg" style="transition-delay: 60ms">
                    {{ $content['form_subtitle'] ?? 'Lengkapi data di bawah ini. Tim kami akan menghubungi Anda melalui WhatsApp.' }}
                </p>
            </div>

            @if ($waDigits)
                {{-- Composed and opened client-side: no server round-trip, and the parent keeps
                     their own copy of the message in their WhatsApp history. --}}
                <form class="space-y-6"
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
                               class="w-full bg-surface-container-high border-none rounded-xl p-4 font-body focus:bg-white focus:ring-2 focus:ring-primary outline-none transition-all">
                    </div>

                    <div>
                        <label for="sd-madya-guardian" class="block font-headline font-bold text-xs text-primary uppercase tracking-wider mb-2">Nama Orang Tua / Wali</label>
                        <input id="sd-madya-guardian" type="text" x-model="guardian" required placeholder="Nama lengkap wali"
                               class="w-full bg-surface-container-high border-none rounded-xl p-4 font-body focus:bg-white focus:ring-2 focus:ring-primary outline-none transition-all">
                    </div>

                    <div>
                        <label for="sd-madya-grade" class="block font-headline font-bold text-xs text-primary uppercase tracking-wider mb-2">Mendaftar untuk</label>
                        <div class="relative">
                            <select id="sd-madya-grade" x-model="grade" required
                                    class="w-full bg-surface-container-high border-none rounded-xl p-4 font-body focus:bg-white focus:ring-2 focus:ring-primary outline-none transition-all appearance-none cursor-pointer">
                                <option value="" disabled>Pilih kelas</option>
                                @foreach ($grades as $grade)
                                    <option value="{{ is_array($grade) ? ($grade['label'] ?? '') : $grade }}">{{ is_array($grade) ? ($grade['label'] ?? '') : $grade }}</option>
                                @endforeach
                            </select>
                            <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-on-surface-variant">
                                <x-tenant.icon name="expand_more" />
                            </span>
                        </div>
                    </div>

                    <div class="pt-4 flex flex-col sm:flex-row items-center justify-between gap-4">
                        <p class="font-body text-sm text-on-surface-variant italic order-2 sm:order-1">Anda akan diarahkan ke WhatsApp.</p>
                        <button type="submit"
                                class="order-1 sm:order-2 w-full sm:w-auto px-10 py-4 bg-primary text-white font-headline font-bold rounded-xl shadow-ambient-lg hover:-translate-y-0.5 transition-all flex items-center justify-center gap-3 group">
                            {{ $content['cta_label'] ?? 'Daftar Sekarang' }}
                            <x-tenant.icon name="arrow_forward" class="group-hover:translate-x-1 transition-transform" />
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
