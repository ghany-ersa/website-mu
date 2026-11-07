<?php

namespace Tests\Feature;

use App\Enums\OrganizationStatus;
use App\Models\Organization;
use App\Models\OrganizationPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The platform GA tag goes on acquisition pages and public tenant sites, but not on admin, the
 * organization dashboard, account, or the builder (including its canvas iframe): those inflated
 * visitor counts with internal traffic and flooded the Pages report with per-organization
 * dashboard URLs. Both sides are asserted, since the regression worth catching is someone
 * re-adding the include to a dashboard layout "for completeness".
 */
class GoogleAnalyticsScopeTest extends TestCase
{
    use RefreshDatabase;

    private const GA_ID = 'G-TESTSCOPE1';

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.google_analytics.measurement_id' => self::GA_ID]);
    }

    public function test_acquisition_pages_carry_the_tag(): void
    {
        foreach ([route('home'), route('landing.masjid'), route('templates.index'), route('articles.index'), route('register')] as $url) {
            $this->get($url)->assertOk()->assertSee(self::GA_ID, escape: false);
        }
    }

    public function test_dashboard_admin_account_and_builder_pages_do_not(): void
    {
        $owner = User::factory()->create();
        $organization = $this->organizationWithHomePage(Organization::factory()->withOwner($owner)->create());

        $this->actingAs($owner);
        $this->get(route('organizations.show', $organization))->assertOk()->assertDontSee(self::GA_ID, escape: false);
        $this->get(route('account.edit'))->assertOk()->assertDontSee(self::GA_ID, escape: false);
        $this->get(route('organizations.builder.page', [$organization, 'home']))->assertOk()->assertDontSee(self::GA_ID, escape: false);
        $this->get(route('organizations.builder.canvas', [$organization, 'home']))->assertOk()->assertDontSee(self::GA_ID, escape: false);

        $this->actingAs(User::factory()->admin()->create());
        $this->get(route('admin.templates.index'))->assertOk()->assertDontSee(self::GA_ID, escape: false);
    }

    /** Public tenant sites keep the tag; only the builder canvas (asserted above) skips it. */
    public function test_tenant_sites_carry_the_tag(): void
    {
        $organization = $this->organizationWithHomePage(
            Organization::factory()->create(['slug' => 'situs-tenant', 'status' => OrganizationStatus::Published])
        );

        $this->get('http://'.$organization->slug.'.'.config('tenancy.domain').'/')
            ->assertOk()
            ->assertSee(self::GA_ID, escape: false);
    }

    private function organizationWithHomePage(Organization $organization): Organization
    {
        OrganizationPage::factory()
            ->create(['organization_id' => $organization->id, 'slug' => 'home', 'is_home' => true])
            ->sections()
            ->create(['key' => 'hero', 'content' => ['headline' => 'Selamat Datang'], 'order' => 0]);

        return $organization;
    }
}
