<?php

namespace App\Models;

use App\Models\Concerns\InvalidatesTenantPageCache;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * A pupil, shown publicly at /siswa/{slug} with the achievements they've earned.
 */
#[Fillable(['organization_id', 'slug', 'name', 'grade', 'enrolled_year', 'graduated_year', 'bio', 'quote', 'hobbies', 'photo', 'is_active', 'order'])]
class Student extends Model
{
    use InvalidatesTenantPageCache;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'hobbies' => 'array',
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
     * @return BelongsToMany<Achievement, $this>
     */
    public function achievements(): BelongsToMany
    {
        return $this->belongsToMany(Achievement::class);
    }

    /**
     * @param  Builder<Student>  $query
     * @return Builder<Student>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Initials for the avatar placeholder shown when a pupil has no photo - at most two, so a
     * long name doesn't overflow the circle.
     */
    public function initials(): string
    {
        return collect(explode(' ', $this->name))
            ->take(2)
            ->map(fn (string $part) => mb_strtoupper(mb_substr($part, 0, 1)))
            ->implode('');
    }
}
