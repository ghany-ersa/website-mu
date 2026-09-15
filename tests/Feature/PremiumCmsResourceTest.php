<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\OrganizationPage;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The CMS resources behind the premium mosque sections - facilities, financial reports and
 * donation programs. Each of their controllers gates on the organization actually having the
 * matching section (abort_unless($organization->hasSection(...))) *before* the policy check,
 * so the section is part of every fixture here; without it these routes 404 for members and
 * non-members alike, which would make an authorization test pass for the wrong reason.
 *
 * Donations get the most attention: they carry money amounts shown publicly as a progress bar,
 * so a transaction attributed to the wrong program misreports what a mosque has collected.
 */
class PremiumCmsResourceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * An organization whose home page carries the given section key, owned by $owner.
     */
    private function organizationWithSection(User $owner, string $sectionKey): Organization
    {
        $organization = Organization::factory()->withOwner($owner)->create();

        OrganizationPage::factory()
            ->create(['organization_id' => $organization->id, 'slug' => 'home', 'is_home' => true])
            ->sections()
            ->create(['key' => $sectionKey, 'content' => [], 'order' => 0]);

        return $organization->fresh();
    }

    // ------------------------------------------------------------ facilities

    public function test_a_member_can_create_update_and_delete_a_facility(): void
    {
        $owner = User::factory()->create();
        $organization = $this->organizationWithSection($owner, 'fasilitas-masjid');

        $this->actingAs($owner)->get(route('organizations.facilities.index', $organization))->assertOk();

        $this->actingAs($owner)->post(route('organizations.facilities.store', $organization), [
            'name' => 'Tempat Wudhu',
            'description' => 'Tersedia untuk jamaah putra dan putri.',
        ])->assertRedirect();

        $facility = $organization->facilities()->firstOrFail();
        $this->assertSame('Tempat Wudhu', $facility->name);

        $this->actingAs($owner)->patch(route('organizations.facilities.update', [$organization, $facility]), [
            'name' => 'Tempat Wudhu Baru',
        ])->assertRedirect();

        $this->assertSame('Tempat Wudhu Baru', $facility->fresh()->name);

        $this->actingAs($owner)->delete(route('organizations.facilities.destroy', [$organization, $facility]))
            ->assertRedirect();

        $this->assertModelMissing($facility);
    }

    public function test_a_facility_requires_a_name(): void
    {
        $owner = User::factory()->create();
        $organization = $this->organizationWithSection($owner, 'fasilitas-masjid');

        $this->actingAs($owner)
            ->from(route('organizations.facilities.create', $organization))
            ->post(route('organizations.facilities.store', $organization), ['description' => 'tanpa nama'])
            ->assertSessionHasErrors('name');

        $this->assertSame(0, $organization->facilities()->count());
    }

    public function test_a_facility_from_another_organization_is_not_found(): void
    {
        $owner = User::factory()->create();
        $organization = $this->organizationWithSection($owner, 'fasilitas-masjid');
        $other = $this->organizationWithSection(User::factory()->create(), 'fasilitas-masjid');

        $facility = $other->facilities()->create(['name' => 'Milik Tetangga']);

        $this->actingAs($owner)
            ->patch(route('organizations.facilities.update', [$organization, $facility]), ['name' => 'Dibajak'])
            ->assertNotFound();

        $this->assertSame('Milik Tetangga', $facility->fresh()->name);
    }

    public function test_a_non_member_cannot_manage_facilities(): void
    {
        $owner = User::factory()->create();
        $outsider = User::factory()->create();
        $organization = $this->organizationWithSection($owner, 'fasilitas-masjid');

        $this->actingAs($outsider)->get(route('organizations.facilities.index', $organization))->assertForbidden();
        $this->actingAs($outsider)->post(route('organizations.facilities.store', $organization), ['name' => 'Selundupan'])
            ->assertForbidden();

        $this->assertSame(0, $organization->facilities()->count());
    }

    // ------------------------------------------------------ financial reports

    public function test_a_member_can_record_and_delete_a_financial_report_entry(): void
    {
        $owner = User::factory()->create();
        $organization = $this->organizationWithSection($owner, 'laporan-keuangan');

        $this->actingAs($owner)->get(route('organizations.financial-reports.index', $organization))->assertOk();

        $this->actingAs($owner)->post(route('organizations.financial-reports.store', $organization), [
            'transacted_at' => '2026-03-15',
            'type' => 'income',
            'category' => 'Infak Jumat',
            'amount' => 1_500_000,
        ])->assertRedirect();

        $entry = $organization->financialReports()->firstOrFail();

        $this->assertSame(1_500_000, (int) $entry->amount);
        // period_month/period_year are derived from the date, never entered separately.
        $this->assertSame(3, (int) $entry->period_month);
        $this->assertSame(2026, (int) $entry->period_year);

        $this->actingAs($owner)->delete(route('organizations.financial-reports.destroy', [$organization, $entry]))
            ->assertRedirect();

        $this->assertModelMissing($entry);
    }

    public function test_a_financial_entry_rejects_an_unknown_type_and_a_missing_amount(): void
    {
        $owner = User::factory()->create();
        $organization = $this->organizationWithSection($owner, 'laporan-keuangan');

        $this->actingAs($owner)
            ->from(route('organizations.financial-reports.create', $organization))
            ->post(route('organizations.financial-reports.store', $organization), [
                'transacted_at' => '2026-03-15',
                'type' => 'sedekah-misterius',
                'category' => 'Tidak Jelas',
            ])
            ->assertSessionHasErrors(['type', 'amount']);

        $this->assertSame(0, $organization->financialReports()->count());
    }

    public function test_a_non_member_cannot_touch_financial_reports(): void
    {
        $owner = User::factory()->create();
        $outsider = User::factory()->create();
        $organization = $this->organizationWithSection($owner, 'laporan-keuangan');

        $this->actingAs($outsider)->get(route('organizations.financial-reports.index', $organization))->assertForbidden();
    }

    // ------------------------------------------------------------- donations

    public function test_a_member_can_create_and_update_a_donation_program(): void
    {
        $owner = User::factory()->create();
        $organization = $this->organizationWithSection($owner, 'donasi-progress');

        $this->actingAs($owner)->get(route('organizations.donations.index', $organization))->assertOk();

        $this->actingAs($owner)->post(route('organizations.donations.store', $organization), [
            'name' => 'Pembangunan Menara',
            'description' => 'Renovasi menara masjid.',
            'target_amount' => 100_000_000,
        ])->assertRedirect();

        $program = $organization->donationPrograms()->firstOrFail();

        $this->assertSame('Pembangunan Menara', $program->name);
        $this->assertNotEmpty($program->slug, 'A donation program needs a slug for its public detail URL.');

        $this->actingAs($owner)->patch(route('organizations.donations.update', [$organization, $program]), [
            'name' => 'Pembangunan Menara Tahap 2',
            'target_amount' => 150_000_000,
        ])->assertRedirect();

        $this->assertSame(150_000_000, (int) $program->fresh()->target_amount);
    }

    public function test_a_donation_program_requires_a_positive_target(): void
    {
        $owner = User::factory()->create();
        $organization = $this->organizationWithSection($owner, 'donasi-progress');

        $this->actingAs($owner)
            ->from(route('organizations.donations.create', $organization))
            ->post(route('organizations.donations.store', $organization), [
                'name' => 'Target Nol',
                'target_amount' => 0,
            ])
            ->assertSessionHasErrors('target_amount');

        $this->assertSame(0, $organization->donationPrograms()->count());
    }

    public function test_an_end_date_before_the_start_date_is_rejected(): void
    {
        $owner = User::factory()->create();
        $organization = $this->organizationWithSection($owner, 'donasi-progress');

        $this->actingAs($owner)
            ->from(route('organizations.donations.create', $organization))
            ->post(route('organizations.donations.store', $organization), [
                'name' => 'Rentang Terbalik',
                'target_amount' => 1_000_000,
                'starts_at' => '2026-05-01',
                'ends_at' => '2026-04-01',
            ])
            ->assertSessionHasErrors('ends_at');
    }

    /**
     * The collected total drives a public progress bar, so it has to reflect exactly the
     * transactions recorded against that program - and drop back when one is removed.
     */
    public function test_recording_transactions_moves_the_programs_collected_total(): void
    {
        $owner = User::factory()->create();
        $organization = $this->organizationWithSection($owner, 'donasi-progress');
        $program = $organization->donationPrograms()->create([
            'name' => 'Wakaf Tanah',
            'slug' => 'wakaf-tanah',
            'target_amount' => 10_000_000,
        ]);

        $this->actingAs($owner)->post(route('organizations.donations.transactions.store', [$organization, $program]), [
            'donor_name' => 'Hamba Allah',
            'amount' => 2_500_000,
            'donated_at' => '2026-03-01',
        ])->assertRedirect();

        $this->actingAs($owner)->post(route('organizations.donations.transactions.store', [$organization, $program]), [
            'donor_name' => 'Jamaah',
            'amount' => 1_500_000,
            'donated_at' => '2026-03-02',
        ])->assertRedirect();

        $this->assertSame(4_000_000, $program->fresh()->collectedAmount());
        $this->assertSame(40.0, $program->fresh()->progressPercent());

        $transaction = $program->transactions()->first();
        $this->actingAs($owner)
            ->delete(route('organizations.donations.transactions.destroy', [$organization, $program, $transaction]))
            ->assertRedirect();

        $this->assertSame(1_500_000, $program->fresh()->collectedAmount());
    }

    /**
     * Over-collecting is allowed, but the bar must not run past 100% - a 130% progress bar
     * would render broken on the public page.
     */
    public function test_progress_is_capped_at_one_hundred_percent(): void
    {
        $owner = User::factory()->create();
        $organization = $this->organizationWithSection($owner, 'donasi-progress');
        $program = $organization->donationPrograms()->create([
            'name' => 'Melebihi Target',
            'slug' => 'melebihi-target',
            'target_amount' => 1_000_000,
        ]);

        $program->transactions()->create(['amount' => 3_000_000, 'donated_at' => now()]);

        $this->assertSame(100.0, $program->fresh()->progressPercent());
    }

    public function test_a_transaction_cannot_be_attached_to_another_organizations_program(): void
    {
        $owner = User::factory()->create();
        $organization = $this->organizationWithSection($owner, 'donasi-progress');
        $other = $this->organizationWithSection(User::factory()->create(), 'donasi-progress');

        $foreignProgram = $other->donationPrograms()->create([
            'name' => 'Program Tetangga',
            'slug' => 'program-tetangga',
            'target_amount' => 5_000_000,
        ]);

        // 403, not 404: DonationProgramPolicy::update() rejects the foreign program before
        // ensureBelongsToOrganization() gets to 404 it. Either answer blocks the write; the
        // assertion records which one actually fires.
        $this->actingAs($owner)
            ->post(route('organizations.donations.transactions.store', [$organization, $foreignProgram]), [
                'amount' => 1_000_000,
                'donated_at' => '2026-03-01',
            ])
            ->assertForbidden();

        $this->assertSame(0, $foreignProgram->transactions()->count());
    }

    public function test_a_non_member_cannot_record_a_donation(): void
    {
        $owner = User::factory()->create();
        $outsider = User::factory()->create();
        $organization = $this->organizationWithSection($owner, 'donasi-progress');
        $program = $organization->donationPrograms()->create([
            'name' => 'Donasi Terbuka',
            'slug' => 'donasi-terbuka',
            'target_amount' => 1_000_000,
        ]);

        $this->actingAs($outsider)
            ->post(route('organizations.donations.transactions.store', [$organization, $program]), [
                'amount' => 1_000_000,
                'donated_at' => '2026-03-01',
            ])
            ->assertForbidden();

        $this->assertSame(0, $program->transactions()->count());
    }

    // ----------------------------------------------------------- plan limits

    /**
     * Creation past the plan's quota is refused rather than silently allowed - the limit is
     * what a paid tier is actually selling.
     */
    public function test_creating_past_the_plan_limit_is_refused(): void
    {
        $owner = User::factory()->create();
        $plan = Plan::create([
            'key' => 'uji-batas-fasilitas',
            'name' => 'Uji Batas',
            'price_monthly' => 10_000,
            'is_active' => true,
        ]);
        $plan->limits()->create(['key' => 'facilities', 'max_count' => 1]);

        $organization = $this->organizationWithSection($owner, 'fasilitas-masjid');
        $organization->update(['plan_id' => $plan->id]);

        $this->actingAs($owner)->post(route('organizations.facilities.store', $organization), ['name' => 'Fasilitas Pertama'])
            ->assertRedirect();

        $this->actingAs($owner)->post(route('organizations.facilities.store', $organization), ['name' => 'Fasilitas Kedua'])
            ->assertRedirect();

        $this->assertSame(1, $organization->fresh()->facilities()->count(), 'The second facility exceeded the plan limit and should not have been created.');
    }
}
