@extends('layouts.app')

@section('title', 'Brand Settings — '.$organization->name.' — Website-mu')

@section('content')
    <div class="max-w-3xl mx-auto" x-data="brandForm()">
        <a href="{{ route('organizations.show', $organization) }}"
           class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 hover:text-primary transition-colors mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">
                <path fill-rule="evenodd" d="M17 10a.75.75 0 0 1-.75.75H5.612l4.158 3.96a.75.75 0 1 1-1.04 1.08l-5.5-5.25a.75.75 0 0 1 0-1.08l5.5-5.25a.75.75 0 1 1 1.04 1.08L5.612 9.25H16.25A.75.75 0 0 1 17 10Z" clip-rule="evenodd" />
            </svg>
            Kembali ke {{ $organization->name }}
        </a>
        <h1 class="text-2xl font-extrabold text-primary mb-2">Brand Settings</h1>
        <p class="text-sm text-gray-500 mb-8">Warna dan logo ini diterapkan ke seluruh halaman {{ $organization->name }}.</p>

        <form action="{{ route('organizations.brand.update', $organization) }}" method="POST">
            @csrf
            @method('PATCH')
            <input type="hidden" name="logo" x-model="logoUrl">

            <div class="bg-white rounded-2xl shadow-soft p-6 space-y-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Logo</label>
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 rounded-xl border border-gray-200 bg-gray-50 overflow-hidden flex items-center justify-center shrink-0">
                            <img x-show="logoUrl" x-cloak :src="logoUrl" alt="" class="w-full h-full object-contain">
                            <span x-show="!logoUrl" class="text-xs text-gray-300">Logo</span>
                        </div>
                        <div class="flex gap-2">
                            <button type="button" @click="openPicker()"
                                    class="px-3.5 py-2.5 rounded-xl border border-gray-200 bg-white text-sm font-semibold text-gray-600 hover:border-primary/40 hover:text-primary transition">
                                <span x-text="logoUrl ? 'Ganti logo' : 'Pilih logo'"></span>
                            </button>
                            <button type="button" x-show="logoUrl" x-cloak @click="logoUrl = ''"
                                    class="px-3.5 py-2.5 rounded-xl border border-gray-200 bg-white text-sm font-semibold text-gray-400 hover:text-red-500 hover:border-red-200 transition">
                                Hapus
                            </button>
                        </div>
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 gap-6">
                    <div>
                        <label for="primary_color" class="block text-sm font-semibold text-gray-700 mb-1">Warna Primer</label>
                        <div class="flex items-center gap-2">
                            <input type="color" x-model="primaryColor"
                                   class="w-11 h-11 rounded-lg border border-gray-200 shrink-0 cursor-pointer">
                            <input type="text" name="primary_color" id="primary_color" x-model="primaryColor"
                                   pattern="^#[0-9a-fA-F]{6}$" placeholder="#2C368B"
                                   class="w-full rounded-lg border px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 transition"
                                   :class="isTooLight(primaryColor) ? 'border-red-300 focus:ring-red-200' : 'border-gray-200 focus:ring-primary/30'">
                        </div>
                        <p x-show="isTooLight(primaryColor)" x-cloak class="text-xs text-red-500 mt-1">
                            Terlalu terang, teks akan sulit dibaca. Pilih warna yang lebih gelap.
                        </p>
                        <p x-show="!isTooLight(primaryColor)" class="text-xs text-gray-400 mt-1">Default: warna template yang dipilih saat organisasi dibuat.</p>
                    </div>

                    <div>
                        <label for="secondary_color" class="block text-sm font-semibold text-gray-700 mb-1">Warna Sekunder</label>
                        <div class="flex items-center gap-2">
                            <input type="color" x-model="secondaryColor"
                                   class="w-11 h-11 rounded-lg border border-gray-200 shrink-0 cursor-pointer">
                            <input type="text" name="secondary_color" id="secondary_color" x-model="secondaryColor"
                                   pattern="^#[0-9a-fA-F]{6}$" placeholder="#079C4E"
                                   class="w-full rounded-lg border px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 transition"
                                   :class="isTooLight(secondaryColor) ? 'border-red-300 focus:ring-red-200' : 'border-gray-200 focus:ring-primary/30'">
                        </div>
                        <p x-show="isTooLight(secondaryColor)" x-cloak class="text-xs text-red-500 mt-1">
                            Terlalu terang (mendekati putih), akan menabrak teks yang memakai warna ini. Pilih warna yang lebih gelap.
                        </p>
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 gap-6">
                    <div>
                        <label for="font_family" class="block text-sm font-semibold text-gray-700 mb-1">Jenis Font</label>
                        <select name="font_family" id="font_family" x-model="fontFamily"
                                class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 transition">
                            @foreach (array_keys(config('branding.fonts')) as $font)
                                <option value="{{ $font }}" style="font-family: {{ config("branding.fonts.$font.stack") }}">{{ $font }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Gaya Sudut</label>
                        <div class="flex gap-2">
                            @foreach (['sharp' => 'Tajam', 'soft' => 'Lembut', 'full' => 'Bulat'] as $token => $label)
                                <button type="button" @click="borderRadius = '{{ $token }}'"
                                        class="flex-1 px-3 py-2.5 border text-sm font-semibold transition"
                                        :class="borderRadius === '{{ $token }}' ? 'border-primary text-primary bg-primary/5' : 'border-gray-200 text-gray-500 hover:border-gray-300'"
                                        style="border-radius: {{ config("branding.radii.$token") }}">
                                    {{ $label }}
                                </button>
                            @endforeach
                        </div>
                        <input type="hidden" name="border_radius" x-model="borderRadius">
                    </div>
                </div>

                <div>
                    <p class="text-xs font-semibold text-gray-500 mb-2">Pratinjau</p>
                    <div class="rounded-xl border border-gray-200 p-4 flex flex-wrap items-center gap-3" :style="`font-family: ${fontStack()}`">
                        <span class="px-4 py-2 text-white text-sm font-semibold" :style="`background-color: ${primaryColor}; border-radius: ${radiusValue()}`">Tombol Primer</span>
                        <span class="px-4 py-2 text-white text-sm font-semibold" :style="`background-color: ${secondaryColor}; border-radius: ${radiusValue()}`">Tombol Sekunder</span>
                        <span class="text-sm font-bold" :style="`color: ${primaryColor}`">Contoh Judul</span>
                    </div>
                </div>

            </div>

            <div class="bg-white rounded-2xl shadow-soft p-6 space-y-6 mt-6">
                <div>
                    <h2 class="font-bold text-gray-800">Kontak</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Ditampilkan di halaman situs dan digunakan pengunjung untuk menghubungi {{ $organization->name }}.</p>
                </div>

                <div class="grid sm:grid-cols-2 gap-6">
                    <div>
                        <label for="phone" class="block text-sm font-semibold text-gray-700 mb-1">Telepon</label>
                        <input type="text" name="phone" id="phone" value="{{ old('phone', $organization->phone) }}"
                               placeholder="0331 123456"
                               class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 transition">
                        @error('phone') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="whatsapp" class="block text-sm font-semibold text-gray-700 mb-1">WhatsApp</label>
                        <input type="text" name="whatsapp" id="whatsapp" value="{{ old('whatsapp', $organization->whatsapp) }}"
                               placeholder="6281234567890"
                               class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 transition">
                        @error('whatsapp') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="email" class="block text-sm font-semibold text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" id="email" value="{{ old('email', $organization->email) }}"
                               placeholder="kontak@organisasi.id"
                               class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 transition">
                        @error('email') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-6">
                <a href="{{ route('organizations.show', $organization) }}"
                   class="px-5 py-2.5 rounded-full text-gray-600 text-sm font-semibold hover:bg-gray-100 transition-colors">
                    Batal
                </a>
                <button type="submit" :disabled="isTooLight(primaryColor) || isTooLight(secondaryColor)"
                        class="px-5 py-2.5 rounded-full bg-primary text-white text-sm font-semibold disabled:opacity-50 disabled:cursor-not-allowed">
                    Simpan
                </button>
            </div>
        </form>

        <div x-show="picker.open" x-cloak
            class="fixed inset-0 z-50 bg-gray-900/50 backdrop-blur-sm flex items-end sm:items-center justify-center p-0 sm:p-4"
            @keydown.escape.window="picker.open = false">
            <div @click.outside="picker.open = false"
                class="bg-white rounded-t-2xl sm:rounded-2xl w-full sm:max-w-2xl max-h-[85vh] flex flex-col shadow-2xl">
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 shrink-0">
                    <h3 class="font-bold text-gray-800">Pilih Logo</h3>
                    <button type="button" @click="picker.open = false"
                            class="w-7 h-7 flex items-center justify-center rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition">&times;</button>
                </div>
                <div class="p-5 border-b border-gray-100 shrink-0">
                    <label class="flex flex-col items-center justify-center gap-1.5 border-2 border-dashed border-gray-200 rounded-xl py-6 cursor-pointer hover:border-primary/40 hover:bg-primary/5 transition text-center">
                        <span class="text-sm font-semibold text-gray-600">Unggah logo baru</span>
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
                                <button type="button" @click="logoUrl = item.url; picker.open = false"
                                        class="aspect-square w-full rounded-xl overflow-hidden bg-gray-100 ring-1 ring-gray-200 hover:ring-2 hover:ring-primary transition p-2">
                                    <img :src="item.url" :alt="item.original_name" class="w-full h-full object-contain">
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
            Alpine.data('brandForm', () => ({
                logoUrl: @json($organization->logo ?? ''),
                primaryColor: @json($organization->primaryColor()),
                secondaryColor: @json($organization->secondaryColor()),
                fontFamily: @json($organization->fontFamily()),
                borderRadius: @json($organization->borderRadius()),
                fontStacks: @json(collect(config('branding.fonts'))->map->stack),
                radii: @json(config('branding.radii')),
                fontStack() {
                    return this.fontStacks[this.fontFamily] ?? this.fontStacks[Object.keys(this.fontStacks)[0]];
                },
                radiusValue() {
                    return this.radii[this.borderRadius] ?? Object.values(this.radii)[0];
                },
                // Mirrors App\Rules\NotTooLightColor (WCAG relative luminance, same 0.85
                // threshold) so the form warns before submit instead of only after a
                // validation round-trip.
                isTooLight(hex) {
                    if (!/^#[0-9a-fA-F]{6}$/.test(hex)) return false;
                    const channels = [1, 3, 5].map((i) => {
                        const c = parseInt(hex.slice(i, i + 2), 16) / 255;
                        return c <= 0.03928 ? c / 12.92 : ((c + 0.055) / 1.055) ** 2.4;
                    });
                    const luminance = 0.2126 * channels[0] + 0.7152 * channels[1] + 0.0722 * channels[2];
                    return luminance > 0.85;
                },
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
                    formData.append('category', 'brand');
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
                    if (!confirm('Hapus gambar ini dari galeri?')) return;
                    if (this.logoUrl === item.url) this.logoUrl = '';
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
