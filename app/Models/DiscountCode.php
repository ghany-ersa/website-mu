<?php

namespace App\Models;

use App\Enums\DiscountCodeType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'type', 'value', 'max_uses', 'used_count', 'valid_from', 'valid_until', 'is_active'])]
class DiscountCode extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => DiscountCodeType::class,
            'value' => 'integer',
            'max_uses' => 'integer',
            'used_count' => 'integer',
            'valid_from' => 'datetime',
            'valid_until' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<PlanChangeRequest, $this>
     */
    public function planChangeRequests(): HasMany
    {
        return $this->hasMany(PlanChangeRequest::class);
    }

    /**
     * Whether this code can still be redeemed right now - active, within its validity window,
     * and under its usage cap (if any).
     */
    public function isUsable(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->valid_from && $this->valid_from->isFuture()) {
            return false;
        }

        if ($this->valid_until && $this->valid_until->isPast()) {
            return false;
        }

        if ($this->max_uses !== null && $this->used_count >= $this->max_uses) {
            return false;
        }

        return true;
    }

    /**
     * Atomically takes one use of this code, returning false if the cap was already reached.
     *
     * A single conditional UPDATE instead of isUsable() followed by increment(): between that
     * check and that write, concurrent checkouts could each still see a free slot and all
     * redeem it - so a code capped at N uses (a 100% one especially, since a fully discounted
     * request is approved without payment) could be redeemed more than N times.
     */
    public function claimUse(): bool
    {
        return static::whereKey($this->getKey())
            ->where(fn ($query) => $query->whereNull('max_uses')->orWhereColumn('used_count', '<', 'max_uses'))
            ->increment('used_count') === 1;
    }

    /**
     * Gives back a use claimed by a request that never got paid for (cancelled, rejected,
     * expired) - otherwise an owner could exhaust a limited code for everyone else just by
     * creating and cancelling requests.
     */
    public function releaseUse(): void
    {
        static::whereKey($this->getKey())->where('used_count', '>', 0)->decrement('used_count');
    }

    /**
     * Rupiah amount this code knocks off a given price - clamped so a fixed-amount code
     * never discounts below zero.
     */
    public function amountFor(int $price): int
    {
        return $this->type === DiscountCodeType::Percent
            ? (int) round($price * $this->value / 100)
            : min($this->value, $price);
    }
}
