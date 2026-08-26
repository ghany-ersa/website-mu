<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePlanRequest;
use App\Http\Requests\UpdatePlanRequest;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PlanController extends Controller
{
    /**
     * Resource keys every plan needs a limit row for — see
     * App\Services\PlanLimitService::RESOURCE_RELATIONS and 'sections_total'.
     *
     * @var array<string, string>
     */
    private const LIMIT_KEYS = [
        'posts' => 'Berita',
        'agendas' => 'Agenda',
        'announcements' => 'Pengumuman',
        'officers' => 'Data Pengurus',
        'programs' => 'Program/Layanan',
        'gallery_photos' => 'Foto Galeri',
        'sections_total' => 'Komponen di Situs (Total)',
    ];

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $plans = Plan::with(['limits', 'components'])->orderBy('price_monthly')->get();

        return view('admin.plans.index', ['plans' => $plans]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.plans.create', [
            'limitKeys' => self::LIMIT_KEYS,
            'sectionKeys' => $this->gatableSectionKeys(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePlanRequest $request): RedirectResponse
    {
        $plan = DB::transaction(function () use ($request) {
            $plan = Plan::create($this->prepare($request));

            $this->syncLimits($plan, $request->validated('limits', []));
            $this->syncComponents($plan, $request->validated('components', []));

            return $plan;
        });

        return redirect()
            ->route('admin.plans.edit', $plan)
            ->with('status', 'Paket berhasil dibuat.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Plan $plan): View
    {
        $plan->load(['limits', 'components']);

        return view('admin.plans.edit', [
            'plan' => $plan,
            'limitKeys' => self::LIMIT_KEYS,
            'sectionKeys' => $this->gatableSectionKeys(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePlanRequest $request, Plan $plan): RedirectResponse
    {
        DB::transaction(function () use ($request, $plan) {
            $plan->update($this->prepare($request));

            $this->syncLimits($plan, $request->validated('limits', []));
            $this->syncComponents($plan, $request->validated('components', []));
        });

        return redirect()
            ->route('admin.plans.edit', $plan)
            ->with('status', 'Paket berhasil disimpan.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Plan $plan): RedirectResponse
    {
        abort_if($plan->organizations()->exists(), 409, 'Paket ini masih dipakai organisasi — pindahkan mereka ke paket lain sebelum menghapus.');

        $plan->delete();

        return redirect()
            ->route('admin.plans.index')
            ->with('status', 'Paket berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function prepare(StorePlanRequest|UpdatePlanRequest $request): array
    {
        return [
            ...$request->safe()->only(['key', 'name', 'description', 'price_monthly']),
            'is_active' => $request->boolean('is_active'),
        ];
    }

    /**
     * Replaces the plan's limits with the submitted values. An empty/missing field means
     * unlimited (max_count = null) for that key — see PlanLimitService::canCreate().
     *
     * @param  array<string, string|null>  $limits
     */
    private function syncLimits(Plan $plan, array $limits): void
    {
        $plan->limits()->delete();

        $plan->limits()->createMany(
            collect(self::LIMIT_KEYS)->keys()->map(fn (string $key) => [
                'key' => $key,
                'max_count' => filled($limits[$key] ?? null) ? (int) $limits[$key] : null,
            ])->all()
        );
    }

    /**
     * Replaces the plan's component gates. Only the section keys submitted as "not allowed"
     * get a row — everything else stays allowed by default (opt-out model, see
     * PlanComponent migration / PlanLimitService::canUseSection()).
     *
     * @param  array<int, string>  $deniedComponentKeys
     */
    private function syncComponents(Plan $plan, array $deniedComponentKeys): void
    {
        $plan->components()->delete();

        $validKeys = array_intersect($deniedComponentKeys, array_keys($this->gatableSectionKeys()));

        $plan->components()->createMany(
            collect($validKeys)->map(fn (string $key) => [
                'component_key' => $key,
                'is_allowed' => false,
            ])->all()
        );
    }

    /**
     * Section keys the builder can gate — every registry entry except locked ones
     * (header/footer), which PlanLimitService::canUseSection() always allows regardless.
     *
     * @return array<string, string>
     */
    private function gatableSectionKeys(): array
    {
        return collect(config('page-builder.sections'))
            ->reject(fn (array $section) => $section['locked'] ?? false)
            ->map(fn (array $section) => $section['label'])
            ->all();
    }
}
