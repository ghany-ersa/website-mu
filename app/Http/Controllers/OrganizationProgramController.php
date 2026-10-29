<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\BuilderAware;
use App\Models\Organization;
use App\Models\Program;
use App\Services\PlanLimitService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrganizationProgramController extends Controller
{
    use BuilderAware;

    public function __construct(private readonly PlanLimitService $planLimitService) {}

    /**
     * Program, Layanan and Kegiatan are the same entity (type column). Program and Layanan are
     * kept as separate pools because one organization can need both - a clinic lists its
     * medical services (layanan) apart from its social programs (program) - but they share one
     * widget, program-unggulan, whose `source` picks the pool (config/page-builder.php).
     * Kegiatan keeps its own widget. ?type= switches the whole index/form to that pool;
     * anything unrecognised defaults to "program".
     *
     * `kegiatan` is a school's activities (the Activity entity of the standalone
     * comprof-SD-Madya project), which adds the image/category/date columns; program and
     * layanan leave those null.
     */
    private function type(Request $request): string
    {
        $type = (string) $request->query('type');

        return in_array($type, ['layanan', 'kegiatan'], true) ? $type : 'program';
    }

    /**
     * The OrganizationSection::dataKey() that must exist on the site for this pool's CMS to
     * open - `layanan` is a program-unggulan section with source layanan, not its own key.
     */
    private function sectionKey(string $type): string
    {
        return match ($type) {
            'layanan' => 'layanan',
            'kegiatan' => 'kegiatan',
            default => 'program-unggulan',
        };
    }

    private function indexLabel(string $type): string
    {
        return match ($type) {
            'layanan' => 'Layanan',
            'kegiatan' => 'Kegiatan',
            default => 'Program Unggulan',
        };
    }

    private function formLabel(string $type): string
    {
        return match ($type) {
            'layanan' => 'Layanan',
            'kegiatan' => 'Kegiatan',
            default => 'Program',
        };
    }

    public function index(Request $request, Organization $organization): View
    {
        $type = $this->type($request);
        abort_unless($organization->hasDataSection($this->sectionKey($type)), 404);

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
        abort_unless($organization->hasDataSection($this->sectionKey($type)), 404);

        $this->authorize('create', [Program::class, $organization]);

        if (! $this->planLimitService->canCreate($organization, 'programs')) {
            return redirect()
                ->route('organizations.programs.index', ['organization' => $organization, 'type' => $type])
                ->with('warning', 'Batas jumlah '.strtolower($this->formLabel($type)).' paket Anda sudah tercapai. Tingkatkan paket infak untuk menambah lagi.');
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
        abort_unless($organization->hasDataSection($this->sectionKey($type)), 404);

        $this->authorize('create', [Program::class, $organization]);

        if (! $this->planLimitService->canCreate($organization, 'programs')) {
            return redirect()
                ->route('organizations.programs.index', ['organization' => $organization, 'type' => $type])
                ->with('warning', 'Batas jumlah '.strtolower($this->formLabel($type)).' paket Anda sudah tercapai. Tingkatkan paket infak untuk menambah lagi.');
        }

        $organization->programs()->create([
            ...$this->validated($request),
            'type' => $type,
            'order' => $organization->programs()->ofType($type)->max('order') + 1,
        ]);

        return redirect()
            ->route('organizations.programs.index', $this->builderIndexParams($request, ['organization' => $organization, 'type' => $type]))
            ->with('status', $this->formLabel($type).' berhasil ditambahkan.');
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
            ->with('status', $this->formLabel($program->type).' berhasil diperbarui.');
    }

    public function destroy(Request $request, Organization $organization, Program $program): RedirectResponse
    {
        $this->authorize('delete', $program);
        $this->ensureBelongsToOrganization($organization, $program);

        $type = $program->type;
        $program->delete();

        return redirect()
            ->route('organizations.programs.index', $this->builderIndexParams($request, ['organization' => $organization, 'type' => $type]))
            ->with('status', $this->formLabel($type).' berhasil dihapus.');
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
            'image' => ['nullable', 'string'],
            'category' => ['nullable', Rule::in(Program::CATEGORIES)],
            'date' => ['nullable', 'date'],
        ]);
    }

    private function ensureBelongsToOrganization(Organization $organization, Program $program): void
    {
        abort_unless($program->organization_id === $organization->id, 404);
    }
}
