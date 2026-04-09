<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $checks = [
            'status' => 'ok',
            'timestamp' => now()->toIso8601String(),
            'checks' => [],
        ];

        // Database
        try {
            DB::connection()->getPdo();
            $checks['checks']['database'] = 'ok';
        } catch (\Exception $e) {
            $checks['checks']['database'] = 'error';
            $checks['status'] = 'degraded';
        }

        // Storage
        $storagePath = storage_path('app');
        $checks['checks']['storage'] = is_writable($storagePath) ? 'ok' : 'error';

        // Cache
        try {
            cache()->put('health_check', true, 10);
            $checks['checks']['cache'] = cache()->get('health_check') ? 'ok' : 'error';
        } catch (\Exception $e) {
            $checks['checks']['cache'] = 'error';
        }

        $statusCode = $checks['status'] === 'ok' ? 200 : 503;

        return response()->json($checks, $statusCode);
    }
}
