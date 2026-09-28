<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\OrganizationPage;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * The CMS controllers' *page* actions - index, create, edit - as opposed to their writes, which
 * OrganizationCmsTest and OrganizationOrgDataTest already cover by POSTing.
 *
 * Worth separating because a write test never renders the Blade form: a broken
 * organizations/{resource}/form.blade.php (a renamed variable, a helper that no longer exists)
 * passes every existing test and 500s the moment a real user clicks "Tambah". These tests GET
 * each page so the view is actually compiled.
 *
 * All six resources share one controller shape - gate on the section existing, authorize, check
 * the plan quota, render - so they're driven from one table rather than six near-identical
 * copies. Anything genuinely per-resource (the ?type= split on programs, reorder endpoints) gets
 * its own test below.
 */
class CmsFormPagesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Each row: [route-segment name, section key that must exist, plan limit key or null,
     * a minimal valid create payload].
     *
     * @return array<string, array{0: string, 1: string, 2: ?string, 3: array<string, mixed>}>
     */
    public static function resources(): array
    {
        return [
            'posts' => ['posts', 'daftar-berita', 'posts', [
                'title' => 'Berita Uji', 'body' => '<p>Isi berita.</p>', 'status' => 'draft',
            ]],
            'agendas' => ['agendas', 'agenda', 'agendas', [
                'title' => 'Kajian Ahad', 'starts_at' => '2026-04-01 08:00:00', 'status' => 'draft',
            ]],
            'announcements' => ['announcements', 'pengumuman', 'announcements', [
                'title' => 'Pengumuman Uji', 'priority' => 'Sedang', 'status' => 'draft',
            ]],
            'officers' => ['officers', 'struktur-pengurus', 'officers', [
                'name' => 'Ketua Ranting', 'role' => 'Ketua',
            ]],
            // No quota key: OrganizationNetworkController is the one resource with no
            // canCreate() check, so it's excluded from quotaResources() below.
            'networks' => ['networks', 'jaringan-aum-ortom', null, [
                'name' => 'SD Muhammadiyah 1', 'type' => 'Pendidikan',
            ]],
            'gallery' => ['gallery', 'galeri', 'gallery_photos', [
                'url' => 'https://example.test/foto.jpg', 'caption' => 'Bakti sosial',
            ]],
        ];
    }

    /**
     * An organization owned by $owner whose home page carries $sectionKey - every one of these
     * controllers 404s before authorizing when the section is absent.
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

    #[DataProvider('resources')]
    public function test_the_index_and_create_pages_render(string $resource, string $sectionKey): void
    {
        $owner = User::factory()->create();
        $organization = $this->organizationWithSection($owner, $sectionKey);

        $this->actingAs($owner)->get(route("organizations.{$resource}.index", $organization))->assertOk();
        $this->actingAs($owner)->get(route("organizations.{$resource}.create", $organization))->assertOk();
    }

    #[DataProvider('resources')]
    public function test_the_edit_page_renders_for_an_existing_record(string $resource, string $sectionKey, ?string $limitKey, array $payload): void
    {
        $owner = User::factory()->create();
        $organization = $this->organizationWithSection($owner, $sectionKey);

        $this->actingAs($owner)
            ->post(route("organizations.{$resource}.store", $organization), $payload)
            ->assertRedirect();

        $record = $this->latestRecordFor($organization, $resource);

        $this->actingAs($owner)
            ->get(route("organizations.{$resource}.edit", [$organization, $record]))
            ->assertOk();
    }

    /**
     * Without the section, the resource's pages must 404 rather than render an editor for
     * content that has nowhere to appear.
     */
    #[DataProvider('resources')]
    public function test_the_pages_404_when_the_section_is_absent(string $resource): void
    {
        $owner = User::factory()->create();
        // A hero-only organization: published, renderable, but without this resource's section.
        $organization = $this->organizationWithSection($owner, 'hero');

        $this->actingAs($owner)->get(route("organizations.{$resource}.index", $organization))->assertNotFound();
        $this->actingAs($owner)->get(route("organizations.{$resource}.create", $organization))->assertNotFound();
    }

    #[DataProvider('resources')]
    public function test_a_non_member_cannot_open_the_pages(string $resource, string $sectionKey): void
    {
        $owner = User::factory()->create();
        $outsider = User::factory()->create();
        $organization = $this->organizationWithSection($owner, $sectionKey);

        $this->actingAs($outsider)->get(route("organizations.{$resource}.index", $organization))->assertForbidden();
        $this->actingAs($outsider)->get(route("organizations.{$resource}.create", $organization))->assertForbidden();
    }

    /**
     * The subset of resources whose controller actually checks a quota - every one except
     * networks, which has no canCreate() call.
     *
     * @return array<string, array{0: string, 1: string, 2: string}>
     */
    public static function quotaResources(): array
    {
        return array_map(
            fn (array $row) => [$row[0], $row[1], $row[2]],
            array_filter(self::resources(), fn (array $row) => $row[2] !== null)
        );
    }

    /**
     * At the quota, "Tambah" must send the owner back to the index with an upgrade nudge rather
     * than rendering a form whose submission would be refused anyway.
     */
    #[DataProvider('quotaResources')]
    public function test_the_create_page_redirects_with_a_warning_at_the_plan_limit(string $resource, string $sectionKey, string $limitKey): void
    {
        $owner = User::factory()->create();
        $plan = Plan::create([
            'key' => 'uji-kuota-'.$resource,
            'name' => 'Uji Kuota',
            'price_monthly' => 10_000,
            'is_active' => true,
        ]);
        $plan->limits()->create(['key' => $limitKey, 'max_count' => 0]);

        $organization = $this->organizationWithSection($owner, $sectionKey);
        $organization->update(['plan_id' => $plan->id]);

        $this->actingAs($owner)
            ->get(route("organizations.{$resource}.create", $organization))
            ->assertRedirect(route("organizations.{$resource}.index", $organization))
            ->assertSessionHas('warning');
    }

    /**
     * Reached from the builder's "Kelola X" link, these pages carry ?from=builder so their
     * back-link returns to the canvas - a separate render path through BuilderAware.
     */
    #[DataProvider('resources')]
    public function test_the_pages_render_in_builder_context(string $resource, string $sectionKey): void
    {
        $owner = User::factory()->create();
        $organization = $this->organizationWithSection($owner, $sectionKey);
        $section = $organization->pages->first()->sections->first();

        $query = '?from=builder&section='.$section->id;

        $this->actingAs($owner)->get(route("organizations.{$resource}.index", $organization).$query)->assertOk();
        $this->actingAs($owner)->get(route("organizations.{$resource}.create", $organization).$query)->assertOk();
    }

    /**
     * The most recently created record of the given resource, resolved through the relation
     * each controller writes to (route segment and relation name don't always match).
     */
    private function latestRecordFor(Organization $organization, string $resource)
    {
        $relation = match ($resource) {
            'gallery' => 'photos',
            default => $resource,
        };

        return $organization->{$relation}()->latest('id')->firstOrFail();
    }

    // ------------------------------------------------------ programs: ?type=

    /**
     * Programs and layanan are one controller and one table split by ?type=, each gated on its
     * own section key - so the pages have to be exercised in both modes, and each mode must
     * 404 when only the *other* section exists.
     */
    public function test_program_pages_render_for_both_types(): void
    {
        $owner = User::factory()->create();

        $programOrg = $this->organizationWithSection($owner, 'program-unggulan');
        $this->actingAs($owner)->get(route('organizations.programs.index', $programOrg))->assertOk();
        $this->actingAs($owner)->get(route('organizations.programs.create', $programOrg))->assertOk();
        // 'layanan' mode is gated on a section this organization doesn't have.
        $this->actingAs($owner)->get(route('organizations.programs.index', $programOrg).'?type=layanan')->assertNotFound();

        $layananOrg = $this->organizationWithSection($owner, 'layanan');
        $this->actingAs($owner)->get(route('organizations.programs.index', $layananOrg).'?type=layanan')->assertOk();
        $this->actingAs($owner)->get(route('organizations.programs.create', $layananOrg).'?type=layanan')->assertOk();
        $this->actingAs($owner)->get(route('organizations.programs.index', $layananOrg))->assertNotFound();
    }

    public function test_a_program_can_be_edited_and_deleted_through_its_pages(): void
    {
        $owner = User::factory()->create();
        $organization = $this->organizationWithSection($owner, 'program-unggulan');

        $this->actingAs($owner)->post(route('organizations.programs.store', $organization), [
            'title' => 'Pengajian Rutin',
            'description' => 'Setiap Ahad pagi.',
            'icon' => '📖',
        ])->assertRedirect();

        $program = $organization->programs()->firstOrFail();

        $this->actingAs($owner)->get(route('organizations.programs.edit', [$organization, $program]))->assertOk();

        $this->actingAs($owner)->patch(route('organizations.programs.update', [$organization, $program]), [
            'title' => 'Pengajian Rutin Ahad',
        ])->assertRedirect();

        $this->assertSame('Pengajian Rutin Ahad', $program->fresh()->title);

        $this->actingAs($owner)->delete(route('organizations.programs.destroy', [$organization, $program]))->assertRedirect();
        $this->assertModelMissing($program);
    }

    public function test_a_program_at_the_plan_limit_cannot_be_created(): void
    {
        $owner = User::factory()->create();
        $plan = Plan::create(['key' => 'uji-kuota-program', 'name' => 'Uji', 'price_monthly' => 10_000, 'is_active' => true]);
        $plan->limits()->create(['key' => 'programs', 'max_count' => 0]);

        $organization = $this->organizationWithSection($owner, 'program-unggulan');
        $organization->update(['plan_id' => $plan->id]);

        // The redirect keeps ?type= so the owner lands back on the list they came from.
        $this->actingAs($owner)
            ->post(route('organizations.programs.store', $organization), ['title' => 'Melebihi Kuota'])
            ->assertRedirect(route('organizations.programs.index', ['organization' => $organization, 'type' => 'program']));

        $this->assertSame(0, $organization->fresh()->programs()->count());
    }

    // --------------------------------------------------------------- reorder

    public function test_reorder_endpoints_persist_the_given_order(): void
    {
        $owner = User::factory()->create();
        $organization = $this->organizationWithSection($owner, 'struktur-pengurus');

        $first = $organization->officers()->create(['name' => 'Pertama', 'role' => 'Ketua', 'order' => 0]);
        $second = $organization->officers()->create(['name' => 'Kedua', 'role' => 'Sekretaris', 'order' => 1]);

        $this->actingAs($owner)->post(route('organizations.officers.reorder', $organization), [
            'officer_ids' => [$second->id, $first->id],
        ])->assertRedirect();

        $this->assertTrue(
            $second->fresh()->order < $first->fresh()->order,
            'The reordered officer should now sort first.'
        );
    }

    public function test_a_non_member_cannot_reorder(): void
    {
        $owner = User::factory()->create();
        $outsider = User::factory()->create();
        $organization = $this->organizationWithSection($owner, 'struktur-pengurus');
        $officer = $organization->officers()->create(['name' => 'Pertama', 'role' => 'Ketua', 'order' => 0]);

        $this->actingAs($outsider)
            ->post(route('organizations.officers.reorder', $organization), ['officer_ids' => [$officer->id]])
            ->assertForbidden();
    }

    // ------------------------------------------------------- update & delete

    /**
     * Every resource's update/destroy through its own route. Covered generically here because
     * the existing CMS tests only exercise a couple of them by hand - announcements and agendas
     * in particular had no update/delete coverage at all, so a broken redirect or a missing
     * ownership check there would have gone unnoticed.
     */
    #[DataProvider('resources')]
    public function test_a_record_can_be_updated_and_deleted(string $resource, string $sectionKey, ?string $limitKey, array $payload): void
    {
        $owner = User::factory()->create();
        $organization = $this->organizationWithSection($owner, $sectionKey);

        $this->actingAs($owner)
            ->post(route("organizations.{$resource}.store", $organization), $payload)
            ->assertRedirect();

        $record = $this->latestRecordFor($organization, $resource);

        $this->actingAs($owner)
            ->patch(route("organizations.{$resource}.update", [$organization, $record]), $payload)
            ->assertRedirect();

        $this->actingAs($owner)
            ->delete(route("organizations.{$resource}.destroy", [$organization, $record]))
            ->assertRedirect();

        $this->assertModelMissing($record);
    }

    /**
     * The ownership guard on the write paths - a record id from another tenant must not be
     * editable or deletable through this organization's routes.
     */
    #[DataProvider('resources')]
    public function test_a_record_from_another_organization_cannot_be_updated_or_deleted(string $resource, string $sectionKey, ?string $limitKey, array $payload): void
    {
        $owner = User::factory()->create();
        $organization = $this->organizationWithSection($owner, $sectionKey);
        $other = $this->organizationWithSection(User::factory()->create(), $sectionKey);

        $this->actingAs($other->members()->first())
            ->post(route("organizations.{$resource}.store", $other), $payload)
            ->assertRedirect();

        $foreign = $this->latestRecordFor($other, $resource);

        // Either 403 (policy) or 404 (ownership guard) is acceptable - both block the write.
        $update = $this->actingAs($owner)
            ->patch(route("organizations.{$resource}.update", [$organization, $foreign]), $payload);
        $this->assertContains($update->getStatusCode(), [403, 404]);

        $delete = $this->actingAs($owner)
            ->delete(route("organizations.{$resource}.destroy", [$organization, $foreign]));
        $this->assertContains($delete->getStatusCode(), [403, 404]);

        $this->assertModelExists($foreign);
    }

    // ----------------------------------------------------------- validation

    public function test_each_resource_rejects_an_empty_submission(): void
    {
        $owner = User::factory()->create();

        foreach (self::resources() as [$resource, $sectionKey]) {
            $organization = $this->organizationWithSection($owner, $sectionKey);

            $this->actingAs($owner)
                ->from(route("organizations.{$resource}.create", $organization))
                ->post(route("organizations.{$resource}.store", $organization), [])
                ->assertSessionHasErrors();
        }
    }
}
