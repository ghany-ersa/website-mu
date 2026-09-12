<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Repairs sewa-aula sections whose `facilities` was saved as a string instead of a list.
 *
 * The builder's properties panel had no editor for this field, so it fell through to the
 * generic text input and wrote a bare string into content[facilities]. The section renders that
 * field with foreach(), so a single save from the panel was enough to make the page - and the
 * builder canvas that previews it - fail with a 500.
 *
 * The view now normalises defensively and the panel has a real list editor, so this only has to
 * clean up rows already written. A string is split on newlines (the most forgiving reading of
 * what someone typed into a single-line input); anything left empty drops the key entirely so
 * the section falls back to its own defaults rather than rendering an empty facilities strip.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (DB::table('organization_sections')->where('key', 'sewa-aula')->get() as $section) {
            $content = json_decode($section->content ?? '', true);

            if (! is_array($content) || ! array_key_exists('facilities', $content)) {
                continue;
            }

            $facilities = $content['facilities'];

            if (is_array($facilities)) {
                continue;
            }

            $repaired = collect(is_string($facilities) ? preg_split('/\r\n|\r|\n/', $facilities) : [])
                ->map(fn ($facility) => trim((string) $facility))
                ->filter()
                ->values()
                ->all();

            if ($repaired === []) {
                unset($content['facilities']);
            } else {
                $content['facilities'] = $repaired;
            }

            DB::table('organization_sections')
                ->where('id', $section->id)
                ->update(['content' => json_encode($content), 'updated_at' => now()]);
        }
    }

    /**
     * Irreversible by design: the original value was malformed data, not a state worth
     * restoring, and the pre-repair string cannot be recovered from the repaired list.
     */
    public function down(): void {}
};
