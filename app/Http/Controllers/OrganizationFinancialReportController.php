<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\BuilderAware;
use App\Models\FinancialReport;
use App\Models\Organization;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class OrganizationFinancialReportController extends Controller
{
    use BuilderAware;

    /**
     * Not plan-limited (see PlanLimitService/Organization::planViolations()) - this is a
     * routine monthly administrative record, not seed/sample content, so capping it would
     * eventually block an organization from recording its own past months' books.
     */
    public function index(Request $request, Organization $organization): View
    {
        abort_unless($organization->hasSection('laporan-keuangan'), 404);
        $this->authorize('viewAny', [FinancialReport::class, $organization]);

        $reports = $organization->financialReports()
            ->orderByDesc('period_year')
            ->orderByDesc('period_month')
            // Dated entries newest-first within their month; undated monthly recaps (which have
            // no transacted_at) sort last.
            ->orderByRaw('transacted_at IS NULL, transacted_at DESC')
            ->get()
            ->groupBy(fn (FinancialReport $report) => $report->period_year.'-'.$report->period_month);

        return view('organizations.financial-reports.index', [
            'organization' => $organization,
            'groupedReports' => $reports,
            ...$this->builderViewData($request),
        ]);
    }

    public function create(Request $request, Organization $organization): View
    {
        abort_unless($organization->hasSection('laporan-keuangan'), 404);
        $this->authorize('create', [FinancialReport::class, $organization]);

        return view('organizations.financial-reports.form', [
            'organization' => $organization,
            'report' => new FinancialReport,
            ...$this->builderViewData($request),
        ]);
    }

    public function store(Request $request, Organization $organization): RedirectResponse
    {
        abort_unless($organization->hasSection('laporan-keuangan'), 404);
        $this->authorize('create', [FinancialReport::class, $organization]);

        $organization->financialReports()->create($this->validated($request));

        return redirect()
            ->route('organizations.financial-reports.index', $this->builderIndexParams($request, ['organization' => $organization]))
            ->with('status', 'Laporan keuangan berhasil ditambahkan.');
    }

    public function edit(Request $request, Organization $organization, FinancialReport $financialReport): View
    {
        $this->authorize('update', $financialReport);
        $this->ensureBelongsToOrganization($organization, $financialReport);

        return view('organizations.financial-reports.form', [
            'organization' => $organization,
            'report' => $financialReport,
            ...$this->builderViewData($request),
        ]);
    }

    public function update(Request $request, Organization $organization, FinancialReport $financialReport): RedirectResponse
    {
        $this->authorize('update', $financialReport);
        $this->ensureBelongsToOrganization($organization, $financialReport);

        $financialReport->update($this->validated($request));

        return redirect()
            ->route('organizations.financial-reports.index', $this->builderIndexParams($request, ['organization' => $organization]))
            ->with('status', 'Laporan keuangan berhasil diperbarui.');
    }

    public function destroy(Request $request, Organization $organization, FinancialReport $financialReport): RedirectResponse
    {
        $this->authorize('delete', $financialReport);
        $this->ensureBelongsToOrganization($organization, $financialReport);

        $financialReport->delete();

        return redirect()
            ->route('organizations.financial-reports.index', $this->builderIndexParams($request, ['organization' => $organization]))
            ->with('status', 'Laporan keuangan berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'transacted_at' => ['required', 'date'],
            'type' => ['required', 'in:income,expense'],
            'category' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'integer', 'min:0'],
        ]);

        // period_month/period_year are derived from the date rather than entered separately:
        // they drive the monthly grouping on both this page and the public section, and two
        // independent inputs could disagree with each other.
        $date = Carbon::parse($validated['transacted_at']);

        return [
            ...$validated,
            'period_month' => $date->month,
            'period_year' => $date->year,
        ];
    }

    private function ensureBelongsToOrganization(Organization $organization, FinancialReport $financialReport): void
    {
        abort_unless($financialReport->organization_id === $organization->id, 404);
    }
}
