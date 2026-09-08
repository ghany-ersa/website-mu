<?php

namespace App\Models;

use App\Models\Concerns\InvalidatesTenantPageCache;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * A competition result on the organization's wall of fame.
 */
#[Fillable(['organization_id', 'title', 'category', 'level', 'rank', 'year', 'description', 'image', 'is_active', 'order'])]
class Achievement extends Model
{
    /**
     * Filter buttons on the public `prestasi` section, and the allowed values for `category`.
     * Stored as a plain string column, so this list - not a DB constraint - keeps the values
     * honest; the controller validates against it.
     *
     * @var list<string>
     */
    public const CATEGORIES = ['Akademik', 'Olahraga', 'Seni', 'Lainnya'];

    /**
     * Competition scope, ordered from narrowest to widest - the order matters, since the
     * student profile reports a pupil's highest level by index in this list.
     *
     * @var list<string>
     */
    public const LEVELS = ['Tingkat Sekolah', 'Tingkat Kecamatan', 'Tingkat Kabupaten', 'Tingkat Provinsi', 'Tingkat Nasional', 'Tingkat Internasional'];

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
     * The pupils credited with this achievement - more than one for team events.
     *
     * @return BelongsToMany<Student, $this>
     */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class);
    }

    /**
     * @param  Builder<Achievement>  $query
     * @return Builder<Achievement>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
