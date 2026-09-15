<?php

namespace App\Http\Controllers\Auth;

use App\Models\Template;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Session;

trait RedirectsAfterAuthentication
{
    /**
     * Send a freshly authenticated user into organization creation with the template
     * they picked before registering/logging in (see TemplateUseController), or to
     * their organization list if they already belong to one, or to organization
     * creation if they don't have one yet.
     */
    private function redirectAfterAuthentication(): RedirectResponse
    {
        $templateSlug = Session::pull('pending_template_slug');

        // Mirrors the filters OrganizationController::create() applies: an exclusive or typeless
        // template would only bounce the user straight back to the picker, so don't forward them
        // into step 2 carrying one.
        $template = $templateSlug
            ? Template::where('slug', $templateSlug)
                ->where('is_active', true)
                ->where('is_exclusive', false)
                ->whereNotNull('organization_type_id')
                ->first()
            : null;

        if ($template) {
            return redirect()->route('organizations.create', ['template' => $template->slug]);
        }

        return redirect()->route('organizations.index');
    }
}
