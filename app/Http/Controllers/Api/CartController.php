<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    // View cart
    public function index(Request $request)
    {
        $cart = Cart::with('product')
        ->where('user_id', $request->user()->id)
        ->get();
        
        $total = $cart->sum(function($item) {
            return $item->quantity * $item->product->price;
        });
        
        return response()->json([
            'items' => $cart,
            'total_items' => $cart->sum('quantity'),
            'total_price' => $total
        ]);
    }
    
    // Add to cart
    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1'
        ]);
        
        $cart = Cart::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'product_id' => $request->product_id
            ],
            [
                'quantity' => DB::raw('quantity + ' . $request->quantity)
            ]
        );
        
        return response()->json(['message' => 'Added to cart', 'cart' => $cart]);
    }
    
    // Update quantity
    public function update(Request $request, Cart $cart)
    {
        if ($cart->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $request->validate(['quantity' => 'required|integer|min:1']);
        $cart->update(['quantity' => $request->quantity]);
        
        return response()->json(['message' => 'Cart updated', 'cart' => $cart]);
    }
    
    // Remove item
    public function remove(Request $request, Cart $cart)
    {
        if ($cart->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $cart->delete();
        return response()->json(['message' => 'Item removed from cart']);
    }
    
    // Clear cart
    public function clear(Request $request)
    {
        Cart::where('user_id', $request->user()->id)->delete();
        return response()->json(['message' => 'Cart cleared']);
    }
}