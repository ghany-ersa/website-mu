<?php

namespace App\Http\Controllers;

use App\Models\OrganizationType;
use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TemplateController extends Controller
{
    /**
     * Full public template catalog, linked from the homepage's trimmed preview grid
     * (see routes/web.php's `/` closure) — this is where every active template lives,
     * filterable by organization type, so the homepage itself can stay to a small
     * curated set without hiding anything from a visitor who wants to browse all of them.
     */
    public function index(Request $request): View
    {
        $typeId = $request->query('organization_type_id');

        $templates = Template::query()
            ->with('organizationType')
            ->where('is_active', true)
            ->when($typeId, fn ($query) => $query->where('organization_type_id', $typeId))
            ->orderByDesc('is_exclusive')
            ->orderBy('name')
            ->get();

        return view('templates.index', [
            'templates' => $templates,
            'organizationTypes' => OrganizationType::orderBy('category')->orderBy('name')->get(),
        ]);
    }
}
