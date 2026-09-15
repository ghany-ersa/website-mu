<?php

namespace Tests\Feature;

use App\Enums\OrganizationStatus;
use App\Enums\PublishStatus;
use App\Models\Agenda;
use App\Models\Announcement;
use App\Models\Organization;
use App\Models\Template;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Regression cover for the three defects found in the 2026-09-16 QA pass. Each test pins the
 * *cause* rather than only the symptom, because in both cases the symptom (a 404, a 500) is easy
 * to make pass again by accident while leaving the underlying trap in place.
 *
 * See docs/qa/COVERAGE-REPORT.md §5.
 */
class DefectRegressionTest extends TestCase
{
    use RefreshDatabase;

    // ------------------------------------------------- DEF-01 / DEF-02

    /**
     * The cause: the tenant route group excluded the whole 'web' middleware *group*, and
     * Router::resolveMiddleware() expands an excluded group into its member classes and rejects
     * each by class from the entire stack - stripping SubstituteBindings and EncryptCookies out
     * of the 'tenant' group too, since both groups contain them. Without SubstituteBindings,
     * every bound tenant route parameter arrived as a raw string.
     *
     * Asserting on the resolved stack (not just on a 200) is what stops a future
     * withoutMiddleware('web') from silently reintroducing this: the detail pages would 404
     * again, but so would anything else that ever gets route-model bound here.
     */
    public function test_tenant_routes_keep_route_model_binding(): void
    {
        $this->assertContains(
            SubstituteBindings::class,
            $this->resolvedTenantMiddleware(),
            'Tenant routes lost SubstituteBindings - bound parameters will arrive as raw strings.'
        );
    }

    /**
     * The other half of the same decision: dropping session/CSRF is deliberate (these pages are
     * pure reads, and a session write per page view would rule out the SELECT-only connection),
     * so the fix must not have quietly restored it.
     */
    public function test_tenant_routes_still_run_without_a_session(): void
    {
        $this->assertNotContains(
            StartSession::class,
            $this->resolvedTenantMiddleware(),
            'Tenant routes must stay session-free.'
        );
    }

    public function test_the_announcement_detail_page_resolves_its_model(): void
    {
        $organization = $this->publishedOrganization();
        $announcement = Announcement::factory()->create([
            'organization_id' => $organization->id,
            'status' => PublishStatus::Published,
        ]);

        $this->get($this->tenantUrl($organization, '/pengumuman/'.$announcement->id))
            ->assertOk()
            ->assertSee($announcement->title);
    }

    public function test_the_agenda_detail_page_resolves_its_model(): void
    {
        $organization = $this->publishedOrganization();
        $agenda = Agenda::factory()->create([
            'organization_id' => $organization->id,
            'status' => PublishStatus::Published,
        ]);

        $this->get($this->tenantUrl($organization, '/agenda/'.$agenda->id))
            ->assertOk()
            ->assertSee($agenda->title);
    }

    /**
     * Binding resolving again must not weaken the ownership and publish gates those controllers
     * apply on top of it - previously unreachable, so never actually exercised.
     */
    public function test_detail_pages_still_reject_draft_and_cross_tenant_records(): void
    {
        $organization = $this->publishedOrganization();
        $neighbour = $this->publishedOrganization('tetangga');

        $draft = Announcement::factory()->create([
            'organization_id' => $organization->id,
            'status' => PublishStatus::Draft,
        ]);
        $published = Announcement::factory()->create([
            'organization_id' => $organization->id,
            'status' => PublishStatus::Published,
        ]);
        $draftAgenda = Agenda::factory()->create([
            'organization_id' => $organization->id,
            'status' => PublishStatus::Draft,
        ]);

        $this->get($this->tenantUrl($organization, '/pengumuman/'.$draft->id))->assertNotFound();
        $this->get($this->tenantUrl($organization, '/agenda/'.$draftAgenda->id))->assertNotFound();
        // Belongs to the neighbour's subdomain, not this one.
        $this->get($this->tenantUrl($neighbour, '/pengumuman/'.$published->id))->assertNotFound();
    }

    // ---------------------------------------------------------- DEF-03

    /**
     * The cause: `phone` is both a column and a method, and Eloquent's __get() falls back to
     * relationship resolution when the attribute isn't loaded - calling phone(), getting a
     * string, and throwing "must return a relationship instance". A factory-built instance that
     * never set the column is exactly that case.
     *
     * Each accessor is called on an instance whose contact columns were never populated, which
     * is what used to throw.
     */
    public function test_contact_accessors_work_on_an_instance_without_contact_columns_loaded(): void
    {
        $organization = Organization::factory()->create();

        $this->assertNull($organization->phone());
        $this->assertNull($organization->email());
        $this->assertNull($organization->whatsapp());
        $this->assertNull($organization->address());
    }

    /**
     * The production path that surfaced it: onboardingChecklist() read $this->phone directly.
     */
    public function test_the_onboarding_checklist_does_not_throw_on_an_unloaded_instance(): void
    {
        $organization = Organization::factory()->create(['logo' => null]);

        $this->assertSame(
            ['brand' => false, 'contact' => false, 'content' => false, 'published' => false],
            $organization->onboardingChecklist()
        );
    }

    /**
     * The narrowed-select case - the same trap, reachable in any production query that doesn't
     * select the contact columns.
     */
    public function test_contact_accessors_work_on_a_narrowed_select(): void
    {
        Organization::factory()->create(['phone' => '0811111111']);

        $organization = Organization::query()->select(['id', 'name', 'slug', 'template_id'])->firstOrFail();

        $this->assertNull($organization->phone(), 'An unselected column reads as absent, not as an error.');
        $this->assertIsArray($organization->onboardingChecklist());
    }

    public function test_contact_accessors_still_prefer_the_organizations_own_value(): void
    {
        $organization = Organization::factory()->create([
            'phone' => '0812345678',
            'email' => 'kontak@example.test',
            'whatsapp' => '6281234567890',
            'address' => 'Jl. Contoh No. 1',
        ]);

        $this->assertSame('0812345678', $organization->phone());
        $this->assertSame('kontak@example.test', $organization->email());
        $this->assertSame('6281234567890', $organization->whatsapp());
        $this->assertSame('Jl. Contoh No. 1', $organization->address());
    }

    /**
     * The fallback half of the chain: with nothing set on the organization, the template's
     * contact block supplies the value.
     */
    /**
     * Deliberately the opposite of brand accessors like primaryColor()/fontFamily(): a
     * template's sample contact info belongs to whatever real organization the template's
     * content was modeled on, so a different organization that simply hasn't filled in its own
     * contact yet must render blank, not that other organization's WhatsApp/address. See
     * Organization::phone()'s doc comment.
     */
    public function test_contact_accessors_do_not_fall_back_to_the_template(): void
    {
        $template = Template::factory()->create([
            'structure' => [
                'pages' => [],
                'contact' => [
                    'phone' => '0800-TEMPLATE',
                    'email' => 'template@example.test',
                    'whatsapp' => '6280000000000',
                    'address' => 'Alamat Template',
                ],
            ],
        ]);

        $organization = Organization::factory()->create(['template_id' => $template->id]);

        $this->assertNull($organization->phone());
        $this->assertNull($organization->email());
        $this->assertNull($organization->whatsapp());
        $this->assertNull($organization->address());
    }

    // --------------------------------------------------------- helpers

    /**
     * The middleware stack the tenant group actually resolves to, captured from inside a real
     * request.
     *
     * It has to be measured in-request: middleware *groups* are registered by the HTTP kernel
     * when it handles a request, so a router resolved straight from the container in a test has
     * no groups at all and would report an empty stack - passing or failing for reasons that
     * have nothing to do with the routes.
     *
     * The probe route is declared with exactly the same domain/middleware modifiers as the real
     * tenant group in routes/web.php, so whatever the group resolves to for it is what the real
     * routes get.
     *
     * @return array<int, string>
     */
    private function resolvedTenantMiddleware(): array
    {
        $captured = [];
        $organization = $this->publishedOrganization();

        Route::matched(function ($event) use (&$captured) {
            if ($event->route->getName() !== 'tenant.announcements.show') {
                return;
            }

            $captured = array_values(array_filter(
                app('router')->gatherRouteMiddleware($event->route),
                'is_string'
            ));
        });

        $announcement = Announcement::factory()->create([
            'organization_id' => $organization->id,
            'status' => PublishStatus::Published,
        ]);

        $this->get($this->tenantUrl($organization, '/pengumuman/'.$announcement->id))->assertOk();

        $this->assertNotEmpty($captured, 'Failed to capture the tenant middleware stack.');

        return $captured;
    }

    private function publishedOrganization(?string $slug = null): Organization
    {
        return Organization::factory()->create(array_filter([
            'slug' => $slug,
            'status' => OrganizationStatus::Published,
        ]));
    }

    private function tenantUrl(Organization $organization, string $path): string
    {
        return 'http://'.$organization->slug.'.'.config('tenancy.domain').$path;
    }
}
