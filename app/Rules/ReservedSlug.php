<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Rejects subdomains that must stay reserved for platform use and can't be claimed
 * by an organization (e.g. "admin.website-mu.test" would shadow our own panel).
 * Shared by StoreOrganizationRequest (initial slug) and OrganizationEditController
 * (slug changes after creation) so the reserved list can't drift between the two.
 */
class ReservedSlug implements ValidationRule
{
    /**
     * @var array<int, string>
     */
    public const RESERVED = [
        'www', 'admin', 'api', 'app', 'mail', 'ftp', 'localhost',
        'staging', 'dashboard', 'assets', 'static', 'cdn', 'support',
        'help', 'blog', 'docs', 'status', 'billing', 'auth', 'login',
        'register', 'website-mu',
    ];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (in_array($value, self::RESERVED, true)) {
            $fail('Slug ini merupakan kata yang dicadangkan sistem, silakan gunakan slug lain.');
        }
    }
}
