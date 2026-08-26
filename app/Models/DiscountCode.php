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
     * Whether this code can still be redeemed right now — active, within its validity window,
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
     * Rupiah amount this code knocks off a given price — clamped so a fixed-amount code
     * never discounts below zero.
     */
    public function amountFor(int $price): int
    {
        return $this->type === DiscountCodeType::Percent
            ? (int) round($price * $this->value / 100)
            : min($this->value, $price);
    }
}
