<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Builder — {{ $organization->name }} — Website-mu</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#2C368B',
                        secondary: '#079C4E',
                        accent: '#F59E0B',
                        softBg: '#F1F3F9',
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    boxShadow: {
                        soft: '0 10px 40px -10px rgba(0,0,0,0.06)',
                        panel: '0 1px 2px rgba(15,23,42,0.04), 0 8px 24px -8px rgba(15,23,42,0.08)',
                        glow: '0 0 0 4px rgba(44,54,139,0.08)',
                    },
                    animation: {
                        'fade-in': 'fadeIn .35s ease both',
                        'pop-in': 'popIn .25s cubic-bezier(.34,1.56,.64,1) both',
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': {
                                opacity: 0,
                                transform: 'translateY(4px)'
                            },
                            '100%': {
                                opacity: 1,
                                transform: 'translateY(0)'
                            }
                        },
                        popIn: {
                            '0%': {
                                opacity: 0,
                                transform: 'scale(.97) translateY(6px)'
                            },
                            '100%': {
                                opacity: 1,
                                transform: 'scale(1) translateY(0)'
                            }
                        },
                    },
                },
            },
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        [x-cloak] {
            display: none !important;
        }

        #section-list::-webkit-scrollbar,
        main::-webkit-scrollbar,
        aside::-webkit-scrollbar {
            width: 6px;
        }

        #section-list::-webkit-scrollbar-thumb,
        main::-webkit-scrollbar-thumb,
        aside::-webkit-scrollbar-thumb {
            background: rgba(15, 23, 42, 0.12);
            border-radius: 999px;
        }

        .sortable-ghost {
            opacity: .4;
        }

        .sortable-drag {
            box-shadow: 0 12px 24px -8px rgba(15, 23, 42, .25) !important;
        }

        .section-icon-wrap svg {
            width: 16px;
            height: 16px;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js" defer></script>
    <script>
        // Kept out of the x-data HTML attribute below: a Blade JSON directive's
        // quoted output would otherwise terminate the attribute early and leak
        // the rest of the Alpine expression as visible page text.
        window.mediaIndexUrl = @json(route('organizations.media.index', $organization));
        window.mediaStoreUrl = @json(route('organizations.media.store', $organization));
        window.csrfToken = @json(csrf_token());
    </script>
</head>
{{-- Mobile-first: below lg, only one of sections/canvas/properties shows at a time via
     activePanel + bottom tab bar. From lg upward, all three sit side by side permanently
     and the tab bar is hidden — see lg: overrides throughout. --}}

<body class="bg-softBg text-gray-800 h-screen overflow-hidden flex flex-col"
    x-data="{
        editingSectionId: null,
        activePanel: 'canvas',
        init() {
            // Returning from the CMS (e.g. Kelola Berita/Agenda/Pengumuman): jump
            // straight back to the section being managed instead of leaving the
            // builder at its default scroll position.
            const params = new URLSearchParams(window.location.search);
            const sectionId = params.get('section');
            if (sectionId) {
                this.selectSection(Number(sectionId));
                params.delete('section');
                params.delete('from');
                const query = params.toString();
                history.replaceState(null, '', window.location.pathname + (query ? '?' + query : ''));
            }
        },
        selectSection(id) {
            this.editingSectionId = id;
            this.activePanel = 'properties';
            // Desktop only: canvas sits alongside the panels, so scroll it to the
            // section being edited. On mobile the canvas is hidden while editing,
            // so there's nothing useful to scroll.
            if (window.innerWidth >= 1024) {
                this.$nextTick(() => scrollCanvasToSection(id));
            }
        },
        mediaPicker: {
            open: false,
            loading: false,
            items: [],
            onPick: null,
            fetched: false,
            async show(onPick) {
                this.onPick = onPick;
                this.open = true;
                if (this.fetched) return;
                this.loading = true;
                const res = await fetch(window.mediaIndexUrl, {
                    headers: { 'Accept': 'application/json' },
                });
                this.items = await res.json();
                this.fetched = true;
                this.loading = false;
            },
            pick(item) {
                if (this.onPick) this.onPick(item);
                this.open = false;
            },
            async upload(files) {
                if (!files || !files.length) return;
                const formData = new FormData();
                [...files].forEach((file) => formData.append('files[]', file));
                this.loading = true;
                const res = await fetch(window.mediaStoreUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': window.csrfToken,
                    },
                    body: formData,
                });
                this.loading = false;
                if (!res.ok) {
                    const body = await res.json().catch(() => null);
                    const message = body?.errors
                        ? Object.values(body.errors).flat().join(' ')
                        : (body?.message || `Unggah gagal (${res.status}).`);
                    alert(message);
                    return;
                }
                const uploaded = await res.json();
                this.items = [...uploaded, ...this.items];
            },
        },
    }">

    {{-- Top bar --}}
    <header
        class="relative bg-gradient-to-r from-gray-900 via-[#1c2360] to-primary text-white text-sm shrink-0 shadow-lg z-10">
        <div class="px-4 lg:px-6 py-3 flex flex-wrap items-center justify-between gap-2">
            <div class="flex items-center gap-3 min-w-0">
                {{-- Mobile: while the properties panel is open, back means "close this
                     section and return to the preview", not "leave the builder" —
                     otherwise it's easy to accidentally exit while editing. lg+: all
                     panels are visible at once, so back always leaves the builder. --}}
                <a href="{{ route('organizations.show', $organization) }}"
                    @click="if (activePanel === 'properties') { $event.preventDefault(); editingSectionId = null; activePanel = 'canvas'; }"
                    class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-300 hover:text-white hover:bg-white/10 transition shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">
                        <path fill-rule="evenodd"
                            d="M17 10a.75.75 0 0 1-.75.75H5.612l4.158 3.96a.75.75 0 1 1-1.04 1.08l-5.5-5.25a.75.75 0 0 1 0-1.08l5.5-5.25a.75.75 0 1 1 1.04 1.08L5.612 9.25H16.25A.75.75 0 0 1 17 10Z"
                            clip-rule="evenodd" />
                    </svg>
                </a>
                <div
                    class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center shrink-0 ring-1 ring-white/15">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                        class="w-4 h-4 text-accent">
                        <path
                            d="M2 4.25A2.25 2.25 0 0 1 4.25 2h11.5A2.25 2.25 0 0 1 18 4.25v8.5A2.25 2.25 0 0 1 15.75 15h-3.105a3.501 3.501 0 0 0 1.1 1.677A.75.75 0 0 1 13.26 18H6.74a.75.75 0 0 1-.484-1.323A3.501 3.501 0 0 0 7.355 15H4.25A2.25 2.25 0 0 1 2 12.75v-8.5Zm1.5 0a.75.75 0 0 1 .75-.75h11.5a.75.75 0 0 1 .75.75v7.5a.75.75 0 0 1-.75.75H4.25a.75.75 0 0 1-.75-.75v-7.5Z" />
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="font-semibold truncate leading-tight">{{ $organization->name }}</p>
                    <p class="text-[11px] text-gray-400 hidden sm:block leading-tight">Page builder</p>
                </div>
            </div>

            {{-- Tahap awal: satu organisasi hanya punya satu halaman (Beranda), jadi tidak
                 ada page switcher di sini — lihat prd.md §24.4. Publish dikontrol di satu
                 tempat saja: dashboard organisasi (organizations.publish, lihat
                 Organization::publish() dan OrganizationSiteController) — builder dulu
                 punya toggle publish terpisah per halaman (OrganizationPage::published_at)
                 yang tidak pernah dibaca oleh situs publik, sengaja dihapus supaya tidak
                 ada dua sumber kebenaran yang bisa tidak sinkron. --}}
            @if ($organization->status === \App\Enums\OrganizationStatus::Published)
                <span class="hidden sm:inline-flex items-center gap-1.5 text-xs text-emerald-300 pr-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    Live
                </span>
            @endif
        </div>
    </header>

    @if (session('status'))
        <div
            class="bg-secondary/10 border-b border-secondary/20 text-secondary px-4 lg:px-6 py-2 text-sm shrink-0 flex items-center gap-2 animate-fade-in">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4 shrink-0">
                <path fill-rule="evenodd"
                    d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z"
                    clip-rule="evenodd" />
            </svg>
            {{ session('status') }}
        </div>
    @endif

    @if (!$currentPage)
        {{-- Seharusnya tidak pernah tercapai — OrganizationBuilderController memanggil
             ensureHomePageExists() sebelum render, yang selalu membuat halaman Beranda
             kalau organisasi belum punya satupun. Dipertahankan sebagai jaring pengaman. --}}
        <div class="flex-1 flex items-center justify-center p-4">
            <div class="text-center max-w-md">
                <h1 class="text-xl font-bold text-gray-800 mb-2">Halaman belum siap</h1>
                <p class="text-sm text-gray-500">Muat ulang halaman ini, atau hubungi dukungan jika masalah berlanjut.
                </p>
            </div>
        </div>
    @else
        <div class="flex-1 flex overflow-hidden pb-14 lg:pb-0">
            {{-- Sidebar: section list, add/remove/duplicate/reorder.
                 Mobile: full-width panel shown only when activePanel === 'sections'.
                 lg+: fixed-width column, always visible. --}}
            <aside :class="activePanel === 'sections' ? 'flex' : 'hidden'"
                class="lg:flex w-full lg:w-80 bg-white lg:border-r border-gray-200/80 flex-col shrink-0">
                <div class="p-4 border-b border-gray-100">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400 mb-2.5">Tambah Section</p>
                    <form action="{{ route('organizations.sections.store', [$organization, $currentPage]) }}"
                        method="POST" class="flex gap-2">
                        @csrf
                        <div class="relative flex-1">
                            <select name="key"
                                class="appearance-none w-full rounded-xl border border-gray-200 pl-3 pr-8 py-2.5 text-sm font-medium text-gray-700 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary/25 focus:border-primary/40 focus:bg-white transition">
                                @foreach ($sectionRegistry as $key => $meta)
                                    <option value="{{ $key }}">{{ $meta['label'] }}</option>
                                @endforeach
                            </select>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                                class="w-4 h-4 text-gray-400 absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none">
                                <path fill-rule="evenodd"
                                    d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <button type="submit"
                            class="w-10 h-10 shrink-0 rounded-xl bg-primary text-white flex items-center justify-center font-semibold shadow-sm hover:shadow-md hover:bg-primary/90 active:scale-95 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                                class="w-4 h-4">
                                <path
                                    d="M10.75 4.75a.75.75 0 0 0-1.5 0v4.5h-4.5a.75.75 0 0 0 0 1.5h4.5v4.5a.75.75 0 0 0 1.5 0v-4.5h4.5a.75.75 0 0 0 0-1.5h-4.5v-4.5Z" />
                            </svg>
                        </button>
                    </form>
                </div>

                <div class="px-4 pt-3.5 pb-1.5 flex items-center justify-between">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">
                        Susunan Halaman
                    </p>
                    <span class="text-[11px] font-semibold text-gray-400 bg-gray-100 rounded-full px-2 py-0.5">
                        {{ $currentPage->sections->count() }}
                    </span>
                </div>

                <ul id="section-list" class="flex-1 overflow-y-auto px-2.5 pb-3 space-y-1">
                    @foreach ($currentPage->sections as $section)
                        <li data-section-id="{{ $section->id }}"
                            data-is-visible="{{ $section->is_visible ? '1' : '0' }}"
                            @click="selectSection({{ $section->id }})"
                            :class="editingSectionId === {{ $section->id }} ? 'bg-primary/5 ring-1 ring-primary/20' :
                                'hover:bg-gray-50'"
                            class="group rounded-xl px-2.5 py-2.5 flex items-center gap-2 cursor-pointer transition {{ !$section->is_visible ? 'opacity-45' : '' }}">
                            <span class="cursor-move text-gray-300 hover:text-gray-500 shrink-0 px-0.5 touch-none">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                                    class="w-4 h-4">
                                    <path
                                        d="M7 4.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Zm0 5.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Zm0 5.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Zm6-11a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Zm0 5.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Zm0 5.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z" />
                                </svg>
                            </span>

                            <span class="section-icon-wrap shrink-0 w-7 h-7 rounded-lg flex items-center justify-center"
                                :class="editingSectionId === {{ $section->id }} ? 'bg-primary text-white' :
                                    'bg-gray-100 text-gray-500 group-hover:bg-gray-200'">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M3 4.25A2.25 2.25 0 0 1 5.25 2h9.5A2.25 2.25 0 0 1 17 4.25v11.5A2.25 2.25 0 0 1 14.75 18h-9.5A2.25 2.25 0 0 1 3 15.75V4.25ZM5.25 3.5a.75.75 0 0 0-.75.75v11.5c0 .414.336.75.75.75h9.5a.75.75 0 0 0 .75-.75V4.25a.75.75 0 0 0-.75-.75h-9.5ZM6 7a.75.75 0 0 1 .75-.75h6.5a.75.75 0 0 1 0 1.5h-6.5A.75.75 0 0 1 6 7Zm0 3a.75.75 0 0 1 .75-.75h6.5a.75.75 0 0 1 0 1.5h-6.5A.75.75 0 0 1 6 10Zm0 3a.75.75 0 0 1 .75-.75h3.5a.75.75 0 0 1 0 1.5h-3.5A.75.75 0 0 1 6 13Z"
                                        clip-rule="evenodd" />
                                </svg>
                            </span>

                            <button type="button" class="flex-1 min-w-0 text-left text-sm font-semibold truncate"
                                :class="editingSectionId === {{ $section->id }} ? 'text-primary' : 'text-gray-700'">
                                {{ $sectionRegistry[$section->key]['label'] ?? $section->key }}
                            </button>

                            <svg data-hidden-icon xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                                class="w-3.5 h-3.5 text-gray-400 shrink-0 {{ $section->is_visible ? 'hidden' : '' }}">
                                <path fill-rule="evenodd"
                                    d="M3.28 2.22a.75.75 0 0 0-1.06 1.06l14.5 14.5a.75.75 0 1 0 1.06-1.06l-1.745-1.745a10.029 10.029 0 0 0 3.3-4.38 1.651 1.651 0 0 0 0-1.185A10.004 10.004 0 0 0 9.999 3a9.956 9.956 0 0 0-4.744 1.194L3.28 2.22ZM7.752 6.69l1.092 1.092a2.5 2.5 0 0 1 3.374 3.373l1.091 1.092a4 4 0 0 0-5.557-5.557Z"
                                    clip-rule="evenodd" />
                                <path
                                    d="m10.748 13.93 2.523 2.523a9.987 9.987 0 0 1-3.27.547c-4.258 0-7.894-2.66-9.337-6.41a1.651 1.651 0 0 1 0-1.186A10.007 10.007 0 0 1 2.839 6.02L6.07 9.252a4 4 0 0 0 4.678 4.678Z" />
                            </svg>

                            <div class="flex items-center shrink-0 opacity-0 group-hover:opacity-100 transition">
                                <form
                                    action="{{ route('organizations.sections.duplicate', [$organization, $section]) }}"
                                    method="POST" @click.stop>
                                    @csrf
                                    <button type="submit" title="Duplikasi"
                                        class="w-6 h-6 flex items-center justify-center rounded-md text-gray-400 hover:text-primary hover:bg-primary/10 transition">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                            fill="currentColor" class="w-3.5 h-3.5">
                                            <path
                                                d="M7 3.5A1.5 1.5 0 0 1 8.5 2h3.879a1.5 1.5 0 0 1 1.06.44l3.122 3.12A1.5 1.5 0 0 1 17 6.622V12.5a1.5 1.5 0 0 1-1.5 1.5h-1v-3.379a3 3 0 0 0-.879-2.121L10.5 5.379A3 3 0 0 0 8.379 4.5H7v-1Z" />
                                            <path
                                                d="M4.5 6A1.5 1.5 0 0 0 3 7.5v9A1.5 1.5 0 0 0 4.5 18h8a1.5 1.5 0 0 0 1.5-1.5V10.62a1.5 1.5 0 0 0-.44-1.06L10.44 6.44A1.5 1.5 0 0 0 9.38 6H4.5Z" />
                                        </svg>
                                    </button>
                                </form>
                                <form
                                    action="{{ route('organizations.sections.destroy', [$organization, $section]) }}"
                                    method="POST" onsubmit="return confirm('Hapus section ini?');" @click.stop>
                                    @csrf @method('DELETE')
                                    <button type="submit" title="Hapus"
                                        class="w-6 h-6 flex items-center justify-center rounded-md text-gray-400 hover:text-red-500 hover:bg-red-50 transition">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                            fill="currentColor" class="w-3.5 h-3.5">
                                            <path fill-rule="evenodd"
                                                d="M8.75 1A2.75 2.75 0 0 0 6 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 1 0 .23 1.482l.149-.022.841 10.518A2.75 2.75 0 0 0 7.596 19h4.807a2.75 2.75 0 0 0 2.742-2.53l.841-10.52.149.023a.75.75 0 0 0 .23-1.482A41.03 41.03 0 0 0 14 4.193V3.75A2.75 2.75 0 0 0 11.25 1h-2.5ZM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4ZM8.58 7.72a.75.75 0 0 0-1.5.06l.3 7.5a.75.75 0 1 0 1.5-.06l-.3-7.5Zm4.34.06a.75.75 0 1 0-1.5-.06l-.3 7.5a.75.75 0 1 0 1.5.06l.3-7.5Z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </li>
                    @endforeach

                    @if ($currentPage->sections->isEmpty())
                        <li class="px-3 py-10 text-center">
                            <p class="text-sm text-gray-400">Belum ada section.<br>Tambahkan dari dropdown di atas.</p>
                        </li>
                    @endif
                </ul>
            </aside>

            {{-- Canvas: rendered in an isolated <iframe> so it can use the organization's own
                 brand colors (Organization::primaryColor()/secondaryColor()) via a separate
                 Tailwind config, without recoloring the builder chrome around it — see
                 organizations/builder/canvas.blade.php.
                 Mobile: full-width panel shown only when activePanel === 'canvas'.
                 lg+: flexible middle column, always visible. --}}
            <main :class="activePanel === 'canvas' ? 'flex' : 'hidden'"
                class="lg:flex flex-col w-full lg:flex-1 overflow-hidden bg-[radial-gradient(circle_at_top,_#e9ecf7_0%,_#eef0f7_45%,_#f1f3f9_100%)]">
                <div class="max-w-4xl w-full mx-auto pt-0 lg:pt-8 px-0 lg:px-4 flex-1 min-h-0 flex flex-col">
                    <div class="hidden lg:flex items-center gap-1.5 mb-3 px-1 shrink-0">
                        <span class="w-2.5 h-2.5 rounded-full bg-red-300"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-300"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-300"></span>
                        <span class="ml-2 text-[11px] text-gray-400 font-medium">Pratinjau Langsung</span>
                    </div>
                    <div class="bg-white shadow-panel lg:rounded-2xl overflow-hidden flex-1 min-h-0 lg:mb-8 ring-1 ring-black/5">
                        <iframe id="canvas-frame" title="Pratinjau {{ $organization->name }}"
                            src="{{ route('organizations.builder.canvas', [$organization, $currentPage]) }}"
                            class="w-full h-full border-0"></iframe>
                    </div>
                </div>
            </main>

            {{-- Properties panel.
                 Mobile: full-width panel shown only when activePanel === 'properties'.
                 lg+: fixed-width column, always visible. --}}
            <aside :class="activePanel === 'properties' ? 'block' : 'hidden'"
                class="lg:block w-full lg:w-96 bg-white lg:border-l border-gray-200/80 overflow-y-auto shrink-0">
                @foreach ($currentPage->sections as $section)
                    <div x-show="editingSectionId === {{ $section->id }}" x-cloak x-transition.opacity.duration.150ms
                        class="animate-fade-in">
                        <div
                            class="sticky top-0 bg-white/90 backdrop-blur px-5 pt-5 pb-3 border-b border-gray-100 flex items-start justify-between gap-3 z-10">
                            <div class="min-w-0">
                                <p class="text-[11px] font-bold uppercase tracking-wider text-primary/70 mb-1">Edit
                                    Section</p>
                                <h2 class="font-bold text-gray-800 leading-tight truncate">
                                    {{ $sectionRegistry[$section->key]['label'] ?? $section->key }}</h2>
                            </div>
                            <button type="button" @click="editingSectionId = null"
                                class="w-7 h-7 shrink-0 rounded-lg flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                                    class="w-4 h-4">
                                    <path
                                        d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z" />
                                </svg>
                            </button>
                        </div>

                        <form action="{{ route('organizations.sections.update', [$organization, $section]) }}"
                            method="POST" class="p-5 space-y-4"
                            x-data="{ saving: false, saved: false }"
                            @submit.prevent="saveSection($event.target, $data)">
                            @csrf
                            @method('PATCH')

                            @foreach ($sectionRegistry[$section->key]['fields'] ?? [] as $field)
                                <div>
                                    <label
                                        class="block text-xs font-semibold text-gray-600 mb-1.5">{{ ucfirst(str_replace('_', ' ', $field)) }}</label>
                                    @if (in_array($field, ['body', 'sambutan', 'subheadline'], true))
                                        <textarea name="content[{{ $field }}]" rows="3"
                                            class="w-full rounded-xl border border-gray-200 px-3.5 py-2.5 text-sm bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary/25 focus:border-primary/40 focus:bg-white transition">{{ is_scalar($section->content[$field] ?? null) ? $section->content[$field] : '' }}</textarea>
                                    @elseif (in_array($field, ['image', 'photo'], true))
                                        @php
                                            $currentUrl = is_scalar($section->content[$field] ?? null) ? $section->content[$field] : '';
                                        @endphp
                                        <div x-data="{ url: @js($currentUrl) }" class="space-y-2">
                                            <input type="hidden" name="content[{{ $field }}]" x-model="url">
                                            <div class="rounded-xl border border-gray-200 bg-gray-50 aspect-video overflow-hidden flex items-center justify-center"
                                                x-show="url" x-cloak>
                                                <img :src="url" alt="" class="w-full h-full object-cover">
                                            </div>
                                            <div class="flex gap-2">
                                                <button type="button"
                                                    @click="mediaPicker.show((item) => url = item.url)"
                                                    class="flex-1 px-3.5 py-2.5 rounded-xl border border-gray-200 bg-white text-sm font-semibold text-gray-600 hover:border-primary/40 hover:text-primary transition">
                                                    <span x-text="url ? 'Ganti gambar' : 'Pilih gambar'"></span>
                                                </button>
                                                <button type="button" x-show="url" x-cloak @click="url = ''"
                                                    class="px-3.5 py-2.5 rounded-xl border border-gray-200 bg-white text-sm font-semibold text-gray-400 hover:text-red-500 hover:border-red-200 transition">
                                                    Hapus
                                                </button>
                                            </div>
                                        </div>
                                    @elseif ($field === 'items' && array_key_exists($section->key, [
                                        'daftar-berita' => true, 'agenda' => true, 'pengumuman' => true,
                                        'struktur-pengurus' => true, 'program-unggulan' => true, 'layanan' => true, 'jaringan-aum-ortom' => true,
                                        'galeri' => true,
                                    ]))
                                        @php
                                            $cmsUrl = match ($section->key) {
                                                'daftar-berita' => route('organizations.posts.index', $organization),
                                                'agenda' => route('organizations.agendas.index', $organization),
                                                'pengumuman' => route('organizations.announcements.index', $organization),
                                                'struktur-pengurus' => route('organizations.officers.index', $organization),
                                                'program-unggulan' => route('organizations.programs.index', $organization).'?type=program',
                                                'layanan' => route('organizations.programs.index', $organization).'?type=layanan',
                                                'jaringan-aum-ortom' => route('organizations.networks.index', $organization),
                                                'galeri' => route('organizations.gallery.index', $organization),
                                            };
                                            $cmsLabel = match ($section->key) {
                                                'daftar-berita' => 'Berita',
                                                'agenda' => 'Agenda',
                                                'pengumuman' => 'Pengumuman',
                                                'struktur-pengurus' => 'Pengurus',
                                                'program-unggulan' => 'Program',
                                                'layanan' => 'Layanan',
                                                'jaringan-aum-ortom' => 'Jaringan AUM/Ortom',
                                                'galeri' => 'Galeri',
                                            };
                                            $cmsSeparator = str_contains($cmsUrl, '?') ? '&' : '?';
                                        @endphp
                                        <a href="{{ $cmsUrl }}{{ $cmsSeparator }}from=builder&amp;section={{ $section->id }}"
                                            class="w-full rounded-xl border border-dashed border-primary/30 bg-primary/5 px-3.5 py-3 text-xs text-primary font-medium flex items-center justify-between gap-2 hover:bg-primary/10 transition">
                                            Section ini otomatis menampilkan {{ $cmsLabel }} terbaru yang diterbitkan
                                            <span class="font-semibold whitespace-nowrap">Kelola {{ $cmsLabel }} &rarr;</span>
                                        </a>
                                    @elseif (in_array($field, ['items', 'stats', 'times'], true))
                                        <div
                                            class="w-full rounded-xl border border-dashed border-gray-200 bg-gray-50 px-3.5 py-3 text-xs text-gray-400 flex items-center gap-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                                fill="currentColor" class="w-4 h-4 shrink-0 text-gray-300">
                                                <path fill-rule="evenodd"
                                                    d="M2.166 4.999A11.954 11.954 0 0 0 10 1.944 11.954 11.954 0 0 0 17.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001Zm11.541 3.708a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            Kelola daftar ini dari CMS (segera hadir)
                                        </div>
                                    @else
                                        <input type="text" name="content[{{ $field }}]"
                                            value="{{ is_scalar($section->content[$field] ?? null) ? $section->content[$field] : ($field === 'org_name' ? $organization->name : '') }}"
                                            class="w-full rounded-xl border border-gray-200 px-3.5 py-2.5 text-sm bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary/25 focus:border-primary/40 focus:bg-white transition">
                                    @endif
                                </div>
                            @endforeach

                            <label
                                class="flex items-center gap-2.5 text-sm text-gray-600 bg-gray-50 rounded-xl px-3.5 py-3 cursor-pointer hover:bg-gray-100 transition">
                                <input type="checkbox" name="is_visible" value="1" @checked($section->is_visible)
                                    class="w-4 h-4 rounded text-primary focus:ring-primary/40 border-gray-300">
                                Tampilkan section ini
                            </label>

                            <button type="submit" :disabled="saving"
                                class="w-full px-4 py-3 rounded-xl bg-primary text-white text-sm font-semibold shadow-sm hover:shadow-lg hover:shadow-primary/25 hover:bg-primary/90 active:scale-[.98] transition disabled:opacity-60">
                                <span x-show="!saving && !saved">Simpan Perubahan</span>
                                <span x-show="saving" x-cloak>Menyimpan…</span>
                                <span x-show="saved" x-cloak>Tersimpan &check;</span>
                            </button>
                        </form>
                    </div>
                @endforeach

                <div x-show="editingSectionId === null"
                    class="h-full flex items-center justify-center p-8 text-center">
                    <div>
                        <div class="w-12 h-12 rounded-2xl bg-gray-100 flex items-center justify-center mx-auto mb-3">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                                class="w-5 h-5 text-gray-400">
                                <path fill-rule="evenodd"
                                    d="M2.24 6.8a.75.75 0 0 0 1.06-.04l1.95-2.1v8.59a.75.75 0 0 0 1.5 0V4.66l1.95 2.1a.75.75 0 1 0 1.1-1.02l-3.25-3.5a.75.75 0 0 0-1.1 0l-3.25 3.5a.75.75 0 0 0 .04 1.06Zm14.52 6.4a.75.75 0 0 0-1.06.04l-1.95 2.1V6.75a.75.75 0 0 0-1.5 0v8.59l-1.95-2.1a.75.75 0 1 0-1.1 1.02l3.25 3.5a.75.75 0 0 0 1.1 0l3.25-3.5a.75.75 0 0 0-.04-1.06Z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <p class="text-sm font-medium text-gray-500">Belum ada section dipilih</p>
                        <p class="text-xs text-gray-400 mt-1">Pilih section di sebelah kiri untuk mengedit isinya.</p>
                    </div>
                </div>
            </aside>
        </div>

        {{-- Mobile tab bar: switches which single panel is visible. Hidden from lg upward,
             where all three panels are shown side by side instead. --}}
        <nav
            class="lg:hidden fixed bottom-3 left-1/2 -translate-x-1/2 bg-white/90 backdrop-blur-md border border-gray-200/80 rounded-full flex items-center gap-0.5 text-[11px] font-semibold z-50 shadow-panel p-1">
            <button type="button" @click="activePanel = 'sections'"
                class="flex items-center gap-1.5 pl-2.5 pr-3 py-1.5 rounded-full transition"
                :class="activePanel === 'sections' ? 'text-primary bg-primary/8' : 'text-gray-400'">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                    class="w-4 h-4 shrink-0">
                    <path fill-rule="evenodd"
                        d="M2 4.25A2.25 2.25 0 0 1 4.25 2h11.5A2.25 2.25 0 0 1 18 4.25v2.5A2.25 2.25 0 0 1 15.75 9H4.25A2.25 2.25 0 0 1 2 6.75v-2.5Zm14.5 8.5A2.25 2.25 0 0 0 14.25 10.5h-8.5A2.25 2.25 0 0 0 3.5 12.75v2.5A2.25 2.25 0 0 0 5.75 17.5h8.5a2.25 2.25 0 0 0 2.25-2.25v-2.5Z"
                        clip-rule="evenodd" />
                </svg>
                Ubah Konten
            </button>
            <button type="button" @click="activePanel = 'canvas'"
                class="flex items-center gap-1.5 pl-2.5 pr-3 py-1.5 rounded-full transition"
                :class="activePanel === 'canvas' ? 'text-primary bg-primary/8' : 'text-gray-400'">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                    class="w-4 h-4 shrink-0">
                    <path d="M10 12.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z" />
                    <path fill-rule="evenodd"
                        d="M.664 10.59a1.651 1.651 0 0 1 0-1.186A10.004 10.004 0 0 1 10 3c4.257 0 7.893 2.66 9.336 6.41.147.381.146.804 0 1.186A10.004 10.004 0 0 1 10 17c-4.257 0-7.893-2.66-9.336-6.41ZM14 10a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z"
                        clip-rule="evenodd" />
                </svg>
                Pratinjau
            </button>
        </nav>

        {{-- Media picker modal: shared across all image/photo/galeri fields, opened via
             mediaPicker.show(onPick) in the properties panel above. --}}
        <div x-show="mediaPicker.open" x-cloak
            class="fixed inset-0 z-[60] bg-gray-900/50 backdrop-blur-sm flex items-end sm:items-center justify-center p-0 sm:p-4"
            @keydown.escape.window="mediaPicker.open = false">
            <div @click.outside="mediaPicker.open = false"
                class="bg-white rounded-t-2xl sm:rounded-2xl w-full sm:max-w-2xl max-h-[85vh] flex flex-col shadow-2xl animate-pop-in">
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 shrink-0">
                    <h3 class="font-bold text-gray-800">Pilih Gambar</h3>
                    <button type="button" @click="mediaPicker.open = false"
                        class="w-7 h-7 flex items-center justify-center rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">
                            <path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z" />
                        </svg>
                    </button>
                </div>

                <div class="p-5 border-b border-gray-100 shrink-0">
                    <label
                        class="flex flex-col items-center justify-center gap-1.5 border-2 border-dashed border-gray-200 rounded-xl py-6 cursor-pointer hover:border-primary/40 hover:bg-primary/5 transition text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-6 h-6 text-gray-400">
                            <path fill-rule="evenodd" d="M10 2a.75.75 0 0 1 .75.75v8.69l2.72-2.72a.75.75 0 1 1 1.06 1.06l-4 4a.75.75 0 0 1-1.06 0l-4-4a.75.75 0 1 1 1.06-1.06l2.72 2.72V2.75A.75.75 0 0 1 10 2ZM3 15.25a.75.75 0 0 1 .75.75v1.5c0 .414.336.75.75.75h11a.75.75 0 0 0 .75-.75v-1.5a.75.75 0 0 1 1.5 0v1.5A2.25 2.25 0 0 1 15.5 19h-11A2.25 2.25 0 0 1 2.25 17.5v-1.5A.75.75 0 0 1 3 15.25Z" clip-rule="evenodd" />
                        </svg>
                        <span class="text-sm font-semibold text-gray-600">Unggah gambar baru</span>
                        <span class="text-xs text-gray-400">PNG, JPG, atau WebP. Maks 5MB.</span>
                        <input type="file" accept="image/*" multiple class="hidden"
                            @change="mediaPicker.upload($event.target.files); $event.target.value = ''">
                    </label>
                </div>

                <div class="flex-1 overflow-y-auto p-5">
                    <div x-show="mediaPicker.loading" class="text-center text-sm text-gray-400 py-8">Memuat…</div>
                    <div x-show="!mediaPicker.loading && mediaPicker.items.length === 0" class="text-center text-sm text-gray-400 py-8">
                        Belum ada gambar. Unggah gambar pertama di atas.
                    </div>
                    <div class="grid grid-cols-3 sm:grid-cols-4 gap-3" x-show="!mediaPicker.loading">
                        <template x-for="item in mediaPicker.items" :key="item.id">
                            <button type="button" @click="mediaPicker.pick(item)"
                                class="aspect-square rounded-xl overflow-hidden bg-gray-100 ring-1 ring-gray-200 hover:ring-2 hover:ring-primary transition">
                                <img :src="item.url" :alt="item.original_name" class="w-full h-full object-cover">
                            </button>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if ($currentPage)
        <script>
            window.reorderSectionsUrl = @json(route('organizations.sections.reorder', [$organization, $currentPage]));

            // The canvas lives in its own <iframe> (see organizations/builder/canvas.blade.php)
            // so it can carry the organization's brand colors independently of the builder
            // chrome. Same-origin, so the parent can reach straight into contentDocument —
            // no postMessage needed.
            function canvasDocument() {
                return document.getElementById('canvas-frame')?.contentDocument ?? null;
            }

            // Swaps the canvas HTML in place (instead of a full iframe reload) so scroll
            // position and Alpine panel state (editingSectionId, activePanel) survive a
            // section save or reorder. Both save and reorder responses share this shape:
            // { canvas: "<rendered html>" }.
            function swapCanvas(canvasHtml) {
                const doc = canvasDocument();
                const win = document.getElementById('canvas-frame')?.contentWindow;
                if (!doc || !win) return;
                const scrollTop = win.scrollY;
                const canvasBody = doc.getElementById('canvas-body');
                if (canvasBody) canvasBody.innerHTML = canvasHtml;
                win.scrollTo(0, scrollTop);
                // Re-observe .reveal elements for the fade-in-on-scroll effect — the
                // IntersectionObserver itself lives outside #canvas-body (see
                // canvas.blade.php) so it survives the innerHTML swap, but it only knows
                // about elements it was told to watch, and this swap just introduced new ones.
                win.observeRevealElements?.();
            }

            // Scrolls the canvas iframe to a given section, waiting for the iframe's own
            // document to finish loading first if it hasn't yet (e.g. on initial page load,
            // when the properties panel opens from a ?section= redirect before the iframe
            // has had a chance to render).
            function scrollCanvasToSection(id) {
                const frame = document.getElementById('canvas-frame');
                if (!frame) return;

                const scroll = () => {
                    frame.contentDocument
                        ?.getElementById('canvas-section-' + id)
                        ?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                };

                if (frame.contentDocument?.readyState === 'complete') {
                    scroll();
                } else {
                    frame.addEventListener('load', scroll, { once: true });
                }
            }

            async function saveSection(form, state) {
                state.saving = true;
                state.saved = false;

                // The form already carries a spoofed _method=PATCH input from @method('PATCH'),
                // which Laravel reads from the body — no extra header needed for that part.
                const res = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': window.csrfToken,
                    },
                    body: new FormData(form),
                });

                state.saving = false;

                if (!res.ok) {
                    alert('Gagal menyimpan perubahan. Coba lagi.');
                    return;
                }

                const data = await res.json();
                swapCanvas(data.canvas);

                // form.action is .../sections/{id} — the last path segment identifies which
                // sidebar row to sync (visibility icon + dimmed state) with the saved section.
                const sectionId = form.action.split('/').pop();
                const sidebarRow = document.querySelector(`#section-list li[data-section-id="${sectionId}"]`);
                if (sidebarRow) {
                    sidebarRow.dataset.isVisible = data.is_visible ? '1' : '0';
                    sidebarRow.classList.toggle('opacity-45', !data.is_visible);
                    sidebarRow.querySelector('[data-hidden-icon]')?.classList.toggle('hidden', data.is_visible);
                }

                state.saved = true;
                setTimeout(() => { state.saved = false; }, 2000);
            }

            new Sortable(document.getElementById('section-list'), {
                handle: '.cursor-move',
                animation: 150,
                ghostClass: 'sortable-ghost',
                dragClass: 'sortable-drag',
                onEnd() {
                    const ids = [...document.querySelectorAll('#section-list [data-section-id]')]
                        .map((el) => el.dataset.sectionId);

                    fetch(window.reorderSectionsUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': window.csrfToken,
                        },
                        body: JSON.stringify({
                            section_ids: ids
                        }),
                    })
                        .then((res) => res.json())
                        .then((data) => swapCanvas(data.canvas));
                },
            });
        </script>
    @endif

</body>

</html>
