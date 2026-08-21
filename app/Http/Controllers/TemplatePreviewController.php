<?php

namespace App\Http\Controllers;

use App\Models\Template;
use Illuminate\View\View;

class TemplatePreviewController extends Controller
{
    /**
     * Render a template's sample structure so its pages can be previewed
     * before it is applied to an organization.
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
        ]);
    }
}
