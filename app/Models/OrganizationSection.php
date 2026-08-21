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
}
