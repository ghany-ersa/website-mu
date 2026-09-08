<?php

namespace App\Models;

use App\Models\Concerns\InvalidatesTenantPageCache;
use Database\Factories\GalleryPhotoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['organization_id', 'url', 'title', 'caption', 'description', 'category', 'is_active', 'order'])]
class GalleryPhoto extends Model
{
    /**
     * Filter buttons on the public gallery, and the allowed values for `category`. Stored as a
     * plain string column (see the migration), so this list - not a DB constraint - is what
     * keeps the values honest; the controller validates against it.
     *
     * @var list<string>
     */
    public const CATEGORIES = ['Kegiatan', 'Prestasi', 'Fasilitas', 'Lainnya'];

    /** @use HasFactory<GalleryPhotoFactory> */
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
     * @param  Builder<GalleryPhoto>  $query
     * @return Builder<GalleryPhoto>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
