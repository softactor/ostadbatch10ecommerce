<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // All products with filters
    public function index(Request $request)
    {
        $query = Product::with(['brand', 'category'])->where('is_active', true);
        
        if ($request->brand_id) {
            $query->where('brand_id', $request->brand_id);
        }
        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }
        
        $products = $query->paginate(20);
        return response()->json($products);
    }
    
    // Latest products
    public function latest()
    {
        $products = Product::with(['brand', 'category'])->latest()->limit(10)->get();
        return response()->json($products);
    }
    
    // Popular products
    public function popular()
    {
        $products = Product::with(['brand', 'category'])->popular()->limit(10)->get();
        return response()->json($products);
    }
    
    // Featured products
    public function featured()
    {
        $products = Product::with(['brand', 'category'])->featured()->latest()->limit(10)->get();
        return response()->json($products);
    }
    
    // Single product details
    public function show(Product $product)
    {
        $product->load(['brand', 'category']);
        
        // Increment view count
        $product->increment('view_count');
        
        // Related products (same category)
        $related = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->latest()
            ->limit(5)
            ->get();
        
        return response()->json([
            'product' => $product,
            'related_products' => $related
        ]);
    }

}
