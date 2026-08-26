<?php

namespace App\Models;

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
     * scalars into it — guard against rendering that as a string field's value.
     */
    public function scalarContent(string $field, string $default = ''): string
    {
        $value = $this->content[$field] ?? null;

        return is_scalar($value) ? (string) $value : $default;
    }

    /**
     * Resolve a text field's display value: the section's own saved content, or — when empty —
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
