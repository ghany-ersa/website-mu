<?php

namespace App\Http\Controllers;

use App\Enums\OrganizationStatus;
use App\Models\Organization;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $urls = collect([
            ['loc' => url('/'), 'priority' => '1.0', 'changefreq' => 'weekly'],
        ]);

        if ($tenantDomain = config('tenancy.domain')) {
            $urls = $urls->merge(
                Organization::where('status', OrganizationStatus::Published)->get()->map(fn (Organization $organization) => [
                    'loc' => "https://{$organization->slug}.{$tenantDomain}/",
                    'priority' => '0.8',
                    'changefreq' => 'weekly',
                ])
            );
        }

        $xml = '<' . '?xml version="1.0" encoding="UTF-8"?' . '>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($urls as $url) {
            $xml .= '<url>'
                .'<loc>'.e($url['loc']).'</loc>'
                .'<changefreq>'.e($url['changefreq']).'</changefreq>'
                .'<priority>'.e($url['priority']).'</priority>'
                .'</url>'."\n";
        }

        $xml .= '</urlset>';

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }
}
