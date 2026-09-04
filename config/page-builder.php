<?php

// Registry of section keys the builder can add to a page. Each key maps by filename
// convention to a partial in resources/views/templates/sections/{key}.blade.php.
// `fields` lists the content[] keys that partial reads, driving the builder's
// properties panel form - see resources/views/templates/sections/*.blade.php.
// `defaults`, when present, seeds content[] on creation (OrganizationSectionController::store())
// with the same fallback text the partial itself would render for an empty field - so a new
// section already looks/reads right without the user having to retype what's effectively
// already there, and the builder's edit form isn't misleadingly blank.
//
// `locked` (bool, default false), when true, marks a section the builder UI must not offer in
// the "Tambah Section" picker and must not let the user delete, duplicate, or drag-reorder - see
// OrganizationPage::footerSection()/ensureFooter() and OrganizationSectionController for the
// enforcement. `header` and `footer` are locked: every page must always start with exactly one
// header and end with exactly one footer. Both have no editable `fields` - they always show the
// organization's own name (see header.blade.php/footer.blade.php) with no override - so neither
// is clickable in the builder sidebar (see edit.blade.php's $hasFields guard).
//
// `cms`, when present, is the single source of truth for a section whose `items` field is backed
// by a separate CMS resource (e.g. agenda items are managed at organizations.agendas.*, not
// inline in the builder) - both edit.blade.php's "Kelola X ->" link and layouts/organization.blade.php's
// sidebar menu read `cms.route`/`cms.label`/`cms.params` from here instead of each hardcoding
// their own section-key -> route/label map.
//
// Section variant registry (which Blade view renders each named layout, and whether picking it
// requires Organization::canUseExclusiveTemplates()) lives in the `section_variants` table, not
// here - see App\Models\SectionVariant / App\Services\SectionVariantResolver and
// database/seeders/SectionVariantSeeder.php.
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
        ],
        'header' => [
            'label' => 'Header',
            'fields' => [],
            'locked' => true,
        ],
        'footer' => [
            'label' => 'Footer',
            'fields' => [],
            'locked' => true,
        ],
        'tentang-organisasi' => [
            'label' => 'Tentang Organisasi',
            'fields' => ['title', 'body', 'image', 'stats'],
            'defaults' => [
                'title' => 'Tentang Organisasi',
                // Same fallback templates/sections/tentang-organisasi.blade.php already shows
                // when `stats` is empty - seeded here too so a newly-added section starts with
                // editable rows in the builder's properties panel instead of an empty stats
                // editor that doesn't match what the canvas is actually rendering.
                'stats' => [
                    ['value' => '10+', 'label' => 'Tahun Berdiri'],
                    ['value' => '100+', 'label' => 'Anggota'],
                    ['value' => '5+', 'label' => 'Program Aktif'],
                ],
            ],
        ],
        'sambutan-ketua' => [
            'label' => 'Sambutan Ketua',
            'fields' => ['nama', 'jabatan', 'sambutan', 'photo'],
        ],
        'struktur-pengurus' => [
            'label' => 'Struktur Pengurus',
            'fields' => ['title', 'items'],
            'defaults' => ['title' => 'Struktur Pengurus'],
            'cms' => ['route' => 'organizations.officers.index', 'label' => 'Pengurus'],
        ],
        'program-unggulan' => [
            'label' => 'Program Unggulan',
            'fields' => ['title', 'items'],
            'defaults' => ['title' => 'Program Unggulan'],
            'cms' => ['route' => 'organizations.programs.index', 'label' => 'Program', 'params' => ['type' => 'program']],
        ],
        'layanan' => [
            'label' => 'Layanan',
            'fields' => ['title', 'items'],
            'defaults' => ['title' => 'Layanan'],
            'cms' => ['route' => 'organizations.programs.index', 'label' => 'Layanan', 'params' => ['type' => 'layanan']],
        ],
        'jaringan-aum-ortom' => [
            'label' => 'Jaringan AUM/Ortom',
            'fields' => ['title', 'items'],
            'defaults' => ['title' => 'Jaringan AUM & Ortom'],
            'cms' => ['route' => 'organizations.networks.index', 'label' => 'Jaringan AUM/Ortom'],
        ],
        'daftar-berita' => [
            'label' => 'Daftar Berita',
            // `category_filter`, when set, restricts the live-organization query (see
            // standar.blade.php/ringkas.blade.php) to posts whose `category` matches exactly —
            // lets two daftar-berita instances on the same page (e.g. "Berita" vs "Opini") pull
            // from disjoint sets of posts instead of both showing the same unfiltered list.
            'fields' => ['title', 'items', 'limit', 'category_filter'],
            'defaults' => ['title' => 'Berita Terbaru'],
            'cms' => ['route' => 'organizations.posts.index', 'label' => 'Berita'],
        ],
        'agenda' => [
            'label' => 'Agenda',
            'fields' => ['title', 'items', 'limit'],
            'defaults' => ['title' => 'Agenda Kegiatan'],
            'cms' => ['route' => 'organizations.agendas.index', 'label' => 'Agenda'],
        ],
        'pengumuman' => [
            'label' => 'Pengumuman',
            'fields' => ['title', 'items', 'limit'],
            'defaults' => ['title' => 'Pengumuman'],
            'cms' => ['route' => 'organizations.announcements.index', 'label' => 'Pengumuman'],
        ],
        'galeri' => [
            'label' => 'Galeri',
            'fields' => ['title', 'items', 'limit'],
            'defaults' => ['title' => 'Galeri'],
            'cms' => ['route' => 'organizations.gallery.index', 'label' => 'Galeri'],
        ],
        'jadwal-salat' => [
            'label' => 'Jadwal Salat',
            'fields' => ['title', 'location', 'times'],
            'defaults' => ['title' => 'Jadwal Salat Hari Ini'],
        ],
        'jadwal-kajian' => [
            'label' => 'Jadwal Kajian',
            'fields' => [],
        ],
        'jadwal-praktik' => [
            'label' => 'Jadwal Praktik Dokter',
            'fields' => ['title', 'doctors'],
            'defaults' => ['title' => 'Jadwal Praktik Dokter'],
        ],
        'donasi-zakat-infak' => [
            'label' => 'Donasi, Zakat & Infak',
            'fields' => ['title', 'body', 'image', 'wa_number', 'wa_message'],
            'defaults' => [
                'title' => 'Donasi, Zakat, dan Infak',
                'wa_message' => 'Assalamu\'alaikum, saya ingin berdonasi untuk {org_name}. Mohon informasi caranya ya',
            ],
        ],
        'ppdb' => [
            'label' => 'PPDB',
            'fields' => ['title', 'body', 'deadline', 'cta_label', 'wa_number', 'wa_message'],
            'defaults' => [
                'title' => 'Penerimaan Peserta Didik Baru',
                'wa_message' => 'Assalamu\'alaikum, saya ingin mendaftar PPDB di {org_name}. Mohon informasi caranya ya',
            ],
        ],
        'formulir-kontak' => [
            'label' => 'Formulir Kontak',
            'fields' => ['title', 'subtitle', 'wa_number', 'wa_message'],
            'defaults' => [
                'title' => 'Hubungi Kami',
                'wa_message' => 'Assalamu\'alaikum, saya ingin bertanya seputar {org_name}.',
            ],
        ],
        'lokasi-peta' => [
            'label' => 'Lokasi & Peta',
            'fields' => ['title', 'address', 'map_embed'],
            'defaults' => ['title' => 'Lokasi'],
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
        ],
    ],

];
