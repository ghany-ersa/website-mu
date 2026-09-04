<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['key', 'name', 'description', 'price_monthly', 'discount_percent_6', 'discount_percent_12', 'is_active', 'hide_branding', 'has_exclusive_templates'])]
class Plan extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price_monthly' => 'integer',
            'discount_percent_6' => 'integer',
            'discount_percent_12' => 'integer',
            'is_active' => 'boolean',
            'hide_branding' => 'boolean',
            'has_exclusive_templates' => 'boolean',
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
     * "Gratis" for a free plan rather than the confusing "Rp 0/bulan" - used everywhere the
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
     * Duration discount percentage for a given billing period - 0 for the 3-month baseline
     * (and for any duration outside the three offered), configured per-plan by an admin.
     */
    public function discountPercentFor(int $months): int
    {
        return match ($months) {
            6 => $this->discount_percent_6,
            12 => $this->discount_percent_12,
            default => 0,
        };
    }

    /**
     * Total price for subscribing at this plan for the given number of months, after applying
     * this plan's duration discount (if any) - the single source of truth for subtotal
     * calculations, used by both PlanChangeRequest::subtotal() and the plan picker UI.
     */
    public function priceForDuration(int $months): int
    {
        $base = $this->price_monthly * $months;
        $discount = $this->discountPercentFor($months);

        return $discount > 0 ? (int) round($base * (100 - $discount) / 100) : $base;
    }

    /**
     * Human-readable feature bullets for pricing UI (e.g. the landing page), generated from
     * this plan's limits rather than hand-written copy, so the list stays accurate as
     * plan_limits rows change. Labels for CMS resource keys are singular Indonesian nouns
     * matched to PlanLimitService::RESOURCE_RELATIONS; a null max_count reads as "Unlimited".
     *
     * Each entry carries `available` so the view can render a cross icon instead of a
     * checkmark for a max_count of 0 - a plain "0 Berita" bullet reads as ambiguous (can I
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

        $features[] = $this->hide_branding
            ? ['label' => 'Tanpa Watermark website-mu', 'available' => true]
            : ['label' => 'Watermark website-mu Tampil', 'available' => false];

        $features[] = $this->has_exclusive_templates
            ? ['label' => 'Akses Template Eksklusif', 'available' => true]
            : ['label' => 'Template Eksklusif Tidak Tersedia', 'available' => false];

        return $features;
    }
}
