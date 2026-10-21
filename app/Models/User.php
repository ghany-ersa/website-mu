<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'whatsapp', 'password', 'onboarding_tours_seen'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Validation regex for an already-normalized number (see normalizeWhatsapp()): an Indonesian
     * mobile number, 62 + 8 + 7-11 more digits.
     */
    public const WHATSAPP_PATTERN = '/^628\d{7,11}$/';

    /**
     * Normalizes whatever the user typed (08xx, +62 8xx, 62-8xx-xxxx, ...) to the bare 62xxx form
     * wa.me links expect, so the stored value is always directly usable as
     * https://wa.me/{whatsapp} - run *before* validating against WHATSAPP_PATTERN, so the
     * rule only has one shape to check instead of every way people format a phone number.
     */
    public static function normalizeWhatsapp(?string $number): ?string
    {
        $digits = preg_replace('/\D/', '', (string) $number);

        if ($digits === '') {
            return null;
        }

        return match (true) {
            str_starts_with($digits, '0') => '62'.substr($digits, 1),
            str_starts_with($digits, '8') => '62'.$digits,
            default => $digits,
        };
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'onboarding_tours_seen' => 'array',
        ];
    }

    public function hasSeenOnboardingTour(string $tour): bool
    {
        return in_array($tour, $this->onboarding_tours_seen ?? [], strict: true);
    }

    public function markOnboardingTourSeen(string $tour): void
    {
        $seen = $this->onboarding_tours_seen ?? [];

        if (in_array($tour, $seen, strict: true)) {
            return;
        }

        $seen[] = $tour;

        $this->forceFill(['onboarding_tours_seen' => $seen])->save();
    }

    /**
     * @return BelongsToMany<Organization, $this>
     */
    public function organizations(): BelongsToMany
    {
        return $this->belongsToMany(Organization::class)
            ->withPivot('role')
            ->withTimestamps();
    }
}
