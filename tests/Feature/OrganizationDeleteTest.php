<?php

namespace Tests\Feature;

use App\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Js;
use Tests\TestCase;

class OrganizationDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_view_and_delete_organization_from_tenant_page(): void
    {
        $owner = User::factory()->create();
        $org = Organization::factory()->create();
        $org->members()->attach($owner->id, ['role' => OrganizationRole::Owner->value]);

        $this->actingAs($owner);

        $showResponse = $this->get(route('organizations.show', $org));
        $showResponse->assertOk();
        $showResponse->assertSee('Hapus Organisasi');
        $showResponse->assertSee(route('organizations.destroy', $org), false);

        $destroyResponse = $this->delete(route('organizations.destroy', $org));
        $destroyResponse->assertRedirect(route('organizations.index'));
        $destroyResponse->assertSessionHas('status', 'Organisasi berhasil dihapus.');

        $this->assertDatabaseMissing('organizations', ['id' => $org->id]);
    }

    /**
     * Regression test: the delete button's confirmAction() modal takes the org name through
     * x-data="{ name: @js(...) }" rather than echoing it straight into the @submit.prevent JS
     * expression - @js() (Illuminate\Support\Js) escapes quotes and backticks into "/\\` so
     * a name containing either can't break out of the Alpine x-data object or the JS string
     * concatenation ('Hapus organisasi ' + name + ...) that uses it.
     */
    public function test_delete_button_confirm_uses_safely_escaped_org_name(): void
    {
        $owner = User::factory()->create();
        $org = Organization::factory()->create(['name' => 'Dante "Guthrie" `Test`']);
        $org->members()->attach($owner->id, ['role' => OrganizationRole::Owner->value]);

        $html = $this->actingAs($owner)->get(route('organizations.show', $org))->getContent();

        $this->assertStringContainsString("confirmAction('Hapus organisasi ' + name + ", $html);
        $this->assertStringContainsString(Js::from($org->name)->toHtml(), $html);
    }

    public function test_admin_delete_button_confirm_uses_safely_escaped_org_name(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $org = Organization::factory()->create(['name' => 'Dante "Guthrie" `Test`']);

        $html = $this->actingAs($admin)->get(route('admin.organizations.show', $org))->getContent();

        $this->assertStringContainsString("confirmAction('Hapus organisasi ' + name + ", $html);
        $this->assertStringContainsString(Js::from($org->name)->toHtml(), $html);
    }

    public function test_editor_cannot_see_or_use_delete_on_tenant_page(): void
    {
        $editor = User::factory()->create();
        $org = Organization::factory()->create();
        $org->members()->attach($editor->id, ['role' => OrganizationRole::Editor->value]);

        $this->actingAs($editor);

        $showResponse = $this->get(route('organizations.show', $org));
        $showResponse->assertOk();
        $showResponse->assertDontSee('Hapus Organisasi');

        $destroyResponse = $this->delete(route('organizations.destroy', $org));
        $destroyResponse->assertForbidden();

        $this->assertDatabaseHas('organizations', ['id' => $org->id]);
    }

    public function test_platform_admin_can_delete_organization_from_admin_page_without_being_a_member(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $org = Organization::factory()->create();
        // Deliberately no membership row for $admin - proving the delete works via the
        // Gate::before bypass (AppServiceProvider), not because the admin happens to also be Owner.
        $this->assertNull($org->fresh()->roleFor($admin));

        $this->actingAs($admin);

        $showResponse = $this->get(route('admin.organizations.show', $org));
        $showResponse->assertOk();
        $showResponse->assertSee('Hapus Organisasi');

        $destroyResponse = $this->delete(route('admin.organizations.destroy', $org));
        $destroyResponse->assertRedirect(route('admin.organizations.index'));
        $destroyResponse->assertSessionHas('status', 'Organisasi berhasil dihapus.');

        $this->assertDatabaseMissing('organizations', ['id' => $org->id]);
    }

    public function test_non_admin_non_member_cannot_reach_admin_delete_route(): void
    {
        $stranger = User::factory()->create(['is_admin' => false]);
        $org = Organization::factory()->create();

        $this->actingAs($stranger);

        $response = $this->get(route('admin.organizations.show', $org));
        $response->assertForbidden();

        $destroyResponse = $this->delete(route('admin.organizations.destroy', $org));
        $destroyResponse->assertForbidden();

        $this->assertDatabaseHas('organizations', ['id' => $org->id]);
    }
}
