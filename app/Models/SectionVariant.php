<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['section_key', 'variant_key', 'view', 'is_exclusive', 'is_default'])]
class SectionVariant extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_exclusive' => 'boolean',
            'is_default' => 'boolean',
        ];
    }
}
