<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RateLimitHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Laravel automatski dodaje X-RateLimit-Limit i X-RateLimit-Remaining
        // kad koristiš throttle middleware. Ovde dodajemo Retry-After header
        // kad se limit prekorači (429 status).
        if ($response->getStatusCode() === 429) {
            $response->headers->set('Retry-After', '60');
        }

        return $response;
    }
}
