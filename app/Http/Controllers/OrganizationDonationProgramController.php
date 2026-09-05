<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\BuilderAware;
use App\Models\DonationProgram;
use App\Models\Organization;
use App\Services\PlanLimitService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class OrganizationDonationProgramController extends Controller
{
    use BuilderAware;

    public function __construct(private readonly PlanLimitService $planLimitService) {}

    public function index(Request $request, Organization $organization): View
    {
        abort_unless($organization->hasSection('donasi-progress'), 404);
        $this->authorize('viewAny', [DonationProgram::class, $organization]);

        return view('organizations.donations.index', [
            'organization' => $organization,
            'programs' => $organization->donationPrograms()->withCount('transactions')->get(),
            ...$this->builderViewData($request),
        ]);
    }

    public function create(Request $request, Organization $organization): View|RedirectResponse
    {
        abort_unless($organization->hasSection('donasi-progress'), 404);
        $this->authorize('create', [DonationProgram::class, $organization]);

        if (! $this->planLimitService->canCreate($organization, 'donation_programs')) {
            return redirect()
                ->route('organizations.donations.index', $organization)
                ->with('warning', 'Batas jumlah program donasi paket Anda sudah tercapai. Upgrade paket untuk menambah lagi.');
        }

        return view('organizations.donations.form', [
            'organization' => $organization,
            'program' => new DonationProgram,
            ...$this->builderViewData($request),
        ]);
    }

    public function store(Request $request, Organization $organization): RedirectResponse
    {
        abort_unless($organization->hasSection('donasi-progress'), 404);
        $this->authorize('create', [DonationProgram::class, $organization]);

        if (! $this->planLimitService->canCreate($organization, 'donation_programs')) {
            return redirect()
                ->route('organizations.donations.index', $organization)
                ->with('warning', 'Batas jumlah program donasi paket Anda sudah tercapai. Upgrade paket untuk menambah lagi.');
        }

        $validated = $this->validated($request);

        $organization->donationPrograms()->create([
            ...$validated,
            'slug' => str($validated['name'])->slug().'-'.Str::random(6),
        ]);

        return redirect()
            ->route('organizations.donations.index', $this->builderIndexParams($request, ['organization' => $organization]))
            ->with('status', 'Program donasi berhasil ditambahkan.');
    }

    public function edit(Request $request, Organization $organization, DonationProgram $donation): View
    {
        $this->authorize('update', $donation);
        $this->ensureBelongsToOrganization($organization, $donation);

        return view('organizations.donations.form', [
            'organization' => $organization,
            'program' => $donation,
            ...$this->builderViewData($request),
        ]);
    }

    public function update(Request $request, Organization $organization, DonationProgram $donation): RedirectResponse
    {
        $this->authorize('update', $donation);
        $this->ensureBelongsToOrganization($organization, $donation);

        $donation->update($this->validated($request));

        return redirect()
            ->route('organizations.donations.index', $this->builderIndexParams($request, ['organization' => $organization]))
            ->with('status', 'Program donasi berhasil diperbarui.');
    }

    public function destroy(Request $request, Organization $organization, DonationProgram $donation): RedirectResponse
    {
        $this->authorize('delete', $donation);
        $this->ensureBelongsToOrganization($organization, $donation);

        $donation->delete();

        return redirect()
            ->route('organizations.donations.index', $this->builderIndexParams($request, ['organization' => $organization]))
            ->with('status', 'Program donasi berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'target_amount' => ['required', 'integer', 'min:1'],
            'cover_photo' => ['nullable', 'string'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ]);
    }

    private function ensureBelongsToOrganization(Organization $organization, DonationProgram $donation): void
    {
        abort_unless($donation->organization_id === $organization->id, 404);
    }
}
