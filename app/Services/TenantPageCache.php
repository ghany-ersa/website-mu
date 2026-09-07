<?php

namespace App\Services;

use App\Models\Organization;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;

/**
 * Caches the full rendered HTML of a public tenant page, keyed by organization + page slug plus
 * a per-organization version number - bumping the version (bump()) is how every cached page for
 * that organization gets invalidated at once, without needing cache tags (the 'file'/'database'
 * cache stores this app targets on shared hosting don't support Cache::tags()).
 *
 * The version itself is cached with no TTL (effectively permanent - it only changes via
 * explicit bump()), while remember() gives the rendered pages a long-but-finite TTL as a safety
 * net in case some future write path forgets to call bump() - see
 * App\Models\Concerns\InvalidatesTenantPageCache for the model event hook that calls it
 * automatically for every CMS resource that feeds a tenant page.
 */
class TenantPageCache
{
    private const PAGE_TTL_SECONDS = 21_600; // 6 hours - safety net, see class doc comment

    // How long one request is allowed to hold the render lock before it's considered stuck and
    // released anyway - render itself (a handful of queries plus Blade) should never come close
    // to this; it's a ceiling against a genuinely wedged worker, not a normal-case budget.
    private const LOCK_SECONDS = 10;

    // How long a request waits for another request's in-flight render before giving up and
    // rendering itself - see remember()'s doc comment for why this exists.
    private const LOCK_WAIT_SECONDS = 5;

    /**
     * On a cache miss, only the first request to arrive actually runs $callback (queries the
     * database, renders every section partial); every other request that misses at the same
     * moment - e.g. many visitors hitting a popular tenant page right as its 6-hour TTL lapses -
     * blocks briefly on Cache::lock() and then reads the result that request just cached,
     * instead of all of them redoing the same expensive render and hammering the database at
     * once (the "thundering herd" problem). A request that waits out the full
     * LOCK_WAIT_SECONDS without the lock becoming free renders on its own rather than failing
     * the page load - correctness (the page must render) always wins over the optimization.
     */
    public static function remember(Organization $organization, string $pageKey, \Closure $callback): string
    {
        $cacheKey = self::pageCacheKey($organization, $pageKey);

        $cached = Cache::get($cacheKey);

        if ($cached !== null) {
            return $cached;
        }

        try {
            return Cache::lock("{$cacheKey}:lock", self::LOCK_SECONDS)
                ->block(self::LOCK_WAIT_SECONDS, function () use ($cacheKey, $callback) {
                    // Re-check now that the lock is held - whoever held it before us may
                    // already have rendered and cached the page while we were waiting.
                    return Cache::get($cacheKey) ?? Cache::remember($cacheKey, self::PAGE_TTL_SECONDS, $callback);
                });
        } catch (LockTimeoutException) {
            // Every request ahead of us is still rendering after LOCK_WAIT_SECONDS (a stuck
            // worker, or just an unlucky pile-up) - render this request's own copy rather than
            // fail the page load. It won't be cached (a plain call, not remember()), so it
            // doesn't fight the lock holder for the write; the next request either finds their
            // result cached by then or repeats this same fallback.
            return $callback();
        }
    }

    /**
     * Invalidates every cached page for this organization by advancing its version number, so
     * every existing tenant-page cache key (which embeds the old version) is simply never read
     * again - the stale entries themselves are left to expire via remember()'s TTL rather than
     * being deleted, since without cache tags there's no way to enumerate which page keys exist
     * for this organization.
     */
    public static function bump(int $organizationId): void
    {
        $key = self::versionCacheKey($organizationId);

        // increment() requires the key to already exist under the 'file'/'database' cache
        // stores this app targets on shared hosting (unlike Redis, they don't auto-vivify a
        // missing key at 0) - add() seeds it first, a no-op if another request already has.
        Cache::add($key, 1);
        Cache::increment($key);
    }

    private static function pageCacheKey(Organization $organization, string $pageKey): string
    {
        return sprintf('tenant-page:%d:%s:v%d', $organization->id, $pageKey, self::version($organization->id));
    }

    private static function version(int $organizationId): int
    {
        return (int) Cache::rememberForever(self::versionCacheKey($organizationId), fn () => 1);
    }

    private static function versionCacheKey(int $organizationId): string
    {
        return "tenant-page-version:{$organizationId}";
    }
}
