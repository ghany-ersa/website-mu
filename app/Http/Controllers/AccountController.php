<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/**
 * The user's own account settings (not an organization's) - one page, one card per setting,
 * each saved through its own PATCH endpoint, same split as OrganizationEditController so a
 * validation error on one card never discards what was typed into another.
 */
class AccountController extends Controller
{
    public function edit(): View
    {
        return view('account.edit', ['user' => Auth::user()]);
    }

    public function updateWhatsapp(Request $request): RedirectResponse
    {
        $request->merge(['whatsapp' => User::normalizeWhatsapp($request->input('whatsapp'))]);

        $validated = $request->validate([
            'whatsapp' => ['required', 'regex:'.User::WHATSAPP_PATTERN],
        ], [
            'whatsapp.required' => 'Nomor WhatsApp wajib diisi.',
            'whatsapp.regex' => 'Nomor WhatsApp tidak valid. Gunakan format 08xxxxxxxxxx.',
        ]);

        Auth::user()->update(['whatsapp' => $validated['whatsapp']]);

        return back()->with('status', 'Nomor WhatsApp berhasil disimpan.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ], [
            'current_password.current_password' => 'Kata sandi saat ini salah.',
        ]);

        Auth::user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('status', 'Kata sandi berhasil diubah.');
    }
}
