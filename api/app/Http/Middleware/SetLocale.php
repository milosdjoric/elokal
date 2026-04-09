<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->header('X-Locale')
            ?? $request->query('locale')
            ?? config('app.locale', 'sr');

        if (in_array($locale, ['sr', 'en', 'de', 'fr'])) {
            app()->setLocale($locale);
        }

        return $next($request);
    }
}
