<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\Plan;
use App\Models\SectionVariant;
use App\Models\Template;
use App\Models\Testimonial;
use App\Models\User;
use App\Services\PlanLimitService;
use Database\Seeders\OrganizationTypeSeeder;
use Database\Seeders\SdMadyaTemplateSeeder;
use Database\Seeders\SectionVariantSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class TestimonialCmsTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private function school(string $planKey = 'eksklusif', bool $sandbox = false): Organization
    {
        if (! Template::where('slug', SdMadyaTemplateSeeder::SLUG)->exists()) {
            $this->seed(OrganizationTypeSeeder::class);
            $this->seed(SectionVariantSeeder::class);
            $this->seed(SdMadyaTemplateSeeder::class);
        }

        $template = Template::where('slug', SdMadyaTemplateSeeder::SLUG)->firstOrFail();

        $this->owner = User::factory()->create();

        $organization = Organization::create([
            'user_id' => $this->owner->id,
            'is_sandbox' => $sandbox,
            'organization_type_id' => $template->organization_type_id,
            'template_id' => $template->id,
            'plan_id' => Plan::where('key', $planKey)->firstOrFail()->id,
            'name' => 'SD Testi',
            'slug' => 'sd-testi-'.Str::random(6),
        ]);
        $organization->ensureHomePageExists();

        return $organization->fresh();
    }

    public function test_the_section_is_exclusive_and_cms_backed(): void
    {
        $this->seed(SectionVariantSeeder::class);

        $this->assertTrue((bool) config('page-builder.sections.testimoni.exclusive'));
        $this->assertSame(
            'organizations.testimonials.index',
            config('page-builder.sections.testimoni.cms.route')
        );

        $variant = SectionVariant::where('section_key', 'testimoni')->where('variant_key', 'sd-madya')->first();
        $this->assertNotNull($variant, 'the sd-madya variant must be registered');
        $this->assertTrue((bool) $variant->is_exclusive);
    }

    public function test_quotes_render_from_the_cms_rather_than_section_content(): void
    {
        // Sample CMS rows are seeded for a SANDBOX only (Organization::seedPagesFromTemplate),
        // which is where the template designer needs the section to look filled in.
        $sandbox = $this->school('eksklusif', sandbox: true);
        $this->assertGreaterThan(0, $sandbox->testimonials()->count(), 'sandbox samples should seed');

        $organization = $this->school();
        Testimonial::where('organization_id', $organization->id)->delete();
        $organization->testimonials()->create([
            'quote' => 'Kutipan dari CMS.',
            'name' => 'Narasumber CMS',
            'role' => 'Alumni',
            'is_active' => true,
            'order' => 0,
        ]);

        // Hidden rows must not reach the page.
        $organization->testimonials()->create([
            'quote' => 'Kutipan tersembunyi.',
            'name' => 'Disembunyikan',
            'is_active' => false,
            'order' => 1,
        ]);

        $page = $organization->pages()->where('is_home', true)->firstOrFail();

        $html = view('organizations.pages._render', [
            'organization' => $organization,
            'page' => $page->load('sections'),
        ])->render();

        $this->assertStringContainsString('Narasumber CMS', $html);
        $this->assertStringNotContainsString('Kutipan tersembunyi', $html);
    }

    public function test_a_lower_plan_cannot_add_quotes(): void
    {
        $limits = app(PlanLimitService::class);

        $starter = $this->school('starter');
        $this->assertSame(0, $limits->effectiveLimit($starter, 'testimonials'));
        $this->assertFalse($limits->canCreate($starter, 'testimonials'));

        // And publishing is blocked, which is where the exclusive template is enforced.
        $this->assertContains('Template ini memerlukan paket Eksklusif', $starter->planViolations());
    }

    public function test_owner_can_manage_quotes_through_the_cms_screen(): void
    {
        $organization = $this->school();
        $user = $this->owner;
        $organization->members()->attach($user->id, ['role' => 'owner']);

        Testimonial::where('organization_id', $organization->id)->delete();

        $this->actingAs($user)
            ->post(route('organizations.testimonials.store', $organization), [
                'quote' => 'Anak saya jadi terbiasa salat tepat waktu.',
                'name' => 'Wali Murid',
                'role' => 'Wali Murid Kelas IV',
                'is_active' => '1',
            ])->assertRedirect();

        $testimonial = $organization->testimonials()->firstOrFail();
        $this->assertSame('Wali Murid', $testimonial->name);

        $this->actingAs($user)->get(route('organizations.testimonials.index', $organization))
            ->assertOk()
            ->assertSee('Wali Murid');

        $this->actingAs($user)
            ->delete(route('organizations.testimonials.destroy', [$organization, $testimonial]))
            ->assertRedirect();

        $this->assertSame(0, $organization->testimonials()->count());
    }
}
