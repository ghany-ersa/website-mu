{{-- Shared by create.blade.php (no $template) and edit.blade.php, so normalize it once here
     rather than guarding every use below. --}}
@php($template = $template ?? null)

@if ($errors->any())
    <div class="mb-6 rounded-lg bg-red-50 border border-red-200 text-red-600 px-4 py-3 text-sm">
        <ul class="list-disc list-inside space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="bg-white rounded-2xl shadow-soft p-6 space-y-6">
    <div class="grid md:grid-cols-2 gap-6">
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1" for="name">Nama Template</label>
            <input type="text" name="name" id="name" value="{{ old('name', $template->name ?? '') }}"
                   class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1" for="slug">Slug</label>
            <input type="text" name="slug" id="slug" value="{{ old('slug', $template->slug ?? '') }}"
                   class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-primary/30">
        </div>
    </div>

    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1" for="organization_type_id">Jenis Organisasi</label>
        <select name="organization_type_id" id="organization_type_id"
                class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
            <option value="">— Generik (semua jenis) —</option>
            @foreach ($organizationTypes as $type)
                <option value="{{ $type->id }}" @selected(old('organization_type_id', $template->organization_type_id ?? '') == $type->id)>
                    {{ $type->name }} ({{ $type->category->label() }})
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1" for="description">Deskripsi</label>
        <textarea name="description" id="description" rows="2"
                  class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">{{ old('description', $template->description ?? '') }}</textarea>
    </div>

    <div x-data="{ preview: null, name: null }">
        <label class="block text-sm font-semibold text-gray-700 mb-1" for="thumbnail">Thumbnail</label>

        <div class="flex flex-col sm:flex-row sm:items-start gap-4">
            {{-- Current image, or the newly picked file once one is chosen. --}}
            <div class="w-full sm:w-40 shrink-0 aspect-[4/3] rounded-lg border border-gray-200 bg-gray-50 overflow-hidden">
                <template x-if="preview">
                    <img :src="preview" alt="Pratinjau thumbnail" class="w-full h-full object-cover">
                </template>
                <div x-show="! preview" class="w-full h-full">
                    @if ($template?->thumbnailUrl())
                        <img src="{{ $template->thumbnailUrl() }}" alt="{{ $template->name }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-gray-300 text-xs">Belum ada</div>
                    @endif
                </div>
            </div>

            <div class="flex-1 min-w-0">
                <input type="file" name="thumbnail" id="thumbnail" accept="image/*"
                       x-on:change="const f = $event.target.files[0]; name = f?.name ?? null; preview = f ? URL.createObjectURL(f) : null"
                       class="block w-full text-sm text-gray-600 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-primary/10 file:text-primary file:text-sm file:font-semibold hover:file:bg-primary/20 file:cursor-pointer">
                <p class="text-xs text-gray-400 mt-1.5">JPG, PNG, atau WebP. Maksimal 5 MB — otomatis dikecilkan dan dikonversi ke WebP.</p>

                @if ($template?->thumbnail_path)
                    <label class="inline-flex items-center gap-2 mt-3 text-sm text-gray-600">
                        <input type="checkbox" name="remove_thumbnail" value="1"
                               class="rounded border-gray-300 text-primary focus:ring-primary/30">
                        Hapus thumbnail saat ini
                    </label>
                @endif
            </div>
        </div>
    </div>

    <details class="rounded-lg border border-gray-200" @if ($errors->has('structure')) open @endif>
        <summary class="cursor-pointer px-4 py-3 text-sm font-semibold text-gray-700">
            Struktur Halaman (JSON) — Lanjutan
        </summary>
        <div class="px-4 pb-4">
            @isset($template)
                <p class="text-xs text-gray-500 mb-2">
                    Cara termudah menyusun struktur adalah lewat
                    <a href="{{ route('admin.templates.design', $template) }}" class="text-secondary font-semibold hover:underline">Edit Visual</a>.
                    Ubah JSON di sini hanya bila perlu penyesuaian manual.
                </p>
            @endisset
            <p class="text-xs text-gray-400 mb-2">
                Format: <code>&#123;"pages": [&#123;"slug", "name", "sections": [&#123;"key", "variant", "content"&#125;]&#125;]&#125;</code>
            </p>
            <textarea name="structure" id="structure" rows="16"
                      class="w-full rounded-lg border border-gray-200 px-3 py-2 text-xs font-mono focus:outline-none focus:ring-2 focus:ring-primary/30">{{ old('structure', isset($template) ? json_encode($template->structure, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : '') }}</textarea>
        </div>
    </details>

    <label class="flex items-center gap-2 text-sm font-semibold text-gray-700">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $template->is_active ?? true))
               class="rounded border-gray-300 text-primary focus:ring-primary/30">
        Aktif
    </label>

    <label class="flex items-center gap-2 text-sm font-semibold text-gray-700">
        <input type="checkbox" name="is_exclusive" value="1" @checked(old('is_exclusive', $template->is_exclusive ?? false))
               class="rounded border-gray-300 text-primary focus:ring-primary/30">
        Eksklusif <span class="font-normal text-gray-400">(hanya untuk organisasi dengan paket yang mendukung template eksklusif)</span>
    </label>

    <label class="flex items-center gap-2 text-sm font-semibold text-gray-700">
        <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $template->is_featured ?? false))
               class="rounded border-gray-300 text-primary focus:ring-primary/30">
        Tampilkan di Landing Page <span class="font-normal text-gray-400">(muncul di grid pilihan template halaman utama)</span>
    </label>
</div>
