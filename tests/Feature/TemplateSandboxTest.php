<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\Plan;
use App\Models\PlanLimit;
use App\Models\Template;
use App\Models\User;
use App\Services\TemplateSandboxService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TemplateSandboxTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    /**
     * A template with more sections than any restrictive plan would allow, spread over two
     * pages - the case that would silently lose sections if the sandbox were plan-capped.
     */
    private function richTemplate(): Template
    {
        return Template::factory()->create([
            'structure' => [
                'sample_org_name' => 'PCM Contoh',
                'brand' => ['primary' => '#111111', 'secondary' => '#222222'],
                'pages' => [
                    [
                        'slug' => 'home',
                        'name' => 'Beranda',
                        'sections' => [
                            ['key' => 'header', 'variant' => 'standar'],
                            ['key' => 'hero', 'variant' => 'standar', 'content' => ['headline' => 'Halo']],
                            ['key' => 'tentang-organisasi', 'variant' => 'standar', 'content' => ['title' => 'Tentang']],
                            ['key' => 'program-unggulan', 'variant' => 'standar', 'content' => ['title' => 'Program']],
                            ['key' => 'daftar-berita', 'variant' => 'standar', 'content' => ['title' => 'Berita']],
                            ['key' => 'formulir-kontak', 'variant' => 'standar', 'content' => ['title' => 'Kontak']],
                            ['key' => 'footer', 'variant' => 'standar'],
                        ],
                    ],
                    [
                        'slug' => 'profil',
                        'name' => 'Profil',
                        'sections' => [
                            ['key' => 'header', 'variant' => 'standar'],
                            ['key' => 'struktur-pengurus', 'variant' => 'standar', 'content' => ['title' => 'Pengurus']],
                            ['key' => 'galeri', 'variant' => 'standar', 'content' => ['title' => 'Galeri']],
                            ['key' => 'footer', 'variant' => 'standar'],
                        ],
                    ],
                ],
            ],
        ]);
    }

    public function test_sandbox_round_trips_a_template_structure_without_losing_sections(): void
    {
        $template = $this->richTemplate();

        $service = app(TemplateSandboxService::class);
        $sandbox = $service->sandboxFor($template, $this->admin());

        $exported = $service->export($sandbox, $template);

        $this->assertSame($template->structure['pages'], $exported['pages']);
        $this->assertSame('PCM Contoh', $exported['sample_org_name']);
        $this->assertSame('#111111', $exported['brand']['primary']);
        $this->assertSame('#222222', $exported['brand']['secondary']);
    }

    public function test_sandbox_is_unlimited_even_when_a_restrictive_plan_exists(): void
    {
        // Plan id 1 is what OrganizationController::store() hands every new organization; make it
        // tight enough that an uncapped clone of richTemplate() would be truncated.
        $plan = Plan::create([
            'key' => 'starter-test',
            'name' => 'Starter Test',
            'price_monthly' => 0,
            'is_active' => true,
        ]);
        PlanLimit::create(['plan_id' => $plan->id, 'key' => 'sections_total', 'max_count' => 3]);
        PlanLimit::create(['plan_id' => $plan->id, 'key' => 'pages_total', 'max_count' => 1]);

        $template = $this->richTemplate();
        $sandbox = app(TemplateSandboxService::class)->sandboxFor($template, $this->admin());
        $sandbox->update(['plan_id' => $plan->id]);
        $sandbox->load('limitOverrides');

        $this->assertCount(2, $sandbox->pages);
        $this->assertSame(11, $sandbox->pages->sum(fn ($page) => $page->sections->count()));
    }

    public function test_sandbox_is_reused_rather_than_recreated(): void
    {
        $template = $this->richTemplate();
        $service = app(TemplateSandboxService::class);
        $admin = $this->admin();

        $first = $service->sandboxFor($template, $admin);
        $first->pages()->where('is_home', true)->first()->sections()->create([
            'key' => 'cta', 'variant' => 'standar', 'content' => ['title' => 'Edit saya'], 'order' => 99,
        ]);

        $second = $service->sandboxFor($template, $admin);

        $this->assertTrue($first->is($second));
        $this->assertSame(1, Organization::where('is_sandbox', true)->count());
        $this->assertNotNull(
            $second->pages()->where('is_home', true)->first()->sections()->where('key', 'cta')->first()
        );
    }

    public function test_sandbox_is_hidden_from_organization_listings(): void
    {
        $admin = $this->admin();
        $template = $this->richTemplate();
        app(TemplateSandboxService::class)->sandboxFor($template, $admin);

        $this->actingAs($admin)->get(route('organizations.index'))->assertDontSee('sandbox-template-');
        $this->actingAs($admin)->get(route('admin.organizations.index'))->assertDontSee('sandbox-template-');
    }

    public function test_admin_can_save_builder_changes_back_to_the_template(): void
    {
        $admin = $this->admin();
        $template = $this->richTemplate();
        $sandbox = app(TemplateSandboxService::class)->sandboxFor($template, $admin);

        $sandbox->pages()->where('is_home', true)->first()->sections()->create([
            'key' => 'cta', 'variant' => 'standar', 'content' => ['title' => 'Gabung Sekarang'], 'order' => 99,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.templates.design.update', $template))
            ->assertRedirect(route('admin.templates.edit', $template));

        $homeSections = collect($template->refresh()->structure['pages'][0]['sections']);
        $cta = $homeSections->firstWhere('key', 'cta');

        $this->assertNotNull($cta);
        $this->assertSame('Gabung Sekarang', $cta['content']['title']);
    }

    public function test_non_admin_cannot_open_the_template_designer(): void
    {
        $template = $this->richTemplate();

        $this->actingAs(User::factory()->create())
            ->get(route('admin.templates.design', $template))
            ->assertForbidden();
    }
}
