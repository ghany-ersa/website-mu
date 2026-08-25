<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrganizationRole;
use App\Http\Controllers\Controller;
use App\Models\Organization;
use Illuminate\View\View;

class OrganizationController extends Controller
{
    /**
     * Display a listing of all registered organizations, grouped either by owner
     * account or by organization type depending on the `group_by` query param.
     */
    public function index(): View
    {
        $groupBy = request('group_by') === 'type' ? 'type' : 'owner';

        $organizations = Organization::with(['organizationType', 'members' => function ($query) {
            $query->wherePivot('role', OrganizationRole::Owner->value);
        }])->orderBy('name')->get();

        $groups = $groupBy === 'type'
            ? $organizations->groupBy(fn (Organization $organization) => $organization->organizationType?->name ?? 'Tanpa Jenis')
            : $organizations->groupBy(function (Organization $organization) {
                $owner = $organization->members->first();

                return $owner ? "{$owner->name} ({$owner->email})" : 'Tanpa Owner';
            });

        return view('admin.organizations.index', [
            'groups' => $groups,
            'groupBy' => $groupBy,
            'total' => $organizations->count(),
        ]);
    }
}
