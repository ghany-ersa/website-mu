<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SectionVariant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Read/toggle-only screen over the section_variants registry (App\Models\SectionVariant) - an
 * admin can see which variants exist per section and flip each one's `is_exclusive` flag at
 * runtime. There is no create/destroy here: a brand-new variant requires a developer to write its
 * Blade view and seed its row (see database/seeders/SectionVariantSeeder.php) first.
 */
class SectionVariantController extends Controller
{
    public function index(): View
    {
        $variants = SectionVariant::orderBy('section_key')
            ->orderBy('variant_key')
            ->get()
            ->groupBy('section_key');

        return view('admin.section-variants.index', ['variantsBySection' => $variants]);
    }

    public function update(Request $request, SectionVariant $sectionVariant): RedirectResponse
    {
        $sectionVariant->update([
            'is_exclusive' => $request->boolean('is_exclusive'),
        ]);

        return redirect()
            ->route('admin.section-variants.index')
            ->with('status', 'Status eksklusif berhasil diperbarui.');
    }
}
