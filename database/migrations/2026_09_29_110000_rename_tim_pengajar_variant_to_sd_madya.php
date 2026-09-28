<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Renames the `struktur-pengurus` section's `tim-pengajar` variant to `sd-madya`, bringing it
 * in line with every other section the SD Madya template styles - hero, tentang-organisasi,
 * program-unggulan, galeri, kegiatan, testimoni, prestasi, formulir-kontak and cta all name
 * their variant after the template (`sd-madya`), and this one alone was named after the
 * section's subject matter instead.
 *
 * A data migration rather than a seeder edit alone, because `variant` is a stored column on
 * both section_variants and organization_sections. Renaming only the seeder would leave any
 * organization already using the variant pointing at a variant_key that no longer exists -
 * SectionVariantResolver::resolveFrom() does not fail on an unknown variant, it silently falls
 * back to the key's `is_default` row (`standar`), so the symptom would not be an error anyone
 * would notice in logs: the school's teacher roster would just quietly start rendering in the
 * generic committee layout, losing the subject/bio fields that only this variant reads (see
 * add_teacher_fields_to_officers_table).
 *
 * Both tables are updated, and down() restores the old key on both, so the rename is reversible
 * in either direction without stranding rows.
 */
return new class extends Migration
{
    public function up(): void
    {
        self::rename('tim-pengajar', 'sd-madya');
    }

    public function down(): void
    {
        self::rename('sd-madya', 'tim-pengajar');
    }

    /**
     * Three places store the variant key, and all three have to move together:
     *
     *   - section_variants   - the catalogue row the resolver looks the key up in.
     *   - organization_sections - what organizations already using the variant point at.
     *   - templates.structure   - the cloning blueprint (a JSON column, so this is a string
     *     replacement rather than a column update). Missing this one would not break any
     *     EXISTING site; it would break every FUTURE one, since seedPagesFromTemplate() copies
     *     `variant` straight out of the structure and would hand each new school a key the
     *     catalogue no longer has.
     */
    private static function rename(string $from, string $to): void
    {
        DB::table('section_variants')
            ->where('section_key', 'struktur-pengurus')
            ->where('variant_key', $from)
            ->update([
                'variant_key' => $to,
                'view' => "templates.sections.struktur-pengurus.{$to}",
            ]);

        DB::table('organization_sections')
            ->where('key', 'struktur-pengurus')
            ->where('variant', $from)
            ->update(['variant' => $to]);

        // Scoped to the exact JSON fragment ("variant":"<key>") rather than the bare key, so a
        // template whose prose happens to contain the word is left alone.
        foreach (DB::table('templates')->select('id', 'structure')->get() as $template) {
            if (! is_string($template->structure) || ! str_contains($template->structure, '"'.$from.'"')) {
                continue;
            }

            DB::table('templates')->where('id', $template->id)->update([
                'structure' => str_replace('"variant":"'.$from.'"', '"variant":"'.$to.'"', $template->structure),
            ]);
        }
    }
};
