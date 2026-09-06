<?php

namespace App\Services;

use App\Models\Organization;
use App\Models\OrganizationSection;
use App\Models\Plan;

/**
 * Central authority for plan-based limits: how many of a given CMS resource an
 * organization may create. Resolution order is always tenant override -> plan -> default,
 * so a negotiated per-tenant exception (OrganizationLimitOverride) always wins over the
 * plan's standard rules.
 *
 * An organization without a plan_id (legacy data predating this feature) is treated as
 * the 'organization' plan rather than failing closed - see the mandatory backfill note
 * in PlanSeeder.
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
        'facilities' => 'facilities',
        'donation_programs' => 'donationPrograms',
        'pages_total' => 'pages',
    ];

    /**
     * Per-request memo of the 'organization' fallback plan used by
     * effectiveLimitForFreshOrganization() when an organization has no plan_id - this plan is
     * the same row for every organization, so re-querying it once per resource key per
     * organization (up to 9 times for one page render) is pure waste. false means "looked up,
     * genuinely doesn't exist" (e.g. PlanSeeder hasn't run) so the negative result gets cached
     * too, as distinct from null which means "not looked up yet".
     */
    private static Plan|false|null $fallbackPlanMemo = null;

    /**
     * Clears the per-process fallback-plan memo - see $fallbackPlanMemo's doc comment. Called
     * from Tests\TestCase::setUp() so a RefreshDatabase rollback between tests can't leave a
     * stale memoized Plan in effect for the next one.
     */
    public static function resetFallbackPlanMemo(): void
    {
        self::$fallbackPlanMemo = null;
    }

    /**
     * Whether the organization may create one more record for the given resource key
     * (e.g. 'posts', 'sections_total'). Existing records over a lowered limit are never
     * counted against the caller - only new creation is blocked.
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
     * The limit actually in force for this key. Resolution order:
     *
     *  1. Tenant override (OrganizationLimitOverride) - a negotiated per-tenant exception
     *     always wins over anything plan-derived.
     *  2. The paid-for plan's frozen limits_snapshot (see
     *     Organization::currentApprovedPlanChangeRequest() and
     *     PlanChangeRequestService::approve()) - so an org that already paid keeps what it
     *     paid for even if an admin edits the plan's limits afterward. An org whose payment
     *     predates this snapshot feature has a null snapshot and falls through to (3).
     *  3. The plan's current live limits (Plan::limitFor()) - used for orgs that haven't
     *     paid yet (limits_snapshot doesn't exist until an admin approves a payment) and as
     *     the fallback for pre-snapshot approvals.
     *
     * Null means unlimited. Exposed publicly (not just via canCreate()/remaining()) so
     * callers that need to compare it against currentCount() directly - e.g. rendering "3
     * over the limit of 5" on the subscription page - don't have to re-derive it from a
     * clamped remaining() value.
     */
    public function effectiveLimit(Organization $organization, string $key): ?int
    {
        $override = $organization->limitOverrides->firstWhere('key', $key);

        if ($override) {
            return $override->max_count;
        }

        $snapshot = $organization->currentApprovedPlanChangeRequest()?->limits_snapshot;

        if ($snapshot !== null && array_key_exists($key, $snapshot)) {
            return $snapshot[$key];
        }

        return $this->effectivePlan($organization)?->limitFor($key);
    }

    /**
     * Same resolution as effectiveLimit(), but trusts the organization's already-loaded `plan`
     * relation instead of re-querying it via effectivePlan() - safe ONLY where the caller
     * guarantees plan_id hasn't been mutated on this instance earlier in the same request (see
     * effectivePlan()'s doc comment for why that reload normally matters). The public tenant
     * site (OrganizationSiteController) is such a caller: it always re-fetches the organization
     * fresh from the database, so there's nothing for the relation cache to go stale against.
     * Falls back to effectiveLimit() whenever `plan` isn't loaded, so passing an organization
     * that wasn't eager-loaded with 'plan.limits' still behaves correctly (just without the
     * query savings).
     */
    public function effectiveLimitForFreshOrganization(Organization $organization, string $key): ?int
    {
        if (! $organization->relationLoaded('plan')) {
            return $this->effectiveLimit($organization, $key);
        }

        $override = $organization->limitOverrides->firstWhere('key', $key);

        if ($override) {
            return $override->max_count;
        }

        $snapshot = $organization->currentApprovedPlanChangeRequest()?->limits_snapshot;

        if ($snapshot !== null && array_key_exists($key, $snapshot)) {
            return $snapshot[$key];
        }

        $plan = $organization->plan ?? $this->memoizedFallbackPlan();

        return $plan?->limitFor($key);
    }

    /**
     * See $fallbackPlanMemo's doc comment - avoids re-querying the same global fallback plan
     * once per resource key for every plan-less organization rendered in this request/test
     * process. Static (not an instance property) because PlanLimitService itself carries no
     * per-call state and is typically resolved fresh via app(PlanLimitService::class) rather
     * than kept as a singleton the caller holds onto across organizations.
     */
    private function memoizedFallbackPlan(): ?Plan
    {
        if (self::$fallbackPlanMemo === null) {
            self::$fallbackPlanMemo = Plan::where('key', 'organization')->first() ?? false;
        }

        return self::$fallbackPlanMemo ?: null;
    }

    /**
     * Actual count of records the organization has for this resource key - unclamped, so
     * unlike remaining() (which floors at 0) this can be compared against effectiveLimit()
     * to tell "at the limit" apart from "over the limit by N".
     */
    public function currentCount(Organization $organization, string $key): int
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
     * cache isn't invalidated by updating the owning model's own foreign key, so a caller that
     * receives a just-saved instance would otherwise see the plan that was current before this
     * exact update.
     *
     * Returns null (rather than throwing) when even the 'organization' fallback plan doesn't
     * exist - e.g. PlanSeeder hasn't run yet. Callers treat a null plan as "no limit
     * enforced", which fails open rather than 404ing every CMS/builder request in an
     * environment where plans simply haven't been seeded.
     */
    private function effectivePlan(Organization $organization): ?Plan
    {
        return $organization->plan()->first() ?? Plan::where('key', 'organization')->first();
    }
}
