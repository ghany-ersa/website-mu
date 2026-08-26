<?php

namespace App\Services;

use App\Models\Organization;
use App\Models\OrganizationSection;
use App\Models\Plan;

/**
 * Central authority for plan-based limits: how many of a given CMS resource an
 * organization may create, and which page-builder section keys it may use. Resolution
 * order for both is always tenant override -> plan -> default, so negotiated per-tenant
 * exceptions (OrganizationLimitOverride/OrganizationComponentOverride) always win over the
 * plan's standard rules.
 *
 * An organization without a plan_id (legacy data predating this feature) is treated as
 * the 'organization' plan rather than failing closed — see reevaluateSections() and the
 * mandatory backfill note in PlanSeeder.
 */
class PlanLimitService
{
    /**
     * Maps a plan_limits/override key to the Organization relation that counts it.
     * Adding a new CMS resource only needs a row here plus seeded plan_limits data.
     *
     * @var array<string, string>
     */
    private const RESOURCE_RELATIONS = [
        'posts' => 'posts',
        'agendas' => 'agendas',
        'announcements' => 'announcements',
        'officers' => 'officers',
        'programs' => 'programs',
        'gallery_photos' => 'photos',
    ];

    /**
     * Whether the organization may create one more record for the given resource key
     * (e.g. 'posts', 'sections_total'). Existing records over a lowered limit are never
     * counted against the caller — only new creation is blocked.
     */
    public function canCreate(Organization $organization, string $key): bool
    {
        if (! $this->effectivePlan($organization)) {
            return true;
        }

        $limit = $this->effectiveLimit($organization, $key);

        if ($limit === null) {
            return true;
        }

        return $this->currentCount($organization, $key) < $limit;
    }

    /**
     * Remaining quota for the resource key, or null if unlimited.
     */
    public function remaining(Organization $organization, string $key): ?int
    {
        $limit = $this->effectiveLimit($organization, $key);

        if ($limit === null) {
            return null;
        }

        return max(0, $limit - $this->currentCount($organization, $key));
    }

    /**
     * Whether the organization may use/add a section with the given key. Locked sections
     * (header/footer) are always allowed — they're never plan-gated.
     */
    public function canUseSection(Organization $organization, string $sectionKey): bool
    {
        if (config("page-builder.sections.{$sectionKey}.locked", false)) {
            return true;
        }

        $override = $organization->componentOverrides->firstWhere('component_key', $sectionKey);

        if ($override) {
            return $override->is_allowed;
        }

        return $this->effectivePlan($organization)?->allowsComponent($sectionKey) ?? true;
    }

    /**
     * Total non-locked sections across every page the organization owns (counted site-wide,
     * not per page, per the 'sections_total' limit key).
     */
    public function countedSectionsTotal(Organization $organization): int
    {
        $lockedKeys = collect(config('page-builder.sections'))
            ->filter(fn (array $section) => $section['locked'] ?? false)
            ->keys();

        return $organization->pages->flatMap->sections
            ->reject(fn (OrganizationSection $section) => $lockedKeys->contains($section->key))
            ->count();
    }

    /**
     * Re-syncs hidden_by_plan on every non-locked section the organization owns against its
     * current effective plan. Call after organizations.plan_id changes. Idempotent: only
     * flips hidden_by_plan itself, never touches is_visible, so a section the user manually
     * hid stays hidden across upgrades/downgrades.
     */
    public function reevaluateSections(Organization $organization): void
    {
        $organization->load('pages.sections');

        $organization->pages->flatMap->sections->each(function (OrganizationSection $section) use ($organization) {
            if (config("page-builder.sections.{$section->key}.locked", false)) {
                return;
            }

            $allowed = $this->canUseSection($organization, $section->key);

            if (! $allowed && ! $section->hidden_by_plan) {
                $section->update(['hidden_by_plan' => true]);
            } elseif ($allowed && $section->hidden_by_plan) {
                $section->update(['hidden_by_plan' => false]);
            }
        });
    }

    private function effectiveLimit(Organization $organization, string $key): ?int
    {
        $override = $organization->limitOverrides->firstWhere('key', $key);

        if ($override) {
            return $override->max_count;
        }

        return $this->effectivePlan($organization)?->limitFor($key);
    }

    private function currentCount(Organization $organization, string $key): int
    {
        if ($key === 'sections_total') {
            return $this->countedSectionsTotal($organization);
        }

        $relation = self::RESOURCE_RELATIONS[$key]
            ?? throw new \InvalidArgumentException("Unknown plan limit key: {$key}");

        return $organization->{$relation}()->count();
    }

    /**
     * Reloads the plan relation rather than trusting $organization->plan: Eloquent's belongsTo
     * cache isn't invalidated by updating the owning model's own foreign key, so a caller (e.g.
     * OrganizationObserver::updated(), which receives the just-saved instance) would otherwise
     * see the plan that was current before this exact update.
     *
     * Returns null (rather than throwing) when even the 'organization' fallback plan doesn't
     * exist — e.g. PlanSeeder hasn't run yet. Callers treat a null plan as "no limit
     * enforced", which fails open rather than 404ing every CMS/builder request in an
     * environment where plans simply haven't been seeded.
     */
    private function effectivePlan(Organization $organization): ?Plan
    {
        return $organization->plan()->first() ?? Plan::where('key', 'organization')->first();
    }
}
