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
use App\Services\Samples\KlinikAisyiyahAmbuluSamples;
use App\Services\Samples\PcaAmbuluSamples;
use App\Services\Samples\PcmAmbuluSamples;
use App\Services\Samples\PortraitPhotos;
use App\Services\Samples\SuaraMuhammadiyahAmbuluSamples;
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
 * Every sample count below is capped to MAX_SAMPLES_PER_RESOURCE and, on top of that, to the
 * organization's own plan limit (see
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
     * Hard ceiling on how many sample rows any single CMS resource gets when a template is
     * cloned into a REAL organization, applied on top of (never instead of) the plan limit -
     * sampleCount() takes the smaller of the two. Skipped entirely for a sandbox organization -
     * see sampleCount()'s doc comment.
     *
     * The showcase templates carry far longer lists than a starting point needs - Suara
     * Muhammadiyah's timRedaksi() alone is 16 officers - and a plan generous enough to accept
     * all of them (Professional allows 20) meant a new organization opened its builder facing a
     * wall of someone else's content to delete before it could enter its own. Three is enough to
     * show what a section looks like filled in, in every section's layout, while staying small
     * enough to clear out.
     *
     * A resource whose plan limit is TIGHTER than this keeps its limit (Starter allows 2
     * announcements, 1 donation program), so cloning still can't leave an organization in
     * violation of its own plan - see Organization::planViolations().
     */
    private const MAX_SAMPLES_PER_RESOURCE = 3;

    /**
     * Template slugs this seeder has faithful, named sample data for (ported from the
     * standalone nurul-huda project's MasjidContentSeeder) - seedAgendas()/seedOfficers()
     * use these in place of their generic "Contoh ..." placeholders, so every other template's
     * samples are unaffected.
     *
     * A LIST, like the other showcases: matching one slug left the standar tier falling through
     * to the generic `[Nama Ketua]` officers and "Contoh Agenda" kajian, even though both tiers
     * describe the same masjid.
     *
     * @var array<int, string>
     */
    private const NURUL_HUDA_TEMPLATE_SLUGS = [
        'masjid-nurul-huda-eksklusif',
        'masjid-nurul-huda-standar',
    ];

    /**
     * The Klinik Pratama Aisyiyah Ambulu showcase (see KlinikAisyiyahAmbuluTemplateSeeder and
     * App\Services\Samples\KlinikAisyiyahAmbuluSamples, the actual source of its content).
     * Organizations on either of these templates get the clinic's real services/announcements/
     * gallery instead of the generic "Contoh Berita Kegiatan" / "Layanan Administrasi"
     * placeholders - exactly what this showcase organization exists to avoid.
     *
     * @var array<int, string>
     */
    private const KLINIK_TEMPLATE_SLUGS = [
        KlinikAisyiyahAmbuluSamples::TEMPLATE_SLUG,
        'klinik-aisyiyah-ambulu-standar',
    ];

    /**
     * The Suara Muhammadiyah Ambulu news-portal showcase (see
     * SuaraMuhammadiyahAmbuluTemplateSeeder and App\Services\Samples\
     * SuaraMuhammadiyahAmbuluSamples). Organizations on either template get the outlet's real
     * news stories and 16-member editorial team instead of generic placeholders.
     *
     * @var array<int, string>
     */
    private const SUARA_MUHAMMADIYAH_TEMPLATE_SLUGS = [
        SuaraMuhammadiyahAmbuluSamples::TEMPLATE_SLUG,
        'suara-muhammadiyah-ambulu-standar',
    ];

    /**
     * The PCM Ambulu cabang-profile showcase (see PcmAmbuluTemplateSeeder,
     * PcmAmbuluEksklusifTemplateSeeder, and App\Services\Samples\PcmAmbuluSamples).
     *
     * Every showcase here is a LIST of slugs because each organization now has both a standard
     * and an exclusive template built from the same Samples class. Matching on a single slug -
     * which is what these were before - silently sent the second tier down the `default => null`
     * branch, so an organization on it got generic "Contoh Berita" placeholders instead of the
     * real content the tier it paid for was supposed to showcase.
     *
     * @var array<int, string>
     */
    private const PCM_TEMPLATE_SLUGS = [
        PcmAmbuluSamples::TEMPLATE_SLUG,
        'pcm-ambulu-eksklusif',
    ];

    /**
     * The PCA Ambulu cabang-Aisyiyah showcase (see PcaAmbuluTemplateSeeder,
     * PcaAmbuluEksklusifTemplateSeeder, and App\Services\Samples\PcaAmbuluSamples). The
     * standard-tier one targets the STARTER plan specifically, so its sample lists are the ones
     * most often truncated here and are ordered with that in mind (see PcaAmbuluSamples'
     * per-method notes on which entries survive which quota).
     *
     * @var array<int, string>
     */
    private const PCA_TEMPLATE_SLUGS = [
        PcaAmbuluSamples::TEMPLATE_SLUG,
        'pca-ambulu-eksklusif',
    ];

    /**
     * Sample imagery for this template is hotlinked from the live Masjid Nurul Huda Ambulu
     * site's own S3 bucket (each URL checked to return 200), so a fresh organization previews
     * the actual mosque instead of stand-in stock photography. Every section guards on an
     * empty photo, so if the source ever moves a file the card degrades rather than breaking.
     */
    private const S3 = 'https://s3.nurul-huda.ambulu.or.id';

    /**
     * Kajian flyers, in the same order as nurulHudaKajianSamples(). Per-ORGANIZATION uploads on
     * the template's sandbox rather than the S3 bucket above, so they 404 if that sandbox's
     * storage is cleared - the poster grid then degrades to agenda/poster's date-block fallback
     * rather than breaking.
     *
     * @var array<int, string>
     */
    private const NURUL_HUDA_KAJIAN_POSTERS = [
        'https://storage.ambulu.or.id/organizations/9/agenda/ab6e857b-3878-419d-9937-77abe8c447ec.webp',
        'https://storage.ambulu.or.id/organizations/9/agenda/21746fb4-ba32-49c6-84fd-d89c1a137ad9.webp',
        'https://storage.ambulu.or.id/organizations/9/agenda/dea3e455-d528-4f90-a261-800238653801.webp',
        'https://storage.ambulu.or.id/organizations/9/agenda/b8e2af80-450c-4dad-9263-90c7d0e00778.webp',
    ];

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
        $slug = $organization->template?->slug;
        $isNurulHuda = in_array($slug, self::NURUL_HUDA_TEMPLATE_SLUGS, true);
        $isKlinik = in_array($slug, self::KLINIK_TEMPLATE_SLUGS, true);
        $isSuaraMuhammadiyah = in_array($slug, self::SUARA_MUHAMMADIYAH_TEMPLATE_SLUGS, true);
        $isPcm = in_array($slug, self::PCM_TEMPLATE_SLUGS, true);
        $isPca = in_array($slug, self::PCA_TEMPLATE_SLUGS, true);

        if (in_array('daftar-berita', $keys, true)) {
            $postSamples = match (true) {
                $isKlinik => KlinikAisyiyahAmbuluSamples::beritaItems(),
                $isSuaraMuhammadiyah => SuaraMuhammadiyahAmbuluSamples::beritaItems(),
                $isPcm => PcmAmbuluSamples::beritaItems(),
                $isPca => PcaAmbuluSamples::beritaItems(),
                default => null,
            };

            self::seedPosts($organization, $limits, $postSamples);
        }

        if (in_array('pengumuman', $keys, true)) {
            self::seedAnnouncements($organization, $limits, $isKlinik ? KlinikAisyiyahAmbuluSamples::pengumumanItems() : null);
        }

        if (in_array('agenda', $keys, true)) {
            $agendaSamples = match (true) {
                $isNurulHuda => self::nurulHudaKajianSamples(),
                $isPcm => PcmAmbuluSamples::agendaItems(),
                $isPca => PcaAmbuluSamples::agendaItems(),
                default => null,
            };

            self::seedAgendas($organization, $limits, $agendaSamples);
        }

        if (in_array('galeri', $keys, true)) {
            $gallerySamples = match (true) {
                $isNurulHuda => self::nurulHudaGallerySamples(),
                $isKlinik => self::toGalleryPhotoSamples(KlinikAisyiyahAmbuluSamples::ruanganPhotos()),
                $isPca => self::toGalleryPhotoSamples(PcaAmbuluSamples::kegiatanPhotos()),
                default => null,
            };

            self::seedGalleryPhotos($organization, $limits, $gallerySamples);
        }

        if (in_array('struktur-pengurus', $keys, true)) {
            $officerSamples = match (true) {
                $isNurulHuda => self::nurulHudaOfficerSamples(),
                $isSuaraMuhammadiyah => SuaraMuhammadiyahAmbuluSamples::timRedaksi(),
                $isPcm => PcmAmbuluSamples::pimpinanHarian(),
                $isPca => PcaAmbuluSamples::pimpinanCabang(),
                default => null,
            };

            // Stand-in portraits on a SANDBOX only. struktur-pengurus renders an empty grey
            // square when photo is null, so without this the template designer (and the
            // "Gunakan Template" preview built from it) showed a wall of blank boxes - the very
            // thing the sandbox exists to let an admin look at. A real organization keeps null:
            // its officer cards should stay empty until it uploads genuine headshots, since a
            // stock face attached to a named person on a live site would misrepresent them.
            // See PortraitPhotos for why the faces are assigned by index, never by name.
            if ($officerSamples !== null && $organization->is_sandbox) {
                $officerSamples = PortraitPhotos::applyTo($officerSamples);
            }

            self::seedOfficers($organization, $limits, $officerSamples);
        }

        if (in_array('jaringan-aum-ortom', $keys, true)) {
            self::seedNetworks($organization, match (true) {
                $isPcm => PcmAmbuluSamples::jaringanItems(),
                $isPca => PcaAmbuluSamples::jaringanItems(),
                default => null,
            });
        }

        // Both section keys are handled in ONE call: 'program' and 'layanan' share a single
        // 'programs' plan quota, and seedPrograms() guards on that combined count, so seeding
        // them as two independent calls meant whichever ran second was skipped outright - a
        // page with both sections (e.g. the exclusive clinic template) rendered its 'layanan'
        // section empty on the live site. Passing both types lets the one call split the
        // available quota between them instead.
        $requestedPrograms = [];

        if (in_array('program-unggulan', $keys, true)) {
            $requestedPrograms['program'] = match (true) {
                $isKlinik => KlinikAisyiyahAmbuluSamples::programItems(),
                $isPcm => PcmAmbuluSamples::programItems(),
                $isPca => PcaAmbuluSamples::programItems(),
                default => null,
            };
        }

        if (in_array('layanan', $keys, true)) {
            $requestedPrograms['layanan'] = $isKlinik ? KlinikAisyiyahAmbuluSamples::layananItems() : null;
        }

        self::seedPrograms($organization, $limits, $requestedPrograms);

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
     * How many of a fixed sample list to actually insert: the smallest of the list's own length,
     * MAX_SAMPLES_PER_RESOURCE, and the organization's plan limit for that resource key (null =
     * unlimited, so only the first two apply). Never negative - a limit of 0 (or an organization
     * already somehow past it) yields 0, i.e. skip entirely.
     *
     * MAX_SAMPLES_PER_RESOURCE itself is skipped for a sandbox organization (TemplateSandboxService)
     * - that cap exists to keep a real, brand-new organization's builder from opening to a wall of
     * someone else's content (see this class's doc comment), which doesn't apply to a sandbox: its
     * whole purpose is to let an admin see/edit the template's full intended content, and it's
     * exactly what TemplatePreviewController now renders for public template-catalog browsing too
     * (see that controller's doc comment) - both need the complete sample list, not the trimmed
     * onboarding version. UNLIMITED_KEYS already frees the sandbox from its plan limit; this frees
     * it from the separate MAX_SAMPLES_PER_RESOURCE ceiling that plan limit alone didn't cover.
     */
    private static function sampleCount(Organization $organization, PlanLimitService $limits, string $key, int $available): int
    {
        $limit = $limits->effectiveLimit($organization, $key);

        if ($organization->is_sandbox) {
            $ceiling = $limit ?? $available;
        } else {
            $ceiling = $limit === null
                ? self::MAX_SAMPLES_PER_RESOURCE
                : min($limit, self::MAX_SAMPLES_PER_RESOURCE);
        }

        return max(0, min($available, $ceiling));
    }

    /**
     * @param  array<int, array{title: string, category: string, body: string}>|null  $customSamples
     */
    private static function seedPosts(Organization $organization, PlanLimitService $limits, ?array $customSamples = null): void
    {
        if ($organization->posts()->exists()) {
            return;
        }

        $samples = $customSamples ?? [
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
            // Every Samples::beritaItems() entry already carries a matching photo, and
            // daftar-berita's $organization branch reads Post::image - so dropping it here left
            // the builder (and every real organization) showing image-less cards while the
            // template PREVIEW, which reads content['items'] instead, showed them fine. The
            // generic fallback samples above have no image, hence the null coalesce.
            'image' => $sample['image'] ?? null,
            'body' => '<p>'.$sample['body'].'</p>',
            'status' => PublishStatus::Published->value,
            'published_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ], $samples, array_keys($samples)));
    }

    /**
     * @param  array<int, array{title: string, priority: string, body?: string, ongoing?: bool}>|null  $customSamples
     */
    private static function seedAnnouncements(Organization $organization, PlanLimitService $limits, ?array $customSamples = null): void
    {
        if ($organization->announcements()->exists()) {
            return;
        }

        $samples = $customSamples ?? [
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
            'body' => '<p>'.($sample['body'] ?? 'Isi pengumuman akan tampil di sini. Edit atau hapus contoh ini kapan saja.').'</p>',
            'priority' => $sample['priority'],
            // 'ongoing' => true (e.g. standing clinic operating-hours notices) leaves this
            // unset - pengumuman/standar.blade.php only shows "Berlaku hingga ..." when
            // valid_until is present, so an announcement that never expires doesn't render a
            // misleading date.
            'valid_until' => ($sample['ongoing'] ?? false) ? null : $now->copy()->addMonth(),
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
            // agenda/poster.blade.php renders agendas.poster as a flyer grid; without this a
            // poster-variant section fell back to the date block for every row. Optional: the
            // generic samples and agenda/standar have no flyer to show.
            'poster' => $sample['poster'] ?? null,
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
     * `photo` is either a path relative to this class's S3 constant (the nurul-huda samples,
     * which all live in that one bucket) or an absolute http(s) URL, so a template whose
     * imagery isn't hosted there can supply its own.
     *
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
            'url' => match (true) {
                ! $customSamples => self::PLACEHOLDER_PHOTO_URL,
                str_starts_with($customSamples[$index]['photo'], 'http') => $customSamples[$index]['photo'],
                default => self::S3.$customSamples[$index]['photo'],
            },
            'caption' => $customSamples[$index]['caption'] ?? 'Foto kegiatan '.($index + 1),
            'order' => $index,
            'created_at' => $now,
            'updated_at' => $now,
        ], range(0, $count - 1)));
    }

    /**
     * Adapts a template's `galeri` section content shape ({image, caption} - see
     * templates/sections/galeri/standar.blade.php) to seedGalleryPhotos()'s custom-sample
     * shape ({photo, caption}), so a sample list defined once (e.g.
     * KlinikAisyiyahAmbuluSamples::ruanganPhotos()) can feed both the template's own preview
     * content and the organization's real GalleryPhoto records without being duplicated in
     * two different key shapes.
     *
     * @param  array<int, array{image: string, caption: string}>  $items
     * @return array<int, array{photo: string, caption: string}>
     */
    private static function toGalleryPhotoSamples(array $items): array
    {
        return array_map(fn (array $item) => ['photo' => $item['image'], 'caption' => $item['caption']], $items);
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
     * per-plan quota, unlike the CMS resources above), so it can't go through sampleCount().
     * MAX_SAMPLES_PER_RESOURCE is still applied directly - the showcase lists run to five and six
     * entries, and leaving the one resource without a quota as the only one cloning in full is
     * the inconsistency the cap exists to remove.
     *
     * @param  array<int, array{name: string, type?: string|null}>|null  $customSamples
     */
    private static function seedNetworks(Organization $organization, ?array $customSamples = null): void
    {
        if ($organization->networks()->exists()) {
            return;
        }

        $samples = $customSamples ?? array_map(
            fn (int $index) => ['name' => '[Nama AUM/Ortom '.$index.']', 'type' => null],
            range(1, 3),
        );

        $samples = array_slice($samples, 0, self::MAX_SAMPLES_PER_RESOURCE);

        if ($samples === []) {
            return;
        }

        $now = now();

        OrganizationNetwork::insert(array_map(fn ($sample, $index) => [
            'organization_id' => $organization->id,
            'name' => $sample['name'],
            'type' => $sample['type'] ?? null,
            'order' => $index,
            'created_at' => $now,
            'updated_at' => $now,
        ], $samples, array_keys($samples)));
    }

    /**
     * Seeds sample programs for every requested type in one pass.
     *
     * `$requested` maps each wanted Program type ('program' and/or 'layanan') to its custom
     * sample list, or null to use that type's generic placeholders. Both types are handled
     * together because they share ONE plan quota: 'programs' is the PlanLimitService key for
     * both (see RESOURCE_RELATIONS and Organization::programs(), which counts them together),
     * so the budget has to be divided between them rather than each type claiming it in full.
     *
     * The quota is split evenly, with any remainder going to the earlier type, and any share a
     * type doesn't use (its sample list being shorter than its share) returned to the pool for
     * the other - so a 5-program budget across 3 'program' + 6 'layanan' samples seeds all 3
     * programs and 2 services, rather than 3 and none.
     *
     * @param  array<string, array<int, array{title: string, description: string, icon: string}>|null>  $requested
     */
    private static function seedPrograms(Organization $organization, PlanLimitService $limits, array $requested): void
    {
        // Guards on the combined resource, not ofType() per type: an organization that already
        // has programs from a previous template (switching templates via
        // OrganizationTemplateController drops pages/sections but never deletes CMS records)
        // would otherwise be pushed past its own plan's limit by a second round of samples.
        if ($requested === [] || $organization->programs()->exists()) {
            return;
        }

        $defaults = [
            'program' => [
                ['title' => 'Program Unggulan 1', 'description' => 'Deskripsi singkat program unggulan pertama.', 'icon' => '⭐'],
                ['title' => 'Program Unggulan 2', 'description' => 'Deskripsi singkat program unggulan kedua.', 'icon' => '🎯'],
                ['title' => 'Program Unggulan 3', 'description' => 'Deskripsi singkat program unggulan ketiga.', 'icon' => '🚀'],
            ],
            'layanan' => [
                ['title' => 'Layanan Konsultasi', 'description' => 'Konsultasi dan pendampingan bagi masyarakat.', 'icon' => '🗣️'],
                ['title' => 'Layanan Administrasi', 'description' => 'Pengurusan surat dan dokumen organisasi.', 'icon' => '📄'],
                ['title' => 'Layanan Sosial', 'description' => 'Bantuan dan pemberdayaan bagi warga kurang mampu.', 'icon' => '❤️'],
            ],
        ];

        $pools = [];

        foreach ($requested as $type => $customSamples) {
            $pools[$type] = $customSamples ?? $defaults[$type] ?? [];
        }

        $budget = self::sampleCount(
            $organization,
            $limits,
            'programs',
            array_sum(array_map('count', $pools)),
        );

        $rows = [];
        $now = now();
        $remaining = count($pools);

        foreach ($pools as $type => $samples) {
            // Ceil so the remainder of an uneven split goes to the earlier type rather than
            // being lost; whatever this type leaves unused stays in $budget for the next one.
            $share = min(count($samples), (int) ceil($budget / max(1, $remaining)));
            $budget -= $share;
            $remaining--;

            // array_slice reindexes from 0, so `order` restarts per type - each type is
            // queried and rendered on its own (Program::ofType()), so their orderings are
            // independent sequences rather than one shared run.
            foreach (array_values(array_slice($samples, 0, $share)) as $index => $sample) {
                $rows[] = [
                    'organization_id' => $organization->id,
                    'type' => $type,
                    'title' => $sample['title'],
                    'description' => $sample['description'],
                    'icon' => $sample['icon'],
                    'order' => $index,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        if ($rows !== []) {
            Program::insert($rows);
        }
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
            // The flyers the takmir uploaded, same files MasjidNurulHudaTemplateSeeder writes
            // into the template's own content['items'] - so the builder's poster grid matches
            // the template preview instead of falling back to a date block for every row.
            'poster' => self::NURUL_HUDA_KAJIAN_POSTERS[$index] ?? null,
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
