<?php

namespace Database\Seeders;

use App\Models\OrganizationType;
use App\Models\Template;
use Illuminate\Database\Seeder;

/**
 * A single "Halaman Kosong" template: one Beranda page with no sections beyond the locked
 * header/footer (see Organization::seedPagesFromTemplate() - a page entry with an empty/absent
 * `sections` array still gets ensureHeader()/ensureFooter() unconditionally, so this really does
 * produce an empty-but-navigable starting page, not a broken one).
 *
 * is_public = false: this exists purely as a "start from scratch" option in the
 * organization-creation picker (OrganizationController::createTemplate(), which does NOT filter
 * on is_public - see that migration's doc comment) - it has no design of its own worth showcasing,
 * so it's excluded from the public catalog (templates.index) and the homepage's featured grid,
 * both of which DO filter on is_public.
 *
 * Pinned to the first organization_type (by id) rather than one per type: a Template can't be
 * typeless (organizations.organization_type_id is NOT NULL, derived from whichever template is
 * picked - see OrganizationController::createTemplate()'s doc comment), so this one row's type is
 * a technicality to satisfy that constraint, not a real "this template is for Muhammadiyah
 * organizations" claim - an organization created from it can freely change its type later the
 * same way any organization can.
 */
class BlankTemplateSeeder extends Seeder
{
    public const SLUG = 'halaman-kosong';

    public function run(): void
    {
        $type = OrganizationType::orderBy('id')->first();

        if (! $type) {
            return;
        }

        Template::updateOrCreate(
            ['slug' => self::SLUG],
            [
                'organization_type_id' => $type->id,
                'name' => 'Halaman Kosong',
                'description' => 'Mulai dari halaman kosong dan susun sendiri section demi section - cocok bila tidak ada template siap pakai yang sesuai.',
                'is_active' => true,
                'is_public' => false,
                'is_exclusive' => false,
                'is_featured' => false,
                'structure' => [
                    'pages' => [
                        [
                            'slug' => 'beranda',
                            'name' => 'Beranda',
                            'sections' => [],
                        ],
                    ],
                ],
            ]
        );
    }
}
