<?php

use App\Models\OrganizationType;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Renames three organization types to name the movement or institution rather than the tier
     * or the AUM category (see OrganizationTypeSeeder's doc comment for the reasoning):
     *
     *   pimpinan-cabang-muhammadiyah -> muhammadiyah        ('Muhammadiyah')
     *   pimpinan-cabang-aisyiyah     -> aisyiyah            ('Aisyiyah')
     *   aum-kesehatan                -> klinikrumah-sakit   ('Klinik/Rumah Sakit')
     *
     * This has to be a migration, not just a seeder edit: OrganizationTypeSeeder matches
     * existing rows by SLUG, so on any database that isn't rebuilt from scratch the reworded
     * entries create brand-new rows and strand the old ones - leaving every existing
     * organization and template still pointed at a type the picker no longer offers.
     *
     * Renaming the row in place (rather than inserting new and repointing) keeps every
     * organization_type_id intact, so no organization or template is touched at all.
     *
     * 'klinikrumah-sakit' is not a typo: Str::slug('Klinik/Rumah Sakit') strips the '/' rather
     * than treating it as a separator, exactly as 'Media/Portal Berita' already slugs to
     * 'mediaportal-berita'. The slug is written literally here to match what the seeder
     * produces.
     *
     * Idempotent and safe on a fresh database: a slug that isn't present is skipped, and if the
     * new slug somehow already exists (e.g. the seeder ran first and created it), the stale old
     * row is folded away only when nothing references it - never deleted out from under an
     * organization.
     */
    private const RENAMES = [
        'pimpinan-cabang-muhammadiyah' => ['slug' => 'muhammadiyah', 'name' => 'Muhammadiyah'],
        'pimpinan-cabang-aisyiyah' => ['slug' => 'aisyiyah', 'name' => 'Aisyiyah'],
        'aum-kesehatan' => ['slug' => 'klinikrumah-sakit', 'name' => 'Klinik/Rumah Sakit'],
    ];

    public function up(): void
    {
        foreach (self::RENAMES as $oldSlug => $new) {
            $this->move($oldSlug, $new['slug'], $new['name']);
        }
    }

    public function down(): void
    {
        foreach (self::RENAMES as $oldSlug => $new) {
            $old = OrganizationType::where('slug', $oldSlug)->first();

            $this->move($new['slug'], $oldSlug, match ($oldSlug) {
                'aum-kesehatan' => 'AUM Kesehatan',
                'pimpinan-cabang-muhammadiyah' => 'Pimpinan Cabang Muhammadiyah',
                default => 'Pimpinan Cabang Aisyiyah',
            });

            unset($old);
        }
    }

    /**
     * Point the $from row at $to. When a $to row already exists (the seeder having run before
     * this migration), the two are merged instead: everything on $from is repointed at $to and
     * $from is dropped, so the duplicate pair doesn't outlive the rename.
     */
    private function move(string $from, string $to, string $name): void
    {
        $source = OrganizationType::where('slug', $from)->first();

        if (! $source) {
            // Nothing to rename. Still normalize the target's display name, so a database where
            // the seeder created the new row first ends up with the same name as one migrated.
            OrganizationType::where('slug', $to)->update(['name' => $name, 'description' => $name]);

            return;
        }

        $target = OrganizationType::where('slug', $to)->first();

        if (! $target) {
            $source->update(['slug' => $to, 'name' => $name, 'description' => $name]);

            return;
        }

        // Both rows exist: keep $target (the one matching the seeder) and move $source's
        // dependents onto it before removing the now-empty duplicate.
        $source->organizations()->update(['organization_type_id' => $target->id]);
        $source->templates()->update(['organization_type_id' => $target->id]);
        $target->update(['name' => $name, 'description' => $name]);
        $source->delete();
    }
};
