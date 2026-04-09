<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class CacheResponse
{
    public function handle(Request $request, Closure $next, int $minutes = 5): Response
    {
        // Samo GET zahtevi se kešuju
        if ($request->method() !== 'GET') {
            return $next($request);
        }

        $cacheKey = 'response_cache:' . md5($request->fullUrl());

        if (Cache::has($cacheKey)) {
            $cached = Cache::get($cacheKey);
            return response($cached['content'], 200)
                ->header('Content-Type', 'application/json')
                ->header('X-Cache', 'HIT');
        }

        $response = $next($request);

        if ($response->getStatusCode() === 200) {
            Cache::put($cacheKey, [
                'content' => $response->getContent(),
            ], now()->addMinutes($minutes));

            $response->headers->set('X-Cache', 'MISS');
        }

        return $response;
    }
}
