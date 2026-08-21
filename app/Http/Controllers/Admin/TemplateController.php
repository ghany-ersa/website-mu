<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTemplateRequest;
use App\Http\Requests\UpdateTemplateRequest;
use App\Models\OrganizationType;
use App\Models\Template;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $templates = Template::with('organizationType')->orderBy('name')->get();

        return view('admin.templates.index', ['templates' => $templates]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.templates.create', [
            'organizationTypes' => OrganizationType::orderBy('name')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTemplateRequest $request): RedirectResponse
    {
        $template = Template::create($this->prepare($request));

        return redirect()
            ->route('admin.templates.edit', $template)
            ->with('status', 'Template berhasil dibuat.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Template $template): View
    {
        return view('admin.templates.edit', [
            'template' => $template,
            'organizationTypes' => OrganizationType::orderBy('name')->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTemplateRequest $request, Template $template): RedirectResponse
    {
        $template->update($this->prepare($request));

        return redirect()
            ->route('admin.templates.edit', $template)
            ->with('status', 'Template berhasil disimpan.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Template $template): RedirectResponse
    {
        $template->delete();

        return redirect()
            ->route('admin.templates.index')
            ->with('status', 'Template berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function prepare(StoreTemplateRequest|UpdateTemplateRequest $request): array
    {
        return [
            ...$request->safe()->except(['structure', 'is_active']),
            'structure' => json_decode((string) $request->validated('structure'), true),
            'is_active' => $request->boolean('is_active'),
        ];
    }
}
