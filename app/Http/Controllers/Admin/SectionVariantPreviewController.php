<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\SectionVariant;
use Illuminate\View\View;

class SectionVariantPreviewController extends Controller
{
    /**
     * Render one specific section variant in isolation - including non-default variants, which
     * organizations/builder/section-preview.blade.php's own preview route can't do (it always
     * resolves a section's default). Mirrors that view's standalone-document scaffold, but with
     * no organization to borrow brand colors from: a fresh, never-persisted Organization instance
     * is passed as `brand` so every accessor it calls (primaryColor(), etc.) falls through to the
     * platform's own hardcoded defaults.
     */
    public function show(SectionVariant $sectionVariant): View
    {
        $content = config("page-builder.sections.{$sectionVariant->section_key}.defaults", []);

        return view('admin.section-variants.preview', [
            'brand' => new Organization,
            'sectionVariant' => $sectionVariant,
            'section' => ['content' => $content],
        ]);
    }
}
