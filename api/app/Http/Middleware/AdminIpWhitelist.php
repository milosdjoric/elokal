<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminIpWhitelist
{
    public function handle(Request $request, Closure $next): Response
    {
        $whitelist = Setting::getValue('admin_ip_whitelist');

        // Ako nema whitelist, sve IP adrese su dozvoljene
        if (! $whitelist) {
            return $next($request);
        }

        $allowedIps = array_map('trim', explode(',', $whitelist));

        if (! in_array($request->ip(), $allowedIps)) {
            abort(403, 'Pristup nije dozvoljen sa vaše IP adrese.');
        }

        return $next($request);
    }
}
