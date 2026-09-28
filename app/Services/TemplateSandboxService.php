<?php

namespace App\Services;

use App\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\Template;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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
                    // Read back here (a sandbox reopening its own template's saved state) but
                    // deliberately NOT read by seedPagesFromTemplate()/prepareForValidation() -
                    // see export()'s comment on why a real organization never inherits this.
                    'logo' => $brand['logo'] ?? null,
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

        // 'logo' rides in `brand` alongside the colors/font/radius it's exported next to, but
        // it is NOT read back the way those are: Organization::seedPagesFromTemplate() and
        // OrganizationSeeder::brandFrom() only ever pull primary/secondary/font/radius from
        // structure['brand'], and StoreOrganizationRequest::prepareForValidation() the same -
        // none of them touch 'logo'. So a real organization created from this template never
        // inherits the logo (see Organization::onboardingChecklist()'s doc comment: "logo,
        // unlike the colors, is never auto-filled from a template") - it's captured here purely
        // so the admin sees it again the next time sandboxFor() rebuilds this sandbox (that path
        // DOES read structure['brand']['logo'] back, since re-opening the editor should show
        // what was last saved).
        $structure['brand'] = [
            'primary' => $sandbox->primaryColor(),
            'secondary' => $sandbox->secondaryColor(),
            'font' => $sandbox->fontFamily(),
            'radius' => $sandbox->borderRadius(),
            'logo' => $sandbox->logo,
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
            'sections' => $page->sections->map(function ($section) use ($sandbox) {
                $data = [
                    'key' => $section->key,
                    'variant' => $section->variant,
                ];

                // Locked sections (header/footer) carry no editable fields, and existing seeded
                // templates omit `content` for them entirely - keep that shape.
                if (filled($section->content)) {
                    $data['content'] = $this->refreshBoundItems($sandbox, $section->key, $section->content);
                }

                return $data;
            })->values()->all(),
        ])->values()->all();

        return $structure;
    }

    /**
     * Rebuilds `content['items']` from the sandbox's own CMS records for every section key
     * that auto-binds to one (see each partial's own "Auto-binds to the organization's ..."
     * doc comment - daftar-berita, program-unggulan, layanan, struktur-pengurus,
     * jaringan-aum-ortom, agenda, pengumuman, galeri, fasilitas-masjid, donasi-progress).
     *
     * Without this, `content['items']` in $section->content is whatever was last written to
     * it - which for a bound section is only ever the placeholder ensureHomePageExists() seeds
     * on first clone, since the sandbox editor itself always renders from the live CMS tables
     * (Organization::programs(), ->donationPrograms(), ...), never from this field. An admin
     * editing donations/officers/etc. through the CMS pages, then clicking "Simpan ke
     * Template", would otherwise export that stale placeholder instead of what they just
     * edited - exactly the mismatch this method exists to close.
     *
     * Deliberately duplicates each view's own $organization-branch mapping rather than the two
     * sharing one implementation: the view runs inside a Blade template (no natural place to
     * extract a shared PHP method both `resources/views` and `app/Services` could call without
     * a new shared class solely for this), and the mapping is small and stable enough per
     * section that keeping this switch in step with the matching view when either changes is a
     * one-line diff, not a maintenance burden. `url` fields the view computes from the tenant
     * subdomain are left null - a template-preview render has no tenant URL to link to for the
     * SAME reason $content['items'] itself does not carry one; every other bound partial's
     * `? $content['items'] ?? ...` fallback branch already treats a missing url the same way.
     *
     * Sections with no CMS binding pass through untouched, and that is verified rather than
     * assumed - the full 25-key registry splits three ways:
     *
     *   - The 10 keys below: every registry entry carrying a `cms` route EXCEPT
     *     laporan-keuangan (config/page-builder.php), i.e. everything an admin edits on a
     *     separate CMS page and therefore everything that could go stale here.
     *   - laporan-keuangan: has a `cms` route, but its partial reads financialReports() into
     *     its own local variables and reads nothing from content beyond `title` - there is no
     *     `items` shape to rebuild, so a template preview genuinely cannot show real figures.
     *     Skipped deliberately, not overlooked.
     *   - Everything else (hero, cta, formulir-kontak, tentang-organisasi, sambutan-ketua,
     *     lokasi-peta, donasi-zakat-infak, ppdb, kalkulator-zakat, header, footer, and the
     *     array-shaped-but-CMS-less jadwal-praktik `doctors`, akad-venue `facilities`,
     *     jadwal-salat `times`): typed straight into the section's own content by the admin,
     *     with no model or CMS route behind them (no Doctor/prayer-time/venue model exists),
     *     so $section->content already IS the source of truth and copying it verbatim is right.
     *
     * Where a key has several variants, they were checked to map the same shape (daftar-berita
     * standar/modern/ringkas, struktur-pengurus standar/modern, program-unggulan
     * standar/modern all match); `agenda` is the one exception and is handled below.
     *
     * @param  array<string, mixed>  $content
     * @return array<string, mixed>
     */
    private function refreshBoundItems(Organization $sandbox, string $sectionKey, array $content): array
    {
        $items = match ($sectionKey) {
            'daftar-berita' => $sandbox->posts()->published()
                ->when($content['category_filter'] ?? null, fn ($q) => $q->where('category', $content['category_filter']))
                ->get()->map(fn ($post) => [
                    'title' => $post->title,
                    'image' => $post->image,
                    'category' => $post->category,
                    'date' => $post->published_at?->translatedFormat('d M Y'),
                    'excerpt' => Str::limit(strip_tags($post->body), 140),
                    'url' => null,
                ])->all(),
            'program-unggulan' => $sandbox->programs()->ofType('program')->get()->map(fn ($program) => [
                'title' => $program->title,
                'description' => $program->description,
                'icon' => $program->icon,
            ])->all(),
            'layanan' => $sandbox->programs()->ofType('layanan')->get()->map(fn ($program) => [
                'title' => $program->title,
                'description' => $program->description,
                'icon' => $program->icon,
            ])->all(),
            'struktur-pengurus' => $sandbox->officers()->get()->map(fn ($officer) => [
                'name' => $officer->name,
                'role' => $officer->role,
                'photo' => $officer->photo,
            ])->all(),
            'jaringan-aum-ortom' => $sandbox->networks()->get()->map(fn ($network) => [
                'name' => $network->name,
                'type' => $network->type,
            ])->all(),
            // Superset of what BOTH agenda variants map: `standar` reads date_year, `poster`
            // reads `poster` (agendas.poster, see the add_poster_to_agendas migration) and
            // ignores date_year - neither variant minds the extra key, so one shape here keeps
            // a template's flyers intact even if an admin switches variant after exporting.
            'agenda' => $sandbox->agendas()->published()->get()->map(fn ($agenda) => [
                'title' => $agenda->title,
                'poster' => $agenda->poster,
                'date_day' => $agenda->starts_at->format('d'),
                'date_month' => $agenda->starts_at->translatedFormat('M'),
                'date_year' => $agenda->starts_at->format('Y'),
                'location' => $agenda->location,
                'time' => $agenda->starts_at->format('H:i'),
                'url' => null,
            ])->all(),
            'pengumuman' => $sandbox->announcements()->published()->get()->map(fn ($announcement) => [
                'title' => $announcement->title,
                'priority' => $announcement->priority,
                'valid_until' => $announcement->valid_until?->translatedFormat('d M Y'),
                'url' => null,
            ])->all(),
            'galeri' => $sandbox->photos()->get()->map(fn ($photo) => [
                'image' => $photo->url,
                'caption' => $photo->caption,
            ])->all(),
            'fasilitas-masjid' => $sandbox->facilities()->get()->map(fn ($facility) => [
                'name' => $facility->name,
                'photo' => $facility->photo,
                'description' => $facility->description,
            ])->all(),
            'donasi-progress' => $sandbox->donationPrograms()->get()
                ->sortBy(fn ($program) => [
                    $program->ends_at !== null && $program->ends_at->isPast() ? 1 : 0,
                    -$program->target_amount,
                ])
                ->values()
                ->map(fn ($program) => [
                    'name' => $program->name,
                    'cover_photo' => $program->cover_photo,
                    'target_amount' => $program->target_amount,
                    'collected_amount' => $program->collectedAmount(),
                    'percent' => $program->progressPercent(),
                    'status' => $program->status(),
                    'url' => null,
                ])->all(),
            default => null,
        };

        if ($items !== null) {
            $content['items'] = $items;
        }

        return $content;
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
