@extends('layouts.app')

@section('title', $organization->name.' — Website-mu')

@section('content')
    <div class="bg-white rounded-2xl shadow-soft p-5 sm:p-6 mb-8">
        <div class="flex items-start justify-between gap-3 mb-5">
            <div class="min-w-0">
                <h1 class="text-xl sm:text-2xl font-extrabold text-primary leading-tight">{{ $organization->name }}</h1>
                <p class="text-sm text-gray-500 mt-0.5">
                    {{ $organization->organizationType?->name }}
                    @if ($organization->region) &middot; {{ $organization->region }} @endif
                </p>
            </div>

            <form action="{{ route('organizations.publish', $organization) }}" method="POST" class="shrink-0"
                  onsubmit="return confirm('{{ $organization->status === \App\Enums\OrganizationStatus::Published ? 'Jadikan draft? Situs tidak lagi bisa diakses publik.' : 'Publikasikan situs ini? Situs akan bisa diakses publik.' }}');">
                @csrf
                @method('PATCH')
                <button type="submit"
                        class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-semibold shadow-sm ring-1 ring-inset transition cursor-pointer {{ $organization->status === \App\Enums\OrganizationStatus::Published ? 'bg-secondary text-white ring-secondary hover:bg-secondary/90' : 'bg-white text-gray-600 ring-gray-300 hover:bg-gray-50' }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $organization->status === \App\Enums\OrganizationStatus::Published ? 'bg-white' : 'bg-gray-400' }}"></span>
                    {{ $organization->status === \App\Enums\OrganizationStatus::Published ? 'Published' : 'Draft' }}
                    <svg class="w-3 h-3 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                </button>
            </form>
        </div>

        <div class="flex flex-col sm:flex-row sm:items-center gap-2.5 sm:gap-3">
            <a href="{{ route('organizations.builder.edit', $organization) }}"
               class="inline-flex items-center justify-center px-4 py-2.5 sm:py-2 rounded-full bg-primary text-white text-sm font-semibold hover:opacity-90 transition-opacity">
                Buka Builder
            </a>

            <div class="flex items-center gap-4 sm:gap-5 flex-wrap">
                <a href="{{ route('organizations.brand.edit', $organization) }}"
                   class="text-primary text-sm font-semibold hover:underline">
                    Brand Settings
                </a>
                @if ($organization->template)
                    <a href="{{ route('templates.preview', $organization->template->slug) }}" target="_blank"
                       class="text-primary text-sm font-semibold hover:underline">
                        Lihat Template &rarr;
                    </a>
                @endif
                @if ($organization->status === \App\Enums\OrganizationStatus::Published && $tenantDomain)
                    <a href="https://{{ $organization->slug }}.{{ $tenantDomain }}" target="_blank"
                       class="text-secondary text-sm font-semibold hover:underline">
                        Lihat Situs &rarr;
                    </a>
                @endif
            </div>
        </div>
    </div>

    @php
        $checklist = $organization->onboardingChecklist();
        $checklistDone = count(array_filter($checklist));
    @endphp
    @unless ($checklist['brand'] && $checklist['contact'] && $checklist['published'])
        <div class="bg-white rounded-2xl shadow-soft p-6 mb-8">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-bold text-gray-800">Langkah Awal</h2>
                <span class="text-xs font-semibold text-gray-400">{{ $checklistDone }}/{{ count($checklist) }} selesai</span>
            </div>
            <div class="space-y-2">
                <a href="{{ route('organizations.brand.edit', $organization) }}"
                   class="flex items-center gap-3 p-3 rounded-xl {{ $checklist['brand'] ? 'bg-secondary/5' : 'bg-gray-50 hover:bg-gray-100' }} transition">
                    <span class="w-6 h-6 rounded-full flex items-center justify-center shrink-0 text-xs font-bold {{ $checklist['brand'] ? 'bg-secondary text-white' : 'bg-white border border-gray-300 text-gray-400' }}">
                        {{ $checklist['brand'] ? '✓' : '1' }}
                    </span>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold {{ $checklist['brand'] ? 'text-gray-500 line-through' : 'text-gray-800' }}">Atur Brand</p>
                        <p class="text-xs text-gray-400">Unggah logo dan sesuaikan warna organisasi</p>
                    </div>
                </a>
                <a href="{{ route('organizations.brand.edit', $organization) }}"
                   class="flex items-center gap-3 p-3 rounded-xl {{ $checklist['contact'] ? 'bg-secondary/5' : 'bg-gray-50 hover:bg-gray-100' }} transition">
                    <span class="w-6 h-6 rounded-full flex items-center justify-center shrink-0 text-xs font-bold {{ $checklist['contact'] ? 'bg-secondary text-white' : 'bg-white border border-gray-300 text-gray-400' }}">
                        {{ $checklist['contact'] ? '✓' : '2' }}
                    </span>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold {{ $checklist['contact'] ? 'text-gray-500 line-through' : 'text-gray-800' }}">Isi Kontak</p>
                        <p class="text-xs text-gray-400">Telepon, WhatsApp, atau email untuk dihubungi pengunjung</p>
                    </div>
                </a>
                <div class="flex items-center gap-3 p-3 rounded-xl {{ $checklist['published'] ? 'bg-secondary/5' : 'bg-gray-50' }}">
                    <span class="w-6 h-6 rounded-full flex items-center justify-center shrink-0 text-xs font-bold {{ $checklist['published'] ? 'bg-secondary text-white' : 'bg-white border border-gray-300 text-gray-400' }}">
                        {{ $checklist['published'] ? '✓' : '3' }}
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold {{ $checklist['published'] ? 'text-gray-500 line-through' : 'text-gray-800' }}">Publish Situs</p>
                        @if ($checklist['published'] && $tenantDomain)
                            <p class="text-xs text-gray-400 truncate">{{ $organization->slug }}.{{ $tenantDomain }}</p>
                        @else
                            <p class="text-xs text-gray-400">Tampilkan situs Anda ke publik</p>
                        @endif
                    </div>
                    <form action="{{ route('organizations.publish', $organization) }}" method="POST" class="shrink-0"
                          onsubmit="return confirm('{{ $checklist['published'] ? 'Jadikan draft? Situs tidak lagi bisa diakses publik.' : 'Publikasikan situs ini? Situs akan bisa diakses publik.' }}');">
                        @csrf
                        @method('PATCH')
                        <button type="submit"
                                class="px-3 py-1.5 rounded-full text-xs font-semibold {{ $checklist['published'] ? 'text-gray-500 hover:bg-gray-100' : 'bg-secondary text-white hover:opacity-90' }} transition">
                            {{ $checklist['published'] ? 'Jadikan Draft' : 'Publikasikan' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endunless

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-8">
        <a href="{{ route('organizations.posts.index', $organization) }}"
           class="bg-white rounded-2xl shadow-soft p-4 sm:p-5 hover:-translate-y-0.5 hover:shadow-lg transition-all">
            <div class="w-9 h-9 rounded-full bg-primary/10 flex items-center justify-center mb-3">
                <svg class="w-4.5 h-4.5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 20H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h9l5 5v9a2 2 0 0 1-2 2Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6M9 16h6M9 8h2" />
                </svg>
            </div>
            <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">Konten</p>
            <p class="font-bold text-gray-800 text-sm sm:text-base">Berita</p>
            <p class="text-xs sm:text-sm text-gray-500 mt-0.5">{{ $organization->posts()->count() }} berita</p>
        </a>
        <a href="{{ route('organizations.agendas.index', $organization) }}"
           class="bg-white rounded-2xl shadow-soft p-4 sm:p-5 hover:-translate-y-0.5 hover:shadow-lg transition-all">
            <div class="w-9 h-9 rounded-full bg-primary/10 flex items-center justify-center mb-3">
                <svg class="w-4.5 h-4.5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3M4 11h16M5 5h14a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z" />
                </svg>
            </div>
            <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">Konten</p>
            <p class="font-bold text-gray-800 text-sm sm:text-base">Agenda</p>
            <p class="text-xs sm:text-sm text-gray-500 mt-0.5">{{ $organization->agendas()->count() }} agenda</p>
        </a>
        <a href="{{ route('organizations.announcements.index', $organization) }}"
           class="bg-white rounded-2xl shadow-soft p-4 sm:p-5 hover:-translate-y-0.5 hover:shadow-lg transition-all">
            <div class="w-9 h-9 rounded-full bg-primary/10 flex items-center justify-center mb-3">
                <svg class="w-4.5 h-4.5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2c0 .5-.2 1-.6 1.4L4 17h5m6 0v1a3 3 0 1 1-6 0v-1m6 0H9" />
                </svg>
            </div>
            <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">Konten</p>
            <p class="font-bold text-gray-800 text-sm sm:text-base">Pengumuman</p>
            <p class="text-xs sm:text-sm text-gray-500 mt-0.5">{{ $organization->announcements()->count() }} pengumuman</p>
        </a>
        <a href="{{ route('organizations.gallery.index', $organization) }}"
           class="bg-white rounded-2xl shadow-soft p-4 sm:p-5 hover:-translate-y-0.5 hover:shadow-lg transition-all">
            <div class="w-9 h-9 rounded-full bg-primary/10 flex items-center justify-center mb-3">
                <svg class="w-4.5 h-4.5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16.5V6a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v10.5M4 16.5 8.5 12a1.5 1.5 0 0 1 2.1 0l1.4 1.4a1.5 1.5 0 0 0 2.1 0L17.5 10 20 12.5M4 16.5V18a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-1.5" />
                    <circle cx="8.5" cy="8.5" r="1.25" />
                </svg>
            </div>
            <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">Konten</p>
            <p class="font-bold text-gray-800 text-sm sm:text-base">Galeri</p>
            <p class="text-xs sm:text-sm text-gray-500 mt-0.5">{{ $organization->photos()->count() }} foto</p>
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-soft p-6 mb-8">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-bold text-gray-800">Anggota Pengelola</h2>
        </div>

        <div class="divide-y divide-gray-100">
            @foreach ($organization->members as $member)
                <div class="py-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 sm:gap-4">
                    <div class="min-w-0">
                        <p class="font-medium text-gray-800 text-sm truncate">{{ $member->name }}</p>
                        <p class="text-xs text-gray-400 truncate">{{ $member->email }}</p>
                    </div>

                    @if ($canManageMembers)
                        <div class="flex items-center gap-2 shrink-0">
                            <form action="{{ route('organizations.members.update', [$organization, $member]) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <select name="role" onchange="this.form.submit()"
                                        class="rounded-lg border border-gray-200 px-2 py-1 text-xs focus:outline-none focus:ring-2 focus:ring-primary/30">
                                    @foreach (\App\Enums\OrganizationRole::cases() as $role)
                                        <option value="{{ $role->value }}" @selected($member->pivot->role === $role->value)>
                                            {{ $role->label() }}
                                        </option>
                                    @endforeach
                                </select>
                            </form>
                            <form action="{{ route('organizations.members.destroy', [$organization, $member]) }}" method="POST"
                                  onsubmit="return confirm('Hapus {{ $member->name }} dari organisasi ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 text-xs font-medium hover:underline">Hapus</button>
                            </form>
                        </div>
                    @else
                        <span class="self-start sm:self-auto px-2 py-1 rounded-full bg-primary/10 text-primary text-xs font-semibold shrink-0">
                            {{ \App\Enums\OrganizationRole::from($member->pivot->role)->label() }}
                        </span>
                    @endif
                </div>
            @endforeach
        </div>

        @if ($canManageMembers)
            <form action="{{ route('organizations.members.store', $organization) }}" method="POST"
                  class="mt-5 pt-5 border-t border-gray-100 flex flex-wrap items-end gap-3">
                @csrf
                <div class="flex-1 min-w-[200px]">
                    <label for="email" class="block text-xs font-semibold text-gray-600 mb-1">Email pengguna terdaftar</label>
                    <input type="email" name="email" id="email" required placeholder="nama@email.com"
                           class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
                </div>
                <button type="submit" class="px-4 py-2 rounded-full bg-primary text-white text-sm font-semibold">
                    Tambah Anggota
                </button>
            </form>
        @endif
    </div>

    @if ($canDelete)
        <div class="bg-white rounded-2xl shadow-soft p-6 border border-red-100">
            <h2 class="font-bold text-red-600 mb-1">Hapus Organisasi</h2>
            <p class="text-sm text-gray-500 mb-4">
                Tindakan ini akan menghapus organisasi beserta seluruh data anggotanya secara permanen dan tidak dapat dibatalkan.
            </p>
            <form action="{{ route('organizations.destroy', $organization) }}" method="POST"
                  onsubmit="return confirm('Hapus organisasi &quot;{{ $organization->name }}&quot;? Tindakan ini tidak dapat dibatalkan.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2 rounded-full bg-red-500 text-white text-sm font-semibold hover:bg-red-600">
                    Hapus Organisasi
                </button>
            </form>
        </div>
    @endif
@endsection
