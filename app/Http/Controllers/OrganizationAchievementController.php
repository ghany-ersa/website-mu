<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\BuilderAware;
use App\Models\Achievement;
use App\Models\Organization;
use App\Services\PlanLimitService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrganizationAchievementController extends Controller
{
    use BuilderAware;

    public function __construct(private readonly PlanLimitService $planLimitService) {}

    public function index(Request $request, Organization $organization): View
    {
        abort_unless($organization->hasSection('prestasi'), 404);
        $this->authorize('viewAny', [Achievement::class, $organization]);

        return view('organizations.achievements.index', [
            'organization' => $organization,
            'achievements' => $organization->achievements()->with('students')->get(),
            ...$this->builderViewData($request),
        ]);
    }

    public function create(Request $request, Organization $organization): View|RedirectResponse
    {
        abort_unless($organization->hasSection('prestasi'), 404);
        $this->authorize('create', [Achievement::class, $organization]);

        if (! $this->planLimitService->canCreate($organization, 'achievements')) {
            return redirect()
                ->route('organizations.achievements.index', $organization)
                ->with('warning', 'Batas jumlah prestasi paket Anda sudah tercapai. Tingkatkan paket infak untuk menambah lagi.');
        }

        return view('organizations.achievements.form', [
            'organization' => $organization,
            'achievement' => new Achievement,
            'students' => $organization->students()->orderBy('name')->get(),
            ...$this->builderViewData($request),
        ]);
    }

    public function store(Request $request, Organization $organization): RedirectResponse
    {
        abort_unless($organization->hasSection('prestasi'), 404);
        $this->authorize('create', [Achievement::class, $organization]);

        if (! $this->planLimitService->canCreate($organization, 'achievements')) {
            return redirect()
                ->route('organizations.achievements.index', $organization)
                ->with('warning', 'Batas jumlah prestasi paket Anda sudah tercapai. Tingkatkan paket infak untuk menambah lagi.');
        }

        [$validated, $studentIds] = $this->validated($request, $organization);

        $achievement = $organization->achievements()->create([
            ...$validated,
            'order' => $organization->achievements()->max('order') + 1,
        ]);
        $achievement->students()->sync($studentIds);

        return redirect()
            ->route('organizations.achievements.index', $this->builderIndexParams($request, ['organization' => $organization]))
            ->with('status', 'Prestasi berhasil ditambahkan.');
    }

    public function edit(Request $request, Organization $organization, Achievement $achievement): View
    {
        $this->authorize('update', $achievement);
        $this->ensureBelongsToOrganization($organization, $achievement);

        return view('organizations.achievements.form', [
            'organization' => $organization,
            'achievement' => $achievement->load('students'),
            'students' => $organization->students()->orderBy('name')->get(),
            ...$this->builderViewData($request),
        ]);
    }

    public function update(Request $request, Organization $organization, Achievement $achievement): RedirectResponse
    {
        $this->authorize('update', $achievement);
        $this->ensureBelongsToOrganization($organization, $achievement);

        [$validated, $studentIds] = $this->validated($request, $organization);

        $achievement->update($validated);
        $achievement->students()->sync($studentIds);

        return redirect()
            ->route('organizations.achievements.index', $this->builderIndexParams($request, ['organization' => $organization]))
            ->with('status', 'Prestasi berhasil diperbarui.');
    }

    public function destroy(Request $request, Organization $organization, Achievement $achievement): RedirectResponse
    {
        $this->authorize('delete', $achievement);
        $this->ensureBelongsToOrganization($organization, $achievement);

        // The pivot rows go with it (cascadeOnDelete), but the students themselves stay.
        $achievement->delete();

        return redirect()
            ->route('organizations.achievements.index', $this->builderIndexParams($request, ['organization' => $organization]))
            ->with('status', 'Prestasi berhasil dihapus.');
    }

    /**
     * @return array{0: array<string, mixed>, 1: list<int>}
     */
    private function validated(Request $request, Organization $organization): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['nullable', Rule::in(Achievement::CATEGORIES)],
            'level' => ['nullable', Rule::in(Achievement::LEVELS)],
            'rank' => ['nullable', 'string', 'max:255'],
            'year' => ['nullable', 'string', 'max:20'],
            'description' => ['nullable', 'string', 'max:2000'],
            'image' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'student_ids' => ['nullable', 'array'],
            // Scoped to this organization, so a crafted request can't credit a pupil from
            // another school - the pivot itself carries no organization_id to check later.
            'student_ids.*' => [
                'integer',
                Rule::exists('students', 'id')->where('organization_id', $organization->id),
            ],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $studentIds = array_map('intval', $validated['student_ids'] ?? []);
        unset($validated['student_ids']);

        return [$validated, $studentIds];
    }

    private function ensureBelongsToOrganization(Organization $organization, Achievement $achievement): void
    {
        abort_unless($achievement->organization_id === $organization->id, 404);
    }
}
