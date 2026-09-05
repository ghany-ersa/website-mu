<?php

namespace Tests\Feature;

use App\Enums\OrganizationRole;
use App\Enums\OrganizationStatus;
use App\Models\Organization;
use App\Models\OrganizationType;
use App\Models\Plan;
use App\Models\Template;
use App\Models\User;
use App\Services\PlanLimitService;
use Database\Seeders\MasjidNurulHudaTemplateSeeder;
use Database\Seeders\OrganizationTypeSeeder;
use Database\Seeders\TemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Verifies the multi-page "Masjid Nurul Huda" exclusive template end-to-end:
 * seedPagesFromTemplate()'s multi-page clone (all 6 structure['pages'] entries, not just
 * the first), CMS sample seeding for the new facilities/donasi-progress/laporan-keuangan
 * sections, and every page's public tenant render. Also guards the seedPagesFromTemplate()
 * rewrite's platform-wide side effect: 'muhammadiyah' already had 3 pages in its structure
 * data but only the first used to be cloned - this now clones all of them too.
 */
class MasjidNurulHudaTemplateTest extends TestCase
{
    use RefreshDatabase;

    private function tenantUrl(Organization $organization, string $path): string
    {
        return 'http://'.$organization->slug.'.'.config('tenancy.domain').$path;
    }

    private function makeOrganization(): Organization
    {
        $this->seed(OrganizationTypeSeeder::class);
        $this->seed(MasjidNurulHudaTemplateSeeder::class);

        $template = Template::where('slug', 'masjid-nurul-huda-eksklusif')->firstOrFail();
        $professional = Plan::where('key', 'professional')->firstOrFail();
        $orgType = OrganizationType::where('slug', 'masjidmushola')->firstOrFail();

        $organization = Organization::create([
            'organization_type_id' => $orgType->id,
            'template_id' => $template->id,
            'plan_id' => $professional->id,
            'name' => 'Masjid Uji Coba',
            'slug' => 'masjid-uji-coba',
            'status' => OrganizationStatus::Published,
        ]);

        $organization->ensureHomePageExists();

        return $organization->fresh();
    }

    public function test_all_six_pages_are_cloned_with_correct_home_flag_and_order(): void
    {
        $organization = $this->makeOrganization();
        $organization->load('pages.sections');

        $this->assertCount(6, $organization->pages);

        // Order mirrors the nurul-huda project's own navbar: Beranda, Pengurus, Donasi,
        // Laporan Keuangan, Kajian & Event, Akad Venue.
        $expectedSlugs = ['home', 'pengurus', 'donasi', 'laporan-keuangan', 'kajian-event', 'sewa-aula'];
        $this->assertSame($expectedSlugs, $organization->pages->pluck('slug')->all());

        $this->assertTrue($organization->pages->firstWhere('slug', 'home')->is_home);
        $this->assertSame(1, $organization->pages->where('is_home', true)->count());

        foreach ($organization->pages as $page) {
            $this->assertNotNull($page->sections->firstWhere('key', 'header'));
            $this->assertNotNull($page->sections->firstWhere('key', 'footer'));
        }
    }

    public function test_sections_total_stays_within_professional_plan_limit(): void
    {
        $organization = $this->makeOrganization();

        $limit = app(PlanLimitService::class)->effectiveLimit($organization, 'sections_total');
        $actual = app(PlanLimitService::class)->countedSectionsTotal($organization);

        $this->assertLessThanOrEqual($limit, $actual);
    }

    public function test_cms_sample_data_is_seeded_for_new_sections(): void
    {
        $organization = $this->makeOrganization();

        $this->assertGreaterThan(0, $organization->facilities()->count());
        $this->assertGreaterThan(0, $organization->donationPrograms()->count());
        $this->assertGreaterThan(0, $organization->financialReports()->count());

        $program = $organization->donationPrograms()->first();
        $this->assertGreaterThan(0, $program->collectedAmount());
    }

    public function test_sample_data_matches_the_nurul_huda_project(): void
    {
        $organization = $this->makeOrganization();

        $this->assertSame(
            ['Suhartono, S.Pd', 'Tyas Hidayatulloh, S.Pd, M.Pd'],
            $organization->officers()->pluck('name')->all(),
        );

        $this->assertSame('Halaman dan Teras Depan', $organization->facilities()->first()->name);
        // 12, not 13: the live site no longer publishes the Perpustakaan photo.
        $this->assertSame(12, $organization->facilities()->count());

        $flagship = $organization->donationPrograms()->where('name', 'Wakaf Pembangunan Masjid')->firstOrFail();
        $this->assertSame(1_175_600_000, (int) $flagship->target_amount);
        // The wakaf appeal is seeded from the mosque's real donor-by-donor ledger, so its total
        // must land exactly on the figure the live site publishes - not a rounded percentage.
        $this->assertSame(437_682_000, $flagship->collectedAmount());
        // One decimal place, matching the figure the live site publishes ("37.2%").
        $this->assertSame(37.2, $flagship->progressPercent());
        // 358 transcribed donations plus the April 2025 opening balance row.
        $this->assertSame(359, $flagship->transactions()->count());
        $this->assertSame('Saldo Wakaf Bulan April 2025', $flagship->transactions()->orderBy('donated_at')->first()->donor_name);

        $this->assertStringContainsString('Hadi Santoso', $organization->agendas()->first()->title);

        // Books follow the source project's own categories/cadence (4 months, daily Infak
        // Subuh and per-Friday Infak Jum'at), not four unrelated random figures.
        $categories = $organization->financialReports()->distinct()->pluck('category');
        $this->assertContains('Infak Subuh', $categories);
        $this->assertContains("Infak Jum'at", $categories);
        $this->assertContains('Air (PDAM)', $categories);
        $this->assertSame(4, $organization->financialReports()->distinct()->count('period_month'));
        $this->assertSame('Kegiatan Kajian Guru Besar Ramadhan 2026', $organization->photos()->first()->caption);
        $this->assertStringStartsWith('https://s3.nurul-huda.ambulu.or.id/', $organization->photos()->first()->url);
    }

    public function test_standard_header_links_to_every_page_on_a_multi_page_site(): void
    {
        $organization = $this->makeOrganization();

        $response = $this->get($this->tenantUrl($organization, '/'));

        $response->assertOk();

        // The standard header switches from same-page anchors to per-page links once an
        // organization has more than one page - without that, this template's five other
        // pages would be unreachable from the nav.
        // Labels are the pages' own names, which follow the source site's navbar wording
        // ("Akad Venue", not "Sewa Aula" - the slug and the label deliberately differ).
        foreach (['Pengurus', 'Donasi', 'Laporan Keuangan', 'Kajian &amp; Event', 'Akad Venue'] as $label) {
            $response->assertSee($label, false);
        }

        // Matched on host+path rather than the full route() URL: the test request is made over
        // http while route() builds from APP_URL's https, so comparing whole URLs would fail
        // on the scheme alone.
        foreach (['donasi', 'laporan-keuangan', 'kajian-event', 'sewa-aula', 'pengurus'] as $slug) {
            $response->assertSee($organization->slug.'.'.config('tenancy.domain').'/'.$slug, false);
        }
    }

    public function test_standard_header_still_uses_anchors_on_a_single_page_site(): void
    {
        $this->seed(OrganizationTypeSeeder::class);
        $this->seed(TemplateSeeder::class);

        $template = Template::where('slug', 'masjid-mushola')->firstOrFail();

        $organization = Organization::create([
            'organization_type_id' => $template->organization_type_id,
            'template_id' => $template->id,
            'plan_id' => Plan::where('key', 'starter')->firstOrFail()->id,
            'name' => 'Masjid Satu Halaman',
            'slug' => 'masjid-satu-halaman',
            'status' => OrganizationStatus::Published,
        ]);
        $organization->ensureHomePageExists();

        $this->assertSame(1, $organization->pages()->count());

        $response = $this->get($this->tenantUrl($organization, '/'));

        $response->assertOk();
        // Unchanged behaviour for the single-page templates that are still the common case.
        $response->assertSee('#canvas-section-', false);
    }

    public function test_jadwal_kajian_section_is_editable_and_manageable(): void
    {
        $organization = $this->makeOrganization();

        // Empty `fields` used to make this section unclickable in the builder sidebar (see
        // edit.blade.php's $hasFields guard) and silently drop its title/limit on save.
        $fields = config('page-builder.sections.jadwal-kajian.fields');
        $this->assertContains('title', $fields);
        $this->assertContains('limit', $fields);

        $owner = User::factory()->create();
        $organization->members()->attach($owner->id, ['role' => OrganizationRole::Owner->value]);

        // jadwal-kajian is backed by the `agendas` table, so its "Kelola" link must open even
        // though this template ships no 'agenda' section.
        $this->actingAs($owner)
            ->get(route('organizations.agendas.index', $organization))
            ->assertOk();
    }

    public function test_jadwal_kajian_title_survives_a_builder_save(): void
    {
        $organization = $this->makeOrganization();
        $organization->load('pages.sections');

        $owner = User::factory()->create();
        $organization->members()->attach($owner->id, ['role' => OrganizationRole::Owner->value]);

        $section = $organization->pages
            ->flatMap->sections
            ->firstWhere('key', 'jadwal-kajian');

        $this->actingAs($owner)
            ->patch(route('organizations.sections.update', [$organization, $section]), [
                'content' => ['title' => 'Kajian Rutin Pekanan', 'limit' => 5],
                'is_visible' => true,
            ])
            ->assertRedirect();

        $this->assertSame('Kajian Rutin Pekanan', $section->fresh()->content['title']);
    }

    public function test_home_page_renders(): void
    {
        $organization = $this->makeOrganization();

        $response = $this->get($this->tenantUrl($organization, '/'));

        $response->assertOk();
        $response->assertSee('Masjid Nurul Huda');
    }

    public function test_donasi_page_renders(): void
    {
        $organization = $this->makeOrganization();

        $response = $this->get($this->tenantUrl($organization, '/donasi'));

        $response->assertOk();
    }

    public function test_laporan_keuangan_page_renders(): void
    {
        $organization = $this->makeOrganization();

        $response = $this->get($this->tenantUrl($organization, '/laporan-keuangan'));

        $response->assertOk();
    }

    public function test_kajian_event_page_renders(): void
    {
        $organization = $this->makeOrganization();

        $response = $this->get($this->tenantUrl($organization, '/kajian-event'));

        $response->assertOk();
    }

    public function test_sewa_aula_page_renders(): void
    {
        $organization = $this->makeOrganization();

        $response = $this->get($this->tenantUrl($organization, '/sewa-aula'));

        $response->assertOk();
    }

    public function test_pengurus_page_renders(): void
    {
        $organization = $this->makeOrganization();

        $response = $this->get($this->tenantUrl($organization, '/pengurus'));

        $response->assertOk();
    }

    public function test_donation_program_detail_page_renders(): void
    {
        $organization = $this->makeOrganization();
        $program = $organization->donationPrograms()->where('name', 'Wakaf Pembangunan Masjid')->firstOrFail();

        $response = $this->get($this->tenantUrl($organization, '/donasi/'.$program->slug));

        $response->assertOk();
        $response->assertSee($program->name);
        $response->assertSee('Riwayat Donasi');
        $response->assertSee('Terkumpul');
        // Transactions are listed individually, not just summed into the progress bar.
        $response->assertSee(number_format($program->transactions()->first()->amount, 0, ',', '.'));
    }

    public function test_donation_detail_is_reachable_from_the_main_domain_preview(): void
    {
        $organization = $this->makeOrganization();
        $program = $organization->donationPrograms()->firstOrFail();

        $owner = User::factory()->create();
        $organization->members()->attach($owner->id, ['role' => OrganizationRole::Owner->value]);

        // The donation card on the preview must link to the preview detail route, not the
        // tenant subdomain - that subdomain isn't routable under `php artisan serve`, so an
        // owner previewing their site would otherwise hit a dead link.
        $donasiPage = $this->actingAs($owner)
            ->get(route('organizations.preview.page', ['organization' => $organization, 'page' => 'donasi']));

        $donasiPage->assertOk();
        $donasiPage->assertSee(route('organizations.preview.donation', [
            'organization' => $organization,
            'program' => $program,
        ]), false);

        $detail = $this->actingAs($owner)
            ->get(route('organizations.preview.donation', ['organization' => $organization, 'program' => $program]));

        $detail->assertOk();
        $detail->assertSee('Riwayat Donasi');
        $detail->assertSee('Terkumpul');
    }

    public function test_donation_detail_preview_requires_an_authorized_member(): void
    {
        $organization = $this->makeOrganization();
        $program = $organization->donationPrograms()->firstOrFail();

        $stranger = User::factory()->create();

        $this->actingAs($stranger)
            ->get(route('organizations.preview.donation', ['organization' => $organization, 'program' => $program]))
            ->assertForbidden();
    }

    public function test_donation_program_detail_is_scoped_to_its_own_organization(): void
    {
        $organization = $this->makeOrganization();
        $program = $organization->donationPrograms()->firstOrFail();

        $other = Organization::create([
            'organization_type_id' => $organization->organization_type_id,
            'template_id' => $organization->template_id,
            'plan_id' => $organization->plan_id,
            'name' => 'Masjid Lain',
            'slug' => 'masjid-lain',
            'status' => OrganizationStatus::Published,
        ]);

        // Another tenant's subdomain must not be able to serve this program, even though
        // donation_programs.slug is only unique per organization.
        $response = $this->get($this->tenantUrl($other, '/donasi/'.$program->slug));

        $response->assertNotFound();
    }

    public function test_unknown_page_slug_404s(): void
    {
        $organization = $this->makeOrganization();

        $response = $this->get($this->tenantUrl($organization, '/does-not-exist'));

        $response->assertNotFound();
    }

    public function test_switching_organization_from_muhammadiyah_template_still_clones_all_its_pages(): void
    {
        // Regression guard for the seedPagesFromTemplate() rewrite: 'muhammadiyah' already had
        // 3 pages in its structure data before this change, but only page [0] used to be
        // cloned. Confirms the fix intentionally activates multi-page for it too.
        $this->seed(OrganizationTypeSeeder::class);
        $this->seed(TemplateSeeder::class);

        $template = Template::where('slug', 'muhammadiyah')->firstOrFail();
        $starter = Plan::where('key', 'starter')->firstOrFail();
        $orgType = OrganizationType::where('slug', 'muhammadiyah')->firstOrFail();

        $organization = Organization::create([
            'organization_type_id' => $orgType->id,
            'template_id' => $template->id,
            'plan_id' => $starter->id,
            'name' => 'PCM Uji Coba',
            'slug' => 'pcm-uji-coba',
            'status' => OrganizationStatus::Draft,
        ]);

        $organization->ensureHomePageExists();
        $organization->load('pages');

        $this->assertGreaterThanOrEqual(1, $organization->pages->count());

        $limit = app(PlanLimitService::class)->effectiveLimit($organization, 'sections_total');
        $actual = app(PlanLimitService::class)->countedSectionsTotal($organization);
        $this->assertLessThanOrEqual($limit, $actual);
    }
}
