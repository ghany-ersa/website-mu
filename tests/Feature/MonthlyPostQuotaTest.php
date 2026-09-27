<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\Plan;
use App\Models\Post;
use App\Services\PlanLimitService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * 'posts' is the one resource whose plan limit resets every calendar month instead of
 * counting an organization's all-time total (see PlanLimitService::MONTHLY_RESOURCES) - these
 * tests pin that a post from an earlier month doesn't count against this month's quota, in
 * either direction: it doesn't block new posts, and it doesn't get silently ignored as a
 * planViolations() over-limit either once it's the only thing left over the cap.
 */
class MonthlyPostQuotaTest extends TestCase
{
    use RefreshDatabase;

    private function organizationWithPlan(string $planKey): Organization
    {
        $plan = Plan::where('key', $planKey)->firstOrFail();

        return Organization::factory()->create([
            'plan_id' => $plan->id,
            'plan_expires_at' => now()->addMonth(),
        ]);
    }

    public function test_posts_from_an_earlier_month_do_not_count_against_this_months_quota(): void
    {
        $organization = $this->organizationWithPlan('starter');

        // Starter's posts limit is 4/month - fill last month's quota well past that.
        Post::factory()->count(6)->create([
            'organization_id' => $organization->id,
            'created_at' => now()->subMonthNoOverflow(),
        ]);

        $service = app(PlanLimitService::class);

        $this->assertSame(0, $service->currentCount($organization, 'posts'));
        $this->assertTrue($service->canCreate($organization, 'posts'));
        $this->assertSame(4, $service->remaining($organization, 'posts'));
    }

    public function test_posts_created_this_month_count_toward_the_quota(): void
    {
        $organization = $this->organizationWithPlan('starter');

        Post::factory()->count(4)->create([
            'organization_id' => $organization->id,
            'created_at' => now(),
        ]);

        $service = app(PlanLimitService::class);

        $this->assertSame(4, $service->currentCount($organization, 'posts'));
        $this->assertFalse($service->canCreate($organization, 'posts'));
        $this->assertSame(0, $service->remaining($organization, 'posts'));
    }

    public function test_a_mix_of_old_and_new_posts_only_counts_the_current_month(): void
    {
        $organization = $this->organizationWithPlan('premium');

        Post::factory()->count(9)->create([
            'organization_id' => $organization->id,
            'created_at' => now()->subMonthNoOverflow(),
        ]);
        Post::factory()->count(2)->create([
            'organization_id' => $organization->id,
            'created_at' => now(),
        ]);

        $service = app(PlanLimitService::class);

        // Premium's posts limit is 7/month.
        $this->assertSame(2, $service->currentCount($organization, 'posts'));
        $this->assertSame(5, $service->remaining($organization, 'posts'));
    }

    /**
     * The regression this guards: planViolations() used to count posts via a direct
     * ->posts()->count() instead of PlanLimitService::currentCount(), so an org that once had
     * a busy month would show as permanently over its posts limit even with zero posts this
     * month - and since that violation blocks publishing, it would never clear on its own.
     */
    public function test_a_busy_earlier_month_does_not_permanently_violate_the_plan(): void
    {
        $organization = $this->organizationWithPlan('starter');

        Post::factory()->count(50)->create([
            'organization_id' => $organization->id,
            'created_at' => now()->subMonthNoOverflow(),
        ]);

        $violations = $organization->planViolations();

        $this->assertEmpty(array_filter(
            $violations,
            fn (string $v) => str_contains($v, 'Berita'),
        ));
    }

    /**
     * The other half of that same regression: going over quota *this* month must still surface
     * as a real violation - the fix isn't "posts never violate", it's "only this month counts".
     */
    public function test_exceeding_this_months_quota_does_violate_the_plan(): void
    {
        $organization = $this->organizationWithPlan('starter');

        Post::factory()->count(6)->create([
            'organization_id' => $organization->id,
            'created_at' => now(),
        ]);

        $violations = $organization->planViolations();

        $this->assertNotEmpty(array_filter(
            $violations,
            fn (string $v) => str_contains($v, 'Berita') && str_contains($v, '2 kelebihan'),
        ));
    }
}
