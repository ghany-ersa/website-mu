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
        <x-form.field name="title" label="Judul" :value="$article->title" required />
        <x-form.field name="slug" label="Slug" :value="$article->slug" required />
    </div>

    <div class="grid md:grid-cols-2 gap-6">
        <x-form.field name="category" label="Kategori" :value="$article->category" placeholder="mis. Kabar Muhammadiyah" />
        <x-form.field name="cover_image" label="URL Gambar Sampul" :value="$article->cover_image" placeholder="https://..." />
    </div>

    <x-form.textarea-field name="excerpt" label="Ringkasan" :value="$article->excerpt" rows="2" />

    <x-form.richtext-field name="body" label="Isi Artikel" :value="$article->body" />

    <x-form.select-field name="status" label="Status"
        :options="collect(\App\Enums\PublishStatus::cases())->mapWithKeys(fn ($status) => [$status->value => $status->label()])"
        :selected="$article->status?->value ?? 'draft'" />
</div>
