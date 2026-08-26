@extends('layouts.organization')

@section('title', ($post->exists ? 'Edit Berita' : 'Tulis Berita').' — '.$organization->name.' — Website-mu')

@section('content')
    <div class="max-w-3xl mx-auto" x-data="postForm()">
        <x-crud.back-link
            :href="route('organizations.posts.index', $organization).$builderQuery"
            label="Kembali ke Berita" />

        <x-crud.page-header
            :title="$post->exists ? 'Edit Berita' : 'Tulis Berita'"
            :subtitle="$organization->name" />

        <x-form.shell
            :action="$post->exists ? route('organizations.posts.update', [$organization, $post]) : route('organizations.posts.store', $organization)"
            :method="$post->exists ? 'PATCH' : 'POST'"
            :from-builder="$fromBuilder"
            :section="request('section')">
            <input type="hidden" name="image" x-model="imageUrl">

            <x-ui.card>
                <x-form.field name="title" label="Judul" :value="$post->title" required />

                <x-form.field name="category" label="Kategori" :value="$post->category" />

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Gambar</label>
                    <div class="rounded-xl border border-gray-200 bg-gray-50 aspect-video overflow-hidden flex items-center justify-center mb-2"
                        x-show="imageUrl" x-cloak>
                        <img :src="imageUrl" alt="" class="w-full h-full object-cover">
                    </div>
                    <div class="flex gap-2">
                        <button type="button" @click="openPicker()"
                                class="flex-1 px-3.5 py-2.5 rounded-xl border border-gray-200 bg-white text-sm font-semibold text-gray-600 hover:border-primary/40 hover:text-primary transition">
                            <span x-text="imageUrl ? 'Ganti gambar' : 'Pilih gambar'"></span>
                        </button>
                        <button type="button" x-show="imageUrl" x-cloak @click="imageUrl = ''"
                                class="px-3.5 py-2.5 rounded-xl border border-gray-200 bg-white text-sm font-semibold text-gray-400 hover:text-red-500 hover:border-red-200 transition">
                            Hapus
                        </button>
                    </div>
                </div>

                <x-form.textarea-field name="excerpt" label="Ringkasan" :value="$post->excerpt" :rows="2" />

                <x-form.textarea-field name="body" label="Isi Berita" :value="$post->body" :rows="8" />

                <x-form.select-field name="status" label="Status"
                    :options="collect(\App\Enums\PublishStatus::cases())->mapWithKeys(fn ($status) => [$status->value => $status->label()])"
                    :selected="$post->status?->value ?? 'draft'" />

                <div class="flex items-center justify-end gap-3 pt-2">
                    <a href="{{ route('organizations.posts.index', $organization) }}{{ $builderQuery }}"
                       class="px-5 py-2.5 rounded-full text-gray-600 text-sm font-semibold hover:bg-gray-100 transition-colors">
                        Batal
                    </a>
                    <button type="submit" class="px-5 py-2.5 rounded-full bg-primary text-white text-sm font-semibold">
                        Simpan
                    </button>
                </div>
            </x-ui.card>
        </x-form.shell>

        <div x-show="picker.open" x-cloak
            class="fixed inset-0 z-50 bg-gray-900/50 backdrop-blur-sm flex items-end sm:items-center justify-center p-0 sm:p-4"
            @keydown.escape.window="picker.open = false">
            <div @click.outside="picker.open = false"
                class="bg-white rounded-t-2xl sm:rounded-2xl w-full sm:max-w-2xl max-h-[85vh] flex flex-col shadow-2xl">
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 shrink-0">
                    <h3 class="font-bold text-gray-800">Pilih Gambar</h3>
                    <button type="button" @click="picker.open = false"
                            class="w-7 h-7 flex items-center justify-center rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition">&times;</button>
                </div>
                <div class="p-5 border-b border-gray-100 shrink-0">
                    <label class="flex flex-col items-center justify-center gap-1.5 border-2 border-dashed border-gray-200 rounded-xl py-6 cursor-pointer hover:border-primary/40 hover:bg-primary/5 transition text-center">
                        <span class="text-sm font-semibold text-gray-600">Unggah gambar baru</span>
                        <span class="text-xs text-gray-400">PNG, JPG, atau WebP. Maks 10MB.</span>
                        <input type="file" accept="image/*" multiple class="hidden"
                               @change="upload($event.target.files); $event.target.value = ''">
                    </label>
                </div>
                <div class="flex-1 overflow-y-auto p-5">
                    <div x-show="picker.loading" class="text-center text-sm text-gray-400 py-8">Memuat…</div>
                    <div x-show="!picker.loading && picker.items.length === 0" class="text-center text-sm text-gray-400 py-8">
                        Belum ada gambar. Unggah gambar pertama di atas.
                    </div>
                    <div class="grid grid-cols-3 sm:grid-cols-4 gap-3" x-show="!picker.loading">
                        <template x-for="item in picker.items" :key="item.id">
                            <div class="relative group">
                                <button type="button" @click="imageUrl = item.url; picker.open = false"
                                        class="aspect-square w-full rounded-xl overflow-hidden bg-gray-100 ring-1 ring-gray-200 hover:ring-2 hover:ring-primary transition">
                                    <img :src="item.url" :alt="item.original_name" class="w-full h-full object-cover">
                                </button>
                                <button type="button" @click.stop="deleteMedia(item)"
                                        class="absolute top-1.5 right-1.5 w-6 h-6 flex items-center justify-center rounded-full bg-gray-900/60 text-white opacity-100 sm:opacity-0 sm:group-hover:opacity-100 hover:bg-red-600 transition"
                                        title="Hapus gambar">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm4-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('postForm', () => ({
                imageUrl: @json($post->image ?? ''),
                picker: { open: false, loading: false, items: [], fetched: false },
                async openPicker() {
                    this.picker.open = true;
                    if (this.picker.fetched) return;
                    this.picker.loading = true;
                    const res = await fetch(@json(route('organizations.media.index', $organization)), {
                        headers: { Accept: 'application/json' },
                    });
                    this.picker.items = await res.json();
                    this.picker.fetched = true;
                    this.picker.loading = false;
                },
                async upload(files) {
                    if (!files || !files.length) return;
                    const formData = new FormData();
                    [...files].forEach((file) => formData.append('files[]', file));
                    formData.append('category', 'berita');
                    this.picker.loading = true;
                    const res = await fetch(@json(route('organizations.media.store', $organization)), {
                        method: 'POST',
                        headers: {
                            Accept: 'application/json',
                            'X-CSRF-TOKEN': @json(csrf_token()),
                        },
                        body: formData,
                    });
                    const uploaded = await res.json();
                    this.picker.items = [...uploaded, ...this.picker.items];
                    this.picker.loading = false;
                },
                async deleteMedia(item) {
                    if (!(await confirmAction('Hapus gambar ini dari galeri?'))) return;
                    if (this.imageUrl === item.url) this.imageUrl = '';
                    const res = await fetch(`${@json(route('organizations.media.index', $organization))}/${item.id}`, {
                        method: 'DELETE',
                        headers: {
                            Accept: 'application/json',
                            'X-CSRF-TOKEN': @json(csrf_token()),
                        },
                    });
                    if (res.ok) {
                        this.picker.items = this.picker.items.filter((i) => i.id !== item.id);
                    }
                },
            }));
        });
    </script>
@endsection
