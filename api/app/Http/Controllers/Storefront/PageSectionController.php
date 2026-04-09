<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\PageSection;
use Illuminate\Http\JsonResponse;

class PageSectionController extends Controller
{
    public function __invoke(string $pageKey = 'homepage'): JsonResponse
    {
        $sections = PageSection::forPage($pageKey)->get();

        return response()->json(['data' => $sections]);
    }
}
