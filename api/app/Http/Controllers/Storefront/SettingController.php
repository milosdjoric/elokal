<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;

class SettingController extends Controller
{
    public function index(): JsonResponse
    {
        $publicGroups = ['general', 'storefront', 'topbar', 'trust', 'cart_checkout', 'badges', 'seo', 'gdpr', 'shipping'];

        $settings = Setting::whereIn('group', $publicGroups)
            ->get()
            ->groupBy('group')
            ->map(fn ($items) => $items->pluck('value', 'key'));

        return response()->json(['data' => $settings]);
    }
}
