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

    {{--
        Same visual language as <x-form.image-picker> (preview card, "Pilih/Ganti Gambar"
        button, drag-and-drop) for consistency with the rest of the admin/organization forms,
        but stays a plain form-submit upload rather than that component's separate AJAX
        picker+gallery: a Template is platform-level (no owning Organization), and
        image-picker's gallery is backed by an organization's own media library
        (organizations.media.index) - there's no equivalent "platform media library" to browse,
        so a fresh upload each time is the right scope here, not a missing feature.
    --}}
    <div x-data="{
            preview: @js($template?->thumbnailUrl()),
            hasExisting: @js((bool) $template?->thumbnail_path),
            removed: false,
            dragOver: false,
            fileName: null,
            onFile(file) {
                if (! file) return;
                this.preview = URL.createObjectURL(file);
                this.fileName = file.name;
                this.removed = false;
            },
         }">
        <label class="block text-sm font-semibold text-gray-700 mb-1" for="thumbnail">Thumbnail</label>

        <div class="flex flex-col sm:flex-row sm:items-start gap-4">
            <div class="w-full sm:w-48 shrink-0 aspect-[4/3] rounded-xl border border-gray-200 bg-gray-50 overflow-hidden flex items-center justify-center"
                 x-show="preview && ! removed" x-cloak>
                <img :src="preview" alt="Pratinjau thumbnail" class="w-full h-full object-cover">
            </div>
            <div x-show="! preview || removed"
                 class="w-full sm:w-48 shrink-0 aspect-[4/3] rounded-xl border border-gray-200 bg-gray-50 flex items-center justify-center text-gray-300 text-xs">
                Belum ada
            </div>

            <div class="flex-1 min-w-0">
                <label
                    class="flex flex-col items-center justify-center gap-1.5 border-2 border-dashed rounded-xl py-6 cursor-pointer transition text-center"
                    :class="dragOver ? 'border-primary/40 bg-primary/5' : 'border-gray-200 hover:border-primary/40 hover:bg-primary/5'"
                    @dragover.prevent="dragOver = true" @dragleave.prevent="dragOver = false"
                    @drop.prevent="dragOver = false; $refs.thumbnailInput.files = $event.dataTransfer.files; onFile($event.dataTransfer.files[0])">
                    <span class="text-sm font-semibold text-gray-600" x-text="preview && ! removed ? 'Ganti Gambar' : 'Pilih Gambar'"></span>
                    <span class="text-xs text-gray-400" x-text="fileName ?? 'JPG, PNG, atau WebP. Maks 5 MB.'"></span>
                    <input type="file" name="thumbnail" id="thumbnail" x-ref="thumbnailInput" accept="image/*" class="hidden"
                           @change="onFile($event.target.files[0])">
                </label>
                <p class="text-xs text-gray-400 mt-1.5">Otomatis dikecilkan dan dikonversi ke WebP.</p>

                <button type="button" x-show="hasExisting && ! removed" x-cloak
                        @click="removed = true; preview = null; fileName = null; $refs.thumbnailInput.value = ''"
                        class="mt-3 text-sm text-gray-400 hover:text-red-500 transition">
                    Hapus thumbnail saat ini
                </button>
                <input type="hidden" name="remove_thumbnail" :value="removed ? '1' : '0'">

                @error('thumbnail')
                    <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
                @enderror
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
