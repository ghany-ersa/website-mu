<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\OrganizationType;
use App\Models\Template;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Creating an organization used to ask for an organization type and auto-pick a template from it.
 * That is now inverted: the user picks a template (OrganizationController::createTemplate()) and
 * the type is derived from it, so these tests pin the derivation and the guards that keep a
 * template which could never yield a valid organization from reaching the insert.
 */
class OrganizationStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_organization_type_is_derived_from_the_chosen_template(): void
    {
        $user = User::factory()->create();
        $type = OrganizationType::factory()->create();
        $template = Template::factory()->create(['organization_type_id' => $type->id, 'is_active' => true]);

        $this->actingAs($user)->post(route('organizations.store'), [
            'template_id' => $template->id,
            'name' => 'Test Org',
            'slug' => 'test-org-'.uniqid(),
        ])->assertRedirect();

        $organization = Organization::where('name', 'Test Org')->firstOrFail();
        $this->assertSame($template->id, $organization->template_id);
        $this->assertSame($type->id, $organization->organization_type_id);
    }

    /**
     * A posted organization_type_id is ignored rather than trusted, so a hand-crafted request
     * can't pair a template with a type that contradicts it.
     */
    public function test_posted_organization_type_is_overridden_by_the_template(): void
    {
        $user = User::factory()->create();
        $templateType = OrganizationType::factory()->create();
        $unrelatedType = OrganizationType::factory()->create();
        $template = Template::factory()->create(['organization_type_id' => $templateType->id, 'is_active' => true]);

        $this->actingAs($user)->post(route('organizations.store'), [
            'template_id' => $template->id,
            'organization_type_id' => $unrelatedType->id,
            'name' => 'Test Org Spoof',
            'slug' => 'test-org-spoof-'.uniqid(),
        ])->assertRedirect();

        $organization = Organization::where('name', 'Test Org Spoof')->firstOrFail();
        $this->assertSame($templateType->id, $organization->organization_type_id);
    }

    public function test_template_id_is_required(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('organizations.store'), [
            'name' => 'No Template Org',
            'slug' => 'no-template-org-'.uniqid(),
        ])->assertSessionHasErrors('template_id');

        $this->assertDatabaseMissing('organizations', ['name' => 'No Template Org']);
    }

    public function test_inactive_template_is_rejected(): void
    {
        $user = User::factory()->create();
        $type = OrganizationType::factory()->create();
        $template = Template::factory()->create(['organization_type_id' => $type->id, 'is_active' => false]);

        $this->actingAs($user)->post(route('organizations.store'), [
            'template_id' => $template->id,
            'name' => 'Inactive Template Org',
            'slug' => 'inactive-template-org-'.uniqid(),
        ])->assertSessionHasErrors('template_id');

        $this->assertDatabaseMissing('organizations', ['name' => 'Inactive Template Org']);
    }

    /**
     * Every organization is created on Starter, which never has has_exclusive_templates.
     */
    public function test_exclusive_template_is_rejected(): void
    {
        $user = User::factory()->create();
        $type = OrganizationType::factory()->create();
        $template = Template::factory()->create([
            'organization_type_id' => $type->id,
            'is_active' => true,
            'is_exclusive' => true,
        ]);

        $this->actingAs($user)->post(route('organizations.store'), [
            'template_id' => $template->id,
            'name' => 'Exclusive Org',
            'slug' => 'exclusive-org-'.uniqid(),
        ])->assertSessionHasErrors('template_id');

        $this->assertDatabaseMissing('organizations', ['name' => 'Exclusive Org']);
    }

    /**
     * organizations.organization_type_id is NOT NULL while templates.organization_type_id is
     * nullable, so a typeless template has to fail as a validation error rather than a SQL error.
     */
    public function test_template_without_an_organization_type_is_rejected(): void
    {
        $user = User::factory()->create();
        $template = Template::factory()->create(['organization_type_id' => null, 'is_active' => true]);

        $this->actingAs($user)->post(route('organizations.store'), [
            'template_id' => $template->id,
            'name' => 'Typeless Org',
            'slug' => 'typeless-org-'.uniqid(),
        ])->assertSessionHasErrors('template_id');

        $this->assertDatabaseMissing('organizations', ['name' => 'Typeless Org']);
    }
}
