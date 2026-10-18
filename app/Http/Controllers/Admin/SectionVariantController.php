<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SectionVariant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Read/toggle-only screen over the section_variants registry (App\Models\SectionVariant) - an
 * admin can see which variants exist per section and flip each one's `is_exclusive`/`is_visible`
 * flags at runtime. There is no create/destroy here: a brand-new variant requires a developer to
 * write its Blade view and seed its row (see database/seeders/SectionVariantSeeder.php) first.
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
        // has() + fallback to the current value, not boolean()'s implicit "absent = false": the
        // admin view's two checkboxes always submit together (see index.blade.php's shared
        // form="..." wiring) so this only matters for any OTHER caller that posts just one field
        // - that caller's silence about the other flag must leave it exactly as it was, not
        // reinterpret "didn't mention is_visible" as "hide it".
        $isVisible = $request->has('is_visible') ? $request->boolean('is_visible') : $sectionVariant->is_visible;

        // The default variant is what every section of this key silently resolves to
        // (SectionVariantResolver::resolveFrom()'s fallback) whenever no explicit variant is
        // picked - hiding it from the builder's dropdown while it's still secretly in active use
        // would be a confusing, broken-looking state (can't be found to switch away from, can't
        // be found to confirm what it even is). An admin must pick a different default first.
        if ($sectionVariant->is_default && ! $isVisible) {
            return redirect()
                ->route('admin.section-variants.index')
                ->with('error', 'Variant default tidak bisa disembunyikan. Jadikan variant lain sebagai default terlebih dahulu.');
        }

        $sectionVariant->update([
            'is_exclusive' => $request->has('is_exclusive') ? $request->boolean('is_exclusive') : $sectionVariant->is_exclusive,
            'is_visible' => $isVisible,
        ]);

        return redirect()
            ->route('admin.section-variants.index')
            ->with('status', 'Status variant berhasil diperbarui.');
    }
}
