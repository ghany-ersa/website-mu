<?php

namespace App\Models;

use Database\Factories\DonationProgramFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['organization_id', 'name', 'slug', 'description', 'target_amount', 'cover_photo', 'starts_at', 'ends_at'])]
class DonationProgram extends Model
{
    /** @use HasFactory<DonationProgramFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'starts_at' => 'date',
            'ends_at' => 'date',
        ];
    }

    /**
     * @return BelongsTo<Organization, $this>
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * @return HasMany<DonationTransaction, $this>
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(DonationTransaction::class);
    }

    public function collectedAmount(): int
    {
        return (int) $this->transactions()->sum('amount');
    }

    /**
     * Progress to one decimal place, matching how the source site reports it ("37.2%") - a
     * whole-number percentage hides meaningful movement on a target this large, where a
     * month of donations can shift the figure by well under one point.
     */
    public function progressPercent(): float
    {
        if ($this->target_amount <= 0) {
            return 0.0;
        }

        return min(100, round($this->collectedAmount() / $this->target_amount * 100, 1));
    }

    /**
     * Lifecycle derived from the program's own date window, not a stored column: 'upcoming'
     * before it opens, 'expired' once it closes, 'completed' when the target is met, else
     * 'active'. Only an 'active' program shows a donate button (see the public detail page) -
     * soliciting for one that has closed or hasn't opened would be misleading.
     */
    public function status(): string
    {
        if ($this->starts_at !== null && $this->starts_at->isFuture()) {
            return 'upcoming';
        }

        if ($this->progressPercent() >= 100) {
            return 'completed';
        }

        if ($this->ends_at !== null && $this->ends_at->isPast()) {
            return 'expired';
        }

        return 'active';
    }
}
