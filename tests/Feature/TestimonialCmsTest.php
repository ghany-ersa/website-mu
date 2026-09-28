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

    /**
     * Gated at the VARIANT level, not the section level - the same split as hero/prestasi/cta.
     * Marking the section itself `exclusive` closed it for every plan and made its own
     * non-exclusive `standar` variant unreachable, which is not what a default variant is for.
     */
    public function test_the_section_is_open_but_its_editorial_variant_is_gated(): void
    {
        $this->seed(SectionVariantSeeder::class);

        $this->assertEmpty(
            config('page-builder.sections.testimoni.exclusive'),
            'gating the section closes it for every plan, including its standar variant'
        );

        $standar = SectionVariant::where('section_key', 'testimoni')->where('variant_key', 'standar')->first();
        $this->assertNotNull($standar);
        $this->assertFalse((bool) $standar->is_exclusive, 'standar must be usable by any plan');
        $this->assertTrue((bool) $standar->is_default);

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

    /**
     * A lower plan gets a real, smaller quota rather than none: at 0 it could add the section
     * but never fill it, leaving an empty state it has no way to resolve.
     */
    public function test_a_lower_plan_can_still_collect_quotes_within_its_quota(): void
    {
        $limits = app(PlanLimitService::class);

        $starter = $this->school('starter');
        $this->assertSame(3, $limits->effectiveLimit($starter, 'testimonials'));
        $this->assertTrue($limits->canCreate($starter, 'testimonials'));

        // Quotas rise with the tier rather than being all-or-nothing.
        $this->assertSame(6, $limits->effectiveLimit($this->school('premium'), 'testimonials'));
        $this->assertSame(12, $limits->effectiveLimit($this->school('eksklusif'), 'testimonials'));
    }

    /**
     * Regression: the scroll-reveal observer fades an element in when it intersects the
     * VIEWPORT. A carousel card past the first screen sits outside the rail's horizontal scroll
     * and never intersects, so a per-card `reveal` left every card but the first at opacity 0 -
     * the carousel looked empty on first paint until you scrolled it sideways. The rail itself
     * is always in view, so the reveal belongs there.
     */
    public function test_carousel_cards_are_not_individually_scroll_revealed(): void
    {
        foreach (['standar', 'sd-madya'] as $variant) {
            $blade = file_get_contents(
                resource_path("views/templates/sections/testimoni/{$variant}.blade.php")
            );

            $this->assertMatchesRegularExpression(
                '/<div class="reveal flex[^"]*snap-x/s',
                $blade,
                "[{$variant}] the rail itself must carry the reveal"
            );

            // Plain CSS scroll-snap: no Alpine, no arrow buttons, nothing to break with JS off.
            $this->assertDoesNotMatchRegularExpression(
                '/x-(data|ref|init)=/',
                $blade,
                "[{$variant}] the slide rail needs no JS"
            );

            $this->assertDoesNotMatchRegularExpression(
                '/<figure class="[^"]*\breveal\b/',
                $blade,
                "[{$variant}] a card inside the rail must not be revealed individually"
            );
        }
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
