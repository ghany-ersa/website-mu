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

    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1" for="thumbnail_path">Path Thumbnail</label>
        <input type="text" name="thumbnail_path" id="thumbnail_path" value="{{ old('thumbnail_path', $template->thumbnail_path ?? '') }}"
               class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
    </div>

    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1" for="structure">
            Struktur Halaman (JSON)
        </label>
        <p class="text-xs text-gray-400 mb-2">
            Format: <code>&#123;"pages": [&#123;"slug", "name", "sections": [&#123;"key", "variant", "content"&#125;]&#125;]&#125;</code>
        </p>
        <textarea name="structure" id="structure" rows="16"
                  class="w-full rounded-lg border border-gray-200 px-3 py-2 text-xs font-mono focus:outline-none focus:ring-2 focus:ring-primary/30">{{ old('structure', isset($template) ? json_encode($template->structure, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : '') }}</textarea>
    </div>

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
</div>
