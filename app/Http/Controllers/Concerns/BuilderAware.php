<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;

/**
 * Shared logic for CRUD controllers whose index/create/edit pages can be reached either
 * standalone or from the page builder's "Kelola X" links (?from=builder&section=...), and
 * whose redirects must preserve that context on the way back.
 */
trait BuilderAware
{
    protected function fromBuilder(Request $request): bool
    {
        return $request->input('from') === 'builder';
    }

    /**
     * @return array<string, mixed>
     */
    protected function builderIndexParams(Request $request, array $extra = []): array
    {
        return $this->fromBuilder($request)
            ? [...$extra, 'from' => 'builder', 'section' => $request->input('section')]
            : $extra;
    }

    /**
     * @return array{fromBuilder: bool, builderQuery: string}
     */
    protected function builderViewData(Request $request): array
    {
        $fromBuilder = $this->fromBuilder($request);

        return [
            'fromBuilder' => $fromBuilder,
            'builderQuery' => $fromBuilder
                ? '?from=builder'.($request->filled('section') ? '&section='.$request->input('section') : '')
                : '',
        ];
    }
}
