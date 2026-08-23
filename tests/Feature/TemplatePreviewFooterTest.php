<?php

namespace Tests\Feature;

use App\Models\Template;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TemplatePreviewFooterTest extends TestCase
{
    use RefreshDatabase;

    public function test_template_preview_renders_footer_without_an_organization(): void
    {
        $template = Template::factory()->create([
            'structure' => [
                'pages' => [
                    ['slug' => 'home', 'name' => 'Home', 'sections' => [
                        ['key' => 'hero', 'content' => []],
                        ['key' => 'footer', 'content' => []],
                    ]],
                ],
            ],
        ]);

        $response = $this->get(route('templates.preview', ['template' => $template->slug]));

        $response->assertOk();
        $response->assertSee('website-mu.id');
    }
}
