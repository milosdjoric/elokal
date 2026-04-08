<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductVariantResource;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VariantController extends Controller
{
    public function index(Product $product): JsonResponse
    {
        $variants = $product->variants()
            ->with(['attributeValues.attribute', 'images'])
            ->get();

        return response()->json(['data' => ProductVariantResource::collection($variants)]);
    }

    public function store(Request $request, Product $product): JsonResponse
    {
        $data = $request->validate([
            'sku' => 'nullable|string|max:100|unique:product_variants',
            'price' => 'nullable|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'weight' => 'nullable|numeric|min:0',
            'stock_quantity' => 'integer|min:0',
            'is_active' => 'boolean',
            'attribute_value_ids' => 'required|array|min:1',
            'attribute_value_ids.*' => 'exists:attribute_values,id',
            'image_ids' => 'nullable|array',
            'image_ids.*' => 'exists:product_images,id',
        ]);

        $variant = $product->variants()->create($data);

        $variant->attributeValues()->sync($data['attribute_value_ids']);
        if (! empty($data['image_ids'])) {
            $variant->images()->sync($data['image_ids']);
        }

        $variant->load(['attributeValues.attribute', 'images']);

        return (new ProductVariantResource($variant))
            ->response()
            ->setStatusCode(201);
    }

    public function update(Request $request, ProductVariant $variant): JsonResponse
    {
        $data = $request->validate([
            'sku' => "nullable|string|max:100|unique:product_variants,sku,{$variant->id}",
            'price' => 'nullable|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'weight' => 'nullable|numeric|min:0',
            'stock_quantity' => 'integer|min:0',
            'is_active' => 'boolean',
            'attribute_value_ids' => 'sometimes|array|min:1',
            'attribute_value_ids.*' => 'exists:attribute_values,id',
            'image_ids' => 'nullable|array',
            'image_ids.*' => 'exists:product_images,id',
        ]);

        $variant->update($data);

        if (isset($data['attribute_value_ids'])) {
            $variant->attributeValues()->sync($data['attribute_value_ids']);
        }
        if (array_key_exists('image_ids', $data)) {
            $variant->images()->sync($data['image_ids'] ?? []);
        }

        $variant->load(['attributeValues.attribute', 'images']);

        return response()->json(['data' => new ProductVariantResource($variant)]);
    }

    public function destroy(ProductVariant $variant): JsonResponse
    {
        $variant->delete();

        return response()->json(['message' => 'Varijanta obrisana.']);
    }

    public function duplicate(ProductVariant $variant): JsonResponse
    {
        $newSku = $variant->sku ? $variant->sku . '-copy' : null;

        $newVariant = $variant->product->variants()->create([
            'sku' => $newSku,
            'price' => $variant->price,
            'sale_price' => $variant->sale_price,
            'weight' => $variant->weight,
            'stock_quantity' => $variant->stock_quantity,
            'is_active' => $variant->is_active,
        ]);

        $newVariant->attributeValues()->sync($variant->attributeValues->pluck('id'));
        $newVariant->images()->sync($variant->images->pluck('id'));

        $newVariant->load(['attributeValues.attribute', 'images']);

        return (new ProductVariantResource($newVariant))
            ->response()
            ->setStatusCode(201);
    }

    public function bulkUpdate(Request $request, Product $product): JsonResponse
    {
        $request->validate([
            'variants' => 'required|array',
            'variants.*.id' => 'required|exists:product_variants,id',
            'variants.*.price' => 'nullable|numeric|min:0',
            'variants.*.stock_quantity' => 'integer|min:0',
            'variants.*.sku' => 'nullable|string|max:100',
            'variants.*.is_active' => 'boolean',
            'variants.*.image_ids' => 'nullable|array',
            'variants.*.image_ids.*' => 'exists:product_images,id',
        ]);

        foreach ($request->variants as $data) {
            $variant = $product->variants()->findOrFail($data['id']);
            $variant->update(collect($data)->except(['id', 'image_ids'])->toArray());
            if (array_key_exists('image_ids', $data)) {
                $variant->images()->sync($data['image_ids'] ?? []);
            }
        }

        $variants = $product->variants()->with(['attributeValues.attribute', 'images'])->get();

        return response()->json([
            'data' => ProductVariantResource::collection($variants),
        ]);
    }
}
