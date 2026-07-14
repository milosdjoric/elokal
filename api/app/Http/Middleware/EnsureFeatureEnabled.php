<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureFeatureEnabled
{
    /**
     * Blokira rutu kad je feature flag ugašen.
     * Upotreba: ->middleware('feature:webhooks') → proverava 'feature_webhooks'.
     */
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        if (! feature("feature_{$feature}")) {
            return response()->json([
                'message' => 'Funkcija je isključena.',
            ], 403);
        }

        return $next($request);
    }
}
