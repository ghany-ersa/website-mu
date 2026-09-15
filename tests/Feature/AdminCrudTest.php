<?php

namespace Tests\Feature;

use App\Enums\PlanChangeRequestStatus;
use App\Enums\PlanOverrideAction;
use App\Models\Article;
use App\Models\Organization;
use App\Models\Plan;
use App\Models\SectionVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The platform-admin CRUD screens. AdminAccessControlTest already proves nobody else can reach
 * them; this covers what they actually do once an admin is inside.
 *
 * Two of these carry real blast radius and get the most attention: deleting a Plan that
 * organizations still sit on would orphan them, and retryApprove() is the manual escape hatch
 * for a payment that settled but failed to activate - it must stay bounded and audited.
 */
class AdminCrudTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    // -------------------------------------------------------------- plans

    public function test_an_admin_can_create_edit_and_delete_a_plan(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->get(route('admin.plans.create'))->assertOk();

        $this->actingAs($admin)->post(route('admin.plans.store'), [
            'key' => 'paket-uji',
            'name' => 'Paket Uji',
            'description' => 'Untuk pengujian.',
            'price_monthly' => 20_000,
            'discount_percent_6' => 10,
            'discount_percent_12' => 20,
            'is_active' => true,
            'has_exclusive_templates' => true,
            'limits' => ['posts' => 30, 'agendas' => 15],
        ])->assertRedirect();

        $plan = Plan::where('key', 'paket-uji')->firstOrFail();

        $this->assertTrue((bool) $plan->has_exclusive_templates);
        $this->assertSame(30, (int) $plan->limitFor('posts'));
        // Duration discounts feed priceForDuration(), so they have to round-trip intact:
        // 20.000 x 12 = 240.000, less the 20% twelve-month discount.
        $this->assertSame(192_000, $plan->priceForDuration(12));

        $this->actingAs($admin)->get(route('admin.plans.edit', $plan))->assertOk();

        $this->actingAs($admin)->patch(route('admin.plans.update', $plan), [
            'key' => 'paket-uji',
            'name' => 'Paket Uji Direvisi',
            'price_monthly' => 22_000,
            'limits' => ['posts' => 50],
        ])->assertRedirect();

        $this->assertSame('Paket Uji Direvisi', $plan->fresh()->name);
        $this->assertSame(50, (int) $plan->fresh()->limitFor('posts'));

        $this->actingAs($admin)->delete(route('admin.plans.destroy', $plan))->assertRedirect();
        $this->assertModelMissing($plan);
    }

    public function test_a_duplicate_plan_key_is_rejected(): void
    {
        $admin = $this->admin();
        Plan::create(['key' => 'sudah-ada', 'name' => 'Sudah Ada', 'price_monthly' => 1_000, 'is_active' => true]);

        $this->actingAs($admin)
            ->from(route('admin.plans.create'))
            ->post(route('admin.plans.store'), [
                'key' => 'sudah-ada',
                'name' => 'Duplikat',
                'price_monthly' => 2_000,
                'limits' => ['posts' => 5],
            ])
            ->assertSessionHasErrors('key');

        $this->assertSame(1, Plan::where('key', 'sudah-ada')->count());
    }

    /**
     * Deleting a plan that organizations still reference would leave them pointing at a missing
     * row - the guard refuses with 409 instead.
     */
    public function test_a_plan_still_in_use_cannot_be_deleted(): void
    {
        $admin = $this->admin();
        $plan = Plan::create(['key' => 'masih-dipakai', 'name' => 'Masih Dipakai', 'price_monthly' => 5_000, 'is_active' => true]);
        Organization::factory()->withOwner()->create(['plan_id' => $plan->id]);

        $this->actingAs($admin)->delete(route('admin.plans.destroy', $plan))->assertStatus(409);

        $this->assertModelExists($plan);
    }

    // ------------------------------------------------------------ articles

    public function test_an_admin_can_create_edit_and_delete_an_article(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->get(route('admin.articles.create'))->assertOk();

        $this->actingAs($admin)->post(route('admin.articles.store'), [
            'title' => 'Artikel Admin',
            'slug' => 'artikel-admin',
            'category' => 'Digitalisasi',
            'body' => '<p>Isi artikel.</p>',
            'status' => 'draft',
        ])->assertRedirect();

        $article = Article::where('slug', 'artikel-admin')->firstOrFail();

        $this->actingAs($admin)->get(route('admin.articles.edit', $article))->assertOk();

        $this->actingAs($admin)->patch(route('admin.articles.update', $article), [
            'title' => 'Artikel Admin Terbit',
            'slug' => 'artikel-admin',
            'body' => '<p>Isi artikel.</p>',
            'status' => 'published',
        ])->assertRedirect();

        $this->assertSame('Artikel Admin Terbit', $article->fresh()->title);

        // Publishing from the admin panel must make it reachable on the public blog.
        $this->get(route('articles.show', $article->fresh()))->assertOk();

        $this->actingAs($admin)->delete(route('admin.articles.destroy', $article))->assertRedirect();
        $this->assertModelMissing($article);
    }

    public function test_a_duplicate_article_slug_is_rejected(): void
    {
        $admin = $this->admin();
        Article::factory()->create(['slug' => 'slug-terpakai']);

        $this->actingAs($admin)
            ->from(route('admin.articles.create'))
            ->post(route('admin.articles.store'), [
                'title' => 'Judul Lain',
                'slug' => 'slug-terpakai',
                'status' => 'draft',
            ])
            ->assertSessionHasErrors('slug');
    }

    // ------------------------------------------------------ section variants

    public function test_an_admin_can_toggle_a_variants_exclusive_flag(): void
    {
        $admin = $this->admin();
        $variant = SectionVariant::firstOrFail();
        $wasExclusive = (bool) $variant->is_exclusive;

        $this->actingAs($admin)
            ->patch(route('admin.section-variants.update', $variant), ['is_exclusive' => ! $wasExclusive])
            ->assertRedirect(route('admin.section-variants.index'));

        $this->assertSame(! $wasExclusive, (bool) $variant->fresh()->is_exclusive);
    }

    public function test_an_admin_can_preview_a_section_variant(): void
    {
        $admin = $this->admin();
        $variant = SectionVariant::firstOrFail();

        $this->actingAs($admin)->get(route('admin.section-variants.preview', $variant))->assertOk();
    }

    // -------------------------------------------------- plan change requests

    /**
     * The retry path for a settled payment whose activation threw. On success it must activate
     * the plan and leave an audit row naming the admin who forced it through.
     */
    public function test_an_admin_can_retry_approving_a_payment_that_failed_to_activate(): void
    {
        $admin = $this->admin();
        $owner = User::factory()->create();
        $organization = Organization::factory()->withOwner($owner)->create();
        $plan = Plan::create(['key' => 'uji-retry', 'name' => 'Uji Retry', 'price_monthly' => 18_000, 'is_active' => true]);

        $request = $organization->planChangeRequests()->create([
            'requested_plan_id' => $plan->id,
            'duration_months' => 3,
            'discount_amount' => 0,
            'requested_by_user_id' => $owner->id,
            'status' => PlanChangeRequestStatus::PaymentReceivedNeedsReview,
            'approve_error' => 'kegagalan sebelumnya',
            'approve_attempts' => 1,
            'midtrans_paid_at' => now(),
        ]);

        $this->actingAs($admin)->post(route('admin.plan-change-requests.retry-approve', $request))->assertRedirect();

        $request->refresh();
        $organization->refresh();

        $this->assertSame(PlanChangeRequestStatus::Approved, $request->status);
        $this->assertNull($request->approve_error);
        $this->assertSame($plan->id, $organization->plan_id);

        $log = $organization->planOverrideLogs()->firstOrFail();
        $this->assertSame(PlanOverrideAction::RetryApprove, $log->action);
        $this->assertSame($admin->id, $log->admin_user_id);
    }

    public function test_retrying_a_request_that_is_not_awaiting_review_is_refused(): void
    {
        $admin = $this->admin();
        $owner = User::factory()->create();
        $organization = Organization::factory()->withOwner($owner)->create();
        $plan = Plan::create(['key' => 'uji-retry-salah', 'name' => 'Uji', 'price_monthly' => 18_000, 'is_active' => true]);

        $request = $organization->planChangeRequests()->create([
            'requested_plan_id' => $plan->id,
            'duration_months' => 3,
            'discount_amount' => 0,
            'requested_by_user_id' => $owner->id,
            'status' => PlanChangeRequestStatus::Pending,
        ]);

        $this->actingAs($admin)->post(route('admin.plan-change-requests.retry-approve', $request))->assertStatus(409);

        $this->assertNull($organization->fresh()->plan_expires_at);
    }

    /**
     * Past config('billing.midtrans.max_approve_attempts'), retrying is refused and the admin
     * is expected to fall back to the plan-override panel - an unbounded retry would keep
     * re-running a failing activation forever.
     */
    public function test_retrying_past_the_attempt_cap_is_refused(): void
    {
        $admin = $this->admin();
        $owner = User::factory()->create();
        $organization = Organization::factory()->withOwner($owner)->create();
        $plan = Plan::create(['key' => 'uji-retry-batas', 'name' => 'Uji', 'price_monthly' => 18_000, 'is_active' => true]);

        $request = $organization->planChangeRequests()->create([
            'requested_plan_id' => $plan->id,
            'duration_months' => 3,
            'discount_amount' => 0,
            'requested_by_user_id' => $owner->id,
            'status' => PlanChangeRequestStatus::PaymentReceivedNeedsReview,
            'approve_attempts' => config('billing.midtrans.max_approve_attempts'),
        ]);

        $this->actingAs($admin)->post(route('admin.plan-change-requests.retry-approve', $request))->assertStatus(409);

        $this->assertSame(PlanChangeRequestStatus::PaymentReceivedNeedsReview, $request->fresh()->status);
    }

    public function test_the_request_list_renders_with_requests_in_every_state(): void
    {
        $admin = $this->admin();
        $owner = User::factory()->create();
        $organization = Organization::factory()->withOwner($owner)->create();
        $plan = Plan::create(['key' => 'uji-daftar', 'name' => 'Uji Daftar', 'price_monthly' => 18_000, 'is_active' => true]);

        foreach (PlanChangeRequestStatus::cases() as $status) {
            $organization->planChangeRequests()->create([
                'requested_plan_id' => $plan->id,
                'duration_months' => 3,
                'discount_amount' => 0,
                'requested_by_user_id' => $owner->id,
                'status' => $status,
            ]);
        }

        $this->actingAs($admin)->get(route('admin.plan-change-requests.index'))->assertOk();
    }
}
