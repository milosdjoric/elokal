<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:products,slug',
            'short_description' => 'nullable|string|max:500',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0|lt:price',
            'sale_price_from' => 'nullable|date',
            'sale_price_to' => 'nullable|date|after:sale_price_from',
            'cost_price' => 'nullable|numeric|min:0',
            'unit_price' => 'nullable|numeric|min:0',
            'unit_label' => 'nullable|string|max:50',
            'sku' => 'nullable|string|max:100|unique:products,sku',
            'stock_quantity' => 'integer|min:0',
            'is_active' => 'boolean',
            'featured' => 'boolean',
            'sort_order' => 'integer|min:0',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
        ];
    }
}
