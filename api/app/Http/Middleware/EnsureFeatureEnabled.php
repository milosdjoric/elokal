<?php

namespace App\Http\Middleware;

use App\Enums\FeatureFlag;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureFeatureEnabled
{
    /**
     * Blokira rutu kad je feature flag ugašen.
     * Upotreba: ->middleware('feature:webhooks') → proverava 'feature_webhooks'.
     * Nepoznat flag = greška u konfiguraciji rute → 500, ne tihi 403.
     */
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $flag = FeatureFlag::tryFrom("feature_{$feature}");

        abort_unless($flag !== null, 500, "Nepoznat feature flag: feature_{$feature}");

        if (! feature($flag)) {
            return response()->json([
                'message' => 'Funkcija je isključena.',
            ], 403);
        }

        return $next($request);
    }
}
