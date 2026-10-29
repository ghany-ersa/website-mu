<?php

namespace App\Models;

use App\Models\Concerns\InvalidatesTenantPageCache;
use Database\Factories\OrganizationSectionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['organization_page_id', 'key', 'variant', 'content', 'order', 'is_visible'])]
class OrganizationSection extends Model
{
    /** @use HasFactory<OrganizationSectionFactory> */
    use HasFactory;

    use InvalidatesTenantPageCache;

    /**
     * Resolved through the parent page rather than a direct column - OrganizationSection has no
     * organization_id of its own (see organization_page_id above).
     */
    public function tenantOrganizationId(): ?int
    {
        return $this->page?->organization_id;
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'content' => 'array',
            'is_visible' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<OrganizationPage, $this>
     */
    public function page(): BelongsTo
    {
        return $this->belongsTo(OrganizationPage::class, 'organization_page_id');
    }

    /**
     * A content[] value read back from JSON can come back as an array (e.g. if a field was
     * repurposed, or bad data was ever saved) even though the builder form only ever writes
     * scalars into it - guard against rendering that as a string field's value.
     */
    public function scalarContent(string $field, string $default = ''): string
    {
        $value = $this->content[$field] ?? null;

        return is_scalar($value) ? (string) $value : $default;
    }

    /**
     * Which Program `type` a program-unggulan section shows: its `source` when that is one of
     * the registry's `sources` (config/page-builder.php), else the first - `program` - so a
     * section saved before `source` existed, or with a tampered value, keeps its old behaviour.
     * Static because template structures and the partials only have the content array.
     *
     * @param  array<string, mixed>|null  $content
     */
    public static function programSource(?array $content): string
    {
        $sources = array_keys(config('page-builder.sections.program-unggulan.sources', []));
        $source = $content['source'] ?? null;

        return in_array($source, $sources, true) ? $source : ($sources[0] ?? 'program');
    }

    /**
     * The key that decides which CMS this section is backed by: the section key itself, except
     * a program-unggulan section reading `layanan` rows counts as `layanan` (the section that
     * used to exist for them). CMS gating, the sidebar menu and sample seeding all go through
     * this, so the Layanan CMS opens exactly when some section on the site shows layanan.
     *
     * @param  array<string, mixed>|null  $content
     */
    public static function dataKeyFor(string $key, ?array $content): string
    {
        if ($key !== 'program-unggulan') {
            return $key;
        }

        return config('page-builder.sections.program-unggulan.sources.'.self::programSource($content).'.data_key', $key);
    }

    public function dataKey(): string
    {
        return self::dataKeyFor($this->key, $this->content);
    }

    /**
     * The registry's `cms` entry for this section, with a program-unggulan section's `source`
     * applied - its "Kelola ..." link must open the pool it actually displays.
     *
     * @return array{route: string, label: string, params?: array<string, string>}|null
     */
    public function cmsConfig(): ?array
    {
        $cms = config("page-builder.sections.{$this->key}.cms");

        if ($cms && $this->key === 'program-unggulan') {
            $source = self::programSource($this->content);
            $cms['params']['type'] = $source;
            $cms['label'] = config("page-builder.sections.program-unggulan.sources.{$source}.label", $cms['label']);
        }

        return $cms;
    }

    /**
     * Copy this section's content, variant and visibility onto the same section key on every
     * other page of the organization - a no-op unless the registry marks the key `shared` (see
     * config/page-builder.php). Updated one model at a time rather than with a bulk query so
     * each write still fires InvalidatesTenantPageCache.
     */
    public function syncAcrossPages(): void
    {
        if (! config("page-builder.sections.{$this->key}.shared", false)) {
            return;
        }

        self::query()
            ->where('key', $this->key)
            ->whereKeyNot($this->getKey())
            ->whereHas('page', fn ($query) => $query->where('organization_id', $this->page->organization_id))
            ->get()
            ->each(fn (self $sibling) => $sibling->update([
                'content' => $this->content,
                'variant' => $this->variant,
                'is_visible' => $this->is_visible,
            ]));
    }

    /**
     * Resolve a text field's display value: the section's own saved content, or - when empty -
     * this section key's configured default (config/page-builder.php), with {org_name}
     * substituted in so a freshly-added section reads naturally before the user edits it.
     */
    public function resolvedFieldValue(string $field, string $orgName): string
    {
        $value = $this->scalarContent($field);

        if ($value !== '') {
            return $value;
        }

        $default = (string) config("page-builder.sections.{$this->key}.defaults.{$field}", '');

        return str_replace('{org_name}', $orgName, $default);
    }
}
