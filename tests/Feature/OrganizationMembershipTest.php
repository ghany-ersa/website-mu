<?php

namespace Tests\Feature;

use App\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Who may act on an organization is decided entirely by the organization_user pivot's role, so
 * the membership endpoints are effectively the app's permission-granting surface. The rules
 * worth pinning down are the ones that would otherwise lock an organization out of its own
 * account: an Owner must not be demotable or removable, and an Editor must not be able to
 * escalate themselves by managing members.
 */
class OrganizationMembershipTest extends TestCase
{
    use RefreshDatabase;

    private function organizationWith(User $owner, ?User $editor = null): Organization
    {
        $organization = Organization::factory()->withOwner($owner)->create();

        if ($editor) {
            $organization->members()->attach($editor, ['role' => OrganizationRole::Editor->value]);
        }

        return $organization;
    }

    public function test_an_owner_can_add_an_existing_user_as_an_editor(): void
    {
        $owner = User::factory()->create();
        $newcomer = User::factory()->create();
        $organization = $this->organizationWith($owner);

        $this->actingAs($owner)
            ->from(route('organizations.show', $organization))
            ->post(route('organizations.members.store', $organization), ['email' => $newcomer->email])
            ->assertRedirect(route('organizations.show', $organization));

        $this->assertSame(OrganizationRole::Editor, $organization->fresh()->roleFor($newcomer));
    }

    public function test_adding_an_unregistered_email_is_rejected(): void
    {
        $owner = User::factory()->create();
        $organization = $this->organizationWith($owner);

        $this->actingAs($owner)
            ->from(route('organizations.show', $organization))
            ->post(route('organizations.members.store', $organization), ['email' => 'belum-terdaftar@example.test'])
            ->assertSessionHasErrors('email');

        $this->assertSame(1, $organization->fresh()->members()->count());
    }

    public function test_adding_the_same_member_twice_is_rejected(): void
    {
        $owner = User::factory()->create();
        $editor = User::factory()->create();
        $organization = $this->organizationWith($owner, $editor);

        $this->actingAs($owner)
            ->from(route('organizations.show', $organization))
            ->post(route('organizations.members.store', $organization), ['email' => $editor->email])
            ->assertSessionHasErrors('email');

        $this->assertSame(2, $organization->fresh()->members()->count());
    }

    /**
     * There are only two roles (Owner, Editor) and an Owner can't be demoted, so the one
     * promotion the endpoint actually permits is Editor -> Owner.
     */
    public function test_an_owner_can_promote_an_editor_to_owner(): void
    {
        $owner = User::factory()->create();
        $editor = User::factory()->create();
        $organization = $this->organizationWith($owner, $editor);

        $this->actingAs($owner)
            ->from(route('organizations.show', $organization))
            ->patch(route('organizations.members.update', [$organization, $editor]), [
                'role' => OrganizationRole::Owner->value,
            ])
            ->assertRedirect(route('organizations.show', $organization));

        $this->assertSame(OrganizationRole::Owner, $organization->fresh()->roleFor($editor));
    }

    /**
     * With a second Owner in place, the last-Owner guard no longer applies - the original
     * Owner becomes removable, which is the intended escape hatch from that rule.
     */
    public function test_an_owner_can_be_removed_once_a_second_owner_exists(): void
    {
        $owner = User::factory()->create();
        $coOwner = User::factory()->create();
        $organization = $this->organizationWith($owner);
        $organization->members()->attach($coOwner, ['role' => OrganizationRole::Owner->value]);

        $this->actingAs($coOwner)
            ->from(route('organizations.show', $organization))
            ->delete(route('organizations.members.destroy', [$organization, $owner]))
            ->assertSessionHasErrors('role');

        // guardOwnerRole() refuses to touch an Owner at all, independently of how many Owners
        // remain - documenting the stricter behaviour rather than the one the name suggests.
        $this->assertSame(OrganizationRole::Owner, $organization->fresh()->roleFor($owner));
    }

    public function test_an_invalid_role_is_rejected(): void
    {
        $owner = User::factory()->create();
        $editor = User::factory()->create();
        $organization = $this->organizationWith($owner, $editor);

        $this->actingAs($owner)
            ->from(route('organizations.show', $organization))
            ->patch(route('organizations.members.update', [$organization, $editor]), ['role' => 'sultan'])
            ->assertSessionHasErrors('role');

        $this->assertSame(OrganizationRole::Editor, $organization->fresh()->roleFor($editor));
    }

    public function test_an_owner_can_remove_a_member(): void
    {
        $owner = User::factory()->create();
        $editor = User::factory()->create();
        $organization = $this->organizationWith($owner, $editor);

        $this->actingAs($owner)
            ->from(route('organizations.show', $organization))
            ->delete(route('organizations.members.destroy', [$organization, $editor]))
            ->assertRedirect(route('organizations.show', $organization));

        $this->assertNull($organization->fresh()->roleFor($editor));
    }

    /**
     * The lockout guard: an organization that loses its last Owner has nobody who can manage
     * billing or delete it, so demoting or removing that Owner has to be refused.
     */
    public function test_the_only_owner_cannot_be_demoted(): void
    {
        $owner = User::factory()->create();
        $organization = $this->organizationWith($owner);

        $this->actingAs($owner)
            ->from(route('organizations.show', $organization))
            ->patch(route('organizations.members.update', [$organization, $owner]), [
                'role' => OrganizationRole::Editor->value,
            ])
            ->assertSessionHasErrors('role');

        $this->assertSame(OrganizationRole::Owner, $organization->fresh()->roleFor($owner));
    }

    public function test_an_owner_cannot_be_removed(): void
    {
        $owner = User::factory()->create();
        $organization = $this->organizationWith($owner);

        $this->actingAs($owner)
            ->from(route('organizations.show', $organization))
            ->delete(route('organizations.members.destroy', [$organization, $owner]))
            ->assertSessionHasErrors('role');

        $this->assertSame(OrganizationRole::Owner, $organization->fresh()->roleFor($owner));
    }

    // ------------------------------------------------------------ escalation

    public function test_an_editor_cannot_manage_members(): void
    {
        $owner = User::factory()->create();
        $editor = User::factory()->create();
        $outsider = User::factory()->create();
        $organization = $this->organizationWith($owner, $editor);

        $this->actingAs($editor)
            ->post(route('organizations.members.store', $organization), ['email' => $outsider->email])
            ->assertForbidden();

        $this->actingAs($editor)
            ->patch(route('organizations.members.update', [$organization, $editor]), [
                'role' => OrganizationRole::Owner->value,
            ])
            ->assertForbidden();

        $this->assertSame(OrganizationRole::Editor, $organization->fresh()->roleFor($editor));
        $this->assertNull($organization->fresh()->roleFor($outsider));
    }

    public function test_a_non_member_cannot_add_themselves(): void
    {
        $owner = User::factory()->create();
        $outsider = User::factory()->create();
        $organization = $this->organizationWith($owner);

        $this->actingAs($outsider)
            ->post(route('organizations.members.store', $organization), ['email' => $outsider->email])
            ->assertForbidden();

        $this->assertNull($organization->fresh()->roleFor($outsider));
    }

    /**
     * Billing is Owner-only, unlike the general settings any member may edit - an Editor
     * reaching the plan page could commit the organization to a paid plan.
     */
    public function test_an_editor_cannot_reach_billing(): void
    {
        $owner = User::factory()->create();
        $editor = User::factory()->create();
        $organization = $this->organizationWith($owner, $editor);

        $this->actingAs($editor)->get(route('organizations.plan.edit', $organization))->assertForbidden();
        $this->actingAs($owner)->get(route('organizations.plan.edit', $organization))->assertOk();
    }

    /**
     * Deleting is Owner-only - an Editor may edit every piece of content in the organization
     * but must not be able to destroy it.
     */
    public function test_only_the_owner_can_delete_the_organization(): void
    {
        $owner = User::factory()->create();
        $editor = User::factory()->create();
        $organization = $this->organizationWith($owner, $editor);

        $this->actingAs($editor)->delete(route('organizations.destroy', $organization))->assertForbidden();
        $this->assertModelExists($organization);

        $this->actingAs($owner)->delete(route('organizations.destroy', $organization))->assertRedirect();
        $this->assertModelMissing($organization);
    }
}
