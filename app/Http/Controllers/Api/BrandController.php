<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function index()
    {
        $brands = Brand::where('is_active', true)->get();
        return response()->json($brands);

        // api resource
    }
    
    public function products(Brand $brand)
    {
        $products = $brand->products()->with('category')->paginate(20);
        return response()->json($products);
    }

}
