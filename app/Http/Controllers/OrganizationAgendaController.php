<?php

namespace App\Http\Controllers;

use App\Models\Agenda;
use App\Models\Organization;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrganizationAgendaController extends Controller
{
    public function index(Organization $organization): View
    {
        abort_unless($organization->hasSection('agenda'), 404);
        $this->authorize('viewAny', [Agenda::class, $organization]);

        return view('organizations.agendas.index', [
            'organization' => $organization,
            'agendas' => $organization->agendas()->get(),
        ]);
    }

    public function create(Organization $organization): View
    {
        abort_unless($organization->hasSection('agenda'), 404);
        $this->authorize('create', [Agenda::class, $organization]);

        return view('organizations.agendas.form', [
            'organization' => $organization,
            'agenda' => new Agenda,
        ]);
    }

    public function store(Request $request, Organization $organization): RedirectResponse
    {
        abort_unless($organization->hasSection('agenda'), 404);
        $this->authorize('create', [Agenda::class, $organization]);

        $organization->agendas()->create($this->validated($request));

        return redirect()
            ->route('organizations.agendas.index', $this->indexParams($request, $organization))
            ->with('status', 'Agenda berhasil disimpan.');
    }

    public function edit(Organization $organization, Agenda $agenda): View
    {
        $this->authorize('update', $agenda);
        $this->ensureBelongsToOrganization($organization, $agenda);

        return view('organizations.agendas.form', [
            'organization' => $organization,
            'agenda' => $agenda,
        ]);
    }

    public function update(Request $request, Organization $organization, Agenda $agenda): RedirectResponse
    {
        $this->authorize('update', $agenda);
        $this->ensureBelongsToOrganization($organization, $agenda);

        $agenda->update($this->validated($request));

        return redirect()
            ->route('organizations.agendas.index', $this->indexParams($request, $organization))
            ->with('status', 'Agenda berhasil diperbarui.');
    }

    public function destroy(Request $request, Organization $organization, Agenda $agenda): RedirectResponse
    {
        $this->authorize('delete', $agenda);
        $this->ensureBelongsToOrganization($organization, $agenda);

        $agenda->delete();

        return redirect()
            ->route('organizations.agendas.index', $this->indexParams($request, $organization))
            ->with('status', 'Agenda berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'starts_at' => ['required', 'date'],
            'location' => ['nullable', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'registration_url' => ['nullable', 'url', 'max:255'],
            'status' => ['required', 'in:draft,published'],
        ]);
    }

    private function ensureBelongsToOrganization(Organization $organization, Agenda $agenda): void
    {
        abort_unless($agenda->organization_id === $organization->id, 404);
    }

    /**
     * @return array<string, mixed>
     */
    private function indexParams(Request $request, Organization $organization): array
    {
        return $request->input('from') === 'builder'
            ? ['organization' => $organization, 'from' => 'builder', 'section' => $request->input('section')]
            : ['organization' => $organization];
    }
}
