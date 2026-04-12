<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Raziul\Sslcommerz\Facades\Sslcommerz;

class PaymentController extends Controller
{
    // চেকআউট - অর্ডার তৈরি ও পেমেন্ট ইনির্শিয়েট
    public function checkout(Request $request)
    {
        $request->validate([
            'shipping_address' => 'required|string',
            'customer_name' => 'required|string',
            'customer_email' => 'required|email',
            'customer_phone' => 'required|string'
        ]);
        
        $user = User::find($request->user_id);// update with auth user
        
        // কার্ট থেকে আইটেম নেওয়া
        $cartItems = Cart::with('product')
            ->where('user_id', $user->id)
            ->get();
        
        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'Cart is empty'], 400);
        }
        
        // ক্যালকুলেশন
        $subtotal = $cartItems->sum(function ($item) {
            return $item->quantity * $item->product->price;
        });
        
        $shippingCost = 100;
        $discount = 0;
        $total = $subtotal + $shippingCost - $discount;
        
        // অর্ডার ক্রিয়েট
        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => 'ORD-' . strtoupper(uniqid()),
            'subtotal' => $subtotal,
            'shipping_cost' => $shippingCost,
            'discount' => $discount,
            'total' => $total,
            'shipping_address' => $request->shipping_address,
            'customer_name' => $request->customer_name,
            'customer_email' => $request->customer_email,
            'customer_phone' => $request->customer_phone,
            'payment_status' => 'pending',
            'order_status' => 'pending'
        ]);
        
        // অর্ডার আইটেম সেভ
        foreach ($cartItems as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product->name,
                'price' => $item->product->price,
                'quantity' => $item->quantity,
                'total' => $item->quantity * $item->product->price
            ]);
        }
        
        // SSLCommerz পেমেন্ট ইনির্শিয়েট
        $paymentResponse = Sslcommerz::setOrder($total, $order->order_number, 'Order #' . $order->order_number)
            ->setCustomer($request->customer_name, $request->customer_email, $request->customer_phone)
            ->setShippingInfo($cartItems->count(), $request->shipping_address)
            ->makePayment();
        
        if ($paymentResponse->success()) {
            return response()->json([
                'message' => 'Payment initiated',
                'order' => $order,
                'payment_url' => $paymentResponse->gatewayPageURL()
            ]);
        }
        
        // পেমেন্ট ফেইল করলে অর্ডার ডিলিট
        $order->delete();
        
        return response()->json([
            'message' => 'Payment initiation failed',
            'error' => $paymentResponse->failedReason()
        ], 400);
    }
    
    // পেমেন্ট সাকসেস কলব্যাক
    public function paymentSuccess(Request $request)
    {
        $transactionId = $request->input('tran_id');
        
        // অর্ডার খুঁজে বের করুন
        $order = Order::where('order_number', $transactionId)->first();
        
        if (!$order) {
            return redirect()->away(env('FRONTEND_URL') . '/payment/failed');
        }
        
        // ✅ সঠিকভাবে amount সহ validatePayment() কল করুন
        $isValid = Sslcommerz::validatePayment($request->all(), $transactionId, (float)$order->total);
        
        if ($isValid) {
            $order->update([
                'payment_status' => 'paid',
                'order_status' => 'processing',
                'transaction_id' => $request->input('bank_tran_id')
            ]);
            
            // কার্ট ক্লিয়ার
            Cart::where('user_id', $order->user_id)->delete();

            $invoiceData = [
                'order_id' => $order->id,
                'pdf_path' => $this->generateInvoicePdf($order),
            ];

            Invoice::create($invoiceData);
            
            return redirect()->away(env('FRONTEND_URL') . '/payment/success?order=' . $order->order_number);
        }
        
        return redirect()->away(env('FRONTEND_URL') . '/payment/failed');
    }
    
    // পেমেন্ট ফেইল কলব্যাক
    public function paymentFailure(Request $request)
    {
        $orderNumber = $request->input('tran_id');
        $order = Order::where('order_number', $orderNumber)->first();
        
        if ($order) {
            $order->update([
                'payment_status' => 'failed',
                'order_status' => 'cancelled'
            ]);
        }
        
        return redirect()->away(env('FRONTEND_URL') . '/payment/failed');
    }
    
    // পেমেন্ট ক্যান্সেল কলব্যাক
    public function paymentCancel(Request $request)
    {
        return redirect()->away(env('FRONTEND_URL') . '/payment/cancel');
    }
    
    // ✅ IPN Handler (সঠিকভাবে amount সহ)
    public function paymentIpn(Request $request)
    {
        \Log::info('SSLCommerz IPN Called', $request->all());
        
        $transactionId = $request->input('tran_id');
        
        if (!$transactionId) {
            return response()->json(['status' => 'error', 'message' => 'No transaction ID'], 400);
        }
        
        $order = Order::where('order_number', $transactionId)->first();
        
        if (!$order) {
            return response()->json(['status' => 'error', 'message' => 'Order not found'], 404);
        }
        
        // ✅ সঠিকভাবে amount সহ validatePayment() কল করুন
        $isValid = Sslcommerz::validatePayment($request->all(), $transactionId, (float)$order->total);
        
        if ($isValid) {
            if ($order->payment_status !== 'paid') {
                $order->update([
                    'payment_status' => 'paid',
                    'order_status' => 'processing',
                    'transaction_id' => $request->input('bank_tran_id', $request->input('tran_id'))
                ]);
                
                // কার্ট ক্লিয়ার
                Cart::where('user_id', $order->user_id)->delete();
                
                \Log::info('Order paid via IPN', ['order_id' => $order->id]);
            }
            
            return response()->json(['status' => 'success']);
        }
        
        \Log::error('IPN Validation Failed', ['tran_id' => $transactionId]);
        
        return response()->json(['status' => 'failed'], 400);
    }

    public function generateInvoicePdf($order) 
    {
        // Pdf::
    //     $pdf = Pdf::loadView('pdf.invoice', $data);
    // return $pdf->download('invoice.pdf');
        return 'pdf path';    
    }
}