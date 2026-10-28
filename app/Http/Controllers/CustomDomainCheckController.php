<?php

namespace App\Http\Controllers;

use App\Services\CustomDomainCheck;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Answers CustomDomainCheck's probe on whatever host the request arrived on - see that class
 * for why a token, not a database lookup, is the whole check.
 */
class CustomDomainCheckController extends Controller
{
    public function __invoke(Request $request): Response
    {
        return response(CustomDomainCheck::token($request->getHost()), 200, [
            'Content-Type' => 'text/plain',
            'Cache-Control' => 'no-store',
        ]);
    }
}
