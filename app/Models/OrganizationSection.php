<?php

namespace App\Models;

use Database\Factories\OrganizationSectionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['organization_page_id', 'key', 'variant', 'content', 'order', 'is_visible', 'hidden_by_plan'])]
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
            'hidden_by_plan' => 'boolean',
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
     * Whether this section should render on the public site: hidden_by_plan (system-managed,
     * flipped on plan downgrade/upgrade) is independent from is_visible (the user's manual
     * toggle) — both must allow it for the section to actually show.
     */
    public function isPubliclyVisible(): bool
    {
        return $this->is_visible && ! $this->hidden_by_plan;
    }
}
