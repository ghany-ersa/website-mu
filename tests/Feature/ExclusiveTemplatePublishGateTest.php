<?php

namespace Tests\Feature;

use App\Enums\OrganizationRole;
use App\Enums\OrganizationStatus;
use App\Models\Organization;
use App\Models\OrganizationType;
use App\Models\Plan;
use App\Models\Template;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Anyone may build on an is_exclusive template regardless of plan; the entitlement is enforced
 * when publishing instead (Organization::planViolations()). These tests pin that split - the
 * point being that the block lifts on upgrade alone, with no template change needed.
 */
class ExclusiveTemplatePublishGateTest extends TestCase
{
    use RefreshDatabase;

    private function organizationOnExclusiveTemplate(string $planKey): Organization
    {
        $type = OrganizationType::factory()->create();
        $template = Template::factory()->create([
            'organization_type_id' => $type->id,
            'is_active' => true,
            'is_exclusive' => true,
        ]);

        return Organization::create([
            'organization_type_id' => $type->id,
            'template_id' => $template->id,
            'plan_id' => Plan::where('key', $planKey)->firstOrFail()->id,
            // Paid for, so the only thing that can still be violated is the template entitlement.
            'plan_expires_at' => now()->addMonths(3),
            'name' => 'Organisasi Eksklusif',
            'slug' => 'organisasi-eksklusif',
            'status' => OrganizationStatus::Draft,
        ]);
    }

    public function test_starter_plan_on_an_exclusive_template_is_a_violation(): void
    {
        $organization = $this->organizationOnExclusiveTemplate('starter');

        $this->assertContains(
            'Template ini memerlukan paket Eksklusif',
            $organization->planViolations(),
        );
        $this->assertTrue($organization->violatesPlanRules());
    }

    /**
     * The whole point of gating at publish time: upgrading alone clears it, so the site the owner
     * already built stays exactly as it is.
     */
    public function test_upgrading_to_professional_clears_the_violation(): void
    {
        $organization = $this->organizationOnExclusiveTemplate('starter');

        $organization->update(['plan_id' => Plan::where('key', 'eksklusif')->firstOrFail()->id]);

        $this->assertNotContains(
            'Template ini memerlukan paket Eksklusif',
            $organization->fresh()->planViolations(),
        );
    }

    public function test_publishing_is_blocked_while_the_plan_lacks_the_entitlement(): void
    {
        $organization = $this->organizationOnExclusiveTemplate('starter');
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
            'Template ini memerlukan paket Eksklusif',
            $organization->planViolations(),
        );
    }
}
