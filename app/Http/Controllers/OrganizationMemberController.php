<?php

namespace App\Http\Controllers;

use App\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class OrganizationMemberController extends Controller
{
    /**
     * Add an existing user to the organization with the given role.
     */
    public function store(Request $request, Organization $organization): RedirectResponse
    {
        $this->authorize('manageMembers', $organization);

        $validated = $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ]);

        $member = User::where('email', $validated['email'])->firstOrFail();

        if ($organization->members()->where('user_id', $member->id)->exists()) {
            throw ValidationException::withMessages(['email' => 'Pengguna ini sudah menjadi anggota organisasi.']);
        }

        $organization->members()->attach($member, ['role' => OrganizationRole::Editor->value]);

        return back()->with('status', "{$member->name} ditambahkan sebagai ".OrganizationRole::Editor->label().'.');
    }

    /**
     * Change a member's role.
     */
    public function update(Request $request, Organization $organization, User $user): RedirectResponse
    {
        $this->authorize('manageMembers', $organization);

        $validated = $request->validate([
            'role' => ['required', Rule::enum(OrganizationRole::class)],
        ]);

        $this->guardLastOwner($organization, $user, OrganizationRole::from($validated['role']));

        $organization->members()->updateExistingPivot($user->id, ['role' => $validated['role']]);

        return back()->with('status', 'Peran anggota berhasil diperbarui.');
    }

    /**
     * Remove a member from the organization.
     */
    public function destroy(Organization $organization, User $user): RedirectResponse
    {
        $this->authorize('manageMembers', $organization);

        $this->guardLastOwner($organization, $user, null);

        $organization->members()->detach($user);

        return back()->with('status', 'Anggota berhasil dihapus dari organisasi.');
    }

    /**
     * Prevent removing/demoting the organization's last remaining Owner.
     */
    private function guardLastOwner(Organization $organization, User $user, ?OrganizationRole $newRole): void
    {
        $currentRole = $organization->roleFor($user);
        $ownerCount = $organization->members()->wherePivot('role', OrganizationRole::Owner->value)->count();

        if ($currentRole === OrganizationRole::Owner && $newRole !== OrganizationRole::Owner && $ownerCount <= 1) {
            throw ValidationException::withMessages(['role' => 'Organisasi harus memiliki minimal satu Owner.']);
        }
    }
}
