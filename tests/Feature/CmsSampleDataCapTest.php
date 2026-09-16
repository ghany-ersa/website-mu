<?php

namespace Tests\Feature;

use App\Enums\OrganizationStatus;
use App\Models\Organization;
use App\Models\OrganizationType;
use App\Models\Plan;
use App\Models\Template;
use Database\Seeders\MasjidNurulHudaTemplateSeeder;
use Database\Seeders\OrganizationTypeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Cloning a template seeds sample CMS rows as a starting point, capped at
 * CmsSampleDataSeeder::MAX_SAMPLES_PER_RESOURCE per resource so a new organization opens its
 * builder with an example of each section rather than a wall of someone else's content to delete.
 *
 * The cap sits ON TOP OF the plan limits, never instead of them: whichever is smaller wins, so
 * cloning still cannot leave an organization in violation of its own plan.
 */
class CmsSampleDataCapTest extends TestCase
{
    use RefreshDatabase;

    private function makeOrganization(string $planKey): Organization
    {
        $this->seed(OrganizationTypeSeeder::class);
        $this->seed(MasjidNurulHudaTemplateSeeder::class);

        $organization = Organization::create([
            'organization_type_id' => OrganizationType::where('slug', 'masjidmushola')->firstOrFail()->id,
            'template_id' => Template::where('slug', 'masjid-nurul-huda-eksklusif')->firstOrFail()->id,
            'plan_id' => Plan::where('key', $planKey)->firstOrFail()->id,
            'name' => 'Masjid Uji Batas',
            'slug' => 'masjid-uji-batas',
            'status' => OrganizationStatus::Draft,
        ]);

        $organization->ensureHomePageExists();

        return $organization->fresh();
    }

    /**
     * Professional's limits are all far above the cap (facilities 15, officers 20, posts 20), so
     * this isolates the cap itself - without it these lists clone in full.
     */
    public function test_no_resource_exceeds_the_cap_on_a_generous_plan(): void
    {
        $organization = $this->makeOrganization('professional');

        foreach ([
            'facilities' => $organization->facilities()->count(),
            'officers' => $organization->officers()->count(),
            'photos' => $organization->photos()->count(),
            'agendas' => $organization->agendas()->count(),
            'networks' => $organization->networks()->count(),
            'donationPrograms' => $organization->donationPrograms()->count(),
        ] as $label => $count) {
            $this->assertLessThanOrEqual(3, $count, "$label seeded more than the cap allows.");
        }
    }

    /**
     * Starter allows only 1 donation program and 2 announcements - tighter than the cap, so the
     * plan limit has to keep winning. A brand-new organization must never start out in violation
     * of its own plan (see Organization::planViolations()).
     */
    public function test_a_tighter_plan_limit_still_wins_over_the_cap(): void
    {
        $organization = $this->makeOrganization('starter');

        $this->assertLessThanOrEqual(1, $organization->donationPrograms()->count());
        $this->assertLessThanOrEqual(2, $organization->announcements()->count());

        // Only the content-quota violations matter here; an unpaid plan is a separate condition
        // this organization has by construction (no PlanChangeRequest was ever approved for it).
        $quotaViolations = array_values(array_filter(
            $organization->planViolations(),
            fn (string $violation) => ! str_contains($violation, 'Pembayaran'),
        ));

        $this->assertSame([], $quotaViolations);
    }

    /**
     * The cap truncates; it must not replace real showcase content with placeholders, and it must
     * keep the source list's own order rather than an arbitrary slice.
     */
    public function test_the_kept_samples_are_the_first_of_the_real_list(): void
    {
        $organization = $this->makeOrganization('professional');

        $this->assertSame('Halaman dan Teras Depan', $organization->facilities()->first()->name);
        $this->assertSame('Suhartono, S.Pd', $organization->officers()->first()->name);
    }
}
