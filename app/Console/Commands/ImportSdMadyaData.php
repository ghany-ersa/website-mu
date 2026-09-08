<?php

namespace App\Console\Commands;

use App\Models\Organization;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PDO;

/**
 * One-shot import of the standalone manajemen-SD-Madya database (the Filament admin that backs
 * the comprof-SD-Madya Nuxt site) into a website-mu organization.
 *
 * This is a migration aid, not a sync: run it once when moving a school onto website-mu. Running
 * it again updates the rows it created rather than duplicating them, keyed by slug (students,
 * teachers) or title (achievements, activities, galleries), so a re-run after fixing source data
 * is safe.
 *
 * Mapping, per the port's design decisions:
 *   teachers    -> officers (with the school columns: subject, bio, email, phone)
 *   activities  -> programs, type "kegiatan"
 *   galleries   -> gallery_photos
 *   students    -> students
 *   achievements-> achievements, plus achievement_student for the many-to-many
 *
 * The source's English enums are mapped to the Indonesian values this app uses (see
 * Achievement::CATEGORIES / ::LEVELS and Program::CATEGORIES). Its `photo`/`image` columns hold
 * paths relative to that project's storage/, so --asset-base prefixes them into absolute URLs;
 * without it the paths are stored as-is and images won't resolve.
 */
class ImportSdMadyaData extends Command
{
    protected $signature = 'import:sd-madya
        {--database= : Path to the source SQLite file (manajemen-SD-Madya/database/database.sqlite)}
        {--organization= : Target organization slug}
        {--asset-base= : URL prefix for photo/image paths, e.g. https://admin.sekolah.sch.id/storage}
        {--dry-run : Report what would be imported without writing anything}';

    protected $description = 'Import students, teachers, achievements, activities and galleries from a manajemen-SD-Madya database';

    /**
     * Source category/level values -> the ones this app stores. Anything not listed falls
     * through as-is, so an unexpected value is preserved rather than silently dropped.
     *
     * @var array<string, string>
     */
    private const VALUE_MAP = [
        // achievements.category
        'ACADEMIC' => 'Akademik', 'AKADEMIK' => 'Akademik',
        'SPORT' => 'Olahraga', 'OLAHRAGA' => 'Olahraga',
        'ART' => 'Seni', 'SENI' => 'Seni',
        'OTHER' => 'Lainnya', 'LAINNYA' => 'Lainnya',
        // achievements.level
        'District Level' => 'Tingkat Kecamatan',
        'Regency Level' => 'Tingkat Kabupaten',
        'Provincial Level' => 'Tingkat Provinsi',
        'National Level' => 'Tingkat Nasional',
        // activities.category and galleries.category
        'Religious' => 'Keagamaan',
        'Extracurricular' => 'Ekstrakurikuler',
        'Academic' => 'Akademik',
        'Social' => 'Sosial',
        'Activity' => 'Kegiatan',
        'Achievement' => 'Prestasi',
        'Facility' => 'Fasilitas',
        'Other' => 'Lainnya',
    ];

    public function handle(): int
    {
        $path = (string) $this->option('database');
        $slug = (string) $this->option('organization');

        if ($path === '' || $slug === '') {
            $this->error('Both --database and --organization are required.');

            return self::FAILURE;
        }

        if (! is_file($path)) {
            $this->error("Source database not found: {$path}");

            return self::FAILURE;
        }

        $organization = Organization::where('slug', $slug)->first();

        if (! $organization) {
            $this->error("Organization not found: {$slug}");

            return self::FAILURE;
        }

        $source = new PDO('sqlite:'.$path, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $dryRun = (bool) $this->option('dry-run');

        $this->info(($dryRun ? '[DRY RUN] ' : '')."Importing into {$organization->name} ({$organization->slug})");

        // One transaction for the whole import: a half-migrated school - achievements whose
        // pupils never landed - is worse than none at all.
        DB::beginTransaction();

        try {
            $counts = [
                'Pengajar (officers)' => $this->importTeachers($source, $organization),
                'Kegiatan (programs)' => $this->importActivities($source, $organization),
                'Galeri (gallery_photos)' => $this->importGalleries($source, $organization),
                'Siswa (students)' => $this->importStudents($source, $organization),
                'Prestasi (achievements)' => $this->importAchievements($source, $organization),
            ];

            $this->newLine();
            $this->table(['Entitas', 'Jumlah'], collect($counts)->map(fn ($count, $label) => [$label, $count])->values());

            if ($dryRun) {
                DB::rollBack();
                $this->warn('Dry run - nothing was written.');

                return self::SUCCESS;
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Import failed, nothing was written: '.$e->getMessage());

            return self::FAILURE;
        }

        $this->info('Selesai.');

        return self::SUCCESS;
    }

    private function importTeachers(PDO $source, Organization $organization): int
    {
        $rows = $this->rows($source, 'teachers');

        foreach ($rows as $index => $row) {
            $organization->officers()->updateOrCreate(
                ['slug' => $row['slug']],
                [
                    'name' => $row['name'],
                    // role is NOT NULL here but position is nullable in the source.
                    'role' => $row['position'] ?: 'Guru',
                    'subject' => $row['subject'] ?? null,
                    'bio' => $row['bio'] ?? null,
                    'photo' => $this->asset($row['photo'] ?? null),
                    'email' => $row['email'] ?? null,
                    'phone' => $row['phone'] ?? null,
                    'is_active' => (bool) ($row['is_active'] ?? true),
                    'order' => $index,
                ]
            );
        }

        return count($rows);
    }

    private function importActivities(PDO $source, Organization $organization): int
    {
        $rows = $this->rows($source, 'activities');

        foreach ($rows as $index => $row) {
            $organization->programs()->updateOrCreate(
                ['type' => 'kegiatan', 'title' => $row['title']],
                [
                    'description' => $row['description'] ?? null,
                    'image' => $this->asset($row['image'] ?? null),
                    'category' => $this->map($row['category'] ?? null),
                    'date' => $row['date'] ?? null,
                    'order' => $index,
                ]
            );
        }

        return count($rows);
    }

    private function importGalleries(PDO $source, Organization $organization): int
    {
        $rows = $this->rows($source, 'galleries');

        foreach ($rows as $index => $row) {
            $organization->photos()->updateOrCreate(
                ['title' => $row['title']],
                [
                    'url' => $this->asset($row['image'] ?? null) ?? '',
                    'caption' => $row['title'],
                    'description' => $row['description'] ?? null,
                    'category' => $this->map($row['category'] ?? null),
                    'is_active' => (bool) ($row['is_active'] ?? true),
                    'order' => $index,
                ]
            );
        }

        return count($rows);
    }

    private function importStudents(PDO $source, Organization $organization): int
    {
        $rows = $this->rows($source, 'students');

        foreach ($rows as $index => $row) {
            $organization->students()->updateOrCreate(
                ['slug' => $row['slug']],
                [
                    'name' => $row['name'],
                    // "Grade 6" in the source reads as "Kelas 6" to an Indonesian parent.
                    'grade' => $row['grade'] ? str_replace('Grade', 'Kelas', $row['grade']) : null,
                    'enrolled_year' => $row['enrolled_year'] ?? null,
                    'graduated_year' => $row['graduated_year'] ?? null,
                    'bio' => $row['bio'] ?? null,
                    'quote' => $row['quote'] ?? null,
                    'hobbies' => $row['hobbies'] ? (json_decode($row['hobbies'], true) ?: []) : [],
                    'photo' => $this->asset($row['photo'] ?? null),
                    'is_active' => (bool) ($row['is_active'] ?? true),
                    'order' => $index,
                ]
            );
        }

        return count($rows);
    }

    private function importAchievements(PDO $source, Organization $organization): int
    {
        $rows = $this->rows($source, 'achievements');

        // Source student id -> the slug we imported it under, so the pivot can be rebuilt
        // against this organization's own student ids.
        $slugBySourceId = collect($this->rows($source, 'students'))->pluck('slug', 'id');
        $studentIdBySlug = $organization->students()->pluck('id', 'slug');

        $pivot = collect($this->rows($source, 'achievement_student'))->groupBy('achievement_id');

        foreach ($rows as $index => $row) {
            $achievement = $organization->achievements()->updateOrCreate(
                ['title' => $row['title']],
                [
                    'category' => $this->map($row['category'] ?? null),
                    'level' => $this->map($row['level'] ?? null),
                    'rank' => $row['rank'] ?? null,
                    'year' => $row['year'] ?? null,
                    'description' => $row['description'] ?? null,
                    'image' => $this->asset($row['image'] ?? null),
                    'is_active' => (bool) ($row['is_active'] ?? true),
                    'order' => $index,
                ]
            );

            // Team events credit several pupils - the whole reason this is a pivot.
            $ids = ($pivot[$row['id']] ?? collect())
                ->map(fn (array $link) => $studentIdBySlug[$slugBySourceId[$link['student_id']] ?? null] ?? null)
                ->filter()
                ->values()
                ->all();

            $achievement->students()->sync($ids);
        }

        return count($rows);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function rows(PDO $source, string $table): array
    {
        return $source->query("select * from {$table}")->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    private function map(?string $value): ?string
    {
        return $value === null ? null : (self::VALUE_MAP[$value] ?? $value);
    }

    /**
     * Turns a source-relative storage path into an absolute URL. Values that are already URLs
     * pass through untouched, so re-running after a partial import doesn't double the prefix.
     */
    private function asset(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $base = rtrim((string) $this->option('asset-base'), '/');

        return $base === '' ? $path : $base.'/'.ltrim($path, '/');
    }
}
