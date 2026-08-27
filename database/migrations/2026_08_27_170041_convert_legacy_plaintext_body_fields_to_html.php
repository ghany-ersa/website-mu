<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * One-time data fix for Post::body, Announcement::body, and Agenda::description: these
 * columns used to be rendered as plain text (`nl2br(e($field))` — see
 * resources/views/organizations/public/_*-body.blade.php before this migration's sibling
 * commit), so existing rows are raw text with literal newlines, not HTML. Now that those
 * fields are edited via the Tiptap rich text editor and rendered unescaped (`{!! $field !!}`,
 * sanitized on save instead), old rows need converting once: escape any literal
 * `<`/`>`/`&` so they keep displaying as text rather than being parsed as markup, then wrap
 * each paragraph in `<p>` so the line breaks the old nl2br() used to produce aren't lost.
 */
return new class extends Migration
{
    private const array COLUMNS = [
        'posts' => 'body',
        'announcements' => 'body',
        'agendas' => 'description',
    ];

    public function up(): void
    {
        foreach (self::COLUMNS as $table => $column) {
            DB::table($table)
                ->whereNotNull($column)
                ->where($column, '!=', '')
                ->orderBy('id')
                ->chunkById(100, function ($rows) use ($table, $column) {
                    foreach ($rows as $row) {
                        $raw = $row->{$column};

                        // Defensive: skip anything that already looks like HTML, in case this
                        // migration ever re-runs against a partially migrated database.
                        if (preg_match('/<\/?[a-z][\s\S]*>/i', $raw) === 1) {
                            continue;
                        }

                        $paragraphs = preg_split('/\r\n|\r|\n/', $raw);
                        $paragraphs = array_filter(array_map('trim', $paragraphs), fn ($p) => $p !== '');

                        $html = collect($paragraphs)
                            ->map(fn ($p) => '<p>'.e($p).'</p>')
                            ->implode('');

                        DB::table($table)->where('id', $row->id)->update([$column => $html]);
                    }
                });
        }
    }

    /**
     * Not meaningfully reversible: converting back to the exact original plain text after
     * escaping + <p>-wrapping is lossy. This is a one-time forward data fix in a
     * prototype-stage app (see CLAUDE.md), so `down()` is intentionally a no-op.
     */
    public function down(): void {}
};
