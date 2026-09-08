<?php

namespace App\Models;

use Database\Factories\TemplateFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Fillable(['organization_type_id', 'name', 'slug', 'description', 'thumbnail_path', 'structure', 'is_active', 'is_exclusive'])]
class Template extends Model
{
    /** @use HasFactory<TemplateFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'structure' => 'array',
            'is_active' => 'boolean',
            'is_exclusive' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<OrganizationType, $this>
     */
    public function organizationType(): BelongsTo
    {
        return $this->belongsTo(OrganizationType::class);
    }

    /**
     * @return HasMany<Organization, $this>
     */
    public function organizations(): HasMany
    {
        return $this->hasMany(Organization::class);
    }

    /**
     * Browser-usable URL for the template's thumbnail, or null when it has none.
     *
     * thumbnail_path holds one of two things, so views must not use it as a src directly:
     *  - a path on the media disk, written by the admin form's upload (the normal case), or
     *  - an absolute URL, still supported because the field was a free-text URL box before
     *    uploading existed and existing rows may hold one.
     */
    public function thumbnailUrl(): ?string
    {
        if (blank($this->thumbnail_path)) {
            return null;
        }

        if (Str::startsWith($this->thumbnail_path, ['http://', 'https://', '/'])) {
            return $this->thumbnail_path;
        }

        return Storage::disk(config('media.disk'))->url($this->thumbnail_path);
    }
}
