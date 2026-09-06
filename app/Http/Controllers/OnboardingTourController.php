<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OnboardingTourController extends Controller
{
    /**
     * Mark an onboarding tour as seen for the current user.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tour' => ['required', 'string', 'in:dashboard,builder'],
        ]);

        $request->user()->markOnboardingTourSeen($validated['tour']);

        return response()->json(['status' => 'ok']);
    }
}
