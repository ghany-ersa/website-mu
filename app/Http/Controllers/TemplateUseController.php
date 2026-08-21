<?php

namespace App\Http\Controllers;

use App\Models\Template;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class TemplateUseController extends Controller
{
    /**
     * Entry point for "Gunakan Template" buttons (homepage and template preview).
     *
     * Guests are sent to register first, with the chosen template remembered in the
     * session so it survives the register/login flow; the auth controllers pick it back
     * up and forward the user straight into organization creation with it pre-selected.
     */
    public function __invoke(Template $template): RedirectResponse
    {
        if (Auth::guest()) {
            Session::put('pending_template_slug', $template->slug);

            return redirect()->route('register');
        }

        return redirect()->route('organizations.create', ['template' => $template->slug]);
    }
}
