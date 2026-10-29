<?php

namespace Tests\Feature;

use App\Enums\OrganizationRole;
use App\Enums\OrganizationStatus;
use App\Models\Organization;
use App\Models\OrganizationPage;
use App\Models\OrganizationType;
use App\Models\Plan;
use App\Models\Template;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * When an org can't publish because a widget/section variant it uses is exclusive but its plan
 * isn't, the reason has to be legible without reading source - the raw
 * Organization::planViolations() strings ("1 widget memerlukan paket Infak Eksklusif") don't say *why*
 * on their own, so the plan page adds a plain-language explanation alongside them (see
 * organizations/plan/edit.blade.php's "why blocked" panel) and the dashboard badge names the
 * specific reason instead of a generic "batas paket Infak dilanggar".
 */
class PublishBlockedExplanationTest extends TestCase
{
    use RefreshDatabase;

    private function organizationOnExclusiveTemplateWithStarterPlan(): array
    {
        $type = OrganizationType::factory()->create();
        $template = Template::factory()->create([
            'organization_type_id' => $type->id,
            'is_active' => true,
            'is_exclusive' => true,
        ]);

        $organization = Organization::create([
            'organization_type_id' => $type->id,
            'template_id' => $template->id,
            'plan_id' => Plan::where('key', 'starter')->firstOrFail()->id,
            'plan_expires_at' => now()->addMonths(3),
            'name' => 'Sekolah Eksklusif',
            'slug' => 'sekolah-eksklusif',
            'status' => OrganizationStatus::Draft,
        ]);

        $page = OrganizationPage::factory()->create(['organization_id' => $organization->id, 'slug' => 'home', 'is_home' => true]);
        // 'kalkulator-zakat' carries config/page-builder.php's `exclusive` flag - this is what
        // actually blocks publishing, not the template's own is_exclusive label.
        $page->sections()->create(['key' => 'kalkulator-zakat', 'content' => [], 'order' => 0]);

        $owner = User::factory()->create();
        $organization->members()->attach($owner, ['role' => OrganizationRole::Owner->value]);

        return [$organization, $owner];
    }

    public function test_publish_attempt_redirects_to_plan_page_with_violation_details(): void
    {
        [$organization, $owner] = $this->organizationOnExclusiveTemplateWithStarterPlan();

        $this->actingAs($owner)
            ->patch(route('organizations.publish', $organization))
            ->assertRedirect(route('organizations.plan.edit', $organization));

        $planPage = $this->actingAs($owner)->get(route('organizations.plan.edit', $organization));

        $planPage->assertOk();
        $planPage->assertSee('Situs belum bisa dipublikasikan');
        $planPage->assertSee('1 widget memerlukan paket Infak Eksklusif');
        // The plain-language explanation, not just the raw violation string.
        $planPage->assertSee('bersifat eksklusif');
        $planPage->assertSee('paket Infak Eksklusif');
    }

    public function test_dashboard_badge_names_the_exclusive_entitlement_specifically(): void
    {
        [$organization, $owner] = $this->organizationOnExclusiveTemplateWithStarterPlan();

        $dashboard = $this->actingAs($owner)->get(route('organizations.show', $organization));

        $dashboard->assertOk();
        $dashboard->assertSee('Perlu paket Infak Eksklusif untuk publikasi');
    }

    /**
     * A plain quota overage (nothing exclusive involved) keeps the generic badge - the specific
     * copy is reserved for when the entitlement really is the only thing in the way.
     */
    public function test_dashboard_badge_stays_generic_for_a_plain_quota_violation(): void
    {
        $organization = Organization::factory()->create([
            'plan_id' => Plan::where('key', 'starter')->firstOrFail()->id,
            'plan_expires_at' => null,
            'status' => OrganizationStatus::Draft,
        ]);
        $owner = User::factory()->create();
        $organization->members()->attach($owner, ['role' => OrganizationRole::Owner->value]);

        $dashboard = $this->actingAs($owner)->get(route('organizations.show', $organization));

        // The dashboard no longer spells out a plain quota overage - its button leads to the plan
        // page, whose violation panel lists the details. The exclusive-only copy must not show.
        $dashboard->assertOk();
        $dashboard->assertSee(route('organizations.plan.edit', $organization), false);
        $dashboard->assertDontSee('Perlu paket Infak Eksklusif untuk publikasi');
    }

    /**
     * The panel is tied to real, current state (planViolations()) rather than the one-request
     * session('error') flash from a failed publish attempt - a plain refresh must not make it
     * vanish while the underlying problem is still there. An org whose only violation is
     * genuinely true (e.g. an exclusive template on Starter) sees the panel from the very first
     * visit, with no publish attempt required first - that's not a false alarm, it's accurate.
     */
    public function test_plan_page_shows_the_violation_panel_without_needing_a_publish_attempt_first(): void
    {
        [$organization, $owner] = $this->organizationOnExclusiveTemplateWithStarterPlan();

        $planPage = $this->actingAs($owner)->get(route('organizations.plan.edit', $organization));

        $planPage->assertOk();
        $planPage->assertSee('Situs belum bisa dipublikasikan');
        $planPage->assertSee('1 widget memerlukan paket Infak Eksklusif');
    }

    /**
     * A Draft org with no violations at all (paid, non-exclusive template) must not see the
     * panel - it has genuinely nothing to fix.
     */
    public function test_plan_page_shows_no_panel_when_there_really_is_no_violation(): void
    {
        $type = OrganizationType::factory()->create();
        $template = Template::factory()->create(['organization_type_id' => $type->id, 'is_exclusive' => false]);

        $organization = Organization::create([
            'organization_type_id' => $type->id,
            'template_id' => $template->id,
            'plan_id' => Plan::where('key', 'starter')->firstOrFail()->id,
            'plan_expires_at' => now()->addMonths(3),
            'name' => 'Organisasi Sehat',
            'slug' => 'organisasi-sehat',
            'status' => OrganizationStatus::Draft,
        ]);
        $owner = User::factory()->create();
        $organization->members()->attach($owner, ['role' => OrganizationRole::Owner->value]);

        $planPage = $this->actingAs($owner)->get(route('organizations.plan.edit', $organization));

        $planPage->assertOk();
        $planPage->assertDontSee('Situs belum bisa dipublikasikan');
    }

    /**
     * The Eksklusif card is badged as the fix and the panel links to it, but nothing pre-selects
     * it - the owner still has to click "Pilih paket Infak ini" themselves. Auto-selecting the most
     * expensive plan on their behalf would read as a dark pattern; see the view's
     * $needsExclusivePlan comment for the full reasoning. No publish attempt needed first, same
     * as the panel above.
     */
    public function test_the_exclusive_plan_is_badged_but_not_preselected(): void
    {
        [$organization, $owner] = $this->organizationOnExclusiveTemplateWithStarterPlan();

        $planPage = $this->actingAs($owner)->get(route('organizations.plan.edit', $organization));
        $html = $planPage->getContent();

        $planPage->assertOk();
        $planPage->assertSee('Paket Infak yang Anda butuhkan');
        $planPage->assertSee('Lihat paket Infak Eksklusif');
        $planPage->assertSee('href="#pricing-cards"', false);

        // "selected" in the Alpine x-data must still be the org's current (Starter) plan id,
        // never the Eksklusif plan's - nothing pre-picks it.
        $eksklusifId = Plan::where('key', 'eksklusif')->firstOrFail()->id;
        $starterId = $organization->plan_id;
        $this->assertMatchesRegularExpression('/"selected":\s*'.$starterId.'\b/', $html);
        $this->assertDoesNotMatchRegularExpression('/"selected":\s*'.$eksklusifId.'\b/', $html);
    }

    /**
     * Regression test for the bug this panel was rebuilt to fix: refreshing the plan page after
     * a failed publish attempt used to lose the panel (it depended on session('error'), which
     * doesn't survive a second request) even though the organization was exactly as unpublishable
     * as before. A second, unrelated visit must see the same panel as the first.
     */
    public function test_the_panel_survives_a_refresh_after_the_failed_publish_attempt(): void
    {
        [$organization, $owner] = $this->organizationOnExclusiveTemplateWithStarterPlan();

        $this->actingAs($owner)->patch(route('organizations.publish', $organization));

        // First visit: session('error') is still set (this is the redirect target).
        $this->actingAs($owner)
            ->get(route('organizations.plan.edit', $organization))
            ->assertSee('Situs belum bisa dipublikasikan');

        // Second visit ("refresh"): a fresh request with no flash session data at all - the
        // panel must still be there because the violation itself hasn't changed.
        $this->actingAs($owner)
            ->get(route('organizations.plan.edit', $organization))
            ->assertSee('Situs belum bisa dipublikasikan');
    }
}
