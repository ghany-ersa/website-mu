<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    // Per-IP cap on account creation: each account can publish a site on a website-mu.id
    // subdomain, so unthrottled sign-up is a cheap way to mass-produce spam/phishing pages.
    Route::post('register', [RegisteredUserController::class, 'store'])->middleware('throttle:5,1');

    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    // Static view only - the "submit" is a client-side redirect to WhatsApp with the
    // entered email embedded in the message (see the view), not a server-side form post.
    // There's no "enter your email, we'll send a reset link" step because there's no email
    // service (see CLAUDE.md); the actual reset link is generated manually by an admin/CS
    // from the admin panel once they've verified the user over WhatsApp (see
    // Admin\UserPasswordResetController).
    Route::view('forgot-password', 'auth.forgot-password')->name('password.forgot');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])->middleware('throttle:6,1')->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
