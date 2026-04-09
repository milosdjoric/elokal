<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PageSection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PageSectionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $pageKey = $request->input('page_key', 'homepage');
        $sections = PageSection::where('page_key', $pageKey)->orderBy('sort_order')->get();

        return response()->json([
            'data' => $sections,
            'types' => PageSection::TYPES,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'page_key' => 'required|string|max:100',
            'type' => 'required|in:' . implode(',', PageSection::TYPES),
            'data' => 'nullable|array',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);

        $section = PageSection::create($data);

        return response()->json(['data' => $section], 201);
    }

    public function update(Request $request, PageSection $pageSection): JsonResponse
    {
        $data = $request->validate([
            'type' => 'sometimes|in:' . implode(',', PageSection::TYPES),
            'data' => 'nullable|array',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);

        $pageSection->update($data);

        return response()->json(['data' => $pageSection]);
    }

    public function destroy(PageSection $pageSection): JsonResponse
    {
        $pageSection->delete();

        return response()->json(['message' => 'Sekcija obrisana.']);
    }

    public function reorder(Request $request): JsonResponse
    {
        $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:page_sections,id',
        ]);

        foreach ($request->order as $index => $id) {
            PageSection::where('id', $id)->update(['sort_order' => $index]);
        }

        return response()->json(['message' => 'Redosled sačuvan.']);
    }
}
