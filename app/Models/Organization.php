<?php

namespace App\Models;

use App\Enums\OrganizationRole;
use App\Enums\OrganizationStatus;
use App\Enums\PlanChangeRequestStatus;
use App\Services\CmsSampleDataSeeder;
use App\Services\PlanLimitService;
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
    'plan_id',
    'plan_expires_at',
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
            'plan_expires_at' => 'datetime',
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
     * @return BelongsTo<Plan, $this>
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * @return HasMany<OrganizationLimitOverride, $this>
     */
    public function limitOverrides(): HasMany
    {
        return $this->hasMany(OrganizationLimitOverride::class);
    }

    /**
     * @return HasMany<PlanChangeRequest, $this>
     */
    public function planChangeRequests(): HasMany
    {
        return $this->hasMany(PlanChangeRequest::class);
    }

    /**
     * The most recent plan change still awaiting payment/admin action, if any — covers both
     * Pending (just submitted) and PaymentConfirmed (org says they've paid) since neither is
     * final yet. A new request submitted via OrganizationPlanController::store() is blocked
     * while one of these exists, so there's never more than one in flight at a time.
     */
    public function pendingPlanChangeRequest(): ?PlanChangeRequest
    {
        return $this->planChangeRequests
            ->whereIn('status', [PlanChangeRequestStatus::Pending, PlanChangeRequestStatus::PaymentConfirmed])
            ->first();
    }

    /**
     * Whether the current plan's paid-for period has lapsed. Informational only — per product
     * decision, an expired plan keeps its existing limits (see PlanLimitService) rather than
     * falling back to a free tier; this only drives the public site's renewal-reminder badge
     * (see organizations/pages/_document.blade.php).
     *
     * A null plan_expires_at is NOT treated as expired here — a brand-new organization has
     * never had a plan_expires_at set at all, which is a different situation ("never paid",
     * see hasPaidForCurrentPlan()) from "was paid, that period is now over". Both drive
     * violation badges, just with a different message.
     */
    public function planIsExpired(): bool
    {
        return $this->plan_expires_at !== null && $this->plan_expires_at->isPast();
    }

    /**
     * Whether there's a currently-valid payment behind this organization's plan_id — i.e. an
     * Approved PlanChangeRequest for the plan this organization is on, whose paid-for period
     * (plan_expires_at) hasn't lapsed yet.
     *
     * Organizations are created with plan_id already set (see OrganizationController::store())
     * so they can use the CMS/builder immediately, but that's not the same as having paid:
     * plan_expires_at stays null until a PlanChangeRequest for it is actually approved. This
     * is what plan_expires_at === null really means — "never paid" — as opposed to
     * planIsExpired()'s "was paid, that period lapsed."
     */
    public function hasPaidForCurrentPlan(): bool
    {
        return $this->plan_expires_at !== null && $this->plan_expires_at->isFuture();
    }

    /**
     * The Approved PlanChangeRequest currently backing this organization's plan_id/
     * plan_expires_at, if hasPaidForCurrentPlan() is true — i.e. the payment that's actually
     * in force right now, not just the most recent one on file (a later request for a
     * different plan could exist as Pending/Rejected without having taken effect).
     *
     * PlanLimitService reads this request's limits_snapshot (frozen at approval time) instead
     * of the plan's live limits, so an org that already paid isn't affected if an admin edits
     * the plan's limits afterward — see PlanChangeRequestService::approve().
     */
    public function currentApprovedPlanChangeRequest(): ?PlanChangeRequest
    {
        if (! $this->hasPaidForCurrentPlan()) {
            return null;
        }

        return $this->planChangeRequests
            ->where('status', PlanChangeRequestStatus::Approved)
            ->where('requested_plan_id', $this->plan_id)
            ->sortByDesc('reviewed_at')
            ->first();
    }

    /**
     * Human-readable list of ways this organization currently breaks its own plan's rules —
     * derived fresh on every call rather than stored, since content counts and plan_expires_at
     * change independently of each other. Three kinds of violation:
     *
     *  - CMS content over the plan's limit (e.g. 25 posts on a 20-post plan) — happens after a
     *    downgrade, since PlanLimitService::canCreate() only blocks *new* creation and never
     *    deletes existing over-limit content.
     *  - Total page-builder sections over 'sections_total' — same downgrade scenario, for
     *    components instead of CMS records.
     *  - The paid period has lapsed (see planIsExpired()), or has never started at all (see
     *    hasPaidForCurrentPlan()) — e.g. a newly created organization, which gets plan_id set
     *    immediately (see OrganizationController::store()) but no plan_expires_at until an
     *    admin approves its first PlanChangeRequest.
     *
     * Used by OrganizationController::publish() to block publishing while any of these hold,
     * and by the public tenant page (_document.blade.php) to show a violation badge on an
     * already-published site instead of silently taking it offline.
     *
     * @return array<int, string>
     */
    public function planViolations(): array
    {
        // key => [relation method, label]
        $resources = [
            'posts' => ['posts', 'Berita'],
            'agendas' => ['agendas', 'Agenda'],
            'announcements' => ['announcements', 'Pengumuman'],
            'officers' => ['officers', 'Data Pengurus'],
            'programs' => ['programs', 'Program/Layanan'],
            'gallery_photos' => ['photos', 'Foto Galeri'],
        ];

        // effectiveLimit() (not Plan::limitFor() directly) so a paid-for limits_snapshot is
        // honored here too — otherwise an org that's protected from a plan's limits being
        // lowered post-payment (see PlanLimitService::effectiveLimit()) would still get
        // flagged as violating rules it never agreed to.
        $service = app(PlanLimitService::class);
        $violations = [];

        foreach ($resources as $key => [$relation, $label]) {
            $limit = $service->effectiveLimit($this, $key);

            if ($limit === null) {
                continue;
            }

            $over = $this->{$relation}()->count() - $limit;

            if ($over > 0) {
                $violations[] = "{$label} melebihi batas paket ({$over} kelebihan)";
            }
        }

        $sectionsLimit = $service->effectiveLimit($this, 'sections_total');

        if ($sectionsLimit !== null) {
            $sectionsOver = $service->countedSectionsTotal($this) - $sectionsLimit;

            if ($sectionsOver > 0) {
                $violations[] = "Jumlah komponen situs melebihi batas paket ({$sectionsOver} kelebihan)";
            }
        }

        if ($this->planIsExpired()) {
            $violations[] = 'Masa aktif paket langganan telah berakhir';
        } elseif (! $this->hasPaidForCurrentPlan()) {
            $violations[] = 'Pembayaran paket langganan belum dikonfirmasi';
        }

        return $violations;
    }

    /**
     * Shortcut for planViolations() !== [] — use this for a plain yes/no gate (e.g. disabling
     * the publish button); use planViolations() when the specific reasons need to be shown.
     */
    public function violatesPlanRules(): bool
    {
        return $this->planViolations() !== [];
    }

    /**
     * Whether this organization's plan grants access to templates marked
     * Template::is_exclusive — used to gate the "Ganti Template" picker
     * (see OrganizationTemplateController) so a Starter/Organization-plan org can't
     * switch onto a Professional-only design.
     *
     * Reloads the plan relation (plan()->first(), not the cached $this->plan) for the same
     * reason PlanLimitService::effectivePlan() does: belongsTo's cache isn't invalidated by
     * updating this model's own plan_id, so a just-upgraded instance would otherwise still
     * report the previous plan's entitlement.
     */
    public function canUseExclusiveTemplates(): bool
    {
        return (bool) $this->plan()->first()?->has_exclusive_templates;
    }

    /**
     * @return HasMany<OrganizationPage, $this>
     */
    public function pages(): HasMany
    {
        return $this->hasMany(OrganizationPage::class)->orderBy('order');
    }

    /**
     * Whether any of this organization's builder pages contain a section with the given key
     * (e.g. 'galeri', 'daftar-berita') — used to gate both the CMS sidebar/dashboard links and
     * the underlying CRUD routes (organizations.gallery.*, organizations.posts.*, etc.) for
     * content types the organization's template never gave it a section to display.
     */
    public function hasSection(string $key): bool
    {
        return $this->pages->flatMap->sections->pluck('key')->contains($key);
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
     *
     * The cloned sections are capped to the org's plan's 'sections_total' limit (excluding
     * locked keys, same as PlanLimitService::countedSectionsTotal() — locked sections like
     * header/footer were never optional and shouldn't eat into the quota). Every organization
     * is created on the Starter plan (see OrganizationController::store()), whose
     * sections_total is tighter than what any current template actually contains, so cloning
     * every section unconditionally used to leave a brand-new organization already over its
     * own plan's limit — see Organization::planViolations() — before the owner had touched
     * anything. Trailing (non-locked) sections are dropped first so the template's opening
     * sections (hero, etc. — the first impression) survive the cut.
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

        $lockedKeys = collect(config('page-builder.sections'))
            ->filter(fn (array $section) => $section['locked'] ?? false)
            ->keys();

        $sections = collect($pageData['sections'] ?? []);
        $limit = app(PlanLimitService::class)->effectiveLimit($this, 'sections_total');

        if ($limit !== null) {
            $unlockedCount = $sections->filter(fn (array $s) => ! $lockedKeys->contains($s['key']))->count();
            $overBy = max(0, $unlockedCount - $limit);

            if ($overBy > 0) {
                $dropped = 0;

                $sections = $sections->reverse()->reject(function (array $section) use ($lockedKeys, $overBy, &$dropped) {
                    if ($dropped >= $overBy || $lockedKeys->contains($section['key'])) {
                        return false;
                    }

                    $dropped++;

                    return true;
                })->reverse()->values();
            }
        }

        $sectionKeys = [];

        foreach ($sections as $sectionOrder => $sectionData) {
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
