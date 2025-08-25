<?php

namespace App\Http\Requests\Products;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->user()->hasBackendAccess();
    }

    public function rules()
    {
        return [
            'brand_id' => 'required|exists:brands,id',
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'model_year' => 'nullable|digits:4|min:2000|max:' . (date('Y') + 2),
            'sku' => 'nullable|string|unique:products,sku',
            'description' => 'nullable|string',
            'short_description' => 'nullable|string|max:500',
            'price_min' => 'nullable|numeric|min:0',
            'price_max' => 'nullable|numeric|min:0|gte:price_min',
            'retail_price' => 'nullable|numeric|min:0',
            'release_date' => 'nullable|date',
            'status' => 'required|in:active,discontinued,coming_soon,draft',
            'is_featured' => 'boolean',
            'main_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'gallery_images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'specifications' => 'nullable|array',
            'features' => 'nullable|array',
            'meta_title' => 'nullable|string|max:60',
            'meta_description' => 'nullable|string|max:160',
            'meta_keywords' => 'nullable|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'brand_id.required' => 'Please select a brand',
            'category_id.required' => 'Please select a category',
            'name.required' => 'Product name is required',
            'status.required' => 'Please select product status',
            'price_max.gte' => 'Maximum price must be greater than or equal to minimum price',
        ];
    }
}