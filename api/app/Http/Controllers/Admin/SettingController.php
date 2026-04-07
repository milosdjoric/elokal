<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index(): JsonResponse
    {
        $settings = Setting::all()->groupBy('group')->map(function ($items) {
            return $items->pluck('value', 'key');
        });

        return response()->json(['data' => $settings]);
    }

    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'settings' => 'required|array',
            'settings.*.group' => 'required|string',
            'settings.*.key' => 'required|string',
            'settings.*.value' => 'nullable|string',
        ]);

        foreach ($request->settings as $item) {
            Setting::setValue($item['group'], $item['key'], $item['value']);
        }

        return response()->json(['message' => 'Podešavanja sačuvana.']);
    }
}
