<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\OrganizationType;
use App\Models\Template;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_template_is_auto_selected_from_organization_type_when_not_given(): void
    {
        $user = User::factory()->create();
        $type = OrganizationType::factory()->create();
        $template = Template::factory()->create(['organization_type_id' => $type->id, 'is_active' => true]);

        $this->actingAs($user)->post(route('organizations.store'), [
            'organization_type_id' => $type->id,
            'name' => 'Test Org',
            'slug' => 'test-org-'.uniqid(),
        ])->assertRedirect();

        $organization = Organization::where('name', 'Test Org')->firstOrFail();
        $this->assertSame($template->id, $organization->template_id);
    }

    public function test_inactive_template_is_not_auto_selected(): void
    {
        $user = User::factory()->create();
        $type = OrganizationType::factory()->create();
        Template::factory()->create(['organization_type_id' => $type->id, 'is_active' => false]);

        $this->actingAs($user)->post(route('organizations.store'), [
            'organization_type_id' => $type->id,
            'name' => 'Test Org 2',
            'slug' => 'test-org-2-'.uniqid(),
        ])->assertRedirect();

        $organization = Organization::where('name', 'Test Org 2')->firstOrFail();
        $this->assertNull($organization->template_id);
    }

    public function test_explicit_template_id_is_not_overridden(): void
    {
        $user = User::factory()->create();
        $type = OrganizationType::factory()->create();
        Template::factory()->create(['organization_type_id' => $type->id, 'is_active' => true]);
        $chosenTemplate = Template::factory()->create(['is_active' => true]);

        $this->actingAs($user)->post(route('organizations.store'), [
            'organization_type_id' => $type->id,
            'template_id' => $chosenTemplate->id,
            'name' => 'Test Org 3',
            'slug' => 'test-org-3-'.uniqid(),
        ])->assertRedirect();

        $organization = Organization::where('name', 'Test Org 3')->firstOrFail();
        $this->assertSame($chosenTemplate->id, $organization->template_id);
    }
}
