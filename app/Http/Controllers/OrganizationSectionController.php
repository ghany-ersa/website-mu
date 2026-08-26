<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\OrganizationPage;
use App\Models\OrganizationSection;
use App\Services\CmsSampleDataSeeder;
use App\Services\GoogleMapsEmbedResolver;
use App\Services\PlanLimitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class OrganizationSectionController extends Controller
{
    public function __construct(private readonly PlanLimitService $planLimitService) {}

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
     * Locked sections (header, footer — see config/page-builder.php) must always exist and
     * stay in their pinned position (header first, footer last), so delete/duplicate is
     * rejected here regardless of which locked section it is.
     */
    private function ensureNotLocked(OrganizationSection $section): void
    {
        abort_if(config('page-builder.sections.'.$section->key.'.locked', false), 403, 'Section ini tidak dapat diubah.');
    }

    /**
     * Unlike delete/duplicate, editing content is only blocked for locked sections that have
     * no editable fields at all (currently just `footer`) — header is locked in position but
     * still exposes `org_name`, which the user must be able to edit from the builder.
     */
    private function ensureContentEditable(OrganizationSection $section): void
    {
        $locked = config('page-builder.sections.'.$section->key.'.locked', false);
        $hasFields = filled(config('page-builder.sections.'.$section->key.'.fields', []));

        abort_if($locked && ! $hasFields, 403, 'Section ini tidak dapat diubah.');
    }

    /**
     * Append a new section of the given key to the page, using its registry defaults.
     *
     * For a CMS-backed section (struktur-pengurus, program-unggulan, layanan, daftar-berita,
     * agenda, pengumuman, jaringan-aum-ortom, galeri — see CmsSampleDataSeeder), the section
     * partial renders live from the organization's CMS tables rather than from `content`
     * (see e.g. templates/sections/daftar-berita.blade.php), so a freshly-added section on an
     * organization with no CMS data yet would otherwise render empty in the canvas. Reusing
     * CmsSampleDataSeeder — the same seeding Organization::seedPagesFromTemplate() runs for a
     * brand-new organization's starter sections — fills it with editable sample rows instead,
     * but only if that CMS table is still empty, so it never touches real content the user has
     * since added.
     */
    public function store(Request $request, Organization $organization, OrganizationPage $page): RedirectResponse
    {
        $this->authorize('update', $organization);

        $addableKeys = array_keys(array_filter(
            config('page-builder.sections'),
            fn (array $meta) => empty($meta['locked'])
        ));

        $validated = $request->validate([
            'key' => ['required', 'string', 'in:'.implode(',', $addableKeys)],
        ]);

        if (! $this->planLimitService->canCreate($organization, 'sections_total')) {
            return redirect()
                ->route('organizations.builder.page', [$organization, $page])
                ->with('warning', 'Batas jumlah komponen paket Anda sudah tercapai. Upgrade paket untuk menambah lagi.');
        }

        $content = config('page-builder.sections.'.$validated['key'].'.defaults', []);

        foreach ($content as $field => $value) {
            if (is_string($value)) {
                $content[$field] = str_replace('{org_name}', $organization->name, $value);
            }
        }

        $page->sections()->create([
            'key' => $validated['key'],
            'content' => $content,
            'order' => $page->sections()->max('order') + 1,
        ]);

        CmsSampleDataSeeder::seed($organization, [$validated['key']]);

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
        $this->ensureContentEditable($section);

        $fields = config('page-builder.sections.'.$section->key.'.fields', []);

        $content = [];
        foreach ($fields as $field) {
            $content[$field] = $request->input("content.$field");
        }

        if (in_array('map_embed', $fields, true) && filled($content['map_embed'] ?? null)) {
            $content['map_embed'] = GoogleMapsEmbedResolver::resolve($content['map_embed']) ?? $content['map_embed'];
        }

        foreach ($fields as $field) {
            if (! str_ends_with($field, 'wa_message') || filled($content[$field] ?? null)) {
                continue;
            }

            $default = config("page-builder.sections.{$section->key}.defaults.{$field}", '');
            $content[$field] = str_replace('{org_name}', $organization->name, $default);
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
        $this->ensureNotLocked($section);

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
        $this->ensureNotLocked($section);

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

        // Header and footer are locked (see config/page-builder.php) and must always stay
        // first/last respectively, so any of their ids the client sends is silently dropped
        // rather than being allowed to move — the drag-and-drop UI shouldn't offer them as
        // draggable in the first place, but this is the authoritative guard regardless of
        // what the request sends.
        $headerId = $page->sections()->where('key', 'header')->value('id');
        $footerId = $page->sections()->where('key', 'footer')->value('id');
        $orderedIds = array_values(array_filter(
            $validated['section_ids'],
            fn (int $id) => $id !== $headerId && $id !== $footerId
        ));

        if ($headerId) {
            $page->sections()->where('id', $headerId)->update(['order' => 0]);
        }

        foreach ($orderedIds as $index => $sectionId) {
            $page->sections()->where('id', $sectionId)->update(['order' => $index + 1]);
        }

        if ($footerId) {
            $page->sections()->where('id', $footerId)->update(['order' => count($orderedIds) + 1]);
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
