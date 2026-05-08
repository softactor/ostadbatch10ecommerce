<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
     public function toArray(Request $request): array
    {
        $imagePath = $this->featured_image ?? $this->image ?? null;

        $imageFullUrl = $imagePath
            ? asset(Storage::url($imagePath))
            : asset('images/no-image.png');

        $price = $this->selling_price ?? $this->price ?? 0;

        return [
            'id' => $this->id,

            'category_id' => $this->category_id,
            'brand_id' => $this->brand_id,

            'name' => $this->name,
            'slug' => $this->slug,
            'sku' => $this->sku,

            'short_description' => $this->short_description,
            'description' => $this->description,

            // Frontend compatible fields
            'price' => number_format((float) $price, 2, '.', ''),
            'stock' => (float) ($this->stock ?? $this->available_stock ?? 0),
            'status' => $this->is_active ? 'Active' : 'Inactive',

            // Original fields
            'selling_price' => $this->selling_price,
            'last_purchase_price' => $this->last_purchase_price,
            'last_landed_cost' => $this->last_landed_cost,
            'last_landed_cost_per_base_unit' => $this->last_landed_cost_per_base_unit,

            'featured_image' => $imagePath,
            'image_url' => $imageFullUrl,
            'image_full_url' => $imageFullUrl,

            'is_active' => (bool) $this->is_active,
            'is_featured' => (bool) $this->is_featured,
            'view_count' => $this->view_count,

            'brand' => $this->whenLoaded('brand', function () {
                return [
                    'id' => $this->brand?->id,
                    'name' => $this->brand?->name,
                ];
            }),

            'category' => $this->whenLoaded('category', function () {
                return [
                    'id' => $this->category?->id,
                    'name' => $this->category?->name,
                ];
            }),

            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
