@extends('layouts.account')

@section('title', 'Buat Organisasi — Website-mu')

@section('content')
    <div class="max-w-3xl mx-auto">
        <h1 class="text-2xl font-extrabold text-primary mb-2">Buat Organisasi Baru</h1>
        <p class="text-sm text-gray-500 mb-8">Lengkapi detail organisasi Anda di bawah ini.</p>

        @if ($selectedTemplate)
            <div class="mb-6 rounded-lg bg-primary/10 border border-primary/20 text-primary px-4 py-3 text-sm">
                Anda memilih template <strong>{{ $selectedTemplate->name }}</strong>.
            </div>
        @endif

        <form action="{{ route('organizations.store') }}" method="POST" id="organization-form">
            @csrf

            @if ($selectedTemplate)
                <input type="hidden" name="template_id" value="{{ $selectedTemplate->id }}">
            @endif

            <div class="bg-white rounded-2xl shadow-soft p-6 space-y-5">
                <div>
                    <label for="organization_type_id" class="block text-sm font-semibold text-gray-700 mb-1">Jenis Organisasi</label>
                    <select name="organization_type_id" id="organization_type_id" required
                            class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
                        <option value="" disabled @selected(! old('organization_type_id', $selectedTemplate?->organization_type_id))>Pilih jenis organisasi</option>
                        @foreach ($organizationTypes->groupBy(fn ($type) => $type->category->label()) as $categoryLabel => $types)
                            <optgroup label="{{ $categoryLabel }}">
                                @foreach ($types as $type)
                                    <option value="{{ $type->id }}"
                                            @selected(old('organization_type_id', $selectedTemplate?->organization_type_id) == $type->id)>
                                        {{ $type->name }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="name" class="block text-sm font-semibold text-gray-700 mb-1">Nama Organisasi</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                           class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
                </div>

                <div>
                    <label for="slug" class="block text-sm font-semibold text-gray-700 mb-1">Slug (subdomain)</label>
                    <div class="flex rounded-lg border border-gray-200 overflow-hidden focus-within:ring-2 focus-within:ring-primary/30">
                        <input type="text" name="slug" id="slug" value="{{ old('slug') }}" required
                               class="w-full px-3 py-2 text-sm font-mono focus:outline-none">
                        <span class="flex items-center px-3 text-sm font-mono text-gray-400 bg-gray-50 border-l border-gray-200 whitespace-nowrap">.website-mu.id</span>
                    </div>
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
@endsection
