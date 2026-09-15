@extends('layouts.account')

@section('title', 'Pilih Template - Website-mu')

@section('content')
    @php
        // Stock-photo fallback for a template with no thumbnail uploaded yet, matching
        // templates/index.blade.php - this page exists to convince the user visually, so an
        // empty grey icon would undercut it more than a generic photo does.
        $defaultImage = 'https://images.unsplash.com/photo-1497215728101-856f4ea42174?auto=format&fit=crop&w=800&q=80';
    @endphp

    <div class="max-w-5xl mx-auto">
        <p class="text-xs font-bold text-primary uppercase tracking-wide mb-1">Langkah 1 dari 2</p>
        <h1 class="text-2xl font-extrabold text-primary mb-2">Pilih Template</h1>
        <p class="text-sm text-gray-500 mb-6">
            Pilih tampilan yang paling mendekati organisasi Anda. Semua bagian masih bisa diubah nanti,
            dan template bisa diganti kapan saja dari pengaturan organisasi.
        </p>

        @if (session('error'))
            <p class="text-sm text-red-600 bg-red-50 border border-red-200 rounded-xl px-4 py-3 mb-6">{{ session('error') }}</p>
        @endif

        <div x-data="{ selected: null, selectedSlug: null }">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5 mb-8">
                @forelse ($templates as $template)
                    @if ($template->is_exclusive)
                        {{-- Locked: every new organization starts on Starter (OrganizationController::store()),
                             so these can never be picked here. Shown rather than hidden so the upgrade path
                             is visible instead of the template simply being absent. --}}
                        <div class="relative block rounded-2xl border-2 border-transparent overflow-hidden bg-white shadow-soft opacity-60">
                            <div class="aspect-[4/3] bg-gray-100">
                                <img src="{{ $template->thumbnailUrl() ?? $defaultImage }}" alt="{{ $template->name }}"
                                     class="w-full h-full object-cover grayscale">
                            </div>
                            <div class="p-4">
                                <div class="flex items-center justify-between gap-2">
                                    <p class="font-bold text-gray-800 text-sm">{{ $template->name }}</p>
                                    <span class="shrink-0 px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 text-[11px] font-bold">Eksklusif</span>
                                </div>
                                <p class="text-xs text-gray-400 mt-0.5">{{ $template->organizationType->name }}</p>
                                <p class="text-xs text-gray-400 mt-2">Tersedia setelah upgrade paket</p>
                                <a href="{{ route('templates.preview', $template->slug) }}" target="_blank"
                                   class="inline-block text-xs text-primary font-semibold mt-2 hover:underline">
                                    Lihat pratinjau &rarr;
                                </a>
                            </div>
                            <div class="absolute top-3 right-3 w-6 h-6 rounded-full bg-gray-800/70 text-white flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3 h-3">
                                    <path fill-rule="evenodd" d="M10 1a4.5 4.5 0 0 0-4.5 4.5V9H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2h-.5V5.5A4.5 4.5 0 0 0 10 1Zm3 8V5.5a3 3 0 1 0-6 0V9h6Z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>
                    @else
                        <label
                            class="relative block rounded-2xl border-2 overflow-hidden cursor-pointer transition-colors bg-white shadow-soft"
                            :class="selected === {{ $template->id }} ? 'border-primary' : 'border-transparent hover:border-gray-200'">
                            <input type="radio" name="template_choice" value="{{ $template->id }}" class="sr-only"
                                   @change="selected = {{ $template->id }}; selectedSlug = @js($template->slug)">

                            <div class="aspect-[4/3] bg-gray-100">
                                <img src="{{ $template->thumbnailUrl() ?? $defaultImage }}" alt="{{ $template->name }}"
                                     class="w-full h-full object-cover">
                            </div>

                            <div class="p-4">
                                <p class="font-bold text-gray-800 text-sm">{{ $template->name }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">{{ $template->organizationType->name }}</p>
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
                    @endif
                @empty
                    <p class="text-gray-500 col-span-full text-center py-10">Belum ada template yang tersedia.</p>
                @endforelse
            </div>

            <div class="flex items-center justify-end gap-3 pb-4">
                <a href="{{ route('organizations.index') }}" class="px-5 py-2.5 rounded-full text-gray-600 text-sm font-semibold hover:bg-gray-100 transition-colors">
                    Batal
                </a>
                <a :href="selectedSlug ? '{{ route('organizations.create') }}?template=' + selectedSlug : '#'"
                   :class="selectedSlug ? '' : 'opacity-40 cursor-not-allowed pointer-events-none'"
                   class="px-5 py-2.5 rounded-full bg-primary text-white text-sm font-semibold transition-opacity">
                    Lanjut: Isi Identitas &rarr;
                </a>
            </div>
        </div>
    </div>
@endsection
