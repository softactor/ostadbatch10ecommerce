<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use App\Models\Product;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    // আমার উইশলিস্ট দেখা
    public function index(Request $request)
    {
        $wishlists = Wishlist::with('product')
            ->where('user_id', $request->user()->id)
            ->get();
        
        return response()->json([
            'count' => $wishlists->count(),
            'items' => $wishlists
        ]);
    }
    
    // উইশলিস্টে প্রোডাক্ট যোগ করা
    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id'
        ]);
        
        // আগে থেকে আছে কিনা চেক
        $exists = Wishlist::where('user_id', $request->user()->id)
            ->where('product_id', $request->product_id)
            ->exists();
        
        if ($exists) {
            return response()->json([
                'message' => 'Product already in wishlist'
            ], 409);
        }
        
        $wishlist = Wishlist::create([
            'user_id' => $request->user()->id,
            'product_id' => $request->product_id
        ]);
        
        return response()->json([
            'message' => 'Added to wishlist',
            'item' => $wishlist
        ], 201);
    }
    
    // উইশলিস্ট থেকে রিমুভ
    public function remove(Request $request, $productId)
    {
        $deleted = Wishlist::where('user_id', $request->user()->id)
            ->where('product_id', $productId)
            ->delete();
        
        if ($deleted) {
            return response()->json(['message' => 'Removed from wishlist']);
        }
        
        return response()->json(['message' => 'Item not found'], 404);
    }
    
    // চেক করা - প্রোডাক্টটি উইশলিস্টে আছে কিনা
    public function check(Request $request, $productId)
    {
        $exists = Wishlist::where('user_id', $request->user()->id)
            ->where('product_id', $productId)
            ->exists();
        
        return response()->json(['is_wishlisted' => $exists]);
    }

}
