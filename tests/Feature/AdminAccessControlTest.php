<?php

namespace Tests\Feature;

use App\Enums\DiscountCodeType;
use App\Enums\PlanChangeRequestStatus;
use App\Models\Article;
use App\Models\DiscountCode;
use App\Models\Organization;
use App\Models\Plan;
use App\Models\SectionVariant;
use App\Models\Template;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * The admin area is gated by one middleware (EnsureUserIsAdmin) applied to a whole route group,
 * which makes it easy to add a route *outside* that group by accident. Rather than listing the
 * admin routes by hand - a list that goes stale the moment one is added - these tests walk the
 * router's own registered routes, so a new admin route is covered the day it's added.
 */
class AdminAccessControlTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Every GET route under the admin. name prefix, with its parameters filled from real
     * records so binding resolves and the request reaches the middleware rather than 404ing
     * earlier for an unrelated reason - a 404 from a missing record would look like a pass
     * while proving nothing about the guard.
     *
     * @return array<int, string>
     */
    private function adminGetUrls(): array
    {
        $organization = Organization::factory()->withOwner()->create();
        $plan = Plan::first() ?? Plan::create(['key' => 'uji', 'name' => 'Uji', 'price_monthly' => 1000, 'is_active' => true]);
        $template = Template::factory()->create();
        $article = Article::factory()->create();
        $discountCode = DiscountCode::create([
            'code' => 'UJIAKSES',
            'type' => DiscountCodeType::Percent,
            'value' => 10,
            'is_active' => true,
        ]);
        $sectionVariant = SectionVariant::first();

        $substitutions = [
            'organization' => $organization->getKey(),
            'plan' => $plan->getKey(),
            'template' => $template->getKey(),
            'article' => $article->getKey(),
            'discount_code' => $discountCode->getKey(),
            'sectionVariant' => $sectionVariant?->getKey(),
            'planChangeRequest' => $organization->planChangeRequests()->create([
                'requested_plan_id' => $plan->getKey(),
                'duration_months' => 3,
                'discount_amount' => 0,
                'requested_by_user_id' => User::factory()->create()->getKey(),
                'status' => PlanChangeRequestStatus::Pending,
            ])->getKey(),
        ];

        $urls = [];

        foreach (Route::getRoutes() as $route) {
            $name = $route->getName();

            if (! $name || ! str_starts_with($name, 'admin.') || ! in_array('GET', $route->methods(), true)) {
                continue;
            }

            $uri = $route->uri();

            foreach ($substitutions as $param => $value) {
                $uri = str_replace(['{'.$param.'}', '{'.$param.'?}'], (string) $value, $uri);
            }

            // Anything still holding an unsubstituted parameter would exercise the router, not
            // the guard - skip rather than assert something misleading.
            if (str_contains($uri, '{')) {
                continue;
            }

            $urls[] = '/'.ltrim($uri, '/');
        }

        return $urls;
    }

    public function test_a_guest_is_redirected_to_login_from_every_admin_page(): void
    {
        $urls = $this->adminGetUrls();

        $this->assertNotEmpty($urls, 'No admin routes were discovered - the guard would be untested.');

        foreach ($urls as $url) {
            $this->get($url)->assertRedirect(route('login'));
        }
    }

    public function test_a_non_admin_user_is_forbidden_from_every_admin_page(): void
    {
        $user = User::factory()->create();

        foreach ($this->adminGetUrls() as $url) {
            $this->actingAs($user)
                ->get($url)
                ->assertForbidden("Expected 403 for a non-admin at {$url} - a 404 here would mean the record was missing and the guard was never exercised.");
        }
    }

    /**
     * The write endpoints matter more than the read ones - a missing guard there changes data,
     * not just leaks a view. Checked explicitly since they can't be walked with a GET.
     */
    public function test_a_non_admin_cannot_reach_admin_write_endpoints(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->withOwner()->create();

        $this->actingAs($user)->post(route('admin.organizations.override-plan', $organization))->assertForbidden();
        $this->actingAs($user)->delete(route('admin.organizations.destroy', $organization))->assertForbidden();
        $this->actingAs($user)->post(route('admin.templates.store'), [])->assertForbidden();
        $this->actingAs($user)->post(route('admin.plans.store'), [])->assertForbidden();
        $this->actingAs($user)->post(route('admin.articles.store'), [])->assertForbidden();
        $this->actingAs($user)->post(route('admin.discount-codes.store'), [])->assertForbidden();
    }

    public function test_an_admin_can_reach_the_admin_dashboards(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('admin.organizations.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.templates.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.plans.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.articles.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.discount-codes.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.section-variants.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.plan-change-requests.index'))->assertOk();
    }

    public function test_an_admin_can_view_an_organization_detail_page(): void
    {
        $admin = User::factory()->admin()->create();
        $organization = Organization::factory()->withOwner()->create();

        $this->actingAs($admin)
            ->get(route('admin.organizations.show', $organization))
            ->assertOk()
            ->assertSee($organization->name);
    }
}
