<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\BuilderAware;
use App\Http\Controllers\Concerns\SanitizesRichText;
use App\Models\Agenda;
use App\Models\Organization;
use App\Services\PlanLimitService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrganizationAgendaController extends Controller
{
    use BuilderAware;
    use SanitizesRichText;

    public function __construct(private readonly PlanLimitService $planLimitService) {}

    public function index(Request $request, Organization $organization): View
    {
        abort_unless($organization->hasSection('agenda'), 404);
        $this->authorize('viewAny', [Agenda::class, $organization]);

        return view('organizations.agendas.index', [
            'organization' => $organization,
            'agendas' => $organization->agendas()->get(),
            ...$this->builderViewData($request),
        ]);
    }

    public function create(Request $request, Organization $organization): View|RedirectResponse
    {
        abort_unless($organization->hasSection('agenda'), 404);
        $this->authorize('create', [Agenda::class, $organization]);

        if (! $this->planLimitService->canCreate($organization, 'agendas')) {
            return redirect()
                ->route('organizations.agendas.index', $organization)
                ->with('warning', 'Batas jumlah agenda paket Anda sudah tercapai. Upgrade paket untuk menambah lagi.');
        }

        return view('organizations.agendas.form', [
            'organization' => $organization,
            'agenda' => new Agenda,
            ...$this->builderViewData($request),
        ]);
    }

    public function store(Request $request, Organization $organization): RedirectResponse
    {
        abort_unless($organization->hasSection('agenda'), 404);
        $this->authorize('create', [Agenda::class, $organization]);

        if (! $this->planLimitService->canCreate($organization, 'agendas')) {
            return redirect()
                ->route('organizations.agendas.index', $organization)
                ->with('warning', 'Batas jumlah agenda paket Anda sudah tercapai. Upgrade paket untuk menambah lagi.');
        }

        $organization->agendas()->create($this->validated($request));

        return redirect()
            ->route('organizations.agendas.index', $this->builderIndexParams($request, ['organization' => $organization]))
            ->with('status', 'Agenda berhasil disimpan.');
    }

    public function edit(Request $request, Organization $organization, Agenda $agenda): View
    {
        $this->authorize('update', $agenda);
        $this->ensureBelongsToOrganization($organization, $agenda);

        return view('organizations.agendas.form', [
            'organization' => $organization,
            'agenda' => $agenda,
            ...$this->builderViewData($request),
        ]);
    }

    public function update(Request $request, Organization $organization, Agenda $agenda): RedirectResponse
    {
        $this->authorize('update', $agenda);
        $this->ensureBelongsToOrganization($organization, $agenda);

        $agenda->update($this->validated($request));

        return redirect()
            ->route('organizations.agendas.index', $this->builderIndexParams($request, ['organization' => $organization]))
            ->with('status', 'Agenda berhasil diperbarui.');
    }

    public function destroy(Request $request, Organization $organization, Agenda $agenda): RedirectResponse
    {
        $this->authorize('delete', $agenda);
        $this->ensureBelongsToOrganization($organization, $agenda);

        $agenda->delete();

        return redirect()
            ->route('organizations.agendas.index', $this->builderIndexParams($request, ['organization' => $organization]))
            ->with('status', 'Agenda berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'starts_at' => ['required', 'date'],
            'location' => ['nullable', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'registration_url' => ['nullable', 'url', 'max:255'],
            'status' => ['required', 'in:draft,published'],
        ]);

        $data['description'] = $this->sanitizeRichText($data['description']);

        return $data;
    }

    private function ensureBelongsToOrganization(Organization $organization, Agenda $agenda): void
    {
        abort_unless($agenda->organization_id === $organization->id, 404);
    }
}
