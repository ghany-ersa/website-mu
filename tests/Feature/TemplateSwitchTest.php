<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\OrganizationPage;
use App\Models\Plan;
use App\Models\Template;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Switching an organization onto another template. This is the most destructive action a tenant
 * can take short of deleting the organization: it drops every existing page (and with them every
 * section and all their authored content) and re-clones from the new template. So the properties
 * that matter are the guards around it - who may do it, which templates are selectable, and that
 * a no-op switch doesn't wipe anything.
 */
class TemplateSwitchTest extends TestCase
{
    use RefreshDatabase;

    private function professionalPlan(): Plan
    {
        return Plan::create([
            'key' => 'uji-eksklusif',
            'name' => 'Uji Eksklusif',
            'price_monthly' => 25_000,
            'has_exclusive_templates' => true,
            'is_active' => true,
        ]);
    }

    /**
     * An is_exclusive template is selectable from any plan - the same "build now, pay to
     * publish" split as picking one at organization-creation time
     * (OrganizationCreateFlowTest) and the page builder's exclusive sections/variants
     * (ExclusiveTemplatePublishGateTest). It's badged in the view, not withheld from `templates`
     * or routed through a separate locked list.
     */
    public function test_the_picker_lists_both_standard_and_exclusive_templates_on_a_lower_plan(): void
    {
        $owner = User::factory()->create();
        $organization = Organization::factory()->withOwner($owner)->create();

        $standard = Template::factory()->create(['name' => 'Template Standar', 'is_exclusive' => false, 'is_active' => true]);
        $exclusive = Template::factory()->create(['name' => 'Template Eksklusif', 'is_exclusive' => true, 'is_active' => true]);

        $response = $this->actingAs($owner)->get(route('organizations.template.edit', $organization))->assertOk();

        $response->assertViewHas('templates', fn ($templates) => $templates->contains('id', $standard->id)
            && $templates->contains('id', $exclusive->id));
        $response->assertSee('Template Eksklusif');
        $response->assertSee('Eksklusif');
    }

    public function test_an_exclusive_plan_stops_badging_exclusive_templates_as_needing_an_upgrade(): void
    {
        $owner = User::factory()->create();
        $organization = Organization::factory()->withOwner($owner)->create(['plan_id' => $this->professionalPlan()->id]);
        Template::factory()->create(['is_exclusive' => true, 'is_active' => true]);

        $this->actingAs($owner)
            ->get(route('organizations.template.edit', $organization))
            ->assertOk()
            ->assertDontSee('perlu paket Infak Eksklusif saat dipublikasikan', false);
    }

    public function test_switching_template_replaces_the_existing_pages(): void
    {
        $owner = User::factory()->create();
        $from = Template::factory()->create(['is_active' => true]);
        $to = Template::factory()->create(['is_active' => true]);

        $organization = Organization::factory()->withOwner($owner)->create(['template_id' => $from->id]);
        $oldPage = OrganizationPage::factory()->create(['organization_id' => $organization->id, 'slug' => 'home', 'is_home' => true]);
        $oldPage->sections()->create(['key' => 'hero', 'content' => ['headline' => 'Konten Lama'], 'order' => 0]);

        $this->actingAs($owner)
            ->patch(route('organizations.template.update', $organization), ['template_id' => $to->id])
            ->assertRedirect(route('organizations.show', $organization));

        $organization->refresh();

        $this->assertSame($to->id, $organization->template_id);
        $this->assertModelMissing($oldPage);
        // Re-cloned rather than left empty - the org must still have a renderable home page.
        $this->assertTrue($organization->pages()->where('is_home', true)->exists());
    }

    /**
     * Re-selecting the current template must short-circuit *before* the pages are deleted -
     * otherwise a stray double-click would destroy the tenant's work for no change at all.
     */
    public function test_reselecting_the_current_template_leaves_the_pages_untouched(): void
    {
        $owner = User::factory()->create();
        $template = Template::factory()->create(['is_active' => true]);
        $organization = Organization::factory()->withOwner($owner)->create(['template_id' => $template->id]);

        $page = OrganizationPage::factory()->create(['organization_id' => $organization->id, 'slug' => 'home', 'is_home' => true]);
        $section = $page->sections()->create(['key' => 'hero', 'content' => ['headline' => 'Harus Bertahan'], 'order' => 0]);

        $this->actingAs($owner)
            ->patch(route('organizations.template.update', $organization), ['template_id' => $template->id])
            ->assertRedirect(route('organizations.template.edit', $organization));

        $this->assertModelExists($page);
        $this->assertSame('Harus Bertahan', $section->fresh()->content['headline']);
    }

    /**
     * Switching onto an exclusive template while on a lower plan is allowed - build now, pay to
     * publish (Organization::planViolations() is the real gate, not this endpoint). The template's
     * own is_exclusive flag is a label, not a publish blocker by itself (that check is
     * deliberately disabled - see Organization::planViolations()); only the widgets/variants
     * actually placed on the org's pages can block publishing. The redirect still surfaces the
     * label as a heads-up.
     */
    public function test_an_exclusive_template_can_be_applied_on_a_lower_plan(): void
    {
        $owner = User::factory()->create();
        $organization = Organization::factory()->withOwner($owner)->create();
        OrganizationPage::factory()->create(['organization_id' => $organization->id, 'slug' => 'home', 'is_home' => true]);

        $exclusive = Template::factory()->create(['is_exclusive' => true, 'is_active' => true]);

        $this->actingAs($owner)
            ->patch(route('organizations.template.update', $organization), ['template_id' => $exclusive->id])
            ->assertRedirect(route('organizations.show', $organization))
            ->assertSessionHas('status', fn (string $status) => str_contains($status, 'berlabel eksklusif'));

        $this->assertSame($exclusive->id, $organization->fresh()->template_id);
        // The template label alone isn't a violation - no exclusive widget/variant was placed.
        $this->assertNotContains(
            'Template ini memerlukan paket Infak Eksklusif',
            $organization->fresh()->planViolations(),
        );
    }

    public function test_an_unknown_template_id_is_rejected(): void
    {
        $owner = User::factory()->create();
        $organization = Organization::factory()->withOwner($owner)->create();

        $this->actingAs($owner)
            ->from(route('organizations.template.edit', $organization))
            ->patch(route('organizations.template.update', $organization), ['template_id' => 999_999])
            ->assertSessionHasErrors('template_id');
    }

    public function test_a_non_member_cannot_view_or_switch_the_template(): void
    {
        $owner = User::factory()->create();
        $outsider = User::factory()->create();
        $organization = Organization::factory()->withOwner($owner)->create();
        $template = Template::factory()->create(['is_active' => true]);

        $this->actingAs($outsider)->get(route('organizations.template.edit', $organization))->assertForbidden();
        $this->actingAs($outsider)
            ->patch(route('organizations.template.update', $organization), ['template_id' => $template->id])
            ->assertForbidden();

        $this->assertNotSame($template->id, $organization->fresh()->template_id);
    }
}
