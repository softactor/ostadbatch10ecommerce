<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::with('children')->whereNull('parent_id')->get();
        return response()->json($categories);
    }
    
    public function products(Category $category)
    {
        $products = $category->products()->with('brand')->paginate(20);
        return response()->json($products);
    }

}
