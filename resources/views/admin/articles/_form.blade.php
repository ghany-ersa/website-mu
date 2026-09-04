@if ($errors->any())
    <div class="mb-6 rounded-lg bg-red-50 border border-red-200 text-red-600 px-4 py-3 text-sm">
        <ul class="list-disc list-inside space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="bg-white rounded-2xl shadow-soft p-6 space-y-6" x-data="articleForm()">
    <div class="grid md:grid-cols-2 gap-6">
        <div>
            <label for="title" class="block text-sm font-semibold text-gray-700 mb-1">Judul</label>
            <input type="text" name="title" id="title" x-model="title" @input="onTitleInput()" required
                   class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
            @error('title')
                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="slug" class="block text-sm font-semibold text-gray-700 mb-1">Slug</label>
            <input type="text" name="slug" id="slug" x-model="slug" @input="onSlugInput()" required
                   class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-primary/30">
            @error('slug')
                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="grid md:grid-cols-2 gap-6">
        <div>
            <label for="category" class="block text-sm font-semibold text-gray-700 mb-1">Kategori</label>
            <template x-if="!addingCategory">
                <select id="category" x-model="category" @change="onCategoryChange()"
                        class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primary/30">
                    <option value="">— Tanpa kategori —</option>
                    @foreach ($categories as $existingCategory)
                        <option value="{{ $existingCategory }}">{{ $existingCategory }}</option>
                    @endforeach
                    <option value="__new__">+ Kategori baru…</option>
                </select>
            </template>
            <template x-if="addingCategory">
                <div class="flex gap-2">
                    <input type="text" x-model="newCategory" x-ref="newCategoryInput" placeholder="Nama kategori baru"
                           class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
                    <button type="button" @click="cancelNewCategory()"
                            class="px-3 py-2 rounded-lg border border-gray-200 text-sm font-semibold text-gray-500 hover:bg-gray-50 transition">
                        Batal
                    </button>
                </div>
            </template>
            <input type="hidden" name="category" :value="addingCategory ? newCategory : category">
            @error('category')
                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <x-form.select-field name="status" label="Status"
            :options="collect(\App\Enums\PublishStatus::cases())->mapWithKeys(fn ($status) => [$status->value => $status->label()])"
            :selected="$article->status?->value ?? 'draft'" />
    </div>

    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1">Gambar Sampul</label>
        <div class="rounded-xl border border-gray-200 bg-gray-50 aspect-video max-w-md overflow-hidden flex items-center justify-center mb-2"
             x-show="coverImage" x-cloak>
            <img :src="coverImage" alt="" class="w-full h-full object-cover">
        </div>
        <div class="flex gap-2 max-w-md">
            <label class="flex-1 px-3.5 py-2.5 rounded-xl border border-gray-200 bg-white text-sm font-semibold text-gray-600 hover:border-primary/40 hover:text-primary transition text-center cursor-pointer">
                <span x-show="!uploading" x-text="coverImage ? 'Ganti gambar' : 'Unggah gambar'"></span>
                <span x-show="uploading" x-cloak>Mengunggah…</span>
                <input type="file" accept="image/*" class="hidden" :disabled="uploading"
                       @change="upload($event.target.files); $event.target.value = ''">
            </label>
            <button type="button" x-show="coverImage" x-cloak @click="coverImage = ''"
                    class="px-3.5 py-2.5 rounded-xl border border-gray-200 bg-white text-sm font-semibold text-gray-400 hover:text-red-500 hover:border-red-200 transition">
                Hapus
            </button>
        </div>
        <input type="hidden" name="cover_image" x-model="coverImage">
        @error('cover_image')
            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
        @enderror
    </div>

    <x-form.richtext-field name="body" label="Isi Artikel" :value="$article->body" />
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('articleForm', () => ({
            title: @json(old('title', $article->title ?? '')),
            slug: @json(old('slug', $article->slug ?? '')),
            slugTouched: @json($article->exists),
            category: @json(old('category', $article->category ?? '')),
            addingCategory: false,
            newCategory: '',
            coverImage: @json(old('cover_image', $article->cover_image ?? '')),
            uploading: false,

            init() {
                const existingCategories = @json($categories);
                if (this.category && ! existingCategories.includes(this.category)) {
                    this.addingCategory = true;
                    this.newCategory = this.category;
                }
            },

            slugify(value) {
                return value
                    .toLowerCase()
                    .trim()
                    .replace(/[^a-z0-9\s-]/g, '')
                    .replace(/\s+/g, '-')
                    .replace(/-+/g, '-')
                    .replace(/^-+|-+$/g, '');
            },

            onTitleInput() {
                if (! this.slugTouched) {
                    this.slug = this.slugify(this.title);
                }
            },

            onSlugInput() {
                this.slugTouched = true;
                this.slug = this.slugify(this.slug);
            },

            onCategoryChange() {
                if (this.category === '__new__') {
                    this.addingCategory = true;
                    this.newCategory = '';
                    this.category = '';
                    this.$nextTick(() => this.$refs.newCategoryInput?.focus());
                }
            },

            cancelNewCategory() {
                this.addingCategory = false;
                this.newCategory = '';
                this.category = '';
            },

            async upload(files) {
                if (! files || ! files.length) return;
                const formData = new FormData();
                formData.append('file', files[0]);
                this.uploading = true;
                try {
                    const res = await fetch(@json(route('admin.articles.images.store')), {
                        method: 'POST',
                        headers: {
                            Accept: 'application/json',
                            'X-CSRF-TOKEN': @json(csrf_token()),
                        },
                        body: formData,
                    });
                    if (! res.ok) throw new Error('Upload gagal');
                    const data = await res.json();
                    this.coverImage = data.url;
                } catch (e) {
                    alert('Gagal mengunggah gambar. Coba lagi.');
                } finally {
                    this.uploading = false;
                }
            },
        }));
    });
</script>
