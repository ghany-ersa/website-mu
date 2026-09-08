<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Template;
use App\Services\TemplateSandboxService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

/**
 * Entry and exit points for editing a Template visually in the ordinary page builder, via a
 * hidden sandbox organization (see TemplateSandboxService).
 */
class TemplateBuilderController extends Controller
{
    public function __construct(private readonly TemplateSandboxService $sandboxService) {}

    /**
     * Send the admin into the page builder for this template's sandbox organization.
     */
    public function edit(Template $template): RedirectResponse
    {
        $sandbox = $this->sandboxService->sandboxFor($template, Auth::user());

        return redirect()->route('organizations.builder.page', [
            $sandbox,
            $sandbox->pages()->where('is_home', true)->first() ?? $sandbox->pages()->first(),
        ]);
    }

    /**
     * Write the sandbox's current pages/sections/brand back to the template's structure.
     */
    public function update(Template $template): RedirectResponse
    {
        $sandbox = $this->sandboxService->sandboxFor($template, Auth::user());

        $template->update(['structure' => $this->sandboxService->export($sandbox, $template)]);

        return redirect()
            ->route('admin.templates.edit', $template)
            ->with('status', 'Struktur template berhasil disimpan dari editor visual.');
    }

    /**
     * Reset the sandbox back to whatever the template's stored structure says, discarding
     * unsaved visual edits - the escape hatch after editing the raw JSON by hand.
     */
    public function resync(Template $template): RedirectResponse
    {
        $sandbox = $this->sandboxService->resync($template, Auth::user());

        return redirect()->route('organizations.builder.page', [
            $sandbox,
            $sandbox->pages()->where('is_home', true)->first() ?? $sandbox->pages()->first(),
        ]);
    }
}
