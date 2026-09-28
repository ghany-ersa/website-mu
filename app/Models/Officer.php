<?php

namespace App\Models;

use App\Models\Concerns\InvalidatesTenantPageCache;
use Database\Factories\OfficerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A person with a titled role at the organization. Backs both a branch's board of officers
 * (the `struktur-pengurus` section's `standar` variant) and a school's teaching staff (its
 * `sd-madya` variant) - subject/bio/email/phone are only filled in by the latter.
 */
#[Fillable(['organization_id', 'slug', 'name', 'role', 'subject', 'bio', 'photo', 'email', 'phone', 'is_active', 'order'])]
class Officer extends Model
{
    /** @use HasFactory<OfficerFactory> */
    use HasFactory;

    use InvalidatesTenantPageCache;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
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
     * @param  Builder<Officer>  $query
     * @return Builder<Officer>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
