<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['key', 'name', 'description', 'price_monthly', 'is_active'])]
class Plan extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price_monthly' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<PlanLimit, $this>
     */
    public function limits(): HasMany
    {
        return $this->hasMany(PlanLimit::class);
    }

    /**
     * @return HasMany<PlanComponent, $this>
     */
    public function components(): HasMany
    {
        return $this->hasMany(PlanComponent::class);
    }

    /**
     * @return HasMany<Organization, $this>
     */
    public function organizations(): HasMany
    {
        return $this->hasMany(Organization::class);
    }

    /**
     * Max count for a given plan_limits key, or null if unlimited/not set.
     */
    public function limitFor(string $key): ?int
    {
        return $this->limits->firstWhere('key', $key)?->max_count;
    }

    /**
     * Whether a section key is allowed for this plan. Sections without a plan_components row
     * are allowed by default (opt-out model) — only restricted sections need a row.
     */
    public function allowsComponent(string $componentKey): bool
    {
        return $this->components->firstWhere('component_key', $componentKey)?->is_allowed ?? true;
    }

    /**
     * Human-readable feature bullets for pricing UI (e.g. the landing page), generated from
     * this plan's limits/components rather than hand-written copy, so the list stays accurate
     * as plan_limits/plan_components rows change. Labels for CMS resource keys are singular
     * Indonesian nouns matched to PlanLimitService::RESOURCE_RELATIONS; a null max_count reads
     * as "Unlimited". Only components explicitly allowed (is_allowed = true rows — i.e. ones
     * this plan unlocks that aren't already allowed-by-default) are listed as exclusive
     * features, since opt-out sections with no row are already implied by "penuh".
     *
     * @return array<int, string>
     */
    public function pricingFeatures(): array
    {
        $resourceLabels = [
            'posts' => 'Berita',
            'agendas' => 'Agenda',
            'announcements' => 'Pengumuman',
            'officers' => 'Data Pengurus',
            'programs' => 'Program/Layanan',
            'gallery_photos' => 'Foto Galeri',
        ];

        $componentLabels = [
            'donasi-zakat-infak' => 'Donasi/Zakat',
            'ppdb' => 'PPDB',
            'formulir-kontak' => 'Formulir Kontak',
            'jadwal-praktik' => 'Jadwal Praktik',
        ];

        $features = [];

        foreach ($resourceLabels as $key => $label) {
            $max = $this->limitFor($key);
            $features[] = $max === null ? "{$label} Unlimited" : "{$max} {$label}";
        }

        if ($sectionsTotal = $this->limitFor('sections_total')) {
            $features[] = "Maks. {$sectionsTotal} Komponen per Situs";
        }

        $exclusive = $this->components
            ->where('is_allowed', true)
            ->pluck('component_key')
            ->map(fn (string $key) => $componentLabels[$key] ?? $key)
            ->all();

        if ($exclusive) {
            $features[] = 'Komponen Eksklusif: '.implode(', ', $exclusive);
        }

        return $features;
    }
}
