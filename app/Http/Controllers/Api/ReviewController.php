<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Product;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    // প্রোডাক্টের সব রিভিউ দেখা
    public function index($productId)
    {
        $product = Product::findOrFail($productId);
        
        $reviews = Review::with('user')
            ->where('product_id', $productId)
            ->approved()
            ->latest()
            ->paginate(10);
        
        return response()->json([
            'average_rating' => $product->average_rating,
            'total_reviews' => $product->reviews_count,
            'rating_distribution' => $product->rating_distribution,
            'reviews' => $reviews
        ]);
    }
    
    // রিভিউ দেওয়া
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'nullable|string|max:255',
            'comment' => 'required|string|min:5'
        ]);
        
        // আগে রিভিউ দিয়ে থাকলে আপডেট করবে
        $review = Review::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'product_id' => $request->product_id
            ],
            [
                'rating' => $request->rating,
                'title' => $request->title,
                'comment' => $request->comment,
                'is_approved' => false // নতুন রিভিউ মডারেশনের জন্য পেন্ডিং
            ]
        );
        
        return response()->json([
            'message' => 'Review submitted successfully. Waiting for approval.',
            'review' => $review
        ], 201);
    }
    
    // আমার দেওয়া রিভিউ দেখা
    public function myReviews(Request $request)
    {
        $reviews = Review::with('product')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(10);
        
        return response()->json($reviews);
    }
    
    // রিভিউ ডিলিট
    public function remove(Request $request, $id)
    {
        $review = Review::where('user_id', $request->user()->id)
            ->where('id', $id)
            ->firstOrFail();
        
        $review->delete();
        
        return response()->json(['message' => 'Review deleted successfully']);
    }
}

