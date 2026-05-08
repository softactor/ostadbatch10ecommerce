<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ProductUpdateRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['brand', 'category']);

        if (! $request->boolean('admin')) {
            $query->where('is_active', true);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $products = $query->latest()->paginate(20);

        return ProductResource::collection($products);
    }

    public function latest()
    {
        $products = Product::with(['brand', 'category'])
            ->where('is_active', true)
            ->latest()
            ->limit(10)
            ->get();

        return ProductResource::collection($products);
    }

    public function popular()
    {
        $products = Product::with(['brand', 'category'])
            ->where('is_active', true)
            ->popular()
            ->limit(10)
            ->get();

        return ProductResource::collection($products);
    }

    public function featured()
    {
        $products = Product::with(['brand', 'category'])
            ->where('is_active', true)
            ->featured()
            ->latest()
            ->limit(10)
            ->get();

        return ProductResource::collection($products);
    }

    public function show(Product $product)
    {
        abort_if(! $product->is_active, 404);

        $product->load(['brand', 'category']);

        $product->increment('view_count');

        $related = Product::with(['brand', 'category'])
            ->where('is_active', true)
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->latest()
            ->limit(5)
            ->get();

        return response()->json([
            'product' => new ProductResource($product),
            'related_products' => ProductResource::collection($related),
        ]);
    }

    public function update(ProductUpdateRequest $request, Product $product)
    {
        $data = $request->validated();

        $data['slug'] = Str::slug($data['name']);
        $data['is_active'] = $request->boolean('is_active');
        $data['is_featured'] = $request->boolean('is_featured');

        $product->update($data);

        $product->load(['brand', 'category']);

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully.',
            'data' => new ProductResource($product),
        ]);
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully.',
        ]);
    }
}