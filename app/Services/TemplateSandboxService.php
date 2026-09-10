<?php

namespace App\Services;

use App\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\Template;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Backs the admin "Edit Visual" flow for templates: instead of hand-writing Template::structure
 * JSON, an admin designs the template inside a throwaway ("sandbox") organization using the
 * normal page builder, then exports it back to structure.
 *
 * The builder is untouched by this - it only ever knows how to edit an Organization's pages and
 * sections, so handing it a real (if hidden) organization is what lets the whole feature reuse it
 * as-is. Organization::seedPagesFromTemplate() already handles structure -> rows; export() here is
 * the inverse direction.
 */
class TemplateSandboxService
{
    /**
     * Limit keys forced to unlimited on a sandbox. Without these the sandbox would be capped like
     * any Starter organization: seedPagesFromTemplate() silently drops trailing sections past
     * 'sections_total' (see Organization::seedPagesFromTemplate()), and the builder hides its "add
     * page" button past 'pages_total' - either one would quietly corrupt the template being edited
     * rather than just limiting the admin.
     *
     * @var array<int, string>
     */
    private const UNLIMITED_KEYS = [
        'sections_total',
        'pages_total',
        'posts',
        'agendas',
        'announcements',
        'officers',
        'programs',
        'gallery_photos',
        'facilities',
        'donation_programs',
    ];

    /**
     * The sandbox organization for a template, creating and seeding it on first use.
     *
     * Idempotent: an admin can leave and re-enter the designer without losing work, since an
     * existing sandbox is returned untouched rather than re-seeded from structure (use resync()
     * to deliberately discard sandbox edits in favour of the template's stored JSON).
     */
    public function sandboxFor(Template $template, User $admin): Organization
    {
        $sandbox = Organization::where('is_sandbox', true)
            ->where('template_id', $template->id)
            ->first();

        if (! $sandbox) {
            $sandbox = DB::transaction(function () use ($template, $admin) {
                $brand = $template->structure['brand'] ?? [];

                $contact = $template->structure['contact'] ?? [];

                $organization = Organization::create([
                    'organization_type_id' => $template->organization_type_id,
                    'template_id' => $template->id,
                    'is_sandbox' => true,
                    'name' => $template->structure['sample_org_name'] ?? $template->name,
                    // Derived from the template id so it's stable and unique per template; a real
                    // tenant can't later claim it because slug is unique:organizations,slug in
                    // StoreOrganizationRequest and OrganizationEditController.
                    'slug' => "sandbox-template-{$template->id}",
                    'primary_color' => $brand['primary'] ?? null,
                    'secondary_color' => $brand['secondary'] ?? null,
                    'font_family' => $brand['font'] ?? null,
                    'border_radius' => $brand['radius'] ?? null,
                    'phone' => $contact['phone'] ?? null,
                    'email' => $contact['email'] ?? null,
                    'whatsapp' => $contact['whatsapp'] ?? null,
                    'address' => $contact['address'] ?? null,
                    'instagram_url' => $contact['instagram_url'] ?? null,
                    'facebook_url' => $contact['facebook_url'] ?? null,
                    'tiktok_url' => $contact['tiktok_url'] ?? null,
                    'youtube_url' => $contact['youtube_url'] ?? null,
                ]);

                $organization->members()->attach($admin->id, ['role' => OrganizationRole::Owner->value]);

                foreach (self::UNLIMITED_KEYS as $key) {
                    $organization->limitOverrides()->create([
                        'key' => $key,
                        'max_count' => null,
                        'note' => 'Sandbox editor template - tanpa batas.',
                    ]);
                }

                return $organization;
            });

            // Outside the transaction, and only after the overrides exist: this clones the
            // template's structure into pages/sections, and reads those limits as it goes.
            $sandbox->ensureHomePageExists();
        }

        return $sandbox;
    }

    /**
     * Serialize a sandbox organization's pages, sections and brand back into the array shape
     * Template::structure holds - the inverse of Organization::seedPagesFromTemplate().
     *
     * `sample_org_name` is carried over from the template's existing structure rather than taken
     * from the sandbox's name: a dozen section partials read it as the stand-in organization name
     * when rendering a template preview (see templates/sections/cta/standar.blade.php).
     *
     * @return array<string, mixed>
     */
    public function export(Organization $sandbox, Template $template): array
    {
        $sandbox->load('pages.sections');

        $structure = $template->structure ?? [];

        $structure['brand'] = [
            'primary' => $sandbox->primaryColor(),
            'secondary' => $sandbox->secondaryColor(),
            'font' => $sandbox->fontFamily(),
            'radius' => $sandbox->borderRadius(),
        ];

        // Contact info is edited on the sandbox org via the normal Brand Setting page
        // (organizations.brand.edit) - captured here so "Simpan ke Template" carries it into
        // structure just like it does for brand colors, instead of silently dropping it.
        $structure['contact'] = [
            'phone' => $sandbox->phone,
            'email' => $sandbox->email,
            'whatsapp' => $sandbox->whatsapp,
            'address' => $sandbox->address,
            'instagram_url' => $sandbox->instagram_url,
            'facebook_url' => $sandbox->facebook_url,
            'tiktok_url' => $sandbox->tiktok_url,
            'youtube_url' => $sandbox->youtube_url,
        ];

        $structure['pages'] = $sandbox->pages->map(fn ($page) => [
            'slug' => $page->slug,
            'name' => $page->name,
            'sections' => $page->sections->map(function ($section) {
                $data = [
                    'key' => $section->key,
                    'variant' => $section->variant,
                ];

                // Locked sections (header/footer) carry no editable fields, and existing seeded
                // templates omit `content` for them entirely - keep that shape.
                if (filled($section->content)) {
                    $data['content'] = $section->content;
                }

                return $data;
            })->values()->all(),
        ])->values()->all();

        return $structure;
    }

    /**
     * Discard the sandbox's current pages/sections and re-clone them from the template's stored
     * structure - for when an admin edits the raw JSON and wants the visual editor to catch up.
     */
    public function resync(Template $template, User $admin): Organization
    {
        $sandbox = $this->sandboxFor($template, $admin);

        $sandbox->pages()->each(fn ($page) => $page->delete());
        $sandbox->refresh()->ensureHomePageExists();

        return $sandbox;
    }
}
