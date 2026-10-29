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
                ->with('warning', 'Batas jumlah agenda paket Anda sudah tercapai. Tingkatkan paket infak untuk menambah lagi.');
        }

        return view('organizations.agendas.form', [
            'organization' => $organization,
            'agenda' => new Agenda,
            'showPoster' => $this->usesPosterVariant($organization),
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
                ->with('warning', 'Batas jumlah agenda paket Anda sudah tercapai. Tingkatkan paket infak untuk menambah lagi.');
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
            'showPoster' => $this->usesPosterVariant($organization),
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
        // A form posted before the schedule switch existed (an open tab across a deploy) only
        // ever meant a one-off date.
        $request->mergeIfMissing(['schedule' => 'once']);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            // A media-library URL, same contract as masjid_facilities.photo - the picker writes
            // the chosen file's URL into this field rather than uploading through this request.
            'poster' => ['nullable', 'string'],
            // 'weekly' = kajian rutin: the form sends a weekday and a free-text time instead of
            // a date; the weekday is folded into starts_at below (see the
            // add_speaker_and_weekly_schedule migration).
            'schedule' => ['required', 'in:once,weekly'],
            'starts_at' => ['exclude_unless:schedule,once', 'required', 'date'],
            'day_of_week' => ['exclude_unless:schedule,weekly', 'required', 'integer', 'between:0,6'],
            'time_label' => ['exclude_unless:schedule,weekly', 'nullable', 'string', 'max:100'],
            'location' => ['nullable', 'string', 'max:255'],
            'speaker' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'registration_url' => ['nullable', 'url', 'max:255'],
            'status' => ['required', 'in:draft,published'],
        ], [
            'starts_at.required' => 'Isi tanggal dan waktu agenda.',
            'day_of_week.required' => 'Pilih hari agenda rutin.',
        ]);

        $data['repeats_weekly'] = $data['schedule'] === 'weekly';

        if ($data['repeats_weekly']) {
            $data['starts_at'] = Agenda::weeklyAnchor((int) $data['day_of_week']);
        } else {
            // exclude_unless dropped it from $data, so without this a weekly agenda switched to
            // a one-off would keep its stale time_label.
            $data['time_label'] = null;
        }

        unset($data['schedule'], $data['day_of_week']);

        if (array_key_exists('description', $data)) {
            $data['description'] = $this->sanitizeRichText($data['description']);
        }

        return $data;
    }

    private function ensureBelongsToOrganization(Organization $organization, Agenda $agenda): void
    {
        abort_unless($agenda->organization_id === $organization->id, 404);
    }

    /**
     * Whether the org's active 'agenda' section variant is 'poster' - the only variant whose
     * Blade partial (templates/sections/agenda/poster.blade.php) renders Agenda::$poster. The
     * 'standar' variant never reads it, so the upload field is hidden rather than shown for
     * every variant regardless of whether it does anything.
     */
    private function usesPosterVariant(Organization $organization): bool
    {
        return $organization->sectionVariant('agenda') === 'poster';
    }
}
