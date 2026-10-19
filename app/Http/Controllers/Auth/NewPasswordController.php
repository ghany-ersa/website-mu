<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PasswordResetLog;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    /**
     * Displays the "set new password" form reached from a reset link - whether that link was
     * mailed (not currently wired up, see CLAUDE.md) or, today, generated and relayed manually
     * by an admin/CS. The token itself isn't validated here; Password::reset() below validates
     * it atomically with the rest of the submission.
     */
    public function create(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
        ]);

        $status = Password::reset(
            $validated,
            function (User $user, string $password) {
                $user->update(['password' => Hash::make($password)]);

                event(new PasswordReset($user));

                // Marks the most recent still-unused log row for this user as consumed, so the
                // admin panel can tell a generated link apart from one that was actually used -
                // best-effort only: a link reset by Laravel's own throttle/expiry without ever
                // being clicked just stays unmarked, which is fine, it's an audit trail, not a
                // source of truth for whether the token is still valid.
                PasswordResetLog::where('target_user_id', $user->id)
                    ->whereNull('used_at')
                    ->latest('id')
                    ->first()
                    ?->update(['used_at' => now()]);
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => __($status),
            ]);
        }

        return redirect()->route('login')->with('status', 'Kata sandi berhasil diubah. Silakan masuk dengan kata sandi baru Anda.');
    }
}
