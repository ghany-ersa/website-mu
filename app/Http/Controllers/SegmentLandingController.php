<?php

namespace App\Http\Controllers;

use App\Models\OrganizationType;
use App\Models\Template;
use Illuminate\View\View;

/**
 * Renders one per-segment SEO landing page (/website-masjid, /website-sekolah, ...) from its
 * entry in config/landing-pages.php. The segment key arrives as a route default set in
 * routes/web.php, not a URL parameter, so each page keeps a flat keyword-bearing URL.
 */
class SegmentLandingController extends Controller
{
    public function __invoke(string $segment): View
    {
        $config = config("landing-pages.segments.$segment");
        abort_unless($config, 404);

        // Looked up by slug like the template seeders do (see CLAUDE.md "Renaming caution"):
        // a renamed type leaves the page rendering without a template grid rather than failing.
        $organizationType = OrganizationType::where('slug', $config['organization_type_slug'])->first();

        // Standard template first so the cheaper entry point is the first card a visitor sees.
        $templates = $organizationType
            ? Template::where('organization_type_id', $organizationType->id)
                ->where('is_active', true)
                ->where('is_public', true)
                ->orderBy('is_exclusive')
                ->orderBy('name')
                ->get()
            : collect();

        return view('landing.segment', [
            'segment' => $segment,
            'page' => $config,
            'organizationType' => $organizationType,
            'templates' => $templates,
        ]);
    }
}
