<?php

namespace Tests\Feature;

use App\Models\Template;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regression tests for `limit` handling on CMS-backed section partials (daftar-berita's three
 * variants, pengumuman/agenda/galeri's `standar`):
 *
 *   1. The template-preview fallback (`content['items']`, used when there's no organization
 *      yet to query CMS tables from) used to ignore `limit` entirely - only the LIVE query
 *      branch respected it - so a static sample `items` array longer than the configured
 *      `limit` rendered every sample item instead of the configured count.
 *   2. A blank/unset `limit` now means "show every item" instead of a hardcoded default cap
 *      (3, 6, 8, ...). It also used to throw a TypeError: the builder's `limit` field is a
 *      plain text input with no dedicated control, so clearing it submits an empty string, not
 *      an omitted key, and Collection::take('')/Builder::take('') both throw rather than treat
 *      that as "no limit".
 */
class TemplatePreviewItemsLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_daftar_berita_modern_respects_limit_against_static_items_in_preview(): void
    {
        $template = Template::factory()->create([
            'structure' => [
                'pages' => [
                    ['slug' => 'home', 'name' => 'Home', 'sections' => [
                        ['key' => 'header', 'content' => []],
                        ['key' => 'daftar-berita', 'variant' => 'modern', 'content' => [
                            'title' => 'Berita',
                            'limit' => 2,
                            'items' => [
                                ['title' => 'Berita Satu'],
                                ['title' => 'Berita Dua'],
                                ['title' => 'Berita Tiga'],
                                ['title' => 'Berita Empat'],
                                ['title' => 'Berita Lima'],
                            ],
                        ]],
                        ['key' => 'footer', 'content' => []],
                    ]],
                ],
            ],
        ]);

        $response = $this->get(route('templates.preview', ['template' => $template->slug]));

        $response->assertOk();
        $response->assertSee('Berita Satu');
        $response->assertSee('Berita Dua');
        $response->assertDontSee('Berita Tiga');
        $response->assertDontSee('Berita Empat');
        $response->assertDontSee('Berita Lima');
    }

    public function test_pengumuman_standar_respects_limit_against_static_items_in_preview(): void
    {
        $template = Template::factory()->create([
            'structure' => [
                'pages' => [
                    ['slug' => 'home', 'name' => 'Home', 'sections' => [
                        ['key' => 'header', 'content' => []],
                        ['key' => 'pengumuman', 'variant' => 'standar', 'content' => [
                            'title' => 'Pengumuman',
                            'limit' => 1,
                            'items' => [
                                ['title' => 'Pengumuman Satu', 'priority' => 'Tinggi'],
                                ['title' => 'Pengumuman Dua', 'priority' => 'Sedang'],
                            ],
                        ]],
                        ['key' => 'footer', 'content' => []],
                    ]],
                ],
            ],
        ]);

        $response = $this->get(route('templates.preview', ['template' => $template->slug]));

        $response->assertOk();
        $response->assertSee('Pengumuman Satu');
        $response->assertDontSee('Pengumuman Dua');
    }

    /**
     * Regression test: a blank/unset `limit` now means "show every item" instead of falling
     * back to that section's hardcoded default cap (3, 6, 8, ...). Also guards against the
     * TypeError an empty-string `limit` used to throw - the builder's `limit` field is a plain
     * text input (edit.blade.php has no dedicated control for it), so a user clearing it
     * submits `''`, not an omitted key, and `Collection::take('')`/`Builder::take('')` both
     * throw rather than treat that as "no limit".
     */
    public function test_daftar_berita_shows_every_item_when_limit_is_unset(): void
    {
        $template = Template::factory()->create([
            'structure' => [
                'pages' => [
                    ['slug' => 'home', 'name' => 'Home', 'sections' => [
                        ['key' => 'header', 'content' => []],
                        ['key' => 'daftar-berita', 'variant' => 'standar', 'content' => [
                            'title' => 'Berita',
                            'items' => [
                                ['title' => 'Berita Satu'],
                                ['title' => 'Berita Dua'],
                                ['title' => 'Berita Tiga'],
                                ['title' => 'Berita Empat'],
                                ['title' => 'Berita Lima'],
                            ],
                        ]],
                        ['key' => 'footer', 'content' => []],
                    ]],
                ],
            ],
        ]);

        $response = $this->get(route('templates.preview', ['template' => $template->slug]));

        $response->assertOk();
        $response->assertSee('Berita Satu');
        $response->assertSee('Berita Dua');
        $response->assertSee('Berita Tiga');
        $response->assertSee('Berita Empat');
        $response->assertSee('Berita Lima');
    }

    public function test_daftar_berita_shows_every_item_when_limit_is_an_empty_string(): void
    {
        $template = Template::factory()->create([
            'structure' => [
                'pages' => [
                    ['slug' => 'home', 'name' => 'Home', 'sections' => [
                        ['key' => 'header', 'content' => []],
                        ['key' => 'daftar-berita', 'variant' => 'standar', 'content' => [
                            'title' => 'Berita',
                            'limit' => '',
                            'items' => [
                                ['title' => 'Berita Satu'],
                                ['title' => 'Berita Dua'],
                                ['title' => 'Berita Tiga'],
                            ],
                        ]],
                        ['key' => 'footer', 'content' => []],
                    ]],
                ],
            ],
        ]);

        $response = $this->get(route('templates.preview', ['template' => $template->slug]));

        $response->assertOk();
        $response->assertSee('Berita Satu');
        $response->assertSee('Berita Dua');
        $response->assertSee('Berita Tiga');
    }
}
