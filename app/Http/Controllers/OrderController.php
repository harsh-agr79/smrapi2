<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\User;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function checkout(Request $request)
    {
        $user = $request->user();
        // return response()->json($request->post());
        try {
            $request->validate([
                'payment_method' => 'required|string',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        }

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

        $deliveryCharge = 200;

        $totalAmount = 0;
        $totalDiscount = 0;
        foreach ($cart as $item) {
            $product = Product::find($item['product_id']);
            if (!$product) continue;
    
            $price = $product->price;
            $discountedPrice = $product->offer ?? $price;
            $subtotal = $discountedPrice * $item['quantity'];
            
            $totalAmount += ($price * $item['quantity']);
            $totalDiscount += ($price - $discountedPrice) * $item['quantity'];
        }
    
        $netTotal = $totalAmount - $totalDiscount + $deliveryCharge;

        $pstat = "pending";

        if($request->post('payment_method') == "cod"){
            $pstat = "cod";
        }
        else{
            $pstat = "pending";
        }
        // Create a new order
        $order = Order::create(array_merge([
            'customer_id'          => $user->id,
            'order_date'           => now(),
            'current_status'       => 'pending',
            'total_amount'         => $totalAmount,
            'delivery_charge'      => $deliveryCharge,
            'discount'             => $totalDiscount,
            'discounted_total'     => $totalAmount - $totalDiscount,
            'net_total'            => $totalAmount - $totalDiscount + $deliveryCharge,
            'payment_status'       => $pstat,
            'last_status_updated'  => now(),
            'billing_address' => json_encode($billingAddress),
        ]));
    
        // Add order items
        foreach ($cart as $cartItem) {
            $product = Product::find($cartItem['product_id']);
            if (!$product) continue;
    
            OrderItem::create([
                'order_id'        => $order->id,
                'customer_id'     => $user->id,
                'product_id'      => $cartItem['product_id'],
                'quantity'        => $cartItem['quantity'],
                'variation'        => json_encode($cartItem['variation']),
                'price'           => $product->price,
                'discounted_price'=> $product->offer ?? $product->price,
            ]);
        }
    
        // Record initial order status
        OrderStatusHistory::create([
            'user_id' => '2',
            'order_id'   => $order->id,
            'status'     => 'pending',
            'changed_at' => now(),
        ]);
    
        // (Optional) Clear customer's cart after checkout
        if ($request->post('payment_method') == "cod") {
            DB::table("users")->where('id', $user->id)->update([
                'cart' => json_encode([]),
               ]);
        }
        // Mail::to($customer->email)->send(new OrderStatusUpdated($order));

        return response()->json([
            'message' => 'Order placed successfully.',
            'order'   => $order->load('OrderItem', 'statusHistory'),
        ], 201);

       
    }

    public function deletePendingOrderOnFailure(Request $request)
    {
        $orderId = $request->post('order_id');

        $customer = $request->user();

        $order = Order::where('id', $orderId)->where('customer_id',$customer->id)->where('payment_status', 'pending')->first();

        if (!$order) {
            return response()->json(['message' => 'Order not found or not pending.'], 404);
        }

        // Delete associated order items first
        $order->OrderItem()->delete();

        // Delete the order itself
        $order->delete();

        return response()->json(['message' => 'Pending order deleted due to payment failure.'], 200);
    }


    public function handlePaymentSuccess(Request $request)
    {
        $customer = $request->user(); // ✅ Get the authenticated user

        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'payment_reference' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string',
        ]);

        // ✅ Create payment entry
        Payment::create([
            'customer_id'=>$customer->id,
            'order_id' => $validated['order_id'],
            'payment_reference' => $validated['payment_reference'],
            'amount' => $validated['amount'],
            'payment_method' => $validated['payment_method'],
        ]);

       DB::table("users")->where('id', $customer->id)->update([
        'cart' => json_encode([]),
       ]);

        // ✅ Update order's payment_status
        Order::where('id', $validated['order_id'])->update([
            'payment_status' => 'paid',
        ]);

        return response()->json(['message' => 'Payment successful, cart cleared, and order updated.'], 200);
    }
}
