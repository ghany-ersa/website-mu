<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\BuilderAware;
use App\Models\Organization;
use App\Models\Program;
use App\Services\PlanLimitService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrganizationProgramController extends Controller
{
    use BuilderAware;

    public function __construct(private readonly PlanLimitService $planLimitService) {}

    /**
     * Program and Layanan are the same entity (type column), both rendered by the same
     * templates/sections/program-unggulan.blade.php partial - see prd.md for the rationale.
     * ?type=layanan switches the whole index/form to the "layanan" pool; anything else
     * defaults to "program".
     */
    private function type(Request $request): string
    {
        return $request->query('type') === 'layanan' ? 'layanan' : 'program';
    }

    private function sectionKey(string $type): string
    {
        return $type === 'layanan' ? 'layanan' : 'program-unggulan';
    }

    private function indexLabel(string $type): string
    {
        return $type === 'layanan' ? 'Layanan' : 'Program Unggulan';
    }

    private function formLabel(string $type): string
    {
        return $type === 'layanan' ? 'Layanan' : 'Program';
    }

    public function index(Request $request, Organization $organization): View
    {
        $type = $this->type($request);
        abort_unless($organization->hasSection($this->sectionKey($type)), 404);

        $this->authorize('viewAny', [Program::class, $organization]);

        return view('organizations.programs.index', [
            'organization' => $organization,
            'type' => $type,
            'label' => $this->indexLabel($type),
            'programs' => $organization->programs()->ofType($type)->get(),
            ...$this->builderViewData($request),
        ]);
    }

    public function create(Request $request, Organization $organization): View|RedirectResponse
    {
        $type = $this->type($request);
        abort_unless($organization->hasSection($this->sectionKey($type)), 404);

        $this->authorize('create', [Program::class, $organization]);

        if (! $this->planLimitService->canCreate($organization, 'programs')) {
            return redirect()
                ->route('organizations.programs.index', ['organization' => $organization, 'type' => $type])
                ->with('warning', ($type === 'layanan' ? 'Batas jumlah layanan' : 'Batas jumlah program').' paket Anda sudah tercapai. Upgrade paket untuk menambah lagi.');
        }

        return view('organizations.programs.form', [
            'organization' => $organization,
            'type' => $type,
            'label' => $this->formLabel($type),
            'program' => new Program,
            ...$this->builderViewData($request),
        ]);
    }

    public function store(Request $request, Organization $organization): RedirectResponse
    {

        $type = $this->type($request);
        abort_unless($organization->hasSection($this->sectionKey($type)), 404);

        $this->authorize('create', [Program::class, $organization]);

        if (! $this->planLimitService->canCreate($organization, 'programs')) {
            return redirect()
                ->route('organizations.programs.index', ['organization' => $organization, 'type' => $type])
                ->with('warning', ($type === 'layanan' ? 'Batas jumlah layanan' : 'Batas jumlah program').' paket Anda sudah tercapai. Upgrade paket untuk menambah lagi.');
        }

        $organization->programs()->create([
            ...$this->validated($request),
            'type' => $type,
            'order' => $organization->programs()->ofType($type)->max('order') + 1,
        ]);

        return redirect()
            ->route('organizations.programs.index', $this->builderIndexParams($request, ['organization' => $organization, 'type' => $type]))
            ->with('status', ($type === 'layanan' ? 'Layanan' : 'Program').' berhasil ditambahkan.');
    }

    public function edit(Request $request, Organization $organization, Program $program): View
    {
        $this->authorize('update', $program);
        $this->ensureBelongsToOrganization($organization, $program);

        return view('organizations.programs.form', [
            'organization' => $organization,
            'type' => $program->type,
            'label' => $this->formLabel($program->type),
            'program' => $program,
            ...$this->builderViewData($request),
        ]);
    }

    public function update(Request $request, Organization $organization, Program $program): RedirectResponse
    {
        $this->authorize('update', $program);
        $this->ensureBelongsToOrganization($organization, $program);

        $program->update($this->validated($request));

        return redirect()
            ->route('organizations.programs.index', $this->builderIndexParams($request, ['organization' => $organization, 'type' => $program->type]))
            ->with('status', ($program->type === 'layanan' ? 'Layanan' : 'Program').' berhasil diperbarui.');
    }

    public function destroy(Request $request, Organization $organization, Program $program): RedirectResponse
    {
        $this->authorize('delete', $program);
        $this->ensureBelongsToOrganization($organization, $program);

        $type = $program->type;
        $program->delete();

        return redirect()
            ->route('organizations.programs.index', $this->builderIndexParams($request, ['organization' => $organization, 'type' => $type]))
            ->with('status', ($type === 'layanan' ? 'Layanan' : 'Program').' berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'icon' => ['nullable', 'string', 'max:10'],
        ]);
    }

    private function ensureBelongsToOrganization(Organization $organization, Program $program): void
    {
        abort_unless($program->organization_id === $organization->id, 404);
    }
}
