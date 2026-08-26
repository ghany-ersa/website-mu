<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrganizationRole;
use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\OrganizationType;
use Illuminate\View\View;

class OrganizationController extends Controller
{
    /**
     * Display a listing of all registered organizations as a searchable, filterable,
     * paginated table.
     */
    public function index(): View
    {
        $search = trim((string) request('q'));
        $typeId = request('organization_type_id');

        $organizations = Organization::query()
            ->with(['organizationType', 'members' => function ($query) {
                $query->wherePivot('role', OrganizationRole::Owner->value);
            }])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhereHas('members', function ($query) use ($search) {
                            $query->where('users.name', 'like', "%{$search}%")
                                ->orWhere('users.email', 'like', "%{$search}%");
                        });
                });
            })
            ->when($typeId, fn ($query) => $query->where('organization_type_id', $typeId))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.organizations.index', [
            'organizations' => $organizations,
            'organizationTypes' => OrganizationType::orderBy('name')->get(),
        ]);
    }
}
