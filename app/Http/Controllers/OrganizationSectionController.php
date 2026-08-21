<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\OrganizationPage;
use App\Models\OrganizationSection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class OrganizationSectionController extends Controller
{
    /**
     * {section} is a grandchild of {organization} (Organization -> Page -> Section), which
     * Laravel's scopeBindings() naming convention can't reach, so routes taking both params
     * directly must verify ownership here instead of relying on route model binding.
     */
    private function ensureBelongsToOrganization(Organization $organization, OrganizationSection $section): void
    {
        if ($section->page->organization_id !== $organization->id) {
            throw new NotFoundHttpException;
        }
    }

    /**
     * Append a new section of the given key to the page, using its registry defaults.
     */
    public function store(Request $request, Organization $organization, OrganizationPage $page): RedirectResponse
    {
        $this->authorize('update', $organization);

        $validated = $request->validate([
            'key' => ['required', 'string', 'in:'.implode(',', array_keys(config('page-builder.sections')))],
        ]);

        $page->sections()->create([
            'key' => $validated['key'],
            'content' => config('page-builder.sections.'.$validated['key'].'.defaults', []),
            'order' => $page->sections()->max('order') + 1,
        ]);

        return redirect()
            ->route('organizations.builder.page', [$organization, $page])
            ->with('status', 'Section berhasil ditambahkan.');
    }

    /**
     * Update a section's content fields and visibility.
     *
     * When called via fetch() from the builder (Accept: application/json), responds with
     * the page's re-rendered canvas HTML instead of redirecting — the builder swaps it in
     * place so the section update doesn't reset scroll position or panel state the way a
     * full-page redirect would.
     */
    public function update(Request $request, Organization $organization, OrganizationSection $section): RedirectResponse|JsonResponse
    {
        $this->authorize('update', $organization);
        $this->ensureBelongsToOrganization($organization, $section);

        $fields = config('page-builder.sections.'.$section->key.'.fields', []);

        $content = [];
        foreach ($fields as $field) {
            $content[$field] = $request->input("content.$field");
        }

        $section->update([
            'content' => $content,
            'is_visible' => $request->boolean('is_visible', true),
        ]);

        if ($request->wantsJson()) {
            $page = $section->page->load('sections');

            return response()->json([
                'is_visible' => $section->is_visible,
                'canvas' => view('organizations.pages._render', [
                    'organization' => $organization,
                    'page' => $page,
                ])->render(),
            ]);
        }

        return redirect()
            ->route('organizations.builder.page', [$organization, $section->page])
            ->with('status', 'Section berhasil diperbarui.');
    }

    /**
     * Remove a section from its page.
     */
    public function destroy(Organization $organization, OrganizationSection $section): RedirectResponse
    {
        $this->authorize('update', $organization);
        $this->ensureBelongsToOrganization($organization, $section);

        $page = $section->page;
        $section->delete();

        return redirect()
            ->route('organizations.builder.page', [$organization, $page])
            ->with('status', 'Section berhasil dihapus.');
    }

    /**
     * Duplicate a section, inserting the copy immediately after the original.
     */
    public function duplicate(Organization $organization, OrganizationSection $section): RedirectResponse
    {
        $this->authorize('update', $organization);
        $this->ensureBelongsToOrganization($organization, $section);

        $page = $section->page;

        $page->sections()
            ->where('order', '>', $section->order)
            ->increment('order');

        $page->sections()->create([
            'key' => $section->key,
            'variant' => $section->variant,
            'content' => $section->content,
            'order' => $section->order + 1,
            'is_visible' => $section->is_visible,
        ]);

        return redirect()
            ->route('organizations.builder.page', [$organization, $page])
            ->with('status', 'Section berhasil diduplikasi.');
    }

    /**
     * Persist a new section order for a page (drag-and-drop reorder).
     *
     * Always called via fetch() from the builder (the drag-and-drop handler), so it responds
     * with the re-rendered canvas HTML the same way update() does — no page reload needed,
     * which would otherwise reset scroll position.
     */
    public function reorder(Request $request, Organization $organization, OrganizationPage $page): JsonResponse
    {
        $this->authorize('update', $organization);

        $validated = $request->validate([
            'section_ids' => ['required', 'array'],
            'section_ids.*' => ['integer', 'exists:organization_sections,id'],
        ]);

        foreach ($validated['section_ids'] as $index => $sectionId) {
            $page->sections()->where('id', $sectionId)->update(['order' => $index]);
        }

        $page->load('sections');

        return response()->json([
            'canvas' => view('organizations.pages._render', [
                'organization' => $organization,
                'page' => $page,
            ])->render(),
        ]);
    }
}
