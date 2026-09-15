<?php

namespace Tests\Feature;

use App\Models\OrganizationType;
use App\Models\Template;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The two-step creation flow: pick a template (step 1), then fill in the identity form (step 2).
 * Step 2 is unreachable without a usable template, which is what makes deriving the organization
 * type from the template safe - see StoreOrganizationRequest::prepareForValidation().
 */
class OrganizationCreateFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_identity_form_redirects_to_the_picker_without_a_template(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('organizations.create'))
            ->assertRedirect(route('organizations.template-picker'));
    }

    public function test_identity_form_renders_with_a_usable_template(): void
    {
        $user = User::factory()->create();
        $type = OrganizationType::factory()->create();
        $template = Template::factory()->create([
            'organization_type_id' => $type->id,
            'is_active' => true,
            'name' => 'Template Layak',
        ]);

        $this->actingAs($user)
            ->get(route('organizations.create', ['template' => $template->slug]))
            ->assertOk()
            ->assertSee('Template Layak')
            ->assertDontSee('Jenis Organisasi');
    }

    public function test_identity_form_rejects_an_exclusive_template(): void
    {
        $user = User::factory()->create();
        $type = OrganizationType::factory()->create();
        $template = Template::factory()->create([
            'organization_type_id' => $type->id,
            'is_active' => true,
            'is_exclusive' => true,
        ]);

        $this->actingAs($user)
            ->get(route('organizations.create', ['template' => $template->slug]))
            ->assertRedirect(route('organizations.template-picker'));
    }

    public function test_identity_form_rejects_a_template_without_an_organization_type(): void
    {
        $user = User::factory()->create();
        $template = Template::factory()->create(['organization_type_id' => null, 'is_active' => true]);

        $this->actingAs($user)
            ->get(route('organizations.create', ['template' => $template->slug]))
            ->assertRedirect(route('organizations.template-picker'));
    }

    /**
     * Exclusive templates are shown locked rather than hidden, so the upgrade path stays visible
     * instead of the template simply being missing from the grid.
     */
    public function test_picker_lists_standard_and_exclusive_templates(): void
    {
        $user = User::factory()->create();
        $type = OrganizationType::factory()->create();
        Template::factory()->create([
            'organization_type_id' => $type->id,
            'is_active' => true,
            'name' => 'Template Standar',
        ]);
        Template::factory()->create([
            'organization_type_id' => $type->id,
            'is_active' => true,
            'is_exclusive' => true,
            'name' => 'Template Eksklusif',
        ]);

        $this->actingAs($user)
            ->get(route('organizations.template-picker'))
            ->assertOk()
            ->assertSee('Template Standar')
            ->assertSee('Template Eksklusif')
            ->assertSee('Tersedia setelah upgrade paket');
    }

    public function test_picker_hides_inactive_and_typeless_templates(): void
    {
        $user = User::factory()->create();
        $type = OrganizationType::factory()->create();
        Template::factory()->create([
            'organization_type_id' => $type->id,
            'is_active' => false,
            'name' => 'Template Nonaktif',
        ]);
        Template::factory()->create([
            'organization_type_id' => null,
            'is_active' => true,
            'name' => 'Template Tanpa Jenis',
        ]);

        $this->actingAs($user)
            ->get(route('organizations.template-picker'))
            ->assertOk()
            ->assertDontSee('Template Nonaktif')
            ->assertDontSee('Template Tanpa Jenis');
    }

    public function test_picker_requires_authentication(): void
    {
        $this->get(route('organizations.template-picker'))->assertRedirect(route('login'));
    }
}
