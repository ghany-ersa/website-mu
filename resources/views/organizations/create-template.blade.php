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
            dan template bisa diganti kapan saja dari pengaturan organisasi. Template
            <span class="inline-flex items-center gap-1 font-semibold text-amber-700">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3.5 h-3.5">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.958a1 1 0 0 0 .95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.367 2.446a1 1 0 0 0-.363 1.118l1.286 3.957c.3.922-.755 1.688-1.538 1.118l-3.367-2.446a1 1 0 0 0-1.176 0l-3.367 2.446c-.783.57-1.838-.196-1.538-1.118l1.286-3.957a1 1 0 0 0-.363-1.118L2.063 9.385c-.783-.57-.38-1.81.588-1.81h4.163a1 1 0 0 0 .95-.69l1.285-3.958Z" />
                </svg>
                Eksklusif
            </span>
            bebas dicoba dan dirancang sekarang juga &mdash; upgrade ke paket tertinggi baru diperlukan nanti saat situs dipublikasikan.
        </p>

        @if (session('error'))
            <p class="text-sm text-red-600 bg-red-50 border border-red-200 rounded-xl px-4 py-3 mb-6">{{ session('error') }}</p>
        @endif

        <div x-data="{ selected: null, selectedSlug: null }">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5 mb-8">
                @forelse ($templates as $template)
                    <label
                        class="group relative block rounded-2xl border-2 overflow-hidden cursor-pointer transition-all bg-white shadow-soft"
                        :class="selected === {{ $template->id }}
                            ? '{{ $template->is_exclusive ? 'border-amber-400 ring-2 ring-amber-200' : 'border-primary' }}'
                            : '{{ $template->is_exclusive ? 'border-amber-200/70 hover:border-amber-300' : 'border-transparent hover:border-gray-200' }}'">
                        <input type="radio" name="template_choice" value="{{ $template->id }}" class="sr-only"
                               @change="selected = {{ $template->id }}; selectedSlug = @js($template->slug)">

                        <div class="relative aspect-[4/3] bg-gray-100">
                            <x-ui.template-thumbnail :template="$template" />

                            @if ($template->is_exclusive)
                                {{-- Selectable like any other: the plan is only enforced at publish time
                                     (Organization::planViolations()), so this badge sets the expectation
                                     up front rather than blocking the choice. --}}
                                <div class="absolute inset-x-0 top-0 h-14 bg-gradient-to-b from-black/40 to-transparent pointer-events-none"></div>
                                <span class="absolute top-2.5 left-2.5 inline-flex items-center gap-1 pl-1.5 pr-2.5 py-1 rounded-full bg-gradient-to-r from-amber-400 to-yellow-500 text-white text-[11px] font-bold shadow-md">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3.5 h-3.5 drop-shadow-sm">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.958a1 1 0 0 0 .95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.367 2.446a1 1 0 0 0-.363 1.118l1.286 3.957c.3.922-.755 1.688-1.538 1.118l-3.367-2.446a1 1 0 0 0-1.176 0l-3.367 2.446c-.783.57-1.838-.196-1.538-1.118l1.286-3.957a1 1 0 0 0-.363-1.118L2.063 9.385c-.783-.57-.38-1.81.588-1.81h4.163a1 1 0 0 0 .95-.69l1.285-3.958Z" />
                                    </svg>
                                    Eksklusif
                                </span>
                            @endif
                        </div>

                        <div class="p-4 {{ $template->is_exclusive ? 'bg-gradient-to-b from-amber-50/60 to-white' : '' }}">
                            <p class="font-bold text-gray-800 text-sm">{{ $template->name }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $template->organizationType->name }}</p>
                            @if ($template->is_exclusive)
                                <p class="flex items-center gap-1 text-[11px] font-semibold text-amber-700 mt-2.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3.5 h-3.5 shrink-0">
                                        <path fill-rule="evenodd" d="M10 1a4.5 4.5 0 0 0-4.5 4.5V9H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2h-.5V5.5A4.5 4.5 0 0 0 10 1Zm3 8V5.5a3 3 0 1 0-6 0V9h6Z" clip-rule="evenodd" />
                                    </svg>
                                    Perlu paket Eksklusif untuk publikasi
                                </p>
                            @endif
                            <a href="{{ route('templates.preview', $template->slug) }}" target="_blank"
                               class="inline-block text-xs text-primary font-semibold mt-2 hover:underline"
                               @click.stop>
                                Lihat pratinjau &rarr;
                            </a>
                        </div>

                        <div x-show="selected === {{ $template->id }}" x-cloak
                             class="absolute top-2.5 right-2.5 w-6 h-6 rounded-full flex items-center justify-center shadow-md {{ $template->is_exclusive ? 'bg-amber-500' : 'bg-primary' }} text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3.5 h-3.5">
                                <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </label>
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
