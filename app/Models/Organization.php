<?php

namespace App\Models;

use App\Enums\OrganizationRole;
use App\Enums\OrganizationStatus;
use App\Services\CmsSampleDataSeeder;
use Database\Factories\OrganizationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'organization_type_id',
    'template_id',
    'name',
    'slug',
    'region',
    'description',
    'status',
    'published_at',
    'primary_color',
    'secondary_color',
    'logo',
    'font_family',
    'border_radius',
    'phone',
    'email',
    'whatsapp',
    'address',
    'instagram_url',
    'facebook_url',
])]
class Organization extends Model
{
    /** @use HasFactory<OrganizationFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => OrganizationStatus::class,
            'published_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    public function roleFor(User $user): ?OrganizationRole
    {
        $role = $this->members->firstWhere('id', $user->id)?->pivot->role;

        return $role ? OrganizationRole::from($role) : null;
    }

    /**
     * Effective primary brand color: the organization's own override if set, else its
     * template's brand color, else the platform default (see templates/preview.blade.php,
     * which applies the same fallback chain for template-only previews).
     */
    public function primaryColor(): string
    {
        return $this->primary_color
            ?? $this->template?->structure['brand']['primary'] ?? null
            ?? '#2C368B';
    }

    /**
     * Effective secondary brand color — see primaryColor() for the fallback chain.
     */
    public function secondaryColor(): string
    {
        return $this->secondary_color
            ?? $this->template?->structure['brand']['secondary'] ?? null
            ?? '#079C4E';
    }

    /**
     * Effective font family key (see config('branding.fonts')) — same 3-tier fallback
     * chain as primaryColor(): own override, then template default, then platform default.
     */
    public function fontFamily(): string
    {
        return $this->font_family
            ?? $this->template?->structure['brand']['font'] ?? null
            ?? 'Plus Jakarta Sans';
    }

    /**
     * Effective border radius token (see config('branding.radii')) — same fallback chain
     * as primaryColor()/fontFamily().
     */
    public function borderRadius(): string
    {
        return $this->border_radius
            ?? $this->template?->structure['brand']['radius'] ?? null
            ?? 'soft';
    }

    /**
     * @return BelongsTo<OrganizationType, $this>
     */
    public function organizationType(): BelongsTo
    {
        return $this->belongsTo(OrganizationType::class);
    }

    /**
     * @return BelongsTo<Template, $this>
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }

    /**
     * @return HasMany<OrganizationPage, $this>
     */
    public function pages(): HasMany
    {
        return $this->hasMany(OrganizationPage::class)->orderBy('order');
    }

    /**
     * @return HasMany<Media, $this>
     */
    public function media(): HasMany
    {
        return $this->hasMany(Media::class)->latest();
    }

    /**
     * @return HasMany<Post, $this>
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class)->latest('published_at');
    }

    /**
     * @return HasMany<Agenda, $this>
     */
    public function agendas(): HasMany
    {
        return $this->hasMany(Agenda::class)->orderBy('starts_at');
    }

    /**
     * @return HasMany<Announcement, $this>
     */
    public function announcements(): HasMany
    {
        return $this->hasMany(Announcement::class)->latest();
    }

    /**
     * @return HasMany<Officer, $this>
     */
    public function officers(): HasMany
    {
        return $this->hasMany(Officer::class)->orderBy('order');
    }

    /**
     * @return HasMany<Program, $this>
     */
    public function programs(): HasMany
    {
        return $this->hasMany(Program::class)->orderBy('order');
    }

    /**
     * @return HasMany<OrganizationNetwork, $this>
     */
    public function networks(): HasMany
    {
        return $this->hasMany(OrganizationNetwork::class)->orderBy('order');
    }

    /**
     * Named to match the `{photo}` route parameter used by the `organizations.gallery.*`
     * resource (scopeBindings() resolves nested bindings by relation name matching the
     * route parameter name, not the URL segment — see routes/web.php).
     *
     * @return HasMany<GalleryPhoto, $this>
     */
    public function photos(): HasMany
    {
        return $this->hasMany(GalleryPhoto::class)->orderBy('order');
    }

    /**
     * Onboarding checklist for the dashboard: key => whether that setup task is done.
     * Publishing the site isn't included as "just open the builder" — a page/sections
     * always exist automatically (see ensureHomePageExists()), so that alone isn't a
     * meaningful signal that the user has actually done anything. Brand is "done" once
     * a logo is set, since logo (unlike the colors) is never auto-filled from a template.
     * Contact is "done" once any of phone/email/whatsapp is set — there's no single
     * required channel, any one of them is a meaningful signal the org filled this in.
     * Content is "done" once at least one page has at least one section — this differs
     * from "a page exists" (always true, not meaningful) because it only becomes true once
     * the org has actually saved something in the builder (adding, editing, or keeping a
     * cloned template section counts as saved intent). Officers isn't a checklist item:
     * it's managed from within the builder (struktur-pengurus section's "Kelola Pengurus →"
     * link) rather than a separate setup step.
     *
     * @return array<string, bool>
     */
    public function onboardingChecklist(): array
    {
        return [
            'brand' => filled($this->logo),
            'contact' => filled($this->phone) || filled($this->email) || filled($this->whatsapp),
            'content' => $this->pages()->whereHas('sections')->exists(),
            'published' => $this->status === OrganizationStatus::Published,
        ];
    }

    /**
     * Flip publish status. Stamps published_at on first publish only — unpublishing or
     * re-publishing never touches it, so it stays a "first went live at" timestamp rather
     * than a "currently published since" one.
     */
    public function publish(bool $published = true): void
    {
        $this->status = $published ? OrganizationStatus::Published : OrganizationStatus::Draft;

        if ($published && $this->published_at === null) {
            $this->published_at = now();
        }

        $this->save();
    }

    /**
     * Ensure this organization owns at least a home page, if it doesn't already have any.
     * Safe to call repeatedly. Clones the template's structure when one is set; otherwise
     * creates a single blank "Beranda" page — the builder only supports one page for now
     * (see prd.md §24.4), so there's no user-facing "create page" flow to fall back to.
     */
    public function ensureHomePageExists(): void
    {
        if ($this->pages()->exists()) {
            return;
        }

        if ($this->template) {
            $this->seedPagesFromTemplate();

            return;
        }

        $page = $this->pages()->create([
            'name' => 'Beranda',
            'slug' => 'beranda',
            'order' => 0,
            'is_home' => true,
        ]);

        $page->ensureFooter();
    }

    /**
     * Clone only the template's first (home) page into an owned page/sections — the
     * builder only supports one page for now, so the template's other pages (if any)
     * aren't cloned yet. Also seeds sample CMS records (see CmsSampleDataSeeder) for
     * whichever CMS-backed sections (galeri, daftar-berita, struktur-pengurus, etc.) the
     * cloned page has, so the builder and the org's own draft/public page show real, editable
     * content immediately instead of an empty list the user has to populate from scratch.
     */
    private function seedPagesFromTemplate(): void
    {
        $pageData = ($this->template->structure['pages'] ?? [])[0] ?? null;

        if (! $pageData) {
            return;
        }

        $page = $this->pages()->create([
            'name' => $pageData['name'] ?? $pageData['slug'],
            'slug' => $pageData['slug'],
            'order' => 0,
            'is_home' => true,
        ]);

        $sectionKeys = [];

        foreach ($pageData['sections'] ?? [] as $sectionOrder => $sectionData) {
            $page->sections()->create([
                'key' => $sectionData['key'],
                'variant' => $sectionData['variant'] ?? null,
                'content' => $sectionData['content'] ?? [],
                'order' => $sectionOrder,
            ]);

            $sectionKeys[] = $sectionData['key'];
        }

        $page->ensureFooter();

        CmsSampleDataSeeder::seed($this, $sectionKeys);
    }
}
