<?php

namespace App\Models\Concerns;

use App\Services\TenantPageCache;

/**
 * Bumps the owning organization's tenant-page cache (see App\Services\TenantPageCache) whenever
 * a model that feeds a public tenant page is created, updated, or deleted - so every CMS
 * controller (posts, announcements, agendas, sections, ...) invalidates the right cache
 * automatically, instead of needing a Cache::forget() call added by hand at every one of their
 * store()/update()/destroy() methods, which is exactly the kind of call a future controller
 * would be one missed line away from forgetting.
 *
 * A model using this trait must implement tenantOrganizationId(): the organization_id to bump,
 * resolved however that model relates to Organization (a direct column, or through a parent
 * relation - see OrganizationSection's implementation for the latter).
 */
trait InvalidatesTenantPageCache
{
    public static function bootInvalidatesTenantPageCache(): void
    {
        static::saved(function ($model): void {
            $model->bumpTenantPageCache();
        });

        static::deleted(function ($model): void {
            $model->bumpTenantPageCache();
        });
    }

    /**
     * Default resolution for the common case: a direct `organization_id` column. Models that
     * relate to Organization indirectly (e.g. OrganizationSection, via its parent page) should
     * override this instead of relying on the column.
     */
    public function tenantOrganizationId(): ?int
    {
        return $this->organization_id;
    }

    private function bumpTenantPageCache(): void
    {
        $organizationId = $this->tenantOrganizationId();

        if ($organizationId !== null) {
            TenantPageCache::bump($organizationId);
        }
    }
}
