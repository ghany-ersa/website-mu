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
 */
class CmsSampleDataSeeder
{
    public static function seed(Organization $organization, array $sectionKeys): void
    {
        $keys = array_unique($sectionKeys);

        if (in_array('daftar-berita', $keys, true)) {
            self::seedPosts($organization);
        }

        if (in_array('pengumuman', $keys, true)) {
            self::seedAnnouncements($organization);
        }

        if (in_array('agenda', $keys, true) || in_array('jadwal-kajian', $keys, true)) {
            self::seedAgendas($organization);
        }

        if (in_array('galeri', $keys, true)) {
            self::seedGalleryPhotos($organization);
        }

        if (in_array('struktur-pengurus', $keys, true)) {
            self::seedOfficers($organization);
        }

        if (in_array('jaringan-aum-ortom', $keys, true)) {
            self::seedNetworks($organization);
        }

        if (in_array('program-unggulan', $keys, true)) {
            self::seedPrograms($organization, 'program');
        }

        if (in_array('layanan', $keys, true)) {
            self::seedPrograms($organization, 'layanan');
        }
    }

    private static function seedPosts(Organization $organization): void
    {
        if ($organization->posts()->exists()) {
            return;
        }

        $samples = [
            ['title' => 'Contoh Berita Kegiatan', 'category' => 'Kegiatan', 'excerpt' => 'Ringkasan singkat berita akan tampil di sini. Edit atau hapus contoh ini kapan saja.'],
            ['title' => 'Contoh Berita Pengumuman Program', 'category' => 'Program', 'excerpt' => 'Ringkasan singkat berita akan tampil di sini. Edit atau hapus contoh ini kapan saja.'],
            ['title' => 'Contoh Berita Sosial Kemasyarakatan', 'category' => 'Sosial', 'excerpt' => 'Ringkasan singkat berita akan tampil di sini. Edit atau hapus contoh ini kapan saja.'],
        ];

        $now = now();

        Post::insert(array_map(fn ($sample, $index) => [
            'organization_id' => $organization->id,
            'title' => $sample['title'],
            'slug' => str($sample['title'])->slug().'-'.$organization->id.'-'.$index,
            'category' => $sample['category'],
            'excerpt' => $sample['excerpt'],
            'body' => $sample['excerpt'],
            'status' => PublishStatus::Published->value,
            'published_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ], $samples, array_keys($samples)));
    }

    private static function seedAnnouncements(Organization $organization): void
    {
        if ($organization->announcements()->exists()) {
            return;
        }

        $samples = [
            ['title' => 'Contoh Pengumuman Penting', 'priority' => 'Tinggi'],
            ['title' => 'Contoh Pengumuman Kegiatan', 'priority' => 'Sedang'],
            ['title' => 'Contoh Pengumuman Umum', 'priority' => 'Rendah'],
        ];

        $now = now();

        Announcement::insert(array_map(fn ($sample) => [
            'organization_id' => $organization->id,
            'title' => $sample['title'],
            'body' => 'Isi pengumuman akan tampil di sini. Edit atau hapus contoh ini kapan saja.',
            'priority' => $sample['priority'],
            'valid_until' => $now->copy()->addMonth(),
            'status' => PublishStatus::Published->value,
            'created_at' => $now,
            'updated_at' => $now,
        ], $samples));
    }

    private static function seedAgendas(Organization $organization): void
    {
        if ($organization->agendas()->exists()) {
            return;
        }

        $samples = [
            ['title' => 'Contoh Agenda Kegiatan', 'days' => 7],
            ['title' => 'Contoh Rapat Koordinasi', 'days' => 14],
            ['title' => 'Contoh Kegiatan Sosial', 'days' => 21],
        ];

        $now = now();

        Agenda::insert(array_map(fn ($sample) => [
            'organization_id' => $organization->id,
            'title' => $sample['title'],
            'starts_at' => $now->copy()->addDays($sample['days'])->setTime(8, 0),
            'location' => 'Lokasi kegiatan',
            'description' => 'Deskripsi agenda akan tampil di sini. Edit atau hapus contoh ini kapan saja.',
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

    private static function seedGalleryPhotos(Organization $organization): void
    {
        if ($organization->photos()->exists()) {
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
        ], range(1, 4)));
    }

    private static function seedOfficers(Organization $organization): void
    {
        if ($organization->officers()->exists()) {
            return;
        }

        $roles = ['Ketua', 'Sekretaris', 'Bendahara', 'Anggota'];
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

    private static function seedPrograms(Organization $organization, string $type): void
    {
        if ($organization->programs()->ofType($type)->exists()) {
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
