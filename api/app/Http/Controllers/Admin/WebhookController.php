<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Webhook;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['data' => Webhook::withCount('logs')->get()]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'url' => 'required|url|max:500',
            'events' => 'required|array|min:1',
            'events.*' => 'string',
            'is_active' => 'boolean',
        ]);

        $data['secret'] = Webhook::generateSecret();

        return response()->json(['data' => Webhook::create($data)], 201);
    }

    public function update(Request $request, Webhook $webhook): JsonResponse
    {
        $data = $request->validate([
            'url' => 'sometimes|url|max:500',
            'events' => 'sometimes|array|min:1',
            'is_active' => 'boolean',
        ]);

        $webhook->update($data);
        return response()->json(['data' => $webhook]);
    }

    public function destroy(Webhook $webhook): JsonResponse
    {
        $webhook->delete();
        return response()->json(['message' => 'Webhook obrisan.']);
    }

    public function logs(Webhook $webhook): JsonResponse
    {
        return response()->json(['data' => $webhook->logs()->limit(50)->get()]);
    }

    public function test(Webhook $webhook): JsonResponse
    {
        // Simulira test event
        $webhook->logs()->create([
            'event' => 'test',
            'status_code' => 200,
            'response_body' => '{"ok": true}',
            'duration_ms' => 120,
            'success' => true,
        ]);

        $webhook->update(['last_triggered_at' => now()]);

        return response()->json(['message' => 'Test webhook poslat.']);
    }
}
