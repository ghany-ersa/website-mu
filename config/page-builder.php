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
// header and end with exactly one footer. Unlike footer, header still has editable fields
// (org_name) and stays clickable in the builder sidebar — only its position/delete/duplicate
// are locked, not its properties panel.
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
                // when `stats` is empty — seeded here too so a newly-added section starts with
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
        ],
        'program-unggulan' => [
            'label' => 'Program Unggulan',
            'fields' => ['title', 'items'],
            'defaults' => ['title' => 'Program Unggulan'],
        ],
        'layanan' => [
            'label' => 'Layanan',
            'fields' => ['title', 'items'],
            'defaults' => ['title' => 'Layanan'],
        ],
        'jaringan-aum-ortom' => [
            'label' => 'Jaringan AUM/Ortom',
            'fields' => ['title', 'items'],
            'defaults' => ['title' => 'Jaringan AUM & Ortom'],
        ],
        'daftar-berita' => [
            'label' => 'Daftar Berita',
            'fields' => ['title', 'items', 'limit'],
            'defaults' => ['title' => 'Berita Terbaru'],
        ],
        'agenda' => [
            'label' => 'Agenda',
            'fields' => ['title', 'items', 'limit'],
            'defaults' => ['title' => 'Agenda Kegiatan'],
        ],
        'pengumuman' => [
            'label' => 'Pengumuman',
            'fields' => ['title', 'items', 'limit'],
            'defaults' => ['title' => 'Pengumuman'],
        ],
        'galeri' => [
            'label' => 'Galeri',
            'fields' => ['title', 'items', 'limit'],
            'defaults' => ['title' => 'Galeri'],
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
