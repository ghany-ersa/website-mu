<?php

// Registry of section keys the builder can add to a page. Each key maps by filename
// convention to a partial in resources/views/templates/sections/{key}.blade.php.
// `fields` lists the content[] keys that partial reads, driving the builder's
// properties panel form — see resources/views/templates/sections/*.blade.php.
// `defaults`, when present, seeds content[] on creation (OrganizationSectionController::store())
// with the same fallback text the partial itself would render for an empty field — so a new
// section already looks/reads right without the user having to retype what's effectively
// already there, and the builder's edit form isn't misleadingly blank.
return [

    'sections' => [
        'hero' => [
            'label' => 'Hero',
            'fields' => ['badge', 'headline', 'subheadline', 'cta_label', 'cta_secondary_label', 'image'],
        ],
        'header' => [
            'label' => 'Header',
            'fields' => ['org_name'],
        ],
        'footer' => [
            'label' => 'Footer',
            'fields' => [],
        ],
        'tentang-organisasi' => [
            'label' => 'Tentang Organisasi',
            'fields' => ['title', 'body', 'image', 'stats'],
            'defaults' => ['title' => 'Tentang Organisasi'],
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
        'donasi-zakat-infak' => [
            'label' => 'Donasi, Zakat & Infak',
            'fields' => ['title', 'body', 'image'],
            'defaults' => ['title' => 'Donasi, Zakat, dan Infak'],
        ],
        'ppdb' => [
            'label' => 'PPDB',
            'fields' => ['title', 'body', 'deadline', 'cta_label'],
            'defaults' => ['title' => 'Penerimaan Peserta Didik Baru'],
        ],
        'formulir-kontak' => [
            'label' => 'Formulir Kontak',
            'fields' => ['title', 'subtitle'],
            'defaults' => ['title' => 'Hubungi Kami'],
        ],
        'lokasi-peta' => [
            'label' => 'Lokasi & Peta',
            'fields' => ['title', 'address'],
            'defaults' => ['title' => 'Lokasi'],
        ],
        'cta' => [
            'label' => 'CTA',
            'fields' => ['title', 'subtitle', 'cta_label'],
            'defaults' => ['title' => 'Mari Bergabung'],
        ],
    ],

];
