<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class ProductUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'brand_id' => ['nullable', 'exists:brands,id'],
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'short_description' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],

            'selling_price' => ['required', 'numeric', 'min:0'],
            'stock' => ['nullable', 'numeric', 'min:0'],

            'is_active' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],

            'featured_image' => ['nullable', 'string', 'max:255'],
        ];
    }
}