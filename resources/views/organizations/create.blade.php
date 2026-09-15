@extends('layouts.account')

@section('title', 'Buat Organisasi - Website-mu')

@section('content')
    <div class="max-w-3xl mx-auto">
        <p class="text-xs font-bold text-primary uppercase tracking-wide mb-1">Langkah 2 dari 2</p>
        <div class="flex items-start justify-between gap-3 mb-2">
            <h1 class="text-2xl font-extrabold text-primary">Buat Organisasi Baru</h1>
            <button type="button" id="btn-create-tour" onclick="window.startOnboardingTour('create')"
                title="Lihat penjelasan form ini"
                class="w-7 h-7 flex items-center justify-center rounded-full text-gray-400 hover:text-primary hover:bg-gray-100 transition shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">
                    <path fill-rule="evenodd"
                        d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0ZM8.94 6.94a.75.75 0 1 1-1.061-1.061 3 3 0 1 1 2.871 5.026v.345a.75.75 0 0 1-1.5 0v-.5c0-.72.57-1.172 1.081-1.287a1.5 1.5 0 1 0-1.391-2.523ZM10 15a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z"
                        clip-rule="evenodd" />
                </svg>
            </button>
        </div>
        <p class="text-sm text-gray-500 mb-8">Lengkapi detail organisasi Anda di bawah ini.</p>

        <div id="selected-template" class="mb-6 flex items-center gap-3 rounded-xl bg-white border border-gray-200 p-3 shadow-soft">
            <div class="w-20 h-14 shrink-0 rounded-lg overflow-hidden bg-gray-100">
                <img src="{{ $selectedTemplate->thumbnailUrl() ?? 'https://images.unsplash.com/photo-1497215728101-856f4ea42174?auto=format&fit=crop&w=800&q=80' }}"
                     alt="{{ $selectedTemplate->name }}" class="w-full h-full object-cover">
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-xs text-gray-400">Template pilihan Anda</p>
                <p class="font-bold text-gray-800 text-sm truncate">{{ $selectedTemplate->name }}</p>
            </div>
            <a href="{{ route('organizations.template-picker') }}"
               class="shrink-0 text-xs text-primary font-semibold hover:underline">Ganti</a>
        </div>

        <form action="{{ route('organizations.store') }}" method="POST" id="organization-form">
            @csrf

            <input type="hidden" name="template_id" value="{{ $selectedTemplate->id }}">

            <div class="bg-white rounded-2xl shadow-soft p-6 space-y-5">
                <div>
                    <label for="name" class="block text-sm font-semibold text-gray-700 mb-1">Nama Organisasi</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                           class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
                </div>

                <div>
                    <label for="slug" class="block text-sm font-semibold text-gray-700 mb-1">Slug (subdomain)</label>
                    <div class="flex rounded-lg border border-gray-200 overflow-hidden focus-within:ring-2 focus-within:ring-primary/30">
                        <input type="text" name="slug" id="slug" value="{{ old('slug') }}" required
                               placeholder="pcm-ambulu"
                               class="w-full px-3 py-2 text-sm font-mono focus:outline-none">
                        <span class="flex items-center px-3 text-sm font-mono text-gray-400 bg-gray-50 border-l border-gray-200 whitespace-nowrap">.website-mu.id</span>
                    </div>
                    <p class="mt-1.5 text-xs text-gray-400">
                        Ini akan jadi alamat website organisasi Anda, contoh: <span class="font-mono text-gray-500">pcm-ambulu.website-mu.id</span>.
                        Gunakan huruf kecil, angka, dan tanda hubung (-), tanpa spasi.
                    </p>
                </div>

                <div>
                    <label for="region" class="block text-sm font-semibold text-gray-700 mb-1">Wilayah</label>
                    <input type="text" name="region" id="region" value="{{ old('region') }}"
                           class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
                </div>

                <div>
                    <label for="description" class="block text-sm font-semibold text-gray-700 mb-1">Deskripsi</label>
                    <textarea name="description" id="description" rows="3"
                              class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">{{ old('description') }}</textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <a href="{{ route('organizations.index') }}" class="px-5 py-2.5 rounded-full text-gray-600 text-sm font-semibold hover:bg-gray-100 transition-colors">
                        Batal
                    </a>
                    <button type="submit" class="px-5 py-2.5 rounded-full bg-primary text-white text-sm font-semibold">
                        Buat Organisasi
                    </button>
                </div>
            </div>
        </form>
    </div>

    <script>
        (function () {
            const slugInput = document.getElementById('slug');

            slugInput.addEventListener('input', () => {
                const cursorFromEnd = slugInput.value.length - slugInput.selectionEnd;

                const sanitized = slugInput.value
                    .toLowerCase()
                    .replace(/\s+/g, '-')
                    .replace(/[^a-z0-9-]/g, '')
                    .replace(/-+/g, '-');

                if (sanitized !== slugInput.value) {
                    slugInput.value = sanitized;
                    const pos = Math.max(0, sanitized.length - cursorFromEnd);
                    slugInput.setSelectionRange(pos, pos);
                }
            });
        })();
    </script>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                window.autoStartOnboardingTour('create', @json($hasSeenCreateTour));
            });
        </script>
    @endpush
@endsection
