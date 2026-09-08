<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\BuilderAware;
use App\Models\Organization;
use App\Models\Student;
use App\Services\PlanLimitService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrganizationStudentController extends Controller
{
    use BuilderAware;

    public function __construct(private readonly PlanLimitService $planLimitService) {}

    public function index(Request $request, Organization $organization): View
    {
        abort_unless($organization->hasSection('prestasi'), 404);
        $this->authorize('viewAny', [Student::class, $organization]);

        return view('organizations.students.index', [
            'organization' => $organization,
            'students' => $organization->students()->withCount('achievements')->get(),
            ...$this->builderViewData($request),
        ]);
    }

    public function create(Request $request, Organization $organization): View|RedirectResponse
    {
        abort_unless($organization->hasSection('prestasi'), 404);
        $this->authorize('create', [Student::class, $organization]);

        if (! $this->planLimitService->canCreate($organization, 'students')) {
            return redirect()
                ->route('organizations.students.index', $organization)
                ->with('warning', 'Batas jumlah siswa paket Anda sudah tercapai. Upgrade paket untuk menambah lagi.');
        }

        return view('organizations.students.form', [
            'organization' => $organization,
            'student' => new Student,
            ...$this->builderViewData($request),
        ]);
    }

    public function store(Request $request, Organization $organization): RedirectResponse
    {
        abort_unless($organization->hasSection('prestasi'), 404);
        $this->authorize('create', [Student::class, $organization]);

        if (! $this->planLimitService->canCreate($organization, 'students')) {
            return redirect()
                ->route('organizations.students.index', $organization)
                ->with('warning', 'Batas jumlah siswa paket Anda sudah tercapai. Upgrade paket untuk menambah lagi.');
        }

        $validated = $this->validated($request);

        $organization->students()->create([
            ...$validated,
            'slug' => $this->uniqueSlug($organization, $validated['name']),
            'order' => $organization->students()->max('order') + 1,
        ]);

        return redirect()
            ->route('organizations.students.index', $this->builderIndexParams($request, ['organization' => $organization]))
            ->with('status', 'Siswa berhasil ditambahkan.');
    }

    public function edit(Request $request, Organization $organization, Student $student): View
    {
        $this->authorize('update', $student);
        $this->ensureBelongsToOrganization($organization, $student);

        return view('organizations.students.form', [
            'organization' => $organization,
            'student' => $student,
            ...$this->builderViewData($request),
        ]);
    }

    public function update(Request $request, Organization $organization, Student $student): RedirectResponse
    {
        $this->authorize('update', $student);
        $this->ensureBelongsToOrganization($organization, $student);

        $validated = $this->validated($request);

        // Only re-slug on an actual rename, so a pupil's public profile URL survives edits to
        // their bio or photo.
        if ($validated['name'] !== $student->name) {
            $validated['slug'] = $this->uniqueSlug($organization, $validated['name'], $student);
        }

        $student->update($validated);

        return redirect()
            ->route('organizations.students.index', $this->builderIndexParams($request, ['organization' => $organization]))
            ->with('status', 'Siswa berhasil diperbarui.');
    }

    public function destroy(Request $request, Organization $organization, Student $student): RedirectResponse
    {
        $this->authorize('delete', $student);
        $this->ensureBelongsToOrganization($organization, $student);

        // Pivot rows go with it; the achievements themselves stay on the wall of fame, just
        // without this pupil credited.
        $student->delete();

        return redirect()
            ->route('organizations.students.index', $this->builderIndexParams($request, ['organization' => $organization]))
            ->with('status', 'Siswa berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'grade' => ['nullable', 'string', 'max:50'],
            'enrolled_year' => ['nullable', 'string', 'max:20'],
            'graduated_year' => ['nullable', 'string', 'max:20'],
            'bio' => ['nullable', 'string', 'max:2000'],
            'quote' => ['nullable', 'string', 'max:255'],
            'hobbies' => ['nullable', 'string', 'max:500'],
            'photo' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        // Entered as one comma-separated line - simpler for the owner than a repeater, and the
        // column stores a JSON list either way. Blank entries from a trailing comma are dropped.
        $validated['hobbies'] = collect(explode(',', (string) ($validated['hobbies'] ?? '')))
            ->map(fn (string $hobby) => trim($hobby))
            ->filter()
            ->values()
            ->all();

        return $validated;
    }

    /**
     * Slugs are unique per organization - two schools may each have a "budi-santoso".
     */
    private function uniqueSlug(Organization $organization, string $name, ?Student $ignore = null): string
    {
        $base = str($name)->slug();
        $slug = (string) $base;
        $suffix = 1;

        while ($organization->students()
            ->where('slug', $slug)
            ->when($ignore?->exists, fn ($query) => $query->whereKeyNot($ignore->getKey()))
            ->exists()
        ) {
            $slug = "{$base}-".++$suffix;
        }

        return $slug;
    }

    private function ensureBelongsToOrganization(Organization $organization, Student $student): void
    {
        abort_unless($student->organization_id === $organization->id, 404);
    }
}
