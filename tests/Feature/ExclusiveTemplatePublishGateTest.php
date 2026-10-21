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
 * Anyone may build with an exclusive widget (config/page-builder.php's `exclusive` flag) or an
 * `is_exclusive` section variant regardless of plan; the entitlement is enforced when publishing
 * instead (Organization::planViolations()). The template's own `is_exclusive` flag is NOT itself
 * a publish blocker - Organization::planViolations() deliberately leaves that check commented out,
 * since a template merely *labelled* exclusive but built entirely from non-exclusive widgets must
 * publish on any plan. Only the widgets/variants actually placed on the org's pages matter.
 */
class ExclusiveTemplatePublishGateTest extends TestCase
{
    use RefreshDatabase;

    private function organizationWithExclusiveWidget(string $planKey): Organization
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
            'plan_id' => Plan::where('key', $planKey)->firstOrFail()->id,
            // Paid for, so the only thing that can still be violated is the widget entitlement.
            'plan_expires_at' => now()->addMonths(3),
            'name' => 'Organisasi Eksklusif',
            'slug' => 'organisasi-eksklusif',
            'status' => OrganizationStatus::Draft,
        ]);

        $page = OrganizationPage::factory()->create(['organization_id' => $organization->id, 'slug' => 'home', 'is_home' => true]);
        // 'kalkulator-zakat' carries config/page-builder.php's `exclusive` flag.
        $page->sections()->create(['key' => 'kalkulator-zakat', 'content' => [], 'order' => 0]);

        return $organization;
    }

    public function test_starter_plan_with_an_exclusive_widget_is_a_violation(): void
    {
        $organization = $this->organizationWithExclusiveWidget('starter');

        $this->assertContains(
            '1 widget memerlukan paket Infak Eksklusif',
            $organization->planViolations(),
        );
        $this->assertTrue($organization->violatesPlanRules());
    }

    /**
     * An is_exclusive template with no exclusive widgets/variants placed on it is not itself a
     * violation - the template label alone doesn't block publishing.
     */
    public function test_an_exclusive_template_with_no_exclusive_widgets_is_not_flagged(): void
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
            'name' => 'Organisasi Label Eksklusif',
            'slug' => 'organisasi-label-eksklusif',
            'status' => OrganizationStatus::Draft,
        ]);

        $this->assertNotContains(
            'Template ini memerlukan paket Infak Eksklusif',
            $organization->planViolations(),
        );
        $this->assertFalse($organization->violatesPlanRules());
    }

    /**
     * The whole point of gating at publish time: upgrading alone clears it, so the site the owner
     * already built stays exactly as it is.
     */
    public function test_upgrading_to_eksklusif_clears_the_violation(): void
    {
        $organization = $this->organizationWithExclusiveWidget('starter');

        $organization->update(['plan_id' => Plan::where('key', 'eksklusif')->firstOrFail()->id]);

        $this->assertNotContains(
            '1 widget memerlukan paket Infak Eksklusif',
            $organization->fresh()->planViolations(),
        );
    }

    public function test_publishing_is_blocked_while_the_plan_lacks_the_entitlement(): void
    {
        $organization = $this->organizationWithExclusiveWidget('starter');
        $owner = User::factory()->create();
        $organization->members()->attach($owner, ['role' => OrganizationRole::Owner->value]);

        $this->actingAs($owner)
            ->patch(route('organizations.publish', $organization), ['status' => 'published'])
            ->assertRedirect();

        $this->assertSame(OrganizationStatus::Draft, $organization->fresh()->status);
    }

    public function test_a_standard_template_on_starter_is_not_flagged(): void
    {
        $type = OrganizationType::factory()->create();
        $template = Template::factory()->create([
            'organization_type_id' => $type->id,
            'is_active' => true,
            'is_exclusive' => false,
        ]);

        $organization = Organization::create([
            'organization_type_id' => $type->id,
            'template_id' => $template->id,
            'plan_id' => Plan::where('key', 'starter')->firstOrFail()->id,
            'plan_expires_at' => now()->addMonths(3),
            'name' => 'Organisasi Standar',
            'slug' => 'organisasi-standar',
            'status' => OrganizationStatus::Draft,
        ]);

        $this->assertNotContains(
            'Template ini memerlukan paket Infak Eksklusif',
            $organization->planViolations(),
        );
    }
}
