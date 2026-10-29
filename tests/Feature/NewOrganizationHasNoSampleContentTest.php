<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\OrganizationType;
use App\Models\Plan;
use App\Models\Template;
use App\Models\User;
use Database\Seeders\KlinikAisyiyahAmbuluStandarTemplateSeeder;
use Database\Seeders\KlinikAisyiyahAmbuluTemplateSeeder;
use Database\Seeders\MasjidNurulHudaStandarTemplateSeeder;
use Database\Seeders\MasjidNurulHudaTemplateSeeder;
use Database\Seeders\OrganizationTypeSeeder;
use Database\Seeders\PcaAmbuluEksklusifTemplateSeeder;
use Database\Seeders\PcaAmbuluTemplateSeeder;
use Database\Seeders\PcmAmbuluEksklusifTemplateSeeder;
use Database\Seeders\PcmAmbuluTemplateSeeder;
use Database\Seeders\SuaraMuhammadiyahAmbuluStandarTemplateSeeder;
use Database\Seeders\SuaraMuhammadiyahAmbuluTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Creating an organization from a template copies the template's LAYOUT - its pages, its
 * sections and those sections' own `content` - but must never copy the showcase's CMS
 * RECORDS: its news posts, agendas, officers, gallery photos, donation programs, financial
 * reports and the rest.
 *
 * Why this is a guarantee worth a dedicated test rather than an assertion tacked onto the
 * Nurul Huda suite: sample rows are seeded already Published (see CmsSampleDataSeeder), so
 * anything that leaks through here is content describing a DIFFERENT institution that goes
 * live on the new organization's tenant domain the moment its owner hits publish - another
 * masjid's kajian schedule under their name. The blast radius is every template, so this
 * checks every seeded template and every CMS relation rather than one of each, and drives
 * the real HTTP path (create form -> builder) rather than calling the cloning method
 * directly, since that is the path a user actually takes.
 */
class NewOrganizationHasNoSampleContentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Every CMS relation on Organization. Listed explicitly rather than derived by reflection
     * so that adding a new CMS resource without adding it here is a visible omission in review,
     * not a silently unchecked table.
     */
    private const CMS_RELATIONS = [
        'posts',
        'agendas',
        'announcements',
        'officers',
        'programs',
        'networks',
        'photos',
        'facilities',
        'financialReports',
        'donationPrograms',
    ];

    /**
     * Plans and section variants already come from Tests\TestCase; this adds the org types the
     * templates are keyed to, plus the template catalogue itself. DatabaseSeeder isn't reusable
     * here because it re-runs PlanSeeder and trips plans.key's unique constraint.
     */
    private function seedTemplateCatalogue(): void
    {
        $this->seed(OrganizationTypeSeeder::class);

        foreach ([
            PcmAmbuluTemplateSeeder::class,
            PcmAmbuluEksklusifTemplateSeeder::class,
            PcaAmbuluTemplateSeeder::class,
            PcaAmbuluEksklusifTemplateSeeder::class,
            KlinikAisyiyahAmbuluTemplateSeeder::class,
            KlinikAisyiyahAmbuluStandarTemplateSeeder::class,
            SuaraMuhammadiyahAmbuluTemplateSeeder::class,
            SuaraMuhammadiyahAmbuluStandarTemplateSeeder::class,
            MasjidNurulHudaTemplateSeeder::class,
            MasjidNurulHudaStandarTemplateSeeder::class,
        ] as $seeder) {
            $this->seed($seeder);
        }
    }

    /**
     * The whole catalogue, driven through the real create-then-open-builder flow. Templates
     * differ in which CMS-backed sections they carry (only the masjid ones have
     * fasilitas-masjid/donasi-progress), so a single template
     * could pass while another still leaked.
     */
    public function test_no_template_copies_its_sample_cms_content_into_a_new_organization(): void
    {
        $this->seedTemplateCatalogue();

        $user = User::factory()->create();

        // A template with no organization_type_id can't be picked at all
        // (StoreOrganizationRequest rejects it via notIn(typelessTemplateIds())), so it has no
        // creation path to leak through.
        $templates = Template::whereNotNull('organization_type_id')->where('is_active', true)->get();

        $this->assertGreaterThan(1, $templates->count(), 'Template catalogue failed to seed.');

        foreach ($templates as $index => $template) {
            $response = $this->actingAs($user)->post(route('organizations.store'), [
                'template_id' => $template->id,
                'name' => 'Organisasi Uji '.$index,
                'slug' => 'organisasi-uji-'.$index,
            ]);

            $response->assertSessionHasNoErrors();
            $response->assertRedirect();

            $organization = Organization::where('slug', 'organisasi-uji-'.$index)->firstOrFail();

            // Cloning happens when the builder is first opened, not on create - so the check
            // has to go through the builder or it would pass against an org that was simply
            // never populated yet.
            $this->actingAs($user)
                ->get(route('organizations.builder.edit', $organization))
                ->assertOk();

            $organization->refresh();

            $this->assertTrue(
                $organization->pages()->exists(),
                "Template [{$template->slug}] cloned no pages at all - this test would pass vacuously.",
            );

            foreach (self::CMS_RELATIONS as $relation) {
                $this->assertSame(
                    0,
                    $organization->{$relation}()->count(),
                    "Template [{$template->slug}] leaked sample [{$relation}] into a new organization.",
                );
            }
        }
    }

    /**
     * Switching an existing organization onto another template re-runs the same cloning path
     * (Organization::replaceTemplateKeepingContent() -> seedPagesFromTemplate()), so it needs
     * the same guarantee: picking a new design must not import the new template's showcase
     * records either.
     */
    public function test_switching_template_does_not_import_the_new_templates_sample_content(): void
    {
        $this->seedTemplateCatalogue();

        $user = User::factory()->create();
        $first = Template::whereNotNull('organization_type_id')->where('is_active', true)->firstOrFail();
        $second = Template::whereNotNull('organization_type_id')
            ->where('is_active', true)
            ->where('id', '!=', $first->id)
            ->firstOrFail();

        $this->actingAs($user)->post(route('organizations.store'), [
            'template_id' => $first->id,
            'name' => 'Organisasi Ganti Tema',
            'slug' => 'organisasi-ganti-tema',
        ])->assertSessionHasNoErrors()->assertRedirect();

        $organization = Organization::where('slug', 'organisasi-ganti-tema')->firstOrFail();

        $this->actingAs($user)->get(route('organizations.builder.edit', $organization))->assertOk();

        $organization->refresh();
        $organization->replaceTemplateKeepingContent($second);
        $organization->refresh();

        foreach (self::CMS_RELATIONS as $relation) {
            $this->assertSame(
                0,
                $organization->{$relation}()->count(),
                "Switching to template [{$second->slug}] leaked sample [{$relation}].",
            );
        }
    }

    /**
     * The counterpart guarantee: a sandbox organization - the platform's own template design
     * tool, and what TemplatePreviewController renders for the public catalogue - still DOES
     * get the sample content. Without this, "no content anywhere" would silently pass this
     * file while breaking the preview, which is the one place the showcase content is the
     * whole point.
     */
    public function test_a_sandbox_organization_still_receives_sample_content(): void
    {
        $this->seedTemplateCatalogue();

        $template = Template::where('slug', 'masjid-nurul-huda-eksklusif')->firstOrFail();

        $sandbox = Organization::create([
            'organization_type_id' => $template->organization_type_id
                ?? OrganizationType::query()->value('id'),
            'template_id' => $template->id,
            'plan_id' => Plan::where('key', 'eksklusif')->firstOrFail()->id,
            'name' => 'Sandbox Uji',
            'slug' => 'sandbox-uji',
            'is_sandbox' => true,
        ]);

        $sandbox->ensureHomePageExists();
        $sandbox->refresh();

        $this->assertGreaterThan(0, $sandbox->officers()->count());
        $this->assertGreaterThan(0, $sandbox->agendas()->count());
    }
}
