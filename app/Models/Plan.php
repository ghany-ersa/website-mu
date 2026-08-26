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
     * "Gratis" for a free plan rather than the confusing "Rp 0/bulan" — used everywhere the
     * price is shown to an organization/visitor (landing page, subscription page). Admin forms
     * still use the raw price_monthly integer since they need an editable value, not this.
     */
    public function formattedPrice(): string
    {
        return $this->price_monthly === 0
            ? 'Gratis'
            : 'Rp '.number_format($this->price_monthly, 0, ',', '.').'/bulan';
    }

    /**
     * Human-readable feature bullets for pricing UI (e.g. the landing page), generated from
     * this plan's limits rather than hand-written copy, so the list stays accurate as
     * plan_limits rows change. Labels for CMS resource keys are singular Indonesian nouns
     * matched to PlanLimitService::RESOURCE_RELATIONS; a null max_count reads as "Unlimited".
     *
     * Each entry carries `available` so the view can render a cross icon instead of a
     * checkmark for a max_count of 0 — a plain "0 Berita" bullet reads as ambiguous (can I
     * make one or not?), so it needs a visibly different marker, not just different wording.
     *
     * @return array<int, array{label: string, available: bool}>
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

        $features = [];

        foreach ($resourceLabels as $key => $label) {
            $max = $this->limitFor($key);
            $features[] = match (true) {
                $max === null => ['label' => "{$label} Unlimited", 'available' => true],
                $max === 0 => ['label' => "{$label} Tidak Tersedia", 'available' => false],
                default => ['label' => "{$max} {$label}", 'available' => true],
            };
        }

        $sectionsTotal = $this->limitFor('sections_total');

        if ($sectionsTotal !== null && $sectionsTotal > 0) {
            $features[] = ['label' => "Maks. {$sectionsTotal} Komponen per Situs", 'available' => true];
        } elseif ($sectionsTotal === 0) {
            $features[] = ['label' => 'Komponen Situs Tidak Tersedia', 'available' => false];
        }

        return $features;
    }
}
