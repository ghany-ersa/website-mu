@extends('layouts.app')

@section('title', 'SEO & Subdomain — '.$organization->name.' — Website-mu')

@section('content')
    <div class="max-w-3xl mx-auto" x-data="seoForm()">
        <a href="{{ route('organizations.show', $organization) }}"
           class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 hover:text-primary transition-colors mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">
                <path fill-rule="evenodd" d="M17 10a.75.75 0 0 1-.75.75H5.612l4.158 3.96a.75.75 0 1 1-1.04 1.08l-5.5-5.25a.75.75 0 0 1 0-1.08l5.5-5.25a.75.75 0 1 1 1.04 1.08L5.612 9.25H16.25A.75.75 0 0 1 17 10Z" clip-rule="evenodd" />
            </svg>
            Kembali ke {{ $organization->name }}
        </a>
        <h1 class="text-2xl font-extrabold text-primary mb-2">SEO & Subdomain</h1>
        <p class="text-sm text-gray-500 mb-8">Atur bagaimana {{ $organization->name }} muncul di hasil pencarian dan saat dibagikan.</p>

        <form action="{{ route('organizations.seo.update', $organization) }}" method="POST">
            @csrf
            @method('PATCH')

            <div class="bg-white rounded-2xl shadow-soft p-6 space-y-6">
                <div>
                    <h2 class="font-bold text-gray-800">Subdomain</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Alamat situs publik Anda.</p>
                </div>

                <div>
                    <label for="slug" class="block text-sm font-semibold text-gray-700 mb-1">Slug</label>
                    <div class="flex items-center rounded-lg border border-gray-200 focus-within:ring-2 focus-within:ring-primary/30 transition overflow-hidden">
                        <input type="text" name="slug" id="slug" x-model="slug"
                               pattern="^[a-z0-9]+(-[a-z0-9]+)*$" minlength="3" maxlength="63" required
                               class="flex-1 min-w-0 px-3 py-2 text-sm font-mono focus:outline-none">
                        @if ($tenantDomain)
                            <span class="px-3 py-2 text-sm text-gray-400 bg-gray-50 border-l border-gray-200 shrink-0">.{{ $tenantDomain }}</span>
                        @endif
                    </div>
                    @error('slug') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    <p class="text-xs text-amber-600 mt-1">
                        Mengubah slug akan mengubah alamat situs Anda. Tautan lama tidak akan lagi berfungsi.
                    </p>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-soft p-6 space-y-6 mt-6">
                <div>
                    <h2 class="font-bold text-gray-800">Nama & Deskripsi Organisasi</h2>
                    <p class="text-xs text-gray-400 mt-0.5">
                        Nama dipakai sebagai judul SEO (meta title), dan deskripsi dipakai langsung sebagai
                        deskripsi SEO (meta description &amp; pratinjau saat tautan dibagikan).
                    </p>
                </div>

                <div>
                    <label for="name" class="block text-sm font-semibold text-gray-700 mb-1">Nama Organisasi</label>
                    <input type="text" name="name" id="name" x-model="name" maxlength="255" required
                           class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 transition">
                    <p class="text-xs text-amber-600 mt-1">
                        Perubahan di sini langsung berdampak pada judul yang tampil di hasil pencarian dan tab browser situs publik Anda.
                    </p>
                    @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="description" class="block text-sm font-semibold text-gray-700 mb-1">Deskripsi</label>
                    <textarea name="description" id="description" rows="3" x-model="description" maxlength="1000"
                              placeholder="Ceritakan singkat tentang {{ $organization->name }}"
                              class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 transition"></textarea>
                    <p class="text-xs text-amber-600 mt-1">
                        Perubahan di sini langsung berdampak pada tampilan hasil pencarian dan pratinjau tautan situs publik Anda.
                    </p>
                    @error('description') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <p class="text-xs font-semibold text-gray-500 mb-2">Pratinjau Hasil Pencarian</p>
                    <div class="rounded-xl border border-gray-200 p-4">
                        <p class="text-[13px] text-gray-500 truncate">{{ $organization->slug }}{{ $tenantDomain ? '.'.$tenantDomain : '' }}</p>
                        <p class="text-lg text-blue-700 truncate" x-text="name"></p>
                        <p class="text-sm text-gray-600 line-clamp-2" x-text="description || 'Belum ada deskripsi.'"></p>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-6">
                <a href="{{ route('organizations.show', $organization) }}"
                   class="px-5 py-2.5 rounded-full text-gray-600 text-sm font-semibold hover:bg-gray-100 transition-colors">
                    Batal
                </a>
                <button type="submit"
                        class="px-5 py-2.5 rounded-full bg-primary text-white text-sm font-semibold">
                    Simpan
                </button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('seoForm', () => ({
                name: @json(old('name', $organization->name)),
                slug: @json(old('slug', $organization->slug)),
                description: @json(old('description', $organization->description)) ?? '',
            }));
        });
    </script>
@endsection
