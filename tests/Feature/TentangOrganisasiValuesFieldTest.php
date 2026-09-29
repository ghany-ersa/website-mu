<?php

namespace Tests\Feature;

use App\Enums\OrganizationRole;
use App\Enums\OrganizationStatus;
use App\Models\Organization;
use App\Models\OrganizationPage;
use App\Models\OrganizationSection;
use App\Models\OrganizationType;
use App\Models\Plan;
use App\Models\Template;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regression coverage for tentang-organisasi/sd-madya.blade.php's `values` field. The builder's
 * properties panel had no dedicated editor for it until this fix, so saving the section wrote
 * whatever the generic text input held - a bare string - and the section then died on foreach()
 * with a 500 (the same failure mode akad-venue/nurul-huda.blade.php's `facilities` field hit
 * before it got its own list editor). Two things had to change together: the builder now renders
 * a proper list editor for `values` (organizations/builder/edit.blade.php), and the section
 * itself tolerates the older corrupted shape so existing data doesn't keep 500ing.
 */
class TentangOrganisasiValuesFieldTest extends TestCase
{
    use RefreshDatabase;

    private function organizationWithHomePage(): array
    {
        $orgType = OrganizationType::factory()->create();
        $template = Template::factory()->create(['organization_type_id' => $orgType->id]);
        $plan = Plan::where('key', 'starter')->firstOrFail();

        $organization = Organization::create([
            'organization_type_id' => $orgType->id,
            'template_id' => $template->id,
            'plan_id' => $plan->id,
            'name' => 'Sekolah Uji',
            'slug' => 'sekolah-uji',
            'status' => OrganizationStatus::Draft,
        ]);

        $owner = User::factory()->create();
        $organization->members()->attach($owner, ['role' => OrganizationRole::Owner->value]);

        $page = OrganizationPage::create([
            'organization_id' => $organization->id,
            'name' => 'Beranda',
            'slug' => 'home',
            'order' => 0,
            'is_home' => true,
        ]);

        return [$organization, $owner, $page];
    }

    /**
     * The crash this regresses: `values` stored as a bare string (the shape the old generic
     * text-input fallback wrote) instead of an array.
     */
    public function test_a_string_values_field_does_not_crash_the_canvas(): void
    {
        [$organization, $owner, $page] = $this->organizationWithHomePage();

        OrganizationSection::create([
            'organization_page_id' => $page->id,
            'key' => 'tentang-organisasi',
            'variant' => 'sd-madya',
            'order' => 1,
            'content' => ['title' => 'Tentang Kami', 'values' => "Amanah\nJujur\nDisiplin"],
        ]);

        $response = $this->actingAs($owner)
            ->get(route('organizations.builder.canvas', [$organization, $page]));

        $response->assertOk();
        $response->assertSee('Amanah');
        $response->assertSee('Jujur');
        $response->assertSee('Disiplin');
    }

    /**
     * The builder's properties panel must offer a real list editor for `values` - not the
     * generic text input that caused the regression above - so new saves can't reintroduce it.
     */
    public function test_builder_renders_a_list_editor_for_values(): void
    {
        [$organization, $owner, $page] = $this->organizationWithHomePage();

        $section = OrganizationSection::create([
            'organization_page_id' => $page->id,
            'key' => 'tentang-organisasi',
            'variant' => 'sd-madya',
            'order' => 1,
            'content' => ['title' => 'Tentang Kami', 'values' => ['Amanah', 'Jujur']],
        ]);

        $response = $this->actingAs($owner)
            ->get(route('organizations.builder.page', [$organization, $page, 'section' => $section->id]));

        $response->assertOk();
        // The Alpine repeater template - `${index}` is an Alpine expression, not Blade
        // interpolation, so this literal string is what actually reaches the response.
        $response->assertSee('content[values][${index}]', false);
        $response->assertSee('+ Tambah nilai', false);
    }

    /**
     * Saving the panel writes a real array to content[values], not a string - the direct fix for
     * the regression.
     */
    public function test_saving_the_panel_writes_an_array_to_values(): void
    {
        [$organization, $owner, $page] = $this->organizationWithHomePage();

        $section = OrganizationSection::create([
            'organization_page_id' => $page->id,
            'key' => 'tentang-organisasi',
            'variant' => 'sd-madya',
            'order' => 1,
            'content' => ['title' => 'Tentang Kami'],
        ]);

        $this->actingAs($owner)
            ->patch(route('organizations.sections.update', [$organization, $section]), [
                'variant' => 'sd-madya',
                'content' => ['title' => 'Tentang Kami', 'values' => ['Amanah', 'Jujur']],
                'is_visible' => true,
            ])
            ->assertRedirect();

        $this->assertSame(['Amanah', 'Jujur'], $section->fresh()->content['values']);
    }
}
