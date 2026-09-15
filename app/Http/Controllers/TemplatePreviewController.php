<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\Template;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;

class TemplatePreviewController extends Controller
{
    /**
     * Render a template's sample structure so its pages can be previewed before it is applied
     * to an organization. Also hands each section the template's sandbox organization (if one
     * exists yet - see sandboxFor()) as `$organization`, so CMS-backed sections (daftar-berita,
     * donasi-progress, galeri, agenda, struktur-pengurus, ...) render their real seeded sample
     * rows and working detail links, the same `isset($organization)` branch they already use on
     * a real tenant page - instead of the static, link-less `content.items` sample arrays those
     * branches otherwise fall back to. Falls back to that JSON-only rendering (unset
     * `$organization`) when no sandbox exists yet, e.g. a template nobody has opened "Edit
     * Visual" on since it was made active.
     */
    public function show(Template $template, ?string $page = null): View
    {
        $pages = collect($template->structure['pages'] ?? []);

        $currentPage = $page
            ? $pages->firstWhere('slug', $page)
            : $pages->first();

        abort_if($currentPage === null, 404);

        return view('templates.preview', [
            'template' => $template,
            'pages' => $pages,
            'currentPage' => $currentPage,
            'organization' => $this->sandboxQuery($template)->first(),
        ]);
    }

    /**
     * Public "detail berita" for a template preview - the same page a real organization's
     * daftar-berita section links to (organizations.public.post), but reachable from the
     * template catalog before any organization exists. Sourced from the template's sandbox
     * organization (see TemplateSandboxService), which already carries real seeded Post rows
     * via CmsSampleDataSeeder - so this shows genuine sample content, not a dead link.
     *
     * Deliberately unauthenticated, unlike OrganizationSiteController::preview()/
     * previewDonationProgram(): those gate on organization membership because they preview a
     * real tenant's own (possibly unpublished) content, but a template's sandbox is sample
     * content anyone browsing /templates should be able to read.
     */
    public function post(Template $template, string $post_slug): View
    {
        $sandbox = $this->sandboxQuery($template)->firstOrFail();
        $post = $sandbox->posts()->where('slug', $post_slug)->firstOrFail();

        return view('organizations.public.post', [
            'organization' => $sandbox,
            'post' => $post,
        ]);
    }

    /**
     * Public "detail donasi" for a template preview - see post() above for the same reasoning
     * (sandbox-sourced, deliberately unauthenticated, mirrors organizations.public.donation-program).
     */
    public function donationProgram(Template $template, string $program_slug): View
    {
        $sandbox = $this->sandboxQuery($template)->firstOrFail();
        $program = $sandbox->donationPrograms()->with('transactions')->where('slug', $program_slug)->firstOrFail();

        return view('organizations.public.donation-program', [
            'organization' => $sandbox,
            'program' => $program,
        ]);
    }

    /**
     * Public "detail agenda" for a template preview - see post() above for the same reasoning
     * (sandbox-sourced, deliberately unauthenticated, mirrors organizations.public.agenda).
     * Keyed by id rather than slug: Agenda has no slug column, same as tenant.agendas.show.
     */
    public function agenda(Template $template, int $agenda): View
    {
        $sandbox = $this->sandboxQuery($template)->firstOrFail();
        $agendaModel = $sandbox->agendas()->published()->findOrFail($agenda);

        return view('organizations.public.agenda', [
            'organization' => $sandbox,
            'agenda' => $agendaModel,
        ]);
    }

    /**
     * The read-only counterpart to TemplateSandboxService::sandboxFor(), which requires an
     * authenticated admin because it can *create* a sandbox on first use. A public visitor here
     * is never authenticated and never should trigger sandbox creation - by the time a template
     * is public enough to preview, an admin has usually already opened "Edit Visual" on it at
     * least once (that's how structure['pages'] itself would have been authored), so the sandbox
     * already exists. show() tolerates it not existing yet (falls back to JSON-only rendering);
     * post()/donationProgram() 404 instead of inventing an owner for a sandbox that isn't there.
     *
     * @return Builder<Organization>
     */
    private function sandboxQuery(Template $template): Builder
    {
        return Organization::where('is_sandbox', true)->where('template_id', $template->id);
    }
}
