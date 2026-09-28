<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\BuilderAware;
use App\Models\Organization;
use App\Models\Testimonial;
use App\Services\PlanLimitService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * CRUD for the quotes behind the `testimoni` section. Mirrors
 * OrganizationAchievementController: every action is gated on the organization actually having
 * the section (hasSection), so the screen can't be reached for a site that doesn't show it.
 */
class OrganizationTestimonialController extends Controller
{
    use BuilderAware;

    private const LIMIT_REACHED = 'Batas jumlah testimoni paket Anda sudah tercapai. Upgrade paket untuk menambah lagi.';

    public function __construct(private readonly PlanLimitService $planLimitService) {}

    public function index(Request $request, Organization $organization): View
    {
        abort_unless($organization->hasSection('testimoni'), 404);
        $this->authorize('viewAny', [Testimonial::class, $organization]);

        return view('organizations.testimonials.index', [
            'organization' => $organization,
            'testimonials' => $organization->testimonials()->get(),
            ...$this->builderViewData($request),
        ]);
    }

    public function create(Request $request, Organization $organization): View|RedirectResponse
    {
        abort_unless($organization->hasSection('testimoni'), 404);
        $this->authorize('create', [Testimonial::class, $organization]);

        if (! $this->planLimitService->canCreate($organization, 'testimonials')) {
            return redirect()
                ->route('organizations.testimonials.index', $organization)
                ->with('warning', self::LIMIT_REACHED);
        }

        return view('organizations.testimonials.form', [
            'organization' => $organization,
            'testimonial' => new Testimonial,
            ...$this->builderViewData($request),
        ]);
    }

    public function store(Request $request, Organization $organization): RedirectResponse
    {
        abort_unless($organization->hasSection('testimoni'), 404);
        $this->authorize('create', [Testimonial::class, $organization]);

        if (! $this->planLimitService->canCreate($organization, 'testimonials')) {
            return redirect()
                ->route('organizations.testimonials.index', $organization)
                ->with('warning', self::LIMIT_REACHED);
        }

        $organization->testimonials()->create([
            ...$this->validated($request),
            'order' => (int) $organization->testimonials()->max('order') + 1,
        ]);

        return redirect()
            ->route('organizations.testimonials.index', $organization)
            ->with('status', 'Testimoni berhasil ditambahkan.');
    }

    public function edit(Request $request, Organization $organization, Testimonial $testimonial): View
    {
        abort_unless($organization->hasSection('testimoni'), 404);
        $this->ensureBelongsToOrganization($organization, $testimonial);
        $this->authorize('update', [$testimonial, $organization]);

        return view('organizations.testimonials.form', [
            'organization' => $organization,
            'testimonial' => $testimonial,
            ...$this->builderViewData($request),
        ]);
    }

    public function update(Request $request, Organization $organization, Testimonial $testimonial): RedirectResponse
    {
        abort_unless($organization->hasSection('testimoni'), 404);
        $this->ensureBelongsToOrganization($organization, $testimonial);
        $this->authorize('update', [$testimonial, $organization]);

        $testimonial->update($this->validated($request));

        return redirect()
            ->route('organizations.testimonials.index', $organization)
            ->with('status', 'Testimoni berhasil diperbarui.');
    }

    public function destroy(Organization $organization, Testimonial $testimonial): RedirectResponse
    {
        abort_unless($organization->hasSection('testimoni'), 404);
        $this->ensureBelongsToOrganization($organization, $testimonial);
        $this->authorize('delete', [$testimonial, $organization]);

        $testimonial->delete();

        return redirect()
            ->route('organizations.testimonials.index', $organization)
            ->with('status', 'Testimoni berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'quote' => ['required', 'string', 'max:1000'],
            'name' => ['required', 'string', 'max:255'],
            'role' => ['nullable', 'string', 'max:255'],
            'photo' => ['nullable', 'string', 'max:2048'],
            'is_active' => ['nullable', 'boolean'],
        ]) + ['is_active' => $request->boolean('is_active')];
    }

    private function ensureBelongsToOrganization(Organization $organization, Testimonial $testimonial): void
    {
        abort_unless($testimonial->organization_id === $organization->id, 404);
    }
}
