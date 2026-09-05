<?php

namespace App\Services;

use App\Enums\PublishStatus;
use App\Models\Agenda;
use App\Models\Announcement;
use App\Models\DonationProgram;
use App\Models\DonationTransaction;
use App\Models\FinancialReport;
use App\Models\GalleryPhoto;
use App\Models\MasjidFacility;
use App\Models\Officer;
use App\Models\Organization;
use App\Models\OrganizationNetwork;
use App\Models\Post;
use App\Models\Program;
use Illuminate\Support\Carbon;

/**
 * Seeds sample CMS records (posts, announcements, agendas, gallery photos, officers,
 * networks, programs/services) for a freshly created organization, one per CMS-backed
 * section key present on its home page - so the builder canvas and the organization's
 * own page (once it publishes) show real, editable content immediately instead of an
 * empty state, and templates/sections/*.blade.php never has to special-case "organization
 * exists but its CMS tables are still empty". Posts/announcements/agendas are seeded
 * already Published (not Draft) specifically so they clear their published()/published_at
 * scopes and actually render in the builder canvas, which queries CMS tables the same way
 * the public page does - there's no builder-only preview path to show drafts through.
 *
 * Only called once, right after Organization::seedPagesFromTemplate() clones the
 * template's sections - never touches an organization that already has any of a given
 * table's rows, so it can't clobber real content a user has since replaced or deleted.
 *
 * Every sample count below is capped to the organization's own plan limit (see
 * PlanLimitService::effectiveLimit()) - every organization is created on the Starter plan
 * (see OrganizationController::store()), whose limits are tighter than these samples'
 * original fixed counts (e.g. 3 announcements vs. Starter's limit of 2), so seeding the
 * fixed count unconditionally used to leave a brand-new organization already in violation
 * of its own plan (see Organization::planViolations()) before the owner had touched
 * anything. A limit of 0 skips that resource's samples entirely rather than seeding one
 * record a Starter org isn't allowed to have at all.
 */
class CmsSampleDataSeeder
{
    /**
     * Template slug this seeder has faithful, named sample data for (ported from the
     * standalone nurul-huda project's MasjidContentSeeder) - seedAgendas()/seedOfficers()
     * use these in place of their generic "Contoh ..." placeholders only for an organization
     * on this exact template, so every other template's samples are unaffected.
     */
    private const NURUL_HUDA_TEMPLATE_SLUG = 'masjid-nurul-huda-eksklusif';

    /**
     * Sample imagery for this template is hotlinked from the live Masjid Nurul Huda Ambulu
     * site's own S3 bucket (each URL checked to return 200), so a fresh organization previews
     * the actual mosque instead of stand-in stock photography. Every section guards on an
     * empty photo, so if the source ever moves a file the card degrades rather than breaking.
     */
    private const S3 = 'https://s3.nurul-huda.ambulu.or.id';

    /**
     * Opening balance carried into the wakaf ledger from April 2025, and the date the program
     * itself opened - both from the nurul-huda project's WakafPembangunanTransactionSeeder.
     */
    private const WAKAF_OPENING_BALANCE = 9_387_000;

    private const WAKAF_STARTS_AT = '2025-04-01';

    /** How many months of books to seed, matching the source project's own window. */
    private const BOOKS_MONTHS_BACK = 4;

    /**
     * Share of each month's non-electricity operational spend, from the nurul-huda project's
     * FinancialReportSeeder.
     *
     * @var array<string, float>
     */
    private const OPERATIONAL_EXPENSE_SHARES = [
        'Gaji Marbot' => 0.55,
        'Kebersihan' => 0.20,
        'Air (PDAM)' => 0.13,
        'Perlengkapan & ATK' => 0.12,
    ];

    public static function seed(Organization $organization, array $sectionKeys): void
    {
        $keys = array_unique($sectionKeys);
        $limits = app(PlanLimitService::class);
        $isNurulHuda = $organization->template?->slug === self::NURUL_HUDA_TEMPLATE_SLUG;

        if (in_array('daftar-berita', $keys, true)) {
            self::seedPosts($organization, $limits);
        }

        if (in_array('pengumuman', $keys, true)) {
            self::seedAnnouncements($organization, $limits);
        }

        if (in_array('agenda', $keys, true) || in_array('jadwal-kajian', $keys, true)) {
            self::seedAgendas($organization, $limits, $isNurulHuda ? self::nurulHudaKajianSamples() : null);
        }

        if (in_array('galeri', $keys, true)) {
            self::seedGalleryPhotos($organization, $limits, $isNurulHuda ? self::nurulHudaGallerySamples() : null);
        }

        if (in_array('struktur-pengurus', $keys, true)) {
            self::seedOfficers($organization, $limits, $isNurulHuda ? self::nurulHudaOfficerSamples() : null);
        }

        if (in_array('jaringan-aum-ortom', $keys, true)) {
            self::seedNetworks($organization);
        }

        if (in_array('program-unggulan', $keys, true)) {
            self::seedPrograms($organization, 'program', $limits);
        }

        if (in_array('layanan', $keys, true)) {
            self::seedPrograms($organization, 'layanan', $limits);
        }

        if (in_array('fasilitas-masjid', $keys, true)) {
            self::seedFacilities($organization, $limits);
        }

        if (in_array('donasi-progress', $keys, true)) {
            self::seedDonationPrograms($organization, $limits);
        }

        if (in_array('laporan-keuangan', $keys, true)) {
            self::seedFinancialReports($organization);
        }
    }

    /**
     * How many of a fixed sample list to actually insert: the smaller of the list's own
     * length and the organization's plan limit for that resource key, or the full list when
     * the plan has no limit (null = unlimited). Never negative - a limit of 0 (or an
     * organization already somehow past it) yields 0, i.e. skip entirely.
     */
    private static function sampleCount(Organization $organization, PlanLimitService $limits, string $key, int $available): int
    {
        $limit = $limits->effectiveLimit($organization, $key);

        return $limit === null ? $available : max(0, min($available, $limit));
    }

    private static function seedPosts(Organization $organization, PlanLimitService $limits): void
    {
        if ($organization->posts()->exists()) {
            return;
        }

        $samples = [
            ['title' => 'Contoh Berita Kegiatan', 'category' => 'Kegiatan', 'body' => 'Ringkasan singkat berita akan tampil di sini. Edit atau hapus contoh ini kapan saja.'],
            ['title' => 'Contoh Berita Pengumuman Program', 'category' => 'Program', 'body' => 'Ringkasan singkat berita akan tampil di sini. Edit atau hapus contoh ini kapan saja.'],
            ['title' => 'Contoh Berita Sosial Kemasyarakatan', 'category' => 'Sosial', 'body' => 'Ringkasan singkat berita akan tampil di sini. Edit atau hapus contoh ini kapan saja.'],
        ];

        $samples = array_slice($samples, 0, self::sampleCount($organization, $limits, 'posts', count($samples)));

        if ($samples === []) {
            return;
        }

        $now = now();

        Post::insert(array_map(fn ($sample, $index) => [
            'organization_id' => $organization->id,
            'title' => $sample['title'],
            'slug' => str($sample['title'])->slug().'-'.$organization->id.'-'.$index,
            'category' => $sample['category'],
            'body' => '<p>'.$sample['body'].'</p>',
            'status' => PublishStatus::Published->value,
            'published_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ], $samples, array_keys($samples)));
    }

    private static function seedAnnouncements(Organization $organization, PlanLimitService $limits): void
    {
        if ($organization->announcements()->exists()) {
            return;
        }

        $samples = [
            ['title' => 'Contoh Pengumuman Penting', 'priority' => 'Tinggi'],
            ['title' => 'Contoh Pengumuman Kegiatan', 'priority' => 'Sedang'],
            ['title' => 'Contoh Pengumuman Umum', 'priority' => 'Rendah'],
        ];

        $samples = array_slice($samples, 0, self::sampleCount($organization, $limits, 'announcements', count($samples)));

        if ($samples === []) {
            return;
        }

        $now = now();

        Announcement::insert(array_map(fn ($sample) => [
            'organization_id' => $organization->id,
            'title' => $sample['title'],
            'body' => '<p>Isi pengumuman akan tampil di sini. Edit atau hapus contoh ini kapan saja.</p>',
            'priority' => $sample['priority'],
            'valid_until' => $now->copy()->addMonth(),
            'status' => PublishStatus::Published->value,
            'created_at' => $now,
            'updated_at' => $now,
        ], $samples));
    }

    /**
     * @param  array<int, array{title: string, days: int, location?: string, description?: string}>|null  $customSamples
     */
    private static function seedAgendas(Organization $organization, PlanLimitService $limits, ?array $customSamples = null): void
    {
        if ($organization->agendas()->exists()) {
            return;
        }

        $samples = $customSamples ?? [
            ['title' => 'Contoh Agenda Kegiatan', 'days' => 7],
            ['title' => 'Contoh Rapat Koordinasi', 'days' => 14],
            ['title' => 'Contoh Kegiatan Sosial', 'days' => 21],
        ];

        $samples = array_slice($samples, 0, self::sampleCount($organization, $limits, 'agendas', count($samples)));

        if ($samples === []) {
            return;
        }

        $now = now();

        Agenda::insert(array_map(fn ($sample) => [
            'organization_id' => $organization->id,
            'title' => $sample['title'],
            'starts_at' => $now->copy()->addDays($sample['days'])->setTime(18, 0),
            'location' => $sample['location'] ?? 'Lokasi kegiatan',
            'description' => $sample['description'] ?? '<p>Deskripsi agenda akan tampil di sini. Edit atau hapus contoh ini kapan saja.</p>',
            'status' => PublishStatus::Published->value,
            'created_at' => $now,
            'updated_at' => $now,
        ], $samples));
    }

    /**
     * Flat gray placeholder (inline SVG data URI, no external request) - gallery_photos.url
     * is NOT NULL and there's no real photo to seed, so this stands in until the user
     * replaces it with an actual upload.
     */
    private const PLACEHOLDER_PHOTO_URL = 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="400" height="400"%3E%3Crect width="400" height="400" fill="%23e5e7eb"/%3E%3C/svg%3E';

    /**
     * @param  array<int, array{caption: string, photo: string}>|null  $customSamples
     */
    private static function seedGalleryPhotos(Organization $organization, PlanLimitService $limits, ?array $customSamples = null): void
    {
        if ($organization->photos()->exists()) {
            return;
        }

        $count = self::sampleCount($organization, $limits, 'gallery_photos', $customSamples ? count($customSamples) : 4);

        if ($count === 0) {
            return;
        }

        $now = now();

        GalleryPhoto::insert(array_map(fn ($index) => [
            'organization_id' => $organization->id,
            'url' => $customSamples
                ? self::S3.$customSamples[$index]['photo']
                : self::PLACEHOLDER_PHOTO_URL,
            'caption' => $customSamples[$index]['caption'] ?? 'Foto kegiatan '.($index + 1),
            'order' => $index,
            'created_at' => $now,
            'updated_at' => $now,
        ], range(0, $count - 1)));
    }

    /**
     * @param  array<int, array{name: string, role: string, photo?: string}>|null  $customSamples
     */
    private static function seedOfficers(Organization $organization, PlanLimitService $limits, ?array $customSamples = null): void
    {
        if ($organization->officers()->exists()) {
            return;
        }

        $samples = $customSamples ?? array_map(
            fn (string $role) => ['name' => '[Nama '.$role.']', 'role' => $role],
            ['Ketua', 'Sekretaris', 'Bendahara', 'Anggota'],
        );

        $samples = array_slice($samples, 0, self::sampleCount($organization, $limits, 'officers', count($samples)));

        if ($samples === []) {
            return;
        }

        $now = now();

        Officer::insert(array_map(fn ($sample, $index) => [
            'organization_id' => $organization->id,
            'name' => $sample['name'],
            'role' => $sample['role'],
            'photo' => $sample['photo'] ?? null,
            'order' => $index,
            'created_at' => $now,
            'updated_at' => $now,
        ], $samples, array_keys($samples)));
    }

    /**
     * Not plan-limited: 'jaringan-aum-ortom' isn't a PlanLimitService resource key (it has no
     * per-plan quota, unlike the CMS resources above), so there's no limit to cap this
     * against.
     */
    private static function seedNetworks(Organization $organization): void
    {
        if ($organization->networks()->exists()) {
            return;
        }

        $now = now();

        OrganizationNetwork::insert(array_map(fn ($index) => [
            'organization_id' => $organization->id,
            'name' => '[Nama AUM/Ortom '.$index.']',
            'type' => null,
            'order' => $index,
            'created_at' => $now,
            'updated_at' => $now,
        ], range(1, 3)));
    }

    private static function seedPrograms(Organization $organization, string $type, PlanLimitService $limits): void
    {
        // Guards on the combined 'programs' resource (both types together), not
        // ofType($type) alone: 'program' and 'layanan' share one plan_limits quota (see the
        // comment below), so seeding 'layanan' samples on top of already-seeded 'program'
        // samples - e.g. after switching to a template with a different section via
        // OrganizationTemplateController, which drops pages/sections but never deletes CMS
        // records - would push the organization over its own plan's limit even though this
        // method never lets a single call insert more samples than that limit allows.
        if ($organization->programs()->exists()) {
            return;
        }

        $samples = $type === 'layanan'
            ? [
                ['title' => 'Layanan Konsultasi', 'description' => 'Konsultasi dan pendampingan bagi masyarakat.', 'icon' => '🗣️'],
                ['title' => 'Layanan Administrasi', 'description' => 'Pengurusan surat dan dokumen organisasi.', 'icon' => '📄'],
                ['title' => 'Layanan Sosial', 'description' => 'Bantuan dan pemberdayaan bagi warga kurang mampu.', 'icon' => '❤️'],
            ]
            : [
                ['title' => 'Program Unggulan 1', 'description' => 'Deskripsi singkat program unggulan pertama.', 'icon' => '⭐'],
                ['title' => 'Program Unggulan 2', 'description' => 'Deskripsi singkat program unggulan kedua.', 'icon' => '🎯'],
                ['title' => 'Program Unggulan 3', 'description' => 'Deskripsi singkat program unggulan ketiga.', 'icon' => '🚀'],
            ];

        // 'programs' is the plan_limits/PlanLimitService key for both types (program and
        // layanan aren't tracked separately there) - see PlanLimitService::RESOURCE_RELATIONS
        // and Organization::programs(), which counts both together.
        $samples = array_slice($samples, 0, self::sampleCount($organization, $limits, 'programs', count($samples)));

        if ($samples === []) {
            return;
        }

        $now = now();

        Program::insert(array_map(fn ($sample, $index) => [
            'organization_id' => $organization->id,
            'type' => $type,
            'title' => $sample['title'],
            'description' => $sample['description'],
            'icon' => $sample['icon'],
            'order' => $index,
            'created_at' => $now,
            'updated_at' => $now,
        ], $samples, array_keys($samples)));
    }

    /**
     * The four recurring "Kajian Malam Ilmu & Iman" sessions from the standalone nurul-huda
     * project's MasjidContentSeeder::seedEvents(). That project models them as weekly Events
     * (day_of_week + speaker + poster); this app's Agenda has no speaker/recurrence columns,
     * so the speaker moves into the title and the weekly cadence becomes four consecutive
     * Friday dates - the closest faithful mapping onto the existing schema.
     *
     * @return array<int, array{title: string, days: int, location: string, description: string}>
     */
    private static function nurulHudaKajianSamples(): array
    {
        $samples = [
            ['speaker' => 'Ust. Hadi Santoso', 'materi' => 'Materi Aqidah Tauhid Kitab Ummul Barahin, Karya Imam Sanusi'],
            ['speaker' => 'Ust. Tyas Hidayatulloh, M.Pd', 'materi' => 'Materi Tafsir Kitab Al Azhar, Karya Buya Hamka'],
            ['speaker' => 'Ust. Affan Kamal Mubarok, B.S., M.A.', 'materi' => 'Materi Shirah Kitab Asy-Syamail Al-Muhammadiyah, Karya Imam Tirmidzi'],
            ['speaker' => 'Ust. Nurhadi Amin, S.Ag', 'materi' => 'Materi Fiqh Kitab Bidayatul Mujtahid, Karya Ibnu Rusyd'],
        ];

        $daysUntilFriday = (5 - (int) now()->dayOfWeek + 7) % 7 ?: 7;

        return array_map(fn (array $sample, int $index) => [
            'title' => 'Kajian Malam Ilmu & Iman - '.$sample['speaker'],
            'days' => $daysUntilFriday + ($index * 7),
            'location' => 'Ruang Utama Masjid',
            'description' => '<p>'.$sample['materi'].'<br>Diawali salat Maghrib berjamaah</p>',
        ], $samples, array_keys($samples));
    }

    /**
     * Committee members from the standalone nurul-huda project's
     * MasjidContentSeeder::seedCommitteeMembers().
     *
     * @return array<int, array{name: string, role: string}>
     */
    private static function nurulHudaOfficerSamples(): array
    {
        return [
            ['name' => 'Suhartono, S.Pd', 'role' => 'Ketua Takmir'],
            ['name' => 'Tyas Hidayatulloh, S.Pd, M.Pd', 'role' => 'Sekretaris'],
        ];
    }

    /**
     * Gallery captions from the standalone nurul-huda project's
     * MasjidContentSeeder::seedGalleryPhotos(), each paired with matching imagery.
     *
     * @return array<int, array{caption: string, photo: string}>
     */
    private static function nurulHudaGallerySamples(): array
    {
        return [
            ['caption' => 'Kegiatan Kajian Guru Besar Ramadhan 2026', 'photo' => '/gallery/01M0F8E78MXYJTGZ12GKHMFS33.jpg'],
            ['caption' => 'Buka Bersama Ramadhan 2026', 'photo' => '/gallery/01M0F8GA32JZ6H6J08PMFTW3M2.jpg'],
            ['caption' => 'Bakti Sosial AMM Ambulu 2026', 'photo' => '/gallery/01M0F4DHD4N4ZX5AWYMEBF3M8W.jpg'],
            ['caption' => 'Kunjungan Muspika 2026', 'photo' => '/gallery/01M0F8HZC8FMCW04Y1KTP59HB6.jpg'],
            ['caption' => 'Akad Nikah Kak Zita & Mas Alfian', 'photo' => '/gallery/01M0F8PE5ECH8A2S0HM4BPH4PS.jpg'],
        ];
    }

    /**
     * Same 13 facilities as the standalone nurul-huda project's MasjidContentSeeder, so a
     * fresh organization on this template reads like the real Masjid Nurul Huda Ambulu site
     * immediately. Photos use picsum.photos seeds (that project's own placeholder pattern for
     * facilities without a real photo yet) rather than its private S3 bucket paths, which
     * aren't reachable from this app.
     */
    private static function seedFacilities(Organization $organization, PlanLimitService $limits): void
    {
        if ($organization->facilities()->exists()) {
            return;
        }

        $samples = [
            ['name' => 'Halaman dan Teras Depan', 'photo' => '/facilities/01M0CQ2ENVEQ1WQYZE5AQM257E.jpg'],
            ['name' => 'Parkiran Utama', 'photo' => '/facilities/01M0CQYYDATZX1SYKX2APKBRJK.jpg'],
            ['name' => 'Taman dan Kolam Masjid', 'photo' => '/facilities/01M0CQ3E3PVMX3QJS7XZSQH757.jpg'],
            ['name' => 'Tempat Jamaah Laki-laki', 'photo' => '/facilities/01M0CQBN9Z92DPB8KC69WCRGA7.jpg'],
            ['name' => 'Tempat Jamaah Perempuan', 'photo' => '/facilities/01M0CQCASWABEETW67AYWWZT7W.jpg'],
            ['name' => 'Tempat Wudhu Laki-laki (Luar)', 'photo' => '/facilities/01M0CQD8CBYZ8W04MTY14KZ4WS.jpg'],
            ['name' => 'Tempat Wudhu Perempuan (Depan)', 'photo' => '/facilities/01M0CQET8624HXDET63JPCTWPM.jpg'],
            ['name' => 'Tempat Wudhu Laki-laki (Belakang)', 'photo' => '/facilities/01M0CQG09YTAADQE878R8HS78W.jpg'],
            ['name' => 'Tempat Wudhu Perempuan (Belakang)', 'photo' => '/facilities/01M0CQGWCMTSMZJS4SD5D7M1X3.jpg'],
            ['name' => 'Parkiran Belakang', 'photo' => '/facilities/01M0CQNYNXE3NP5K5NHG5JP3CR.jpg'],
            ['name' => 'Ruang Masjid Lantai 2', 'photo' => '/facilities/01M0CQRCJTJA6SK8KBFXWE43GQ.jpg'],
            ['name' => 'Alat Sholat', 'photo' => '/facilities/01M17A75E57K13N172M7D1H78R.jpg'],
        ];

        $samples = array_slice($samples, 0, self::sampleCount($organization, $limits, 'facilities', count($samples)));

        if ($samples === []) {
            return;
        }

        $now = now();

        MasjidFacility::insert(array_map(fn ($sample, $index) => [
            'organization_id' => $organization->id,
            'name' => $sample['name'],
            'photo' => self::S3.$sample['photo'],
            'description' => null,
            'order' => $index,
            'created_at' => $now,
            'updated_at' => $now,
        ], $samples, array_keys($samples)));
    }

    /**
     * Same 5 donation programs (names, target amounts, date ranges, transaction counts) as
     * the standalone nurul-huda project's MasjidContentSeeder::seedDonationPrograms(), sliced
     * to the organization's donation_programs plan limit - lower plans see the biggest/most
     * representative program(s) first rather than an arbitrary subset.
     */
    private static function seedDonationPrograms(Organization $organization, PlanLimitService $limits): void
    {
        if ($organization->donationPrograms()->exists()) {
            return;
        }

        $programs = [
            [
                'name' => 'Wakaf Pembangunan Masjid',
                'description' => 'Program wakaf untuk pembangunan dan renovasi fasilitas masjid, mencakup penutupan sungai/jembatan, pemavingan halaman parkir, teras masjid, dan payung Nabawi.',
                'target_amount' => 1_175_600_000,
                'starts_at' => Carbon::parse(self::WAKAF_STARTS_AT),
                'ends_at' => now()->addYear(),
                // Real donor-by-donor ledger (see wakafLedger()), so no synthetic count/percent.
                'transactions' => null,
                'percent' => null,
                'photo' => '/donation-programs/wakaf-pembangunan-masjid/cover.jpg',
            ],
            [
                'name' => 'Renovasi Atap Masjid',
                'description' => null,
                'target_amount' => 50_000_000,
                'starts_at' => now()->subWeeks(3),
                'ends_at' => now()->addMonths(2),
                'transactions' => 8,
                'percent' => 42,
                'photo' => '/facilities/01M0CQRCJTJA6SK8KBFXWE43GQ.jpg',
            ],
            [
                'name' => 'Santunan Anak Yatim',
                'description' => null,
                'target_amount' => 20_000_000,
                'starts_at' => now()->subMonths(2),
                'ends_at' => now()->addMonth(),
                'transactions' => 12,
                'percent' => 85,
                'photo' => '/gallery/01M0F4DHD4N4ZX5AWYMEBF3M8W.jpg',
            ],
            [
                'name' => 'Pembangunan Perpustakaan',
                'description' => null,
                'target_amount' => 15_000_000,
                'starts_at' => now()->subMonth(),
                'ends_at' => now()->subDays(3),
                'transactions' => 3,
                'percent' => 100,
                'photo' => '/gallery/01M0F8E78MXYJTGZ12GKHMFS33.jpg',
            ],
            [
                'name' => 'Wakaf Al-Quran',
                'description' => null,
                'target_amount' => 10_000_000,
                'starts_at' => now()->addWeek(),
                'ends_at' => now()->addMonths(3),
                'transactions' => 0,
                'percent' => 0,
                'photo' => '/facilities/01M17A75E57K13N172M7D1H78R.jpg',
            ],
        ];

        $programs = array_slice($programs, 0, self::sampleCount($organization, $limits, 'donation_programs', count($programs)));

        if ($programs === []) {
            return;
        }

        $now = now();

        foreach ($programs as $index => $program) {
            $programId = DonationProgram::insertGetId([
                'organization_id' => $organization->id,
                'name' => $program['name'],
                'slug' => str($program['name'])->slug().'-'.$organization->id,
                'description' => $program['description'],
                'target_amount' => $program['target_amount'],
                'cover_photo' => self::S3.$program['photo'],
                'starts_at' => $program['starts_at'],
                'ends_at' => $program['ends_at'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            if ($program['transactions'] === null) {
                self::seedWakafLedger($programId);

                continue;
            }

            if ($program['transactions'] === 0) {
                continue;
            }

            $donorNames = ['Hamba Allah', 'Bapak Suhartono', 'Ibu Aminah', 'Keluarga Muslim', 'Hamba Allah'];
            // Spread across the program's own date range so donated_at values look organic
            // rather than all landing on the same seeding timestamp.
            $spanDays = max(1, (int) $program['starts_at']->diffInDays($program['ends_at']->min($now)));

            // Amounts are apportioned out of the program's intended total rather than drawn
            // independently: random per-donation amounts made the flagship Rp1.17bn appeal show
            // ~3% while a small Rp20m one overshot to 100%. Each donation gets a random share of
            // the total (so the ledger looks hand-entered), rounded down to the nearest 10k like
            // a real transfer, with the largest one absorbing the rounding remainder - which
            // keeps the headline percentage exact without leaving a stray Rp 1 row behind.
            $collectedTarget = (int) round($program['target_amount'] * $program['percent'] / 100);
            $count = $program['transactions'];

            $weights = array_map(fn () => fake()->numberBetween(40, 160), range(1, $count));
            $weightTotal = array_sum($weights);

            $amounts = array_map(
                fn (int $weight) => max(10_000, (intdiv((int) round($collectedTarget * $weight / $weightTotal), 10_000)) * 10_000),
                $weights,
            );

            $largestIndex = array_search(max($amounts), $amounts, true);
            $amounts[$largestIndex] += $collectedTarget - array_sum($amounts);

            $rows = array_map(fn (int $i) => [
                'donation_program_id' => $programId,
                'donor_name' => $donorNames[$i % count($donorNames)],
                'amount' => $amounts[$i],
                'donated_at' => $program['starts_at']->copy()->addDays($i % $spanDays),
                'created_at' => $now,
                'updated_at' => $now,
            ], range(0, $count - 1));

            DonationTransaction::insert($rows);
        }
    }

    /**
     * Monthly books ported from the nurul-huda project's FinancialReportSeeder: income is
     * split into daily Infak Subuh and per-Friday Infak Jum'at (so the totals scale with the
     * actual length of each month), while operational spend is drawn as one figure and then
     * apportioned - Listrik on its own range, the rest by fixed shares. That's what makes the
     * numbers read like real books rather than four unrelated random figures.
     *
     * Not plan-limited (see OrganizationFinancialReportController's doc comment).
     */
    /**
     * The wakaf appeal's real ledger: an opening balance row plus 358 donor-by-donor entries
     * transcribed from the mosque's own WhatsApp financial reports (database/data/
     * nurul-huda-wakaf-transactions.json, extracted from that project's seeder). Kept as a data
     * file rather than inlined here because it dwarfs the rest of this class, and loaded lazily
     * so organizations on every other template never pay to read it.
     *
     * Using the real ledger is also what makes the headline figure honest: it sums to exactly
     * the Rp 437.682.000 the live site reports, instead of a percentage worked backwards from
     * the target.
     */
    private static function seedWakafLedger(int $programId): void
    {
        $now = now();

        $rows = [[
            'donation_program_id' => $programId,
            'donor_name' => 'Saldo Wakaf Bulan April 2025',
            'amount' => self::WAKAF_OPENING_BALANCE,
            'donated_at' => '2025-04-30',
            'created_at' => $now,
            'updated_at' => $now,
        ]];

        $path = database_path('data/nurul-huda-wakaf-transactions.json');

        foreach (json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR) as [$date, $donor, $amount]) {
            $rows[] = [
                'donation_program_id' => $programId,
                'donor_name' => $donor,
                'amount' => $amount,
                'donated_at' => $date,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            DonationTransaction::insert($chunk);
        }
    }

    private static function seedFinancialReports(Organization $organization): void
    {
        if ($organization->financialReports()->exists()) {
            return;
        }

        $now = now();
        $rows = [];

        foreach (range(self::BOOKS_MONTHS_BACK, 1) as $monthsAgo) {
            $period = $now->copy()->subMonths($monthsAgo)->startOfMonth();
            $daysInMonth = $period->daysInMonth;
            $fridayCount = self::countFridays($period);

            $infakSubuh = 0;
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $infakSubuh += fake()->numberBetween(150_000, 190_000);
            }

            $infakJumat = 0;
            for ($i = 0; $i < $fridayCount; $i++) {
                $infakJumat += fake()->numberBetween(1_000_000, 1_500_000);
            }

            $base = [
                'organization_id' => $organization->id,
                'period_month' => $period->month,
                'period_year' => $period->year,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $rows[] = [...$base, 'type' => 'income', 'category' => 'Infak Subuh', 'amount' => $infakSubuh];
            $rows[] = [...$base, 'type' => 'income', 'category' => "Infak Jum'at", 'amount' => $infakJumat];

            foreach (self::splitOperationalExpense(fake()->numberBetween(7_000_000, 8_000_000)) as $category => $amount) {
                $rows[] = [...$base, 'type' => 'expense', 'category' => $category, 'amount' => $amount];
            }
        }

        FinancialReport::insert($rows);
    }

    /**
     * Splits one month's operational spend across categories: Listrik is drawn on its own
     * range (it's metered, not proportional to anything else), and the remainder is divided by
     * fixed shares, with the last category absorbing the rounding remainder so the parts always
     * sum back to the total.
     *
     * @return array<string, int>
     */
    private static function splitOperationalExpense(int $totalExpense): array
    {
        $listrik = fake()->numberBetween(2_900_000, 3_500_000);
        $remaining = $totalExpense - $listrik;

        $categories = array_keys(self::OPERATIONAL_EXPENSE_SHARES);
        $lastCategory = end($categories);

        $amounts = ['Listrik' => $listrik];
        $allocated = 0;

        foreach (self::OPERATIONAL_EXPENSE_SHARES as $category => $share) {
            if ($category === $lastCategory) {
                continue;
            }

            $amount = (int) round($remaining * $share, -3);
            $amounts[$category] = $amount;
            $allocated += $amount;
        }

        $amounts[$lastCategory] = $remaining - $allocated;

        return $amounts;
    }

    private static function countFridays(Carbon $period): int
    {
        $count = 0;
        $cursor = $period->copy()->startOfMonth();
        $end = $period->copy()->endOfMonth();

        while ($cursor->lte($end)) {
            if ($cursor->isFriday()) {
                $count++;
            }

            $cursor->addDay();
        }

        return $count;
    }
}
