<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AttributeController extends Controller
{
    public function index(): JsonResponse
    {
        $attributes = Attribute::with('values')->orderBy('sort_order')->get();

        return response()->json(['data' => $attributes]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:attributes',
            'type' => 'required|in:' . implode(',', Attribute::TYPES),
            'is_filterable' => 'boolean',
            'is_visible_on_card' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $attribute = Attribute::create($data);

        return response()->json(['data' => $attribute->load('values')], 201);
    }

    public function update(Request $request, Attribute $attribute): JsonResponse
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'slug' => "sometimes|string|max:255|unique:attributes,slug,{$attribute->id}",
            'type' => 'sometimes|in:' . implode(',', Attribute::TYPES),
            'is_filterable' => 'boolean',
            'is_visible_on_card' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        $attribute->update($data);

        return response()->json(['data' => $attribute->load('values')]);
    }

    public function destroy(Attribute $attribute): JsonResponse
    {
        $attribute->delete();

        return response()->json(['message' => 'Atribut obrisan.']);
    }

    // --- Values ---

    public function storeValue(Request $request, Attribute $attribute): JsonResponse
    {
        $data = $request->validate([
            'value' => 'required|string|max:255',
            'color_hex' => 'nullable|string|max:7',
            'image_path' => 'nullable|string|max:500',
            'sort_order' => 'integer|min:0',
        ]);

        $value = $attribute->values()->create($data);

        return response()->json(['data' => $value], 201);
    }

    public function updateValue(Request $request, AttributeValue $value): JsonResponse
    {
        $data = $request->validate([
            'value' => 'sometimes|string|max:255',
            'color_hex' => 'nullable|string|max:7',
            'image_path' => 'nullable|string|max:500',
            'sort_order' => 'integer|min:0',
        ]);

        $value->update($data);

        return response()->json(['data' => $value]);
    }

    public function destroyValue(AttributeValue $value): JsonResponse
    {
        $value->delete();

        return response()->json(['message' => 'Vrednost obrisana.']);
    }
}
