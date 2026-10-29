<?php

namespace App\Models;

use App\Models\Concerns\InvalidatesTenantPageCache;
use Database\Factories\GalleryPhotoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['organization_id', 'url', 'title', 'caption', 'description', 'category', 'is_active', 'order'])]
class GalleryPhoto extends Model
{
    /**
     * Starter suggestions for `category`, offered alongside whatever categories the
     * organization has already typed. `category` is free text (each distinct value becomes a
     * filter button on the public gallery) - a fixed list proved too narrow for real
     * organizations, so this is only the seed for the form's datalist, not a whitelist.
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
     * The text shown for a photo on the public gallery and the CMS list. The form no longer
     * asks for a separate `caption` (title + description cover it), but rows created before
     * that still carry one - falling back keeps those photos labelled without a data migration.
     *
     * @return Attribute<string|null, never>
     */
    protected function label(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->title ?: $this->caption);
    }

    /**
     * Category suggestions for the form: the starter list plus every category this
     * organization already uses, so a tag typed once is offered (with the same spelling)
     * the next time.
     *
     * @return list<string>
     */
    public static function categoryOptions(Organization $organization): array
    {
        return collect(self::CATEGORIES)
            ->merge($organization->photos()->whereNotNull('category')->distinct()->pluck('category'))
            ->unique(fn (string $category) => mb_strtolower($category))
            ->values()
            ->all();
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
