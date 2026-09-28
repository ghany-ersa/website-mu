<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\Plan;
use App\Models\Template;
use App\Models\User;
use Database\Seeders\BlankTemplateSeeder;
use Database\Seeders\OrganizationTypeSeeder;
use Database\Seeders\SdMadyaTemplateSeeder;
use Database\Seeders\SectionVariantSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationSidebarCmsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Every section that declares a `cms` entry in the registry needs a sidebar item, or its
     * screen is only reachable by typing the URL. The menu is built by hand in
     * layouts/organization.blade.php, so a new CMS-backed section is easy to add without ever
     * appearing in the nav - which is exactly what happened to prestasi, testimoni and kegiatan.
     */
    public function test_every_cms_backed_section_has_a_sidebar_entry(): void
    {
        $sidebar = file_get_contents(resource_path('views/layouts/organization.blade.php'));

        foreach (config('page-builder.sections') as $key => $section) {
            if (empty($section['cms'])) {
                continue;
            }

            $this->assertStringContainsString(
                "sections.{$key}.cms.route",
                $sidebar,
                "Section [{$key}] is CMS-backed but has no sidebar entry; its screen would be unreachable from the nav."
            );
        }
    }

    public function test_the_school_sections_show_up_in_the_nav(): void
    {
        $this->seed(OrganizationTypeSeeder::class);
        $this->seed(SectionVariantSeeder::class);
        $this->seed(SdMadyaTemplateSeeder::class);

        $template = Template::where('slug', SdMadyaTemplateSeeder::SLUG)->firstOrFail();
        $user = User::factory()->create();

        $organization = Organization::create([
            'user_id' => $user->id,
            'organization_type_id' => $template->organization_type_id,
            'template_id' => $template->id,
            'plan_id' => Plan::where('key', 'eksklusif')->firstOrFail()->id,
            'name' => 'SD Nav',
            'slug' => 'sd-nav',
        ]);
        $organization->members()->attach($user->id, ['role' => 'owner']);
        $organization->ensureHomePageExists();

        $html = $this->actingAs($user)
            ->get(route('organizations.show', $organization))
            ->assertOk()
            ->getContent();

        foreach (['Prestasi', 'Testimoni', 'Kegiatan'] as $label) {
            $this->assertStringContainsString($label, $html, "[{$label}] is missing from the sidebar.");
        }

        $this->assertStringContainsString(route('organizations.achievements.index', $organization), $html);
        $this->assertStringContainsString(route('organizations.testimonials.index', $organization), $html);
    }

    /** A menu item only appears when the organization actually has that section. */
    public function test_a_section_the_organization_lacks_stays_hidden(): void
    {
        $this->seed(OrganizationTypeSeeder::class);
        $this->seed(SectionVariantSeeder::class);
        $this->seed(BlankTemplateSeeder::class);

        $template = Template::where('slug', BlankTemplateSeeder::SLUG)->firstOrFail();
        $user = User::factory()->create();

        $organization = Organization::create([
            'user_id' => $user->id,
            'organization_type_id' => $template->organization_type_id,
            'template_id' => $template->id,
            'plan_id' => Plan::where('key', 'eksklusif')->firstOrFail()->id,
            'name' => 'Kosong',
            'slug' => 'kosong-nav',
        ]);
        $organization->members()->attach($user->id, ['role' => 'owner']);
        $organization->ensureHomePageExists();

        $html = $this->actingAs($user)
            ->get(route('organizations.show', $organization))
            ->assertOk()
            ->getContent();

        $this->assertStringNotContainsString(route('organizations.testimonials.index', $organization), $html);
    }
}
