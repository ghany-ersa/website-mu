<?php

namespace Tests\Feature;

use App\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
     * Regression test: the delete button previously used an Alpine confirmAction() modal whose
     * @submit.prevent expression had `&quot;` around the org name inside a JS template literal
     * — Blade renders that as a literal 6-character string, not a real quote character, making
     * the resulting JS a syntax error (button silently did nothing when clicked). Switched to a
     * plain onsubmit="return confirm(...)" with string concatenation (not a template literal —
     * a backtick in the org name would otherwise terminate it early), with the name passed
     * through Illuminate\Support\Js::from() so it's safe regardless of quotes/backticks in it.
     */
    public function test_delete_button_confirm_uses_safely_escaped_org_name(): void
    {
        $owner = User::factory()->create();
        $org = Organization::factory()->create(['name' => 'Dante "Guthrie" `Test`']);
        $org->members()->attach($owner->id, ['role' => OrganizationRole::Owner->value]);

        $html = $this->actingAs($owner)->get(route('organizations.show', $org))->getContent();

        $this->assertStringContainsString("onsubmit=\"return confirm('Hapus organisasi ' + ", $html);
        $this->assertStringContainsString(\Illuminate\Support\Js::from($org->name)->toHtml(), $html);
    }

    public function test_admin_delete_button_confirm_uses_safely_escaped_org_name(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $org = Organization::factory()->create(['name' => 'Dante "Guthrie" `Test`']);

        $html = $this->actingAs($admin)->get(route('admin.organizations.show', $org))->getContent();

        $this->assertStringContainsString("onsubmit=\"return confirm('Hapus organisasi ' + ", $html);
        $this->assertStringContainsString(\Illuminate\Support\Js::from($org->name)->toHtml(), $html);
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
        // Deliberately no membership row for $admin — proving the delete works via the
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
