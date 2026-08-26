@extends('layouts.organization')

@section('title', $organization->name . ' — Website-mu')

@section('content')
    <div class="bg-white rounded-2xl shadow-soft p-5 sm:p-6 mb-8">
        <div class="flex items-start justify-between gap-3 mb-5">
            <div class="min-w-0">
                <h1 class="text-xl sm:text-2xl font-extrabold text-primary leading-tight">{{ $organization->name }}</h1>
                <p class="text-sm text-gray-500 mt-0.5">
                    {{ $organization->organizationType?->name }}
                    @if ($organization->region)
                        &middot; {{ $organization->region }}
                    @endif
                </p>
            </div>

            @php
                $blockedFromPublishing = $organization->status !== \App\Enums\OrganizationStatus::Published && $organization->violatesPlanRules();
            @endphp
            <div class="shrink-0 flex flex-col items-end gap-1.5">
                <form action="{{ route('organizations.publish', $organization) }}" method="POST"
                    x-data @submit.prevent="if (await confirmAction('{{ $organization->status === \App\Enums\OrganizationStatus::Published ? 'Jadikan draft? Situs tidak lagi bisa diakses publik.' : 'Publikasikan situs ini? Situs akan bisa diakses publik.' }}', { danger: false, confirmLabel: 'Ya, Lanjutkan' })) $el.submit()">
                    @csrf
                    @method('PATCH')
                    <button type="submit" {{ $blockedFromPublishing ? 'disabled' : '' }}
                        class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-semibold shadow-sm ring-1 ring-inset transition {{ $blockedFromPublishing ? 'bg-gray-50 text-gray-400 ring-gray-200 cursor-not-allowed' : 'cursor-pointer '.($organization->status === \App\Enums\OrganizationStatus::Published ? 'bg-secondary text-white ring-secondary hover:bg-secondary/90' : 'bg-white text-gray-600 ring-gray-300 hover:bg-gray-50') }}">
                        <span
                            class="w-1.5 h-1.5 rounded-full {{ $organization->status === \App\Enums\OrganizationStatus::Published ? 'bg-white' : 'bg-gray-400' }}"></span>
                        {{ $organization->status === \App\Enums\OrganizationStatus::Published ? 'Published' : 'Draft' }}
                        <svg class="w-3 h-3 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </button>
                </form>
                @if ($blockedFromPublishing)
                    <a href="{{ route('organizations.plan.edit', $organization) }}" class="text-xs font-semibold text-amber-600 hover:underline whitespace-nowrap">
                        ⚠ Batasi paket dilanggar
                    </a>
                @endif
            </div>
        </div>

        {{-- Primary action: full-width, unmissable, distinct from the settings row below. --}}
        <a href="{{ route('organizations.builder.edit', $organization) }}"
            class="flex items-center justify-center gap-2 w-full px-4 py-3.5 sm:py-3 rounded-xl bg-primary text-white text-sm font-bold hover:opacity-90 active:opacity-80 transition-opacity shadow-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M9.53 16.122a3 3 0 0 0-5.78 1.128 2.25 2.25 0 0 1-2.4 2.245 4.5 4.5 0 0 0 8.4-2.245c0-.399-.078-.78-.22-1.128Zm0 0a15.998 15.998 0 0 0 3.388-1.62m-5.043-.025a15.994 15.994 0 0 1 1.622-3.395m3.42 3.42a15.995 15.995 0 0 0 4.764-4.648l3.876-5.814a1.151 1.151 0 0 0-1.597-1.597L14.146 6.32a15.996 15.996 0 0 0-4.649 4.763m3.42 3.42a6.776 6.776 0 0 0-3.42-3.42" />
            </svg>
            Buka Builder
        </a>

        {{-- Settings row: consistently sized, icon + label pills so each is scannable at a glance. --}}
        <div class="flex gap-2 mt-3 overflow-x-auto -mx-5 px-5 sm:mx-0 sm:px-0 sm:flex-wrap">
            <a href="{{ route('organizations.brand.edit', $organization) }}"
                class="inline-flex items-center gap-1.5 shrink-0 px-3.5 py-2 rounded-full ring-1 ring-inset ring-gray-200 text-gray-700 text-sm font-semibold hover:bg-gray-50 hover:ring-gray-300 transition-colors">
                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M4.098 19.902a3.75 3.75 0 0 0 5.304 0l6.401-6.402M4.098 19.902a3.75 3.75 0 0 1 0-5.304l6.401-6.402m-6.401 6.402L14.802 4.5a3.75 3.75 0 1 1 5.304 5.304l-9.594 9.594" />
                </svg>
                Brand Settings
            </a>
            <a href="{{ route('organizations.edit.edit', $organization) }}"
                class="inline-flex items-center gap-1.5 shrink-0 px-3.5 py-2 rounded-full ring-1 ring-inset ring-gray-200 text-gray-700 text-sm font-semibold hover:bg-gray-50 hover:ring-gray-300 transition-colors">
                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" />
                </svg>
                Edit Organisasi
            </a>
            <a href="{{ route('organizations.template.edit', $organization) }}"
                class="inline-flex items-center gap-1.5 shrink-0 px-3.5 py-2 rounded-full ring-1 ring-inset ring-gray-200 text-gray-700 text-sm font-semibold hover:bg-gray-50 hover:ring-gray-300 transition-colors">
                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M2.25 15.75l5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3 8.25v8.25a1.5 1.5 0 0 0 1.5 1.5h15a1.5 1.5 0 0 0 1.5-1.5V8.25m-18 0V6a1.5 1.5 0 0 1 1.5-1.5h15a1.5 1.5 0 0 1 1.5 1.5v2.25m-18 0h18M9.75 6.75h.008v.008H9.75V6.75Z" />
                </svg>
                Ganti Template
            </a>
            @can('manageBilling', $organization)
                <a href="{{ route('organizations.plan.edit', $organization) }}"
                    class="inline-flex items-center gap-1.5 shrink-0 px-3.5 py-2 rounded-full ring-1 ring-inset ring-gray-200 text-gray-700 text-sm font-semibold hover:bg-gray-50 hover:ring-gray-300 transition-colors">
                    <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                    </svg>
                    Langganan — {{ $organization->plan?->name ?? 'Belum Diatur' }}
                </a>
            @endcan
            @if ($organization->status === \App\Enums\OrganizationStatus::Published && $tenantDomain)
                <a href="https://{{ $organization->slug }}.{{ $tenantDomain }}" target="_blank"
                    class="inline-flex items-center gap-1.5 shrink-0 px-3.5 py-2 rounded-full ring-1 ring-inset ring-secondary/30 text-secondary text-sm font-semibold hover:bg-secondary/5 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                    </svg>
                    Lihat Situs
                </a>
            @endif
        </div>
    </div>

    @php
        $checklist = $organization->onboardingChecklist();
        $checklistDone = count(array_filter($checklist));
    @endphp
    @unless ($checklist['brand'] && $checklist['contact'] && $checklist['content'] && $checklist['published'])
        <div class="bg-white rounded-2xl shadow-soft p-6 mb-8">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-bold text-gray-800">Langkah Awal</h2>
                <span class="text-xs font-semibold text-gray-400">{{ $checklistDone }}/{{ count($checklist) }} selesai</span>
            </div>
            <div class="space-y-2">
                <a href="{{ route('organizations.brand.edit', $organization) }}"
                    class="flex items-center gap-3 p-3 rounded-xl {{ $checklist['brand'] ? 'bg-secondary/5' : 'bg-gray-50 hover:bg-gray-100' }} transition">
                    <span
                        class="w-6 h-6 rounded-full flex items-center justify-center shrink-0 text-xs font-bold {{ $checklist['brand'] ? 'bg-secondary text-white' : 'bg-white border border-gray-300 text-gray-400' }}">
                        {{ $checklist['brand'] ? '✓' : '1' }}
                    </span>
                    <div class="min-w-0">
                        <p
                            class="text-sm font-semibold {{ $checklist['brand'] ? 'text-gray-500 line-through' : 'text-gray-800' }}">
                            Atur Brand</p>
                        <p class="text-xs text-gray-400">Unggah logo dan sesuaikan warna organisasi</p>
                    </div>
                </a>
                <a href="{{ route('organizations.brand.edit', $organization) }}"
                    class="flex items-center gap-3 p-3 rounded-xl {{ $checklist['contact'] ? 'bg-secondary/5' : 'bg-gray-50 hover:bg-gray-100' }} transition">
                    <span
                        class="w-6 h-6 rounded-full flex items-center justify-center shrink-0 text-xs font-bold {{ $checklist['contact'] ? 'bg-secondary text-white' : 'bg-white border border-gray-300 text-gray-400' }}">
                        {{ $checklist['contact'] ? '✓' : '2' }}
                    </span>
                    <div class="min-w-0">
                        <p
                            class="text-sm font-semibold {{ $checklist['contact'] ? 'text-gray-500 line-through' : 'text-gray-800' }}">
                            Isi Kontak</p>
                        <p class="text-xs text-gray-400">Telepon, WhatsApp, atau email untuk dihubungi pengunjung</p>
                    </div>
                </a>
                <a href="{{ route('organizations.builder.edit', $organization) }}"
                    class="flex items-center gap-3 p-3 rounded-xl {{ $checklist['content'] ? 'bg-secondary/5' : 'bg-gray-50 hover:bg-gray-100' }} transition">
                    <span
                        class="w-6 h-6 rounded-full flex items-center justify-center shrink-0 text-xs font-bold {{ $checklist['content'] ? 'bg-secondary text-white' : 'bg-white border border-gray-300 text-gray-400' }}">
                        {{ $checklist['content'] ? '✓' : '3' }}
                    </span>
                    <div class="min-w-0">
                        <p
                            class="text-sm font-semibold {{ $checklist['content'] ? 'text-gray-500 line-through' : 'text-gray-800' }}">
                            Susun Halaman</p>
                        <p class="text-xs text-gray-400">Kelola konten dan section situs Anda di builder</p>
                    </div>
                </a>
                <div
                    class="flex items-center gap-3 p-3 rounded-xl {{ $checklist['published'] ? 'bg-secondary/5' : 'bg-gray-50' }}">
                    <span
                        class="w-6 h-6 rounded-full flex items-center justify-center shrink-0 text-xs font-bold {{ $checklist['published'] ? 'bg-secondary text-white' : 'bg-white border border-gray-300 text-gray-400' }}">
                        {{ $checklist['published'] ? '✓' : '4' }}
                    </span>
                    <div class="min-w-0 flex-1">
                        <p
                            class="text-sm font-semibold {{ $checklist['published'] ? 'text-gray-500 line-through' : 'text-gray-800' }}">
                            Publish Situs</p>
                        @if ($checklist['published'] && $tenantDomain)
                            <p class="text-xs text-gray-400 truncate">{{ $organization->slug }}.{{ $tenantDomain }}</p>
                        @else
                            <p class="text-xs text-gray-400">Tampilkan situs Anda ke publik</p>
                        @endif
                    </div>
                    <form action="{{ route('organizations.publish', $organization) }}" method="POST" class="shrink-0"
                        x-data @submit.prevent="if (await confirmAction('{{ $checklist['published'] ? 'Jadikan draft? Situs tidak lagi bisa diakses publik.' : 'Publikasikan situs ini? Situs akan bisa diakses publik.' }}', { danger: false, confirmLabel: 'Ya, Lanjutkan' })) $el.submit()">
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

    @php
        // Only show a content quick-link if the organization's builder pages actually contain
        // that section — an org whose template never included e.g. galeri shouldn't see a
        // content shortcut for something it has no section to display (see also the sidebar
        // filter in layouts/organization.blade.php, which applies the same rule).
        $activeSectionKeys = $organization->pages->flatMap->sections->pluck('key')->unique();

        $contentLinks = collect([
            [
                'route' => 'organizations.posts.index',
                'section' => 'daftar-berita',
                'label' => 'Berita',
                'count' => $organization->posts()->count(),
                'countLabel' => 'berita',
                'paths' => [
                    'M19 20H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h9l5 5v9a2 2 0 0 1-2 2Z',
                    'M9 12h6M9 16h6M9 8h2',
                ],
            ],
            [
                'route' => 'organizations.agendas.index',
                'section' => 'agenda',
                'label' => 'Agenda',
                'count' => $organization->agendas()->count(),
                'countLabel' => 'agenda',
                'paths' => ['M8 7V3m8 4V3M4 11h16M5 5h14a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z'],
            ],
            [
                'route' => 'organizations.announcements.index',
                'section' => 'pengumuman',
                'label' => 'Pengumuman',
                'count' => $organization->announcements()->count(),
                'countLabel' => 'pengumuman',
                'paths' => ['M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2c0 .5-.2 1-.6 1.4L4 17h5m6 0v1a3 3 0 1 1-6 0v-1m6 0H9'],
            ],
            [
                'route' => 'organizations.gallery.index',
                'section' => 'galeri',
                'label' => 'Galeri',
                'count' => $organization->photos()->count(),
                'countLabel' => 'foto',
                'paths' => ['M4 16.5V6a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v10.5M4 16.5 8.5 12a1.5 1.5 0 0 1 2.1 0l1.4 1.4a1.5 1.5 0 0 0 2.1 0L17.5 10 20 12.5M4 16.5V18a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-1.5'],
                'circle' => ['cx' => '8.5', 'cy' => '8.5', 'r' => '1.25'],
            ],
        ])->filter(fn ($item) => $activeSectionKeys->contains($item['section']));
    @endphp

    @if ($contentLinks->isNotEmpty())
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-8">
            @foreach ($contentLinks as $item)
                <a href="{{ route($item['route'], $organization) }}"
                    class="bg-white rounded-2xl shadow-soft p-4 sm:p-5 hover:-translate-y-0.5 hover:shadow-lg transition-all">
                    <div class="w-9 h-9 rounded-full bg-primary/10 flex items-center justify-center mb-3">
                        <svg class="w-4.5 h-4.5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2">
                            @foreach ($item['paths'] as $d)
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $d }}" />
                            @endforeach
                            @isset($item['circle'])
                                <circle cx="{{ $item['circle']['cx'] }}" cy="{{ $item['circle']['cy'] }}"
                                    r="{{ $item['circle']['r'] }}" />
                            @endisset
                        </svg>
                    </div>
                    <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">Konten</p>
                    <p class="font-bold text-gray-800 text-sm sm:text-base">{{ $item['label'] }}</p>
                    <p class="text-xs sm:text-sm text-gray-500 mt-0.5">{{ $item['count'] }} {{ $item['countLabel'] }}</p>
                </a>
            @endforeach
        </div>
    @endif

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
                            <form action="{{ route('organizations.members.update', [$organization, $member]) }}"
                                method="POST">
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
                            <form action="{{ route('organizations.members.destroy', [$organization, $member]) }}"
                                method="POST"
                                x-data @submit.prevent="if (await confirmAction(`Hapus ${@json($member->name)} dari organisasi ini?`)) $el.submit()">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="text-red-500 text-xs font-medium hover:underline">Hapus</button>
                            </form>
                        </div>
                    @else
                        <span
                            class="self-start sm:self-auto px-2 py-1 rounded-full bg-primary/10 text-primary text-xs font-semibold shrink-0">
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
                    <label for="email" class="block text-xs font-semibold text-gray-600 mb-1">Email pengguna
                        terdaftar</label>
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
                Tindakan ini akan menghapus organisasi beserta seluruh data anggotanya secara permanen dan tidak dapat
                dibatalkan.
            </p>
            <form action="{{ route('organizations.destroy', $organization) }}" method="POST"
                x-data @submit.prevent="if (await confirmAction(`Hapus organisasi &quot;${@json($organization->name)}&quot;? Tindakan ini tidak dapat dibatalkan.`)) $el.submit()">
                @csrf
                @method('DELETE')
                <button type="submit"
                    class="px-4 py-2 rounded-full bg-red-500 text-white text-sm font-semibold hover:bg-red-600">
                    Hapus Organisasi
                </button>
            </form>
        </div>
    @endif
@endsection
