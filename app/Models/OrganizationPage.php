<?php

namespace App\Models;

use App\Models\Concerns\InvalidatesTenantPageCache;
use Database\Factories\OrganizationPageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

#[Fillable(['organization_id', 'name', 'slug', 'order', 'is_home', 'published_at'])]
class OrganizationPage extends Model
{
    /** @use HasFactory<OrganizationPageFactory> */
    use HasFactory;

    use InvalidatesTenantPageCache;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_home' => 'boolean',
            'published_at' => 'datetime',
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
     * @return HasMany<OrganizationSection, $this>
     */
    public function sections(): HasMany
    {
        return $this->hasMany(OrganizationSection::class)->orderBy('order');
    }

    /**
     * Every page must always render exactly one header first and one footer last,
     * regardless of what the `order` column says - pulls the header/footer rows out and
     * pins them to the front/back, so a stray reorder request can never move either out
     * of place. Callers that need this (the render partial, the builder sidebar) should
     * use this instead of the raw sections() relation.
     *
     * @return Collection<int, OrganizationSection>
     */
    public function sectionsInDisplayOrder(): Collection
    {
        $sections = $this->sections;

        return $sections->where('key', 'header')->values()
            ->concat($sections->whereNotIn('key', ['header', 'footer'])->values())
            ->concat($sections->where('key', 'footer')->values());
    }

    /**
     * Create this page's header section if it doesn't already have one. Safe to call
     * repeatedly (e.g. on every page-creation path) - see config/page-builder.php's
     * `locked` doc comment for why header must always exist and can't be user-managed.
     * Display position is enforced by sectionsInDisplayOrder() regardless of `order`,
     * but `order` is still set to 0 here so a fresh page's raw sections() order matches
     * what's actually displayed.
     */
    public function ensureHeader(): void
    {
        if ($this->sections()->where('key', 'header')->exists()) {
            return;
        }

        $this->sections()->create([
            'key' => 'header',
            'content' => config('page-builder.sections.header.defaults', []),
            'order' => 0,
        ]);
    }

    /**
     * Create this page's footer section if it doesn't already have one. Safe to call
     * repeatedly (e.g. on every page-creation path) - see config/page-builder.php's
     * `locked` doc comment for why footer must always exist and can't be user-managed.
     */
    public function ensureFooter(): void
    {
        if ($this->sections()->where('key', 'footer')->exists()) {
            return;
        }

        $this->sections()->create([
            'key' => 'footer',
            'content' => config('page-builder.sections.footer.defaults', []),
            'order' => $this->sections()->max('order') + 1,
        ]);
    }
}
