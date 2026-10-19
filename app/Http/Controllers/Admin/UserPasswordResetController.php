<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\PasswordResetLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;

class UserPasswordResetController extends Controller
{
    /**
     * Generates a password reset link for a user and hands it back to the admin to relay
     * manually over WhatsApp - there's no email service wired up (see CLAUDE.md), so the
     * normal Password::sendResetLink() mail step is skipped entirely. Reuses Laravel's own
     * broker/token table so the link lands on the same public reset form a mailed link would,
     * just without ever calling Notification::send().
     */
    public function store(Organization $organization, User $user): RedirectResponse
    {
        // Not inside scopeBindings(): {user} would be scoped through Organization::user(),
        // which doesn't exist (the relation is members()) - same reason OrganizationMemberController's
        // routes sit outside that group too. Verified manually instead.
        abort_unless($organization->members()->where('user_id', $user->id)->exists(), 404);

        $token = Password::createToken($user);

        $url = route('password.reset', ['token' => $token, 'email' => $user->email]);

        PasswordResetLog::create([
            'admin_user_id' => Auth::id(),
            'target_user_id' => $user->id,
            'organization_id' => $organization->id,
        ]);

        return redirect()
            ->route('admin.organizations.show', $organization)
            ->with('status', 'Tautan reset kata sandi untuk '.$user->name.' berhasil dibuat. Salin dan kirimkan secara manual ke pengguna.')
            ->with('generatedResetLink', $url);
    }
}
