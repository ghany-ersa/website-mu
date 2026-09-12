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

        // Same page/section shape and every non-bound field survives the round trip untouched.
        $this->assertCount(2, $exported['pages']);
        $this->assertSame(
            collect($template->structure['pages'])->map(fn ($page) => collect($page['sections'])->map->key->all())->all(),
            collect($exported['pages'])->map(fn ($page) => collect($page['sections'])->map->key->all())->all(),
        );
        $this->assertSame('Program', collect($exported['pages'][0]['sections'])->firstWhere('key', 'program-unggulan')['content']['title']);
        $this->assertSame('Berita', collect($exported['pages'][0]['sections'])->firstWhere('key', 'daftar-berita')['content']['title']);
        $this->assertSame('Pengurus', collect($exported['pages'][1]['sections'])->firstWhere('key', 'struktur-pengurus')['content']['title']);
        $this->assertSame('Galeri', collect($exported['pages'][1]['sections'])->firstWhere('key', 'galeri')['content']['title']);
        $this->assertSame('PCM Contoh', $exported['sample_org_name']);
        $this->assertSame('#111111', $exported['brand']['primary']);
        $this->assertSame('#222222', $exported['brand']['secondary']);

        // Auto-bound sections (see TemplateSandboxService::refreshBoundItems()) instead carry
        // whatever the sandbox's own CMS records hold at export time - here, the generic
        // placeholders CmsSampleDataSeeder seeds on first clone, NOT the structure's original
        // (absent) `items`. Confirms export() doesn't just echo back stale/missing content for
        // these keys.
        $this->assertNotEmpty(collect($exported['pages'][0]['sections'])->firstWhere('key', 'program-unggulan')['content']['items']);
        $this->assertNotEmpty(collect($exported['pages'][1]['sections'])->firstWhere('key', 'struktur-pengurus')['content']['items']);
        $this->assertNotEmpty(collect($exported['pages'][1]['sections'])->firstWhere('key', 'galeri')['content']['items']);
    }

    /**
     * Regression test: TemplateSandboxService::export() used to copy $section->content as
     * stored, so an admin editing a bound section's underlying CMS records (donations,
     * officers, ...) through the normal builder pages and then clicking "Simpan ke Template"
     * exported whatever content['items'] happened to already be - stale or absent - instead of
     * what they had just edited. refreshBoundItems() closes that by rebuilding `items` from the
     * sandbox's live CMS relation on every export.
     */
    public function test_export_reflects_cms_edits_made_after_the_sandbox_was_created(): void
    {
        $admin = $this->admin();
        $template = $this->richTemplate();
        $service = app(TemplateSandboxService::class);
        $sandbox = $service->sandboxFor($template, $admin);

        // Overwrite the CMS record a bound section reads, the way editing it through the
        // builder's officers page would - not by touching $section->content directly.
        $officer = $sandbox->officers()->first();
        $officer->update(['name' => 'Nama Diedit Lewat CMS']);

        $exported = $service->export($sandbox->fresh(), $template);

        $names = collect($exported['pages'][1]['sections'])
            ->firstWhere('key', 'struktur-pengurus')['content']['items'];

        $this->assertContains('Nama Diedit Lewat CMS', collect($names)->pluck('name')->all());
    }

    /**
     * The agenda section has two variants mapping DIFFERENT shapes: `standar` reads date_year,
     * `poster` reads agendas.poster. export() writes one superset shape for the key, so a
     * template built on the poster variant keeps its flyers - this guards the `poster` field
     * specifically, which an earlier version of refreshBoundItems() dropped.
     */
    public function test_export_keeps_agenda_posters_for_the_poster_variant(): void
    {
        $admin = $this->admin();
        $template = Template::factory()->create([
            'structure' => [
                'sample_org_name' => 'Masjid Contoh',
                'brand' => ['primary' => '#111111', 'secondary' => '#222222'],
                'pages' => [[
                    'slug' => 'home',
                    'name' => 'Beranda',
                    'sections' => [
                        ['key' => 'header', 'variant' => 'standar'],
                        ['key' => 'agenda', 'variant' => 'poster', 'content' => ['title' => 'Kajian']],
                        ['key' => 'footer', 'variant' => 'standar'],
                    ],
                ]],
            ],
        ]);

        $service = app(TemplateSandboxService::class);
        $sandbox = $service->sandboxFor($template, $admin);

        $sandbox->agendas()->first()->update(['poster' => 'https://example.test/flyer.jpg']);

        $items = collect($service->export($sandbox->fresh(), $template)['pages'][0]['sections'])
            ->firstWhere('key', 'agenda')['content']['items'];

        $this->assertContains('https://example.test/flyer.jpg', collect($items)->pluck('poster')->all());
        // date_year is what the `standar` variant needs - both keys ride along together.
        $this->assertArrayHasKey('date_year', $items[0]);
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

    /**
     * Regression test: TemplateSandboxService::sandboxFor() never sets a plan_id, so the
     * sandbox has none - Organization::canUseExclusiveTemplates() used to read that as "no
     * entitlement" and block picking an exclusive section variant (hero/modern here) while an
     * admin was designing the template, even though the template itself may well be intended
     * as exclusive. is_sandbox now always qualifies (see that method's doc comment).
     */
    public function test_admin_can_pick_an_exclusive_variant_while_designing_a_template(): void
    {
        $admin = $this->admin();
        $template = $this->richTemplate();
        $sandbox = app(TemplateSandboxService::class)->sandboxFor($template, $admin);

        $this->assertNull($sandbox->plan_id);
        $this->assertTrue($sandbox->canUseExclusiveTemplates());

        $heroSection = $sandbox->pages()->where('is_home', true)->first()
            ->sections()->where('key', 'hero')->firstOrFail();

        $this->actingAs($admin)
            ->patch(route('organizations.sections.update', [$sandbox, $heroSection]), [
                'content' => ['headline' => 'Judul Eksklusif'],
                'variant' => 'modern',
                'is_visible' => '1',
            ])
            ->assertRedirect();

        $this->assertSame('modern', $heroSection->fresh()->variant);
    }

    public function test_non_admin_cannot_open_the_template_designer(): void
    {
        $template = $this->richTemplate();

        $this->actingAs(User::factory()->create())
            ->get(route('admin.templates.design', $template))
            ->assertForbidden();
    }
}
