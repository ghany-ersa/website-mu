<?php

namespace Tests\Feature;

use App\Enums\OrganizationStatus;
use App\Enums\PlanChangeRequestStatus;
use App\Enums\PlanOverrideAction;
use App\Models\Organization;
use App\Models\OrganizationPage;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Two things that stand between a half-finished tenant and a live public site: the publish
 * gate (Organization::planViolations()) and the admin operations that can move a plan without
 * a payment. Both decide whether a site is allowed to be online, so both need the negative
 * cases pinned, not just the happy path.
 */
class PublishingGateAndAdminOpsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * An organization on a paid, in-date plan with a renderable home page - i.e. one that
     * should pass the gate, so tests can take a single thing away and see it fail.
     */
    private function paidOrganization(User $owner, ?Plan $plan = null): Organization
    {
        $plan ??= Plan::create([
            'key' => 'uji-terbit',
            'name' => 'Uji Terbit',
            'price_monthly' => 18_000,
            'is_active' => true,
        ]);

        $organization = Organization::factory()->withOwner($owner)->create([
            'plan_id' => $plan->id,
            'plan_expires_at' => now()->addMonths(3),
        ]);

        OrganizationPage::factory()
            ->create(['organization_id' => $organization->id, 'slug' => 'home', 'is_home' => true])
            ->sections()
            ->create(['key' => 'hero', 'content' => ['headline' => 'Selamat Datang'], 'order' => 0]);

        // planViolations() treats an organization that never had an approved payment as
        // unpaid, regardless of plan_expires_at - so give it one.
        $organization->planChangeRequests()->create([
            'requested_plan_id' => $plan->id,
            'duration_months' => 3,
            'discount_amount' => 0,
            'requested_by_user_id' => $owner->id,
            'status' => PlanChangeRequestStatus::Approved,
            'reviewed_at' => now(),
        ]);

        return $organization->fresh();
    }

    // ------------------------------------------------------------ publish gate

    public function test_a_paid_organization_can_be_published_and_unpublished(): void
    {
        $owner = User::factory()->create();
        $organization = $this->paidOrganization($owner);

        $this->actingAs($owner)->patch(route('organizations.publish', $organization))->assertRedirect();
        $organization->refresh();

        $this->assertSame(OrganizationStatus::Published, $organization->status);
        $this->assertNotNull($organization->published_at);
        $firstPublishedAt = $organization->published_at;

        $this->actingAs($owner)->patch(route('organizations.publish', $organization))->assertRedirect();
        $organization->refresh();

        $this->assertSame(OrganizationStatus::Draft, $organization->status);
        // published_at is a "first went live at" stamp, not "currently published since".
        $this->assertTrue($firstPublishedAt->equalTo($organization->published_at));
    }

    public function test_republishing_does_not_reset_the_first_published_timestamp(): void
    {
        $owner = User::factory()->create();
        $organization = $this->paidOrganization($owner);

        $this->actingAs($owner)->patch(route('organizations.publish', $organization));
        $firstPublishedAt = $organization->fresh()->published_at;

        $this->actingAs($owner)->patch(route('organizations.publish', $organization)); // to draft
        $this->actingAs($owner)->patch(route('organizations.publish', $organization)); // live again

        $this->assertTrue($firstPublishedAt->equalTo($organization->fresh()->published_at));
    }

    /**
     * Content over the plan's quota blocks publishing - the gate is what stops a tenant from
     * downgrading and keeping a site that exceeds what they now pay for.
     */
    public function test_publishing_is_blocked_while_content_exceeds_the_plan_limit(): void
    {
        $owner = User::factory()->create();
        $plan = Plan::create([
            'key' => 'uji-batas-terbit',
            'name' => 'Uji Batas Terbit',
            'price_monthly' => 10_000,
            'is_active' => true,
        ]);
        $plan->limits()->create(['key' => 'posts', 'max_count' => 1]);

        $organization = $this->paidOrganization($owner, $plan);
        $organization->posts()->createMany([
            ['title' => 'Satu', 'slug' => 'satu', 'content' => 'x'],
            ['title' => 'Dua', 'slug' => 'dua', 'content' => 'x'],
        ]);

        $violations = $organization->fresh()->planViolations();

        $this->assertNotEmpty($violations);
        $this->assertStringContainsString('Berita', implode(' ', $violations));

        $this->actingAs($owner)
            ->patch(route('organizations.publish', $organization))
            ->assertRedirect(route('organizations.plan.edit', $organization));

        $this->assertSame(OrganizationStatus::Draft, $organization->fresh()->status);
    }

    public function test_publishing_is_blocked_when_the_paid_period_has_lapsed(): void
    {
        $owner = User::factory()->create();
        $organization = $this->paidOrganization($owner);
        $organization->update(['plan_expires_at' => now()->subDay()]);

        $this->actingAs($owner)
            ->patch(route('organizations.publish', $organization))
            ->assertRedirect(route('organizations.plan.edit', $organization));

        $this->assertSame(OrganizationStatus::Draft, $organization->fresh()->status);
        $this->assertContains('Masa aktif paket langganan telah berakhir', $organization->fresh()->planViolations());
    }

    /**
     * An already-live site is never taken offline automatically - it keeps serving with a
     * violation badge - so unpublishing has to remain possible even while the gate would block
     * publishing.
     */
    public function test_unpublishing_stays_possible_while_in_violation(): void
    {
        $owner = User::factory()->create();
        $organization = $this->paidOrganization($owner);
        $organization->publish(true);
        $organization->update(['plan_expires_at' => now()->subDay()]);

        $this->actingAs($owner)->patch(route('organizations.publish', $organization))->assertRedirect();

        $this->assertSame(OrganizationStatus::Draft, $organization->fresh()->status);
    }

    public function test_an_organization_with_no_plan_cannot_be_published(): void
    {
        $owner = User::factory()->create();
        $organization = Organization::factory()->withOwner($owner)->create(['plan_id' => null]);

        $this->actingAs($owner)
            ->patch(route('organizations.publish', $organization))
            ->assertRedirect(route('organizations.plan.edit', $organization));

        $this->assertSame(OrganizationStatus::Draft, $organization->fresh()->status);
    }

    // --------------------------------------------------------- admin overrides

    /**
     * The override panel is the admin's bypass around payment. It has to write an audit row -
     * a plan moved by hand with no record of who did it or why is exactly what an audit would
     * ask about.
     */
    public function test_an_admin_override_activates_a_plan_and_writes_an_audit_log(): void
    {
        $admin = User::factory()->admin()->create();
        $organization = Organization::factory()->withOwner()->create();
        $plan = Plan::create([
            'key' => 'uji-override-admin',
            'name' => 'Uji Override',
            'price_monthly' => 25_000,
            'is_active' => true,
        ]);

        $this->actingAs($admin)->post(route('admin.organizations.override-plan', $organization), [
            'plan_id' => $plan->id,
            'plan_expires_at' => now()->addYear()->toDateString(),
            'note' => 'Kerja sama dakwah, digratiskan satu tahun.',
        ])->assertRedirect(route('admin.organizations.show', $organization));

        $organization->refresh();

        $this->assertSame($plan->id, $organization->plan_id);
        $this->assertTrue($organization->plan_expires_at->isFuture());

        $log = $organization->planOverrideLogs()->firstOrFail();

        $this->assertSame($admin->id, $log->admin_user_id);
        $this->assertSame(PlanOverrideAction::OverridePlan, $log->action);
        $this->assertSame($plan->id, $log->to_plan_id);
        $this->assertStringContainsString('Kerja sama dakwah', $log->note);
    }

    public function test_an_override_without_a_note_is_rejected(): void
    {
        $admin = User::factory()->admin()->create();
        $organization = Organization::factory()->withOwner()->create();
        $plan = Plan::create(['key' => 'uji-tanpa-catatan', 'name' => 'Uji', 'price_monthly' => 1_000, 'is_active' => true]);

        $this->actingAs($admin)
            ->from(route('admin.organizations.show', $organization))
            ->post(route('admin.organizations.override-plan', $organization), [
                'plan_id' => $plan->id,
                'plan_expires_at' => now()->addYear()->toDateString(),
            ])
            ->assertSessionHasErrors('note');

        $this->assertSame(0, $organization->planOverrideLogs()->count());
    }

    public function test_an_admin_can_reject_a_plan_change_request(): void
    {
        $admin = User::factory()->admin()->create();
        $owner = User::factory()->create();
        $organization = Organization::factory()->withOwner($owner)->create();
        $plan = Plan::create(['key' => 'uji-tolak', 'name' => 'Uji Tolak', 'price_monthly' => 18_000, 'is_active' => true]);

        $request = $organization->planChangeRequests()->create([
            'requested_plan_id' => $plan->id,
            'duration_months' => 3,
            'discount_amount' => 0,
            'requested_by_user_id' => $owner->id,
            'status' => PlanChangeRequestStatus::Pending,
        ]);

        $this->actingAs($admin)->post(route('admin.plan-change-requests.reject', $request), [
            'admin_note' => 'Transfer tidak ditemukan.',
        ])->assertRedirect();

        $request->refresh();

        $this->assertSame(PlanChangeRequestStatus::Rejected, $request->status);
        $this->assertSame($admin->id, $request->reviewed_by_user_id);
        // A rejection must not activate anything.
        $this->assertNull($organization->fresh()->plan_expires_at);
    }

    public function test_an_admin_can_create_a_plan_and_an_article(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post(route('admin.plans.store'), [
            'key' => 'paket-baru',
            'name' => 'Paket Baru',
            'price_monthly' => 30_000,
            'is_active' => true,
            // Required by StorePlanRequest - a plan is defined by its quota rows, so there's no
            // valid "plan without limits" to submit.
            'limits' => ['posts' => 25, 'agendas' => 10],
        ])->assertRedirect();

        $this->assertDatabaseHas('plans', ['key' => 'paket-baru']);
        $this->assertDatabaseHas('plan_limits', ['key' => 'posts', 'max_count' => 25]);

        $this->actingAs($admin)->post(route('admin.articles.store'), [
            'title' => 'Panduan Rilis',
            'slug' => 'panduan-rilis',
            'category' => 'Digitalisasi',
            'body' => '<p>Isi panduan.</p>',
            'status' => 'draft',
        ])->assertRedirect();

        $this->assertDatabaseHas('articles', ['title' => 'Panduan Rilis']);
    }
}
