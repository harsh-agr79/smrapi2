<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;

class OrderController extends Controller
{
    public function checkout(Request $request)
    {
        $user = $request->user();

        
        $cart = $user->cart ?? []; // Decode JSON cart

        if (is_string($cart)) {
            $cart = json_decode($cart, true);  // Decode JSON string to array
        }

        $billingAddressData = is_string($user->billing_address) 
        ? json_decode($user->billing_address, true) 
        : $user->billing_address;

        $billingAddress = collect($billingAddressData ?? [])
            ->firstWhere('is_default', true); // Get default billing address

        if (empty($cart)) {
            return response()->json(['message' => 'Cart is empty.'], 400);
        }

        if (empty($billingAddress)) {
            return response()->json(['message' => 'No default billing address found.'], 400);
        }

        DB::beginTransaction();

        try {
            // Calculate total amount
            $totalAmount = collect($cart)->sum(function ($item) {
                $product = Product::findOrFail($item['product_id']);
                return $product->price * $item['quantity'];
            });
            $orderNumber = strtoupper(uniqid('ORDER-'));
            // Create order
            $order = Order::create([
                'user_id' => $user->id,
                'billing_address' => json_encode($billingAddress),
                'status' => 'pending',
                'total_amount' => $totalAmount,
                'subtotal' => $totalAmount,
                'order_number' => $orderNumber,
                'payment_method' => $request->input('payment_method', 'unknown'),
            ]);

            // Add items to order
            foreach ($cart as $item) {
                $product = Product::findOrFail($item['product_id']);
                $total = $product->price * $item['quantity'];

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                    'variation' => $item['variation'] ?? null,
                    'total' => $total,
                ]);
            }

            // Create payment
            Payment::create([
                'order_id' => $order->id,
                'user_id' => $user->id,
                'amount' => $totalAmount,
                'status' => 'pending',
                'payment_method' => $request->input('payment_method', 'unknown'),
            ]);

            // Update user's cart (clear it after checkout)
            $user->update(['cart' => json_encode([])]);

            DB::commit();

            return response()->json([
                'message' => 'Order created successfully.',
                'order' => $order,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create order.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
