<?php

namespace App\Models;

use App\Models\Concerns\InvalidatesTenantPageCache;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A domain the organization already owns (e.g. pcmambulu.or.id), served as its public site
 * instead of {slug}.{tenancy.domain}. Admin-managed only: the hosting has a limited number of
 * domain slots and each one is attached by hand (cPanel alias + AutoSSL), so there is no
 * tenant self-service flow and no DNS ownership challenge - see Admin\OrganizationDomainController.
 *
 * Invalidates the tenant page cache on every change because activation switches which host
 * TenantUrl::host() returns, and cached HTML embeds absolute links to the old host.
 */
#[Fillable(['hostname', 'is_active', 'last_check_ok', 'last_check_result', 'last_checked_at', 'activated_by', 'activated_at', 'notes'])]
class OrganizationDomain extends Model
{
    use InvalidatesTenantPageCache;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'last_check_ok' => 'boolean',
            'last_checked_at' => 'datetime',
            'activated_at' => 'datetime',
        ];
    }

    /**
     * Reduces whatever an admin pastes ("https://WWW.PcmAmbulu.or.id/beranda", "pcmambulu.or.id.")
     * to a bare hostname, so the unique index and host lookups compare like with like.
     * Internationalized names are stored in punycode, which is what arrives in the Host header.
     */
    public static function normalize(string $input): string
    {
        $host = strtolower(trim($input));
        $host = preg_replace('#^[a-z][a-z0-9+.-]*://#', '', $host);
        $host = preg_split('#[/?\#]#', $host)[0];
        $host = preg_replace('#:\d+$#', '', $host);
        $host = rtrim($host, '.');

        if ($host !== '' && function_exists('idn_to_ascii')) {
            $host = idn_to_ascii($host, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46) ?: $host;
        }

        return $host;
    }

    /**
     * The same hostname with "www." added or removed. Both variants reach the site (one
     * redirects to the other - see ResolveTenantOrganization), so both have to be treated as
     * taken when checking uniqueness.
     */
    public static function wwwCounterpart(string $hostname): string
    {
        return str_starts_with($hostname, 'www.') ? substr($hostname, 4) : 'www.'.$hostname;
    }

    /**
     * @return BelongsTo<Organization, $this>
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function activatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'activated_by');
    }
}
