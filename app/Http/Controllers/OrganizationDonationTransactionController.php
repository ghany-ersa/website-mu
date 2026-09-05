<?php

namespace App\Http\Controllers;

use App\Models\DonationProgram;
use App\Models\DonationTransaction;
use App\Models\Organization;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrganizationDonationTransactionController extends Controller
{
    /**
     * Transactions are recorded manually by the organization's own admins (this isn't a
     * payment gateway integration - see the exclusive-template plan's scope), so unlike
     * DonationProgram itself, transactions aren't plan-limited: capping how many donations
     * an org can *record* would misrepresent real money that came in.
     */
    public function create(Request $request, Organization $organization, DonationProgram $donation): View
    {
        $this->authorize('update', $donation);
        $this->ensureBelongsToOrganization($organization, $donation);

        return view('organizations.donations.transactions.form', [
            'organization' => $organization,
            'donation' => $donation,
        ]);
    }

    public function store(Request $request, Organization $organization, DonationProgram $donation): RedirectResponse
    {
        $this->authorize('update', $donation);
        $this->ensureBelongsToOrganization($organization, $donation);

        $donation->transactions()->create($this->validated($request));

        return redirect()
            ->route('organizations.donations.edit', [$organization, $donation])
            ->with('status', 'Transaksi donasi berhasil dicatat.');
    }

    public function destroy(Organization $organization, DonationProgram $donation, DonationTransaction $transaction): RedirectResponse
    {
        $this->authorize('update', $donation);
        $this->ensureBelongsToOrganization($organization, $donation);
        abort_unless($transaction->donation_program_id === $donation->id, 404);

        $transaction->delete();

        return redirect()
            ->route('organizations.donations.edit', [$organization, $donation])
            ->with('status', 'Transaksi donasi berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'donor_name' => ['nullable', 'string', 'max:255'],
            'amount' => ['required', 'integer', 'min:1'],
            'donated_at' => ['required', 'date'],
        ]);
    }

    private function ensureBelongsToOrganization(Organization $organization, DonationProgram $donation): void
    {
        abort_unless($donation->organization_id === $organization->id, 404);
    }
}
