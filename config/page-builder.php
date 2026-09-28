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
// header and end with exactly one footer. Locked is independent of editability: both expose an
// `org_name` field so a long organization name can be shortened for the wordmark (it defaults to
// the organization's own name when blank - see header/footer standar.blade.php), which is what
// makes them clickable in the builder sidebar (edit.blade.php's $hasFields guard) and editable
// server-side (OrganizationSectionController::ensureContentEditable()). They still can't be
// added, deleted, duplicated, or reordered.
//
// `hidden` (bool, default false), when true, marks a section still under development: the
// "Tambah Section" picker must not offer it (edit.blade.php's $sectionRegistry filter) and it
// must not render on the public site (organizations/pages/_render.blade.php) even if an
// organization's page already has one (e.g. seeded by a template). Unlike `locked`, the builder's
// own editor still shows and lets existing instances be edited/removed, so in-progress work isn't
// blocked - only end-user-facing surfaces are gated.
//
// `exclusive` (bool, default false), when true, marks a section only an organization on a plan
// with `has_exclusive_templates` may add - Organization::canUseExclusiveTemplates(). The "Tambah
// Section" picker still lists it, greyed out and badged, so the capability is discoverable rather
// than invisible; OrganizationSectionController::store() is what actually enforces it. This is a
// *section*-level gate and is deliberately separate from the variant-level `is_exclusive` column
// (see below): that one only governs which layout may be picked for a section the organization
// already has, so on its own it let any plan add these sections and simply render their sole
// variant. The premium mosque sections (fasilitas-masjid, donasi-progress, laporan-keuangan,
// kalkulator-zakat, akad-venue) need both flags.
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
            'fields' => ['org_name'],
            'locked' => true,
        ],
        'footer' => [
            'label' => 'Footer',
            'fields' => ['org_name'],
            'locked' => true,
        ],
        'tentang-organisasi' => [
            'label' => 'Tentang Organisasi',
            // label/vision_*/values_*: extra copy slots the `sd-madya` variant lays out
            // (eyebrow label, a vision card and a values card); ignored by `standar`.
            'fields' => ['title', 'body', 'image', 'stats', 'label', 'vision_title', 'vision_body', 'values_title', 'values'],
            // How many `stats` entries the builder's editor lets an owner add. Matches this
            // section's stats grid, which is `grid-cols-3` in sd-madya.blade.php - a fourth entry
            // would wrap onto a row of its own and look like a mistake. The builder reads this
            // key rather than hardcoding a number per section (see builder/edit.blade.php).
            'stats_max' => 3,
            'defaults' => [
                'title' => 'Tentang Organisasi',
                'stats' => [],
            ],
        ],
        'sambutan-ketua' => [
            'label' => 'Sambutan Ketua',
            'fields' => ['nama', 'jabatan', 'sambutan', 'photo'],
        ],
        'struktur-pengurus' => [
            'label' => 'Struktur Pengurus',
            // label/subtitle are read only by the `tim-pengajar` variant's header block.
            'fields' => ['title', 'items', 'label', 'subtitle'],
            'defaults' => ['title' => 'Struktur Pengurus'],
            'cms' => ['route' => 'organizations.officers.index', 'label' => 'Pengurus'],
        ],
        'program-unggulan' => [
            'label' => 'Program Unggulan',
            'fields' => ['title', 'items', 'subtitle'],
            'defaults' => ['title' => 'Program Unggulan'],
            'cms' => ['route' => 'organizations.programs.index', 'label' => 'Program', 'params' => ['type' => 'program']],
        ],
        'layanan' => [
            'label' => 'Layanan',
            'fields' => ['title', 'items'],
            'defaults' => ['title' => 'Layanan'],
            'cms' => ['route' => 'organizations.programs.index', 'label' => 'Layanan', 'params' => ['type' => 'layanan']],
        ],
        // A school's activities. Same Program model as program-unggulan/layanan, keyed by the
        // `kegiatan` type - see OrganizationProgramController::type().
        'kegiatan' => [
            'label' => 'Kegiatan',
            'fields' => ['title', 'items', 'subtitle'],
            'defaults' => ['title' => 'Kegiatan Sekolah'],
            'cms' => ['route' => 'organizations.programs.index', 'label' => 'Kegiatan', 'params' => ['type' => 'kegiatan']],
        ],
        // Quotes from alumni/parents. Content-only: written once at setup and rarely changed,
        // so they live in the section's own `items` rather than earning a CMS table.
        'testimoni' => [
            'label' => 'Testimoni',
            'fields' => ['title', 'label', 'items'],
            'defaults' => ['title' => 'Apa Kata Mereka?'],
        ],
        'prestasi' => [
            'label' => 'Prestasi',
            'fields' => ['title', 'subtitle', 'label', 'items', 'limit', 'stats'],
            // Four, not three like tentang-organisasi: this section's strip is
            // `md:grid-cols-4` (standar.blade.php), and the SD Madya template seeds exactly four
            // ("500+ Trofi", "98% Lulusan Terbaik", "12 Ekskul Unggulan", "20+ Tahun
            // Pengabdian"). Capping at 3 here would make the seeded content unreachable to edit.
            //
            // Only the `standar` variant renders it - the `sd-madya` one is the homepage teaser
            // (a fixed 3-tile bento with no stats strip), so the field simply has no effect
            // there rather than being hidden per variant: the registry's `fields` are per
            // SECTION, not per variant, and a variant that ignores a field is normal here (see
            // tentang-organisasi's label/vision_* which `standar` ignores).
            'stats_max' => 4,
            'defaults' => ['title' => 'Prestasi'],
            'cms' => ['route' => 'organizations.achievements.index', 'label' => 'Prestasi'],
        ],
        'jaringan-aum-ortom' => [
            'label' => 'Jaringan AUM/Ortom',
            'fields' => ['title', 'items'],
            'defaults' => ['title' => 'Jaringan AUM & Ortom'],
            'cms' => ['route' => 'organizations.networks.index', 'label' => 'AUM/Ortom'],
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
        // Absorbed the former 'jadwal-kajian' section, whose view had become byte-for-byte
        // identical to this one over the same `agendas` table. The section itself stays open to
        // every plan; only its `poster` variant is exclusive (see SectionVariantSeeder), so a
        // lower plan still gets its agenda list - it just doesn't get the flyer grid.
        // `subtitle` is read by the poster variant only.
        'agenda' => [
            'label' => 'Agenda / Jadwal',
            'fields' => ['title', 'subtitle', 'items', 'limit'],
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
            // `subtitle` renders under the heading; without it here the builder's properties
            // panel would never offer the field, leaving the copy unreachable rather than empty.
            'fields' => ['title', 'subtitle', 'items', 'limit'],
            'defaults' => ['title' => 'Galeri'],
            'cms' => ['route' => 'organizations.gallery.index', 'label' => 'Galeri'],
        ],
        'jadwal-salat' => [
            'label' => 'Jadwal Salat',
            'fields' => ['title', 'location', 'times'],
            'defaults' => ['title' => 'Jadwal Salat Hari Ini'],
            // Not yet location-aware (times are manually typed, not fetched by coordinates) -
            // hidden from new-section picker and public render until that's built. See the
            // `hidden` doc comment above.
            'hidden' => true,
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
            // address/hours/form_*/grades/cta_label: laid out only by the `sd-madya` variant,
            // which renders an enrolment form beside the contact details.
            'fields' => ['title', 'subtitle', 'wa_number', 'wa_message', 'address', 'hours', 'hours_title', 'form_title', 'form_subtitle', 'grades', 'cta_label'],
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
        'fasilitas-masjid' => [
            'label' => 'Fasilitas Masjid',
            'exclusive' => true,
            // `subtitle` is rendered under the heading by the nurul-huda variant; without it
            // here the builder's properties panel would never offer the field, so the copy
            // would be unreachable rather than merely empty.
            'fields' => ['title', 'subtitle', 'items', 'limit'],
            'defaults' => ['title' => 'Fasilitas Masjid'],
            'cms' => ['route' => 'organizations.facilities.index', 'label' => 'Fasilitas'],
        ],
        'donasi-progress' => [
            'label' => 'Donasi & Progress',
            'exclusive' => true,
            'fields' => ['title', 'subtitle', 'items', 'limit'],
            'defaults' => ['title' => 'Program Donasi Aktif'],
            'cms' => ['route' => 'organizations.donations.index', 'label' => 'Program Donasi'],
        ],
        'laporan-keuangan' => [
            'label' => 'Laporan Keuangan',
            'exclusive' => true,
            'fields' => ['title'],
            'defaults' => ['title' => 'Laporan Keuangan'],
            'cms' => ['route' => 'organizations.financial-reports.index', 'label' => 'Laporan'],
        ],
        'kalkulator-zakat' => [
            'label' => 'Kalkulator Zakat',
            'exclusive' => true,
            'fields' => ['title', 'gold_price_per_gram', 'nisab_grams', 'cta_label', 'wa_number', 'wa_message'],
            'defaults' => [
                'title' => 'Kalkulator Zakat',
                'gold_price_per_gram' => 1500000,
                'nisab_grams' => 85,
                'cta_label' => 'Konsultasi Zakat',
                'wa_message' => 'Assalamu\'alaikum, saya ingin bertanya seputar zakat di {org_name}.',
            ],
        ],
        // Named 'akad-venue', not 'sewa-aula': many masjid deliberately avoid rental wording for
        // a place of worship - it reads as monetising religious space - and ask for a voluntary
        // infak instead. `infak_note` carries that framing and is blank by default, so a masjid
        // that does charge a fixed fee can simply write that there instead.
        'akad-venue' => [
            'label' => 'Akad Nikah',
            'exclusive' => true,
            'fields' => [
                'hero_title', 'hero_subtitle', 'availability_badge',
                'wa_number', 'facilities', 'infak_note', 'image',
            ],
            'defaults' => [
                'hero_title' => 'Aula Serbaguna Masjid',
                'availability_badge' => 'Terbuka untuk Jamaah',
                'infak_note' => 'Tidak ada tarif sewa, jamaah dipersilakan berinfak semampunya.',
            ],
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
