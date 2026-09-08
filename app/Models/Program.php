<?php

namespace App\Models;

use App\Models\Concerns\InvalidatesTenantPageCache;
use Database\Factories\ProgramFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['organization_id', 'type', 'title', 'description', 'icon', 'image', 'category', 'date', 'order'])]
class Program extends Model
{
    /**
     * Categories for the `kegiatan` type (a school's activities), used to group them on the
     * public page. Stored as a plain string column - this list, not a DB constraint, is what
     * keeps the values honest; the controller validates against it.
     *
     * @var list<string>
     */
    public const CATEGORIES = ['Keagamaan', 'Ekstrakurikuler', 'Akademik', 'Sosial', 'Lainnya'];

    /** @use HasFactory<ProgramFactory> */
    use HasFactory;

    use InvalidatesTenantPageCache;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
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
     * @param  Builder<Program>  $query
     * @return Builder<Program>
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }
}
