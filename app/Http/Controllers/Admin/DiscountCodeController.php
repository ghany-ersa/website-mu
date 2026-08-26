<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDiscountCodeRequest;
use App\Http\Requests\UpdateDiscountCodeRequest;
use App\Models\DiscountCode;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DiscountCodeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $search = trim((string) request('q'));

        $discountCodes = DiscountCode::query()
            ->when($search !== '', fn ($query) => $query->where('code', 'like', "%{$search}%"))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.discount-codes.index', ['discountCodes' => $discountCodes]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.discount-codes.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDiscountCodeRequest $request): RedirectResponse
    {
        $discountCode = DiscountCode::create($this->prepare($request));

        return redirect()
            ->route('admin.discount-codes.edit', $discountCode)
            ->with('status', 'Kode diskon berhasil dibuat.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DiscountCode $discountCode): View
    {
        return view('admin.discount-codes.edit', ['discountCode' => $discountCode]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDiscountCodeRequest $request, DiscountCode $discountCode): RedirectResponse
    {
        $discountCode->update($this->prepare($request));

        return redirect()
            ->route('admin.discount-codes.edit', $discountCode)
            ->with('status', 'Kode diskon berhasil disimpan.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DiscountCode $discountCode): RedirectResponse
    {
        abort_if($discountCode->used_count > 0, 409, 'Kode yang sudah pernah dipakai tidak dapat dihapus — nonaktifkan saja agar riwayat penggunaan tetap utuh.');

        $discountCode->delete();

        return redirect()
            ->route('admin.discount-codes.index')
            ->with('status', 'Kode diskon berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function prepare(StoreDiscountCodeRequest|UpdateDiscountCodeRequest $request): array
    {
        return [
            ...$request->safe()->only(['code', 'type', 'value', 'max_uses', 'valid_from', 'valid_until']),
            'is_active' => $request->boolean('is_active'),
        ];
    }
}
