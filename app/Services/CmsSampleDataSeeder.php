<?php

namespace App\Services;

use App\Enums\PublishStatus;
use App\Models\Agenda;
use App\Models\Announcement;
use App\Models\GalleryPhoto;
use App\Models\Officer;
use App\Models\Organization;
use App\Models\OrganizationNetwork;
use App\Models\Post;
use App\Models\Program;

/**
 * Seeds sample CMS records (posts, announcements, agendas, gallery photos, officers,
 * networks, programs/services) for a freshly created organization, one per CMS-backed
 * section key present on its home page — so the builder canvas and the organization's
 * own page (once it publishes) show real, editable content immediately instead of an
 * empty state, and templates/sections/*.blade.php never has to special-case "organization
 * exists but its CMS tables are still empty". Posts/announcements/agendas are seeded
 * already Published (not Draft) specifically so they clear their published()/published_at
 * scopes and actually render in the builder canvas, which queries CMS tables the same way
 * the public page does — there's no builder-only preview path to show drafts through.
 *
 * Only called once, right after Organization::seedPagesFromTemplate() clones the
 * template's sections — never touches an organization that already has any of a given
 * table's rows, so it can't clobber real content a user has since replaced or deleted.
 *
 * Every sample count below is capped to the organization's own plan limit (see
 * PlanLimitService::effectiveLimit()) — every organization is created on the Starter plan
 * (see OrganizationController::store()), whose limits are tighter than these samples'
 * original fixed counts (e.g. 3 announcements vs. Starter's limit of 2), so seeding the
 * fixed count unconditionally used to leave a brand-new organization already in violation
 * of its own plan (see Organization::planViolations()) before the owner had touched
 * anything. A limit of 0 skips that resource's samples entirely rather than seeding one
 * record a Starter org isn't allowed to have at all.
 */
class CmsSampleDataSeeder
{
    public static function seed(Organization $organization, array $sectionKeys): void
    {
        $keys = array_unique($sectionKeys);
        $limits = app(PlanLimitService::class);

        if (in_array('daftar-berita', $keys, true)) {
            self::seedPosts($organization, $limits);
        }

        if (in_array('pengumuman', $keys, true)) {
            self::seedAnnouncements($organization, $limits);
        }

        if (in_array('agenda', $keys, true) || in_array('jadwal-kajian', $keys, true)) {
            self::seedAgendas($organization, $limits);
        }

        if (in_array('galeri', $keys, true)) {
            self::seedGalleryPhotos($organization, $limits);
        }

        if (in_array('struktur-pengurus', $keys, true)) {
            self::seedOfficers($organization, $limits);
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
    }

    /**
     * How many of a fixed sample list to actually insert: the smaller of the list's own
     * length and the organization's plan limit for that resource key, or the full list when
     * the plan has no limit (null = unlimited). Never negative — a limit of 0 (or an
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

    private static function seedAgendas(Organization $organization, PlanLimitService $limits): void
    {
        if ($organization->agendas()->exists()) {
            return;
        }

        $samples = [
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
            'starts_at' => $now->copy()->addDays($sample['days'])->setTime(8, 0),
            'location' => 'Lokasi kegiatan',
            'description' => '<p>Deskripsi agenda akan tampil di sini. Edit atau hapus contoh ini kapan saja.</p>',
            'status' => PublishStatus::Published->value,
            'created_at' => $now,
            'updated_at' => $now,
        ], $samples));
    }

    /**
     * Flat gray placeholder (inline SVG data URI, no external request) — gallery_photos.url
     * is NOT NULL and there's no real photo to seed, so this stands in until the user
     * replaces it with an actual upload.
     */
    private const PLACEHOLDER_PHOTO_URL = 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="400" height="400"%3E%3Crect width="400" height="400" fill="%23e5e7eb"/%3E%3C/svg%3E';

    private static function seedGalleryPhotos(Organization $organization, PlanLimitService $limits): void
    {
        if ($organization->photos()->exists()) {
            return;
        }

        $count = self::sampleCount($organization, $limits, 'gallery_photos', 4);

        if ($count === 0) {
            return;
        }

        $now = now();

        GalleryPhoto::insert(array_map(fn ($index) => [
            'organization_id' => $organization->id,
            'url' => self::PLACEHOLDER_PHOTO_URL,
            'caption' => 'Foto kegiatan '.$index,
            'order' => $index,
            'created_at' => $now,
            'updated_at' => $now,
        ], range(1, $count)));
    }

    private static function seedOfficers(Organization $organization, PlanLimitService $limits): void
    {
        if ($organization->officers()->exists()) {
            return;
        }

        $roles = ['Ketua', 'Sekretaris', 'Bendahara', 'Anggota'];
        $roles = array_slice($roles, 0, self::sampleCount($organization, $limits, 'officers', count($roles)));

        if ($roles === []) {
            return;
        }

        $now = now();

        Officer::insert(array_map(fn ($role, $index) => [
            'organization_id' => $organization->id,
            'name' => '[Nama '.$role.']',
            'role' => $role,
            'order' => $index,
            'created_at' => $now,
            'updated_at' => $now,
        ], $roles, array_keys($roles)));
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
        // samples — e.g. after switching to a template with a different section via
        // OrganizationTemplateController, which drops pages/sections but never deletes CMS
        // records — would push the organization over its own plan's limit even though this
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
        // layanan aren't tracked separately there) — see PlanLimitService::RESOURCE_RELATIONS
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
}
