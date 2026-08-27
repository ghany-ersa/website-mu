<?php

// Registry of section keys the builder can add to a page. Each key maps by filename
// convention to a partial in resources/views/templates/sections/{key}.blade.php.
// `fields` lists the content[] keys that partial reads, driving the builder's
// properties panel form — see resources/views/templates/sections/*.blade.php.
// `defaults`, when present, seeds content[] on creation (OrganizationSectionController::store())
// with the same fallback text the partial itself would render for an empty field — so a new
// section already looks/reads right without the user having to retype what's effectively
// already there, and the builder's edit form isn't misleadingly blank.
//
// `locked` (bool, default false), when true, marks a section the builder UI must not offer in
// the "Tambah Section" picker and must not let the user delete, duplicate, or drag-reorder — see
// OrganizationPage::footerSection()/ensureFooter() and OrganizationSectionController for the
// enforcement. `header` and `footer` are locked: every page must always start with exactly one
// header and end with exactly one footer. Both have no editable `fields` — they always show the
// organization's own name (see header.blade.php/footer.blade.php) with no override — so neither
// is clickable in the builder sidebar (see edit.blade.php's $hasFields guard).
//
// `cms`, when present, is the single source of truth for a section whose `items` field is backed
// by a separate CMS resource (e.g. agenda items are managed at organizations.agendas.*, not
// inline in the builder) — both edit.blade.php's "Kelola X ->" link and layouts/organization.blade.php's
// sidebar menu read `cms.route`/`cms.label`/`cms.params` from here instead of each hardcoding
// their own section-key -> route/label map.
//
// `variants` + `default_variant`, when present, let a section render as one of several distinct
// layouts (not just brand colors) instead of the single partial at
// resources/views/templates/sections/{key}.blade.php — `variants` maps a variant name to a view
// path under resources/views/templates/sections/{key}/{variant}.blade.php. Which variant renders
// is resolved by App\Services\SectionVariantResolver from (in order) the section's own `variant`
// column, then `default_variant`. A section with no `variants` key here has exactly one look and
// is unaffected by any of this.
// Two keys may point at the same view path (e.g. an alias kept for old data) — the builder's
// property panel dropdown (organizations/builder/edit.blade.php) dedups those by view path so
// aliases never show up as separate, confusingly-duplicate options.
return [

    'sections' => [
        'hero' => [
            'label' => 'Hero',
            'fields' => [
                'badge', 'headline', 'subheadline',
                'cta_label', 'cta_type', 'cta_section', 'cta_url', 'cta_wa_number', 'cta_wa_message',
                'cta_secondary_label', 'cta_secondary_type', 'cta_secondary_section', 'cta_secondary_url', 'cta_secondary_wa_number', 'cta_secondary_wa_message',
                'image',
            ],
            'defaults' => [
                'cta_wa_message' => 'Assalamu\'alaikum, saya ingin bertanya seputar {org_name}.',
                'cta_secondary_wa_message' => 'Assalamu\'alaikum, saya ingin bertanya seputar {org_name}.',
            ],
            'variants' => [
                'standar' => 'templates.sections.hero.standar',
                'modern' => 'templates.sections.hero.modern',
                'headline-berita' => 'templates.sections.hero.headline-berita',
            ],
            'default_variant' => 'standar',
        ],
        'header' => [
            'label' => 'Header',
            'fields' => [],
            'locked' => true,
            'variants' => [
                'standar' => 'templates.sections.header.standar',
            ],
            'default_variant' => 'standar',
        ],
        'footer' => [
            'label' => 'Footer',
            'fields' => [],
            'locked' => true,
            'variants' => [
                'standar' => 'templates.sections.footer.standar',
            ],
            'default_variant' => 'standar',
        ],
        'tentang-organisasi' => [
            'label' => 'Tentang Organisasi',
            'fields' => ['title', 'body', 'image', 'stats'],
            'defaults' => [
                'title' => 'Tentang Organisasi',
                // Same fallback templates/sections/tentang-organisasi.blade.php already shows
                // when `stats` is empty — seeded here too so a newly-added section starts with
                // editable rows in the builder's properties panel instead of an empty stats
                // editor that doesn't match what the canvas is actually rendering.
                'stats' => [
                    ['value' => '10+', 'label' => 'Tahun Berdiri'],
                    ['value' => '100+', 'label' => 'Anggota'],
                    ['value' => '5+', 'label' => 'Program Aktif'],
                ],
            ],
            'variants' => [
                'standar' => 'templates.sections.tentang-organisasi.standar',
                'modern' => 'templates.sections.tentang-organisasi.modern',
            ],
            'default_variant' => 'standar',
        ],
        'sambutan-ketua' => [
            'label' => 'Sambutan Ketua',
            'fields' => ['nama', 'jabatan', 'sambutan', 'photo'],
            'variants' => [
                'standar' => 'templates.sections.sambutan-ketua.standar',
                'modern' => 'templates.sections.sambutan-ketua.modern',
            ],
            'default_variant' => 'standar',
        ],
        'struktur-pengurus' => [
            'label' => 'Struktur Pengurus',
            'fields' => ['title', 'items'],
            'defaults' => ['title' => 'Struktur Pengurus'],
            'cms' => ['route' => 'organizations.officers.index', 'label' => 'Pengurus'],
            'variants' => [
                'standar' => 'templates.sections.struktur-pengurus.standar',
                'modern' => 'templates.sections.struktur-pengurus.modern',
            ],
            'default_variant' => 'standar',
        ],
        'program-unggulan' => [
            'label' => 'Program Unggulan',
            'fields' => ['title', 'items'],
            'defaults' => ['title' => 'Program Unggulan'],
            'cms' => ['route' => 'organizations.programs.index', 'label' => 'Program', 'params' => ['type' => 'program']],
            'variants' => [
                'standar' => 'templates.sections.program-unggulan.standar',
                'modern' => 'templates.sections.program-unggulan.modern',
            ],
            'default_variant' => 'standar',
        ],
        'layanan' => [
            'label' => 'Layanan',
            'fields' => ['title', 'items'],
            'defaults' => ['title' => 'Layanan'],
            'cms' => ['route' => 'organizations.programs.index', 'label' => 'Layanan', 'params' => ['type' => 'layanan']],
            'variants' => [
                'standar' => 'templates.sections.layanan.standar',
            ],
            'default_variant' => 'standar',
        ],
        'jaringan-aum-ortom' => [
            'label' => 'Jaringan AUM/Ortom',
            'fields' => ['title', 'items'],
            'defaults' => ['title' => 'Jaringan AUM & Ortom'],
            'cms' => ['route' => 'organizations.networks.index', 'label' => 'Jaringan AUM/Ortom'],
            'variants' => [
                'standar' => 'templates.sections.jaringan-aum-ortom.standar',
            ],
            'default_variant' => 'standar',
        ],
        'daftar-berita' => [
            'label' => 'Daftar Berita',
            // `category_filter`, when set, restricts the live-organization query (see
            // mozaik.blade.php/ringkas.blade.php) to posts whose `category` matches exactly —
            // lets two daftar-berita instances on the same page (e.g. "Berita" vs "Opini") pull
            // from disjoint sets of posts instead of both showing the same unfiltered list.
            'fields' => ['title', 'items', 'limit', 'category_filter'],
            'defaults' => ['title' => 'Berita Terbaru'],
            'cms' => ['route' => 'organizations.posts.index', 'label' => 'Berita'],
            // `standar`/`modern` are aliases with no view file of their own — they intentionally
            // point at the same views as `mozaik`/`sorotan` so a template can pick a variant
            // consistent with the naming other sections use (standar/modern) without daftar-berita
            // needing its own standar.blade.php/modern.blade.php. Declared before the named keys
            // so the builder's variant dropdown (which dedups by view path, keeping the last key
            // declared per path) shows only Sorotan/Mozaik/Ringkas, never these aliases as
            // separate options.
            'variants' => [
                'standar' => 'templates.sections.daftar-berita.mozaik',
                'modern' => 'templates.sections.daftar-berita.sorotan',
                'sorotan' => 'templates.sections.daftar-berita.sorotan',
                'mozaik' => 'templates.sections.daftar-berita.mozaik',
                'ringkas' => 'templates.sections.daftar-berita.ringkas',
            ],
            'default_variant' => 'standar',
        ],
        'agenda' => [
            'label' => 'Agenda',
            'fields' => ['title', 'items', 'limit'],
            'defaults' => ['title' => 'Agenda Kegiatan'],
            'cms' => ['route' => 'organizations.agendas.index', 'label' => 'Agenda'],
            'variants' => [
                'standar' => 'templates.sections.agenda.standar',
            ],
            'default_variant' => 'standar',
        ],
        'pengumuman' => [
            'label' => 'Pengumuman',
            'fields' => ['title', 'items', 'limit'],
            'defaults' => ['title' => 'Pengumuman'],
            'cms' => ['route' => 'organizations.announcements.index', 'label' => 'Pengumuman'],
            'variants' => [
                'standar' => 'templates.sections.pengumuman.standar',
            ],
            'default_variant' => 'standar',
        ],
        'galeri' => [
            'label' => 'Galeri',
            'fields' => ['title', 'items', 'limit'],
            'defaults' => ['title' => 'Galeri'],
            'cms' => ['route' => 'organizations.gallery.index', 'label' => 'Galeri'],
            'variants' => [
                'standar' => 'templates.sections.galeri.standar',
            ],
            'default_variant' => 'standar',
        ],
        'jadwal-salat' => [
            'label' => 'Jadwal Salat',
            'fields' => ['title', 'location', 'times'],
            'defaults' => ['title' => 'Jadwal Salat Hari Ini'],
            'variants' => [
                'standar' => 'templates.sections.jadwal-salat.standar',
            ],
            'default_variant' => 'standar',
        ],
        'jadwal-kajian' => [
            'label' => 'Jadwal Kajian',
            'fields' => [],
            'variants' => [
                'standar' => 'templates.sections.jadwal-kajian.standar',
            ],
            'default_variant' => 'standar',
        ],
        'jadwal-praktik' => [
            'label' => 'Jadwal Praktik Dokter',
            'fields' => ['title', 'doctors'],
            'defaults' => ['title' => 'Jadwal Praktik Dokter'],
            'variants' => [
                'standar' => 'templates.sections.jadwal-praktik.standar',
            ],
            'default_variant' => 'standar',
        ],
        'donasi-zakat-infak' => [
            'label' => 'Donasi, Zakat & Infak',
            'fields' => ['title', 'body', 'image', 'wa_number', 'wa_message'],
            'defaults' => [
                'title' => 'Donasi, Zakat, dan Infak',
                'wa_message' => 'Assalamu\'alaikum, saya ingin berdonasi untuk {org_name}. Mohon informasi caranya ya',
            ],
            'variants' => [
                'standar' => 'templates.sections.donasi-zakat-infak.standar',
            ],
            'default_variant' => 'standar',
        ],
        'ppdb' => [
            'label' => 'PPDB',
            'fields' => ['title', 'body', 'deadline', 'cta_label', 'wa_number', 'wa_message'],
            'defaults' => [
                'title' => 'Penerimaan Peserta Didik Baru',
                'wa_message' => 'Assalamu\'alaikum, saya ingin mendaftar PPDB di {org_name}. Mohon informasi caranya ya',
            ],
            'variants' => [
                'standar' => 'templates.sections.ppdb.standar',
            ],
            'default_variant' => 'standar',
        ],
        'formulir-kontak' => [
            'label' => 'Formulir Kontak',
            'fields' => ['title', 'subtitle', 'wa_number', 'wa_message'],
            'defaults' => [
                'title' => 'Hubungi Kami',
                'wa_message' => 'Assalamu\'alaikum, saya ingin bertanya seputar {org_name}.',
            ],
            'variants' => [
                'standar' => 'templates.sections.formulir-kontak.standar',
            ],
            'default_variant' => 'standar',
        ],
        'lokasi-peta' => [
            'label' => 'Lokasi & Peta',
            'fields' => ['title', 'address', 'map_embed'],
            'defaults' => ['title' => 'Lokasi'],
            'variants' => [
                'standar' => 'templates.sections.lokasi-peta.standar',
            ],
            'default_variant' => 'standar',
        ],
        'cta' => [
            'label' => 'CTA',
            'fields' => [
                'title', 'subtitle',
                'cta_label', 'cta_type', 'cta_section', 'cta_url', 'cta_wa_number', 'cta_wa_message',
            ],
            'defaults' => [
                'title' => 'Mari Bergabung',
                'cta_wa_message' => 'Assalamu\'alaikum, saya ingin bertanya seputar {org_name}.',
            ],
            'variants' => [
                'standar' => 'templates.sections.cta.standar',
                'modern' => 'templates.sections.cta.modern',
                'newsletter' => 'templates.sections.cta.newsletter',
            ],
            'default_variant' => 'standar',
        ],
    ],

];
