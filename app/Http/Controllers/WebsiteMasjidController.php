<?php

namespace App\Http\Controllers;

use App\Models\OrganizationType;
use App\Models\Template;
use Illuminate\View\View;

/**
 * SEO landing page for one segment (masjid & mushola), at /website-masjid.
 *
 * The homepage pitches every organization type at once, so it can only rank for broad terms
 * ("website muhammadiyah"). A page per segment lets each one own its own keyword cluster
 * ("website masjid", "laporan keuangan masjid", "donasi masjid online") with a focused title,
 * H1, and FAQ. Masjid goes first because its premium sections (laporan keuangan, donasi
 * progress, kalkulator zakat, akad nikah) are the platform's most distinctive features.
 *
 * Copy lives in the view rather than config: this is hand-written marketing text, not a
 * registry other code reads. A second segment page should copy this shape, not abstract it.
 */
class WebsiteMasjidController extends Controller
{
    /** Matched by slug like the template seeders do - see OrganizationTypeSeeder before renaming. */
    private const TYPE_SLUG = 'masjidmushola';

    public function __invoke(): View
    {
        $organizationType = OrganizationType::where('slug', self::TYPE_SLUG)->first();

        // Standard template first so the cheaper entry point is the first card a visitor sees.
        $templates = $organizationType
            ? Template::where('organization_type_id', $organizationType->id)
                ->where('is_active', true)
                ->where('is_public', true)
                ->orderBy('is_exclusive')
                ->orderBy('name')
                ->get()
            : collect();

        return view('landing.website-masjid', [
            'organizationType' => $organizationType,
            'templates' => $templates,
        ]);
    }
}
