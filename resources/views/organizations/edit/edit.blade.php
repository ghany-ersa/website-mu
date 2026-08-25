@extends('layouts.organization')

@section('title', 'Edit Organisasi — '.$organization->name.' — Website-mu')

@section('content')
    <div class="max-w-3xl mx-auto">
        <a href="{{ route('organizations.show', $organization) }}"
           class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 hover:text-primary transition-colors mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">
                <path fill-rule="evenodd" d="M17 10a.75.75 0 0 1-.75.75H5.612l4.158 3.96a.75.75 0 1 1-1.04 1.08l-5.5-5.25a.75.75 0 0 1 0-1.08l5.5-5.25a.75.75 0 1 1 1.04 1.08L5.612 9.25H16.25A.75.75 0 0 1 17 10Z" clip-rule="evenodd" />
            </svg>
            Kembali ke {{ $organization->name }}
        </a>
        <h1 class="text-2xl font-extrabold text-primary mb-2">Edit Organisasi</h1>
        <p class="text-sm text-gray-500 mb-8">
            Atur bagaimana {{ $organization->name }} muncul di hasil pencarian dan saat dibagikan. Setiap bagian
            di bawah bersifat sangat mendasar dan berdampak langsung ke situs publik Anda &mdash; disimpan terpisah
            agar tidak berubah tanpa sengaja.
        </p>

        {{-- Nama Organisasi --}}
        <div class="bg-white rounded-2xl shadow-soft p-6 mb-6" x-data="editableField({{ Js::from($organization->name) }}, {{ Js::from($errors->has('name')) }})">
            <div class="flex items-start justify-between gap-3 mb-1">
                <div>
                    <h2 class="font-bold text-gray-800">Nama Organisasi</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Dipakai sebagai judul SEO (meta title) situs publik.</p>
                </div>
                <button type="button" x-show="!editing" @click="startEditing()"
                        class="shrink-0 px-3 py-1.5 rounded-full border border-gray-200 text-xs font-semibold text-gray-600 hover:border-primary/40 hover:text-primary transition">
                    Edit
                </button>
            </div>

            <p x-show="!editing" class="text-sm font-semibold text-gray-800 mt-3" x-text="value"></p>

            <form x-show="editing" x-cloak action="{{ route('organizations.edit.name.update', $organization) }}" method="POST" class="mt-3">
                @csrf
                @method('PATCH')
                <input type="text" name="name" x-model="value" maxlength="255" required
                       class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 transition">
                <p class="text-xs text-amber-600 mt-1.5">
                    Perubahan di sini langsung berdampak pada judul yang tampil di hasil pencarian dan tab browser situs publik Anda.
                </p>
                @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                <div class="flex items-center gap-2 mt-3">
                    <button type="submit" class="px-4 py-2 rounded-full bg-primary text-white text-sm font-semibold">Simpan</button>
                    <button type="button" @click="cancel()" class="px-4 py-2 rounded-full text-gray-500 text-sm font-semibold hover:bg-gray-100 transition">Batal</button>
                </div>
            </form>
        </div>

        {{-- Subdomain --}}
        <div class="bg-white rounded-2xl shadow-soft p-6 mb-6" x-data="editableField({{ Js::from($organization->slug) }}, {{ Js::from($errors->has('slug')) }})">
            <div class="flex items-start justify-between gap-3 mb-1">
                <div>
                    <h2 class="font-bold text-gray-800">Subdomain</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Alamat situs publik Anda.</p>
                </div>
                <button type="button" x-show="!editing" @click="startEditing()"
                        class="shrink-0 px-3 py-1.5 rounded-full border border-gray-200 text-xs font-semibold text-gray-600 hover:border-primary/40 hover:text-primary transition">
                    Edit
                </button>
            </div>

            <p x-show="!editing" class="text-sm font-semibold text-gray-800 mt-3">
                <span x-text="value"></span>{{ $tenantDomain ? '.'.$tenantDomain : '' }}
            </p>

            <form x-show="editing" x-cloak action="{{ route('organizations.edit.slug.update', $organization) }}" method="POST" class="mt-3">
                @csrf
                @method('PATCH')
                <div class="flex items-center rounded-lg border border-gray-200 focus-within:ring-2 focus-within:ring-primary/30 transition overflow-hidden">
                    <input type="text" name="slug" x-model="value"
                           pattern="^[a-z0-9]+(-[a-z0-9]+)*$" minlength="3" maxlength="63" required
                           class="flex-1 min-w-0 px-3 py-2 text-sm font-mono focus:outline-none">
                    @if ($tenantDomain)
                        <span class="px-3 py-2 text-sm text-gray-400 bg-gray-50 border-l border-gray-200 shrink-0">.{{ $tenantDomain }}</span>
                    @endif
                </div>
                <p class="text-xs text-amber-600 mt-1.5">
                    Mengubah slug akan mengubah alamat situs Anda. Tautan lama tidak akan lagi berfungsi.
                </p>
                @error('slug') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                <div class="flex items-center gap-2 mt-3">
                    <button type="submit" class="px-4 py-2 rounded-full bg-primary text-white text-sm font-semibold">Simpan</button>
                    <button type="button" @click="cancel()" class="px-4 py-2 rounded-full text-gray-500 text-sm font-semibold hover:bg-gray-100 transition">Batal</button>
                </div>
            </form>
        </div>

        {{-- Deskripsi --}}
        <div class="bg-white rounded-2xl shadow-soft p-6 mb-6" x-data="editableField({{ Js::from($organization->description ?? '') }}, {{ Js::from($errors->has('description')) }})">
            <div class="flex items-start justify-between gap-3 mb-1">
                <div>
                    <h2 class="font-bold text-gray-800">Deskripsi</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Dipakai langsung sebagai deskripsi SEO (meta description &amp; pratinjau saat tautan dibagikan).</p>
                </div>
                <button type="button" x-show="!editing" @click="startEditing()"
                        class="shrink-0 px-3 py-1.5 rounded-full border border-gray-200 text-xs font-semibold text-gray-600 hover:border-primary/40 hover:text-primary transition">
                    Edit
                </button>
            </div>

            <p x-show="!editing" class="text-sm text-gray-700 mt-3" x-text="value || 'Belum ada deskripsi.'"></p>

            <form x-show="editing" x-cloak action="{{ route('organizations.edit.description.update', $organization) }}" method="POST" class="mt-3">
                @csrf
                @method('PATCH')
                <textarea name="description" rows="3" x-model="value" maxlength="1000"
                          placeholder="Ceritakan singkat tentang {{ $organization->name }}"
                          class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 transition"></textarea>
                <p class="text-xs text-amber-600 mt-1.5">
                    Perubahan di sini langsung berdampak pada tampilan hasil pencarian dan pratinjau tautan situs publik Anda.
                </p>
                @error('description') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                <div class="flex items-center gap-2 mt-3">
                    <button type="submit" class="px-4 py-2 rounded-full bg-primary text-white text-sm font-semibold">Simpan</button>
                    <button type="button" @click="cancel()" class="px-4 py-2 rounded-full text-gray-500 text-sm font-semibold hover:bg-gray-100 transition">Batal</button>
                </div>
            </form>
        </div>

        {{-- Pratinjau --}}
        <div class="bg-white rounded-2xl shadow-soft p-6">
            <p class="text-xs font-semibold text-gray-500 mb-2">Pratinjau Hasil Pencarian</p>
            <div class="rounded-xl border border-gray-200 p-4">
                <p class="text-[13px] text-gray-500 truncate">{{ $organization->slug }}{{ $tenantDomain ? '.'.$tenantDomain : '' }}</p>
                <p class="text-lg text-blue-700 truncate">{{ $organization->name }}</p>
                <p class="text-sm text-gray-600 line-clamp-2">{{ $organization->description ?: 'Belum ada deskripsi.' }}</p>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            // Each field on this page (name/slug/description) is its own isolated
            // read-only-until-Edit form — see OrganizationEditController, which saves
            // each one through its own PATCH endpoint rather than one combined submit.
            Alpine.data('editableField', (initial, hasError) => ({
                editing: hasError,
                original: initial,
                value: initial,
                startEditing() {
                    this.editing = true;
                },
                cancel() {
                    this.value = this.original;
                    this.editing = false;
                },
            }));
        });
    </script>
@endsection
