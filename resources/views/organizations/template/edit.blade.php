@extends('layouts.organization')

@section('title', 'Ganti Template - '.$organization->name.' - Website-mu')

@section('content')
    <div class="max-w-5xl mx-auto">
        <a href="{{ route('organizations.show', $organization) }}"
           class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 hover:text-primary transition-colors mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">
                <path fill-rule="evenodd" d="M17 10a.75.75 0 0 1-.75.75H5.612l4.158 3.96a.75.75 0 1 1-1.04 1.08l-5.5-5.25a.75.75 0 0 1 0-1.08l5.5-5.25a.75.75 0 1 1 1.04 1.08L5.612 9.25H16.25A.75.75 0 0 1 17 10Z" clip-rule="evenodd" />
            </svg>
            Kembali ke {{ $organization->name }}
        </a>

        <div class="mb-8">
            <span class="text-primary font-bold tracking-wider uppercase text-xs bg-blue-50 px-3 py-1 rounded-full">Tampilan</span>
            <h1 class="text-3xl font-extrabold text-gray-900 mt-3">Ganti Template {{ $organization->name }}</h1>
            <p class="text-sm text-gray-500 mt-1">
                Template saat ini: <span class="font-semibold text-gray-700">{{ $organization->template?->name ?? 'Belum diatur' }}</span>
            </p>
        </div>

        <div class="flex items-start gap-3 bg-amber-50 border border-amber-200 text-amber-800 rounded-2xl p-4 sm:p-5 mb-8 text-sm">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-5 h-5 shrink-0 mt-0.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
            </svg>
            <p>
                Mengganti template akan menyusun ulang halaman mengikuti susunan template baru. <strong>Isi yang sudah
                Anda tulis pada widget dengan jenis yang sama tetap dipertahankan</strong> (mis. teks di widget
                "Tentang Organisasi" tetap ada bila template baru juga punya widget itu); tampilannya mengikuti
                desain template baru. Widget yang tidak ada pada template baru ikut hilang, dan widget baru yang
                belum pernah ada sebelumnya tampil dengan isi bawaan template. Berita, agenda, pengurus, dan konten
                CMS lain yang sudah tersimpan tidak pernah terhapus.
            </p>
        </div>

        @if (! $organization->canUseExclusiveTemplates())
            <div class="flex items-start gap-3 bg-blue-50 border border-blue-200 text-primary rounded-2xl p-4 sm:p-5 mb-8 text-sm">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5 shrink-0 mt-0.5">
                    <path fill-rule="evenodd" d="M10 1a4.5 4.5 0 0 0-4.5 4.5V9H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2h-.5V5.5A4.5 4.5 0 0 0 10 1Zm3 8V5.5a3 3 0 1 0-6 0V9h6Z" clip-rule="evenodd" />
                </svg>
                <p>
                    Template bertanda <span class="font-semibold">Eksklusif</span> tetap bisa dipilih dan dipakai sekarang
                    juga - situs hanya tidak bisa dipublikasikan dengannya sampai organisasi berlangganan paket
                    <span class="font-semibold">Eksklusif</span>.
                    <a href="{{ route('organizations.plan.edit', $organization) }}" class="font-semibold underline">Upgrade paket organisasi</a>
                    kapan saja.
                </p>
            </div>
        @endif

        <form action="{{ route('organizations.template.update', $organization) }}" method="POST"
              x-data="{ selected: {{ Js::from($organization->template_id) }} }"
              @submit.prevent="if (await confirmAction('Halaman akan disusun ulang mengikuti template baru. Isi widget dengan jenis yang sama tetap dipertahankan. Lanjutkan?', { confirmLabel: 'Ya, Ganti Template' })) $el.submit()">
            @csrf
            @method('PATCH')
            <input type="hidden" name="template_id" x-model="selected">

            @error('template_id')
                <p class="text-sm text-red-600 bg-red-50 border border-red-200 rounded-xl px-4 py-3 mb-6">{{ $message }}</p>
            @enderror

            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5 mb-8">
                @foreach ($templates as $template)
                    <label
                        class="relative block rounded-2xl border-2 overflow-hidden cursor-pointer transition-colors bg-white shadow-soft"
                        :class="selected === {{ $template->id }} ? 'border-primary' : 'border-transparent hover:border-gray-200'">
                        <input type="radio" name="template_id_radio" value="{{ $template->id }}"
                               x-model.number="selected" class="sr-only">

                        <div class="aspect-[4/3] bg-gray-100">
                            <x-ui.template-thumbnail :template="$template" />
                        </div>

                        <div class="p-4">
                            <div class="flex items-center justify-between gap-2">
                                <p class="font-bold text-gray-800 text-sm">{{ $template->name }}</p>
                                @if ($template->is_exclusive)
                                    <span class="shrink-0 px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 text-[11px] font-bold">Eksklusif</span>
                                @endif
                            </div>
                            @if ($template->organizationType)
                                <p class="text-xs text-gray-400 mt-0.5">{{ $template->organizationType->name }}</p>
                            @endif
                            @if ($template->is_exclusive && ! $organization->canUseExclusiveTemplates())
                                <p class="text-xs text-amber-600 mt-2">Bisa langsung dipakai &mdash; perlu paket Eksklusif saat dipublikasikan.</p>
                            @endif
                            <a href="{{ route('templates.preview', $template->slug) }}" target="_blank"
                               class="inline-block text-xs text-primary font-semibold mt-2 hover:underline"
                               @click.stop>
                                Lihat pratinjau &rarr;
                            </a>
                        </div>

                        <div x-show="selected === {{ $template->id }}" x-cloak
                             class="absolute top-3 right-3 w-6 h-6 rounded-full bg-primary text-white flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3.5 h-3.5">
                                <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </label>
                @endforeach
            </div>

            <div class="flex items-center gap-3">
                <button type="submit"
                        :disabled="selected === {{ $organization->template_id ?? 'null' }}"
                        :class="selected === {{ $organization->template_id ?? 'null' }} ? 'opacity-40 cursor-not-allowed' : ''"
                        class="px-5 py-2.5 rounded-full bg-primary text-white text-sm font-semibold">
                    Gunakan Template Ini
                </button>
            </div>
        </form>
    </div>
@endsection
