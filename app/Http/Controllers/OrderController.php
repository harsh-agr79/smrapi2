<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidCouponException;
use App\Models\Coupon;
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
        // return response()->json($request->post('payment_method'));
        // dd($request);
        try {
            $request->validate([
                'payment_method' => 'required|string',
                'coupon_code' => 'nullable|string|exists:coupons,code',
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
            if (!$product)
                continue;

            $price = $product->price;
            $discountedPrice = $product->offer ?? $price;
            // $subtotal = $discountedPrice * $item['quantity'];

            $totalAmount += ($price * $item['quantity']);
            $totalDiscount += ($price - $discountedPrice) * $item['quantity'];
        }

        // $netTotal = $totalAmount - $totalDiscount + $deliveryCharge;
        $discountedTotalBeforeCoupon = $totalAmount - $totalDiscount;
        $couponDiscount = 0;
        $coupon = null;

        // --- COUPON VALIDATION & CALCULATION ---
        if ($request->filled('coupon_code')) {
            $coupon = Coupon::where('code', $request->coupon_code)->first();

            // Check if it exists first
            if (!$coupon) {
                return response()->json([
                    'status' => false,
                    'message' => 'The coupon code entered is invalid.'
                ], 422);
            }

            try {
                // This will throw an exception if any rule fails
                $coupon->isValidFor($user->id, $discountedTotalBeforeCoupon);

                // If it passes, calculate the discount amount safely
                $couponDiscount = $coupon->calculateDiscount($discountedTotalBeforeCoupon);

            } catch (\App\Exceptions\InvalidCouponException $e) {
                // Catch our custom exception and return the exact error message to the client
                return response()->json([
                    'status' => false,
                    'message' => $e->getMessage()
                ], 422);
            }
        }

        $finalDiscount = $totalDiscount + $couponDiscount;
        $finalDiscountedTotal = $discountedTotalBeforeCoupon - $couponDiscount;
        $netTotal = max(0, $finalDiscountedTotal + $deliveryCharge);

        // $pstat = "pending";

        // if($request->post('payment_method') == "cod"){
        //     $pstat = "cod";
        // }
        // else{
        //     $pstat = "pending";
        // }
        $pstat = ($request->post('payment_method') == "cod") ? "cod" : "pending";

        DB::beginTransaction();

        try {
            // Create a new order
            $order = Order::create(array_merge([
                'customer_id' => $user->id,
                'order_date' => now(),
                'current_status' => 'pending',
                'total_amount' => $totalAmount,
                'delivery_charge' => $deliveryCharge,
                'discount' => $finalDiscount,
                'discounted_total' => $finalDiscountedTotal,
                'net_total' => $netTotal,
                'payment_status' => $pstat,
                'last_status_updated' => now(),
                'billing_address' => json_encode($billingAddress),
            ]));

            // Add order items
            foreach ($cart as $cartItem) {
                $product = Product::find($cartItem['product_id']);
                if (!$product)
                    continue;

                OrderItem::create([
                    'order_id' => $order->id,
                    'customer_id' => $user->id,
                    'product_id' => $cartItem['product_id'],
                    'quantity' => $cartItem['quantity'],
                    'variation' => json_encode($cartItem['variation']),
                    'price' => $product->price,
                    'discounted_price' => $product->offer ?? $product->price,
                ]);
            }

            if ($coupon) {
                $order->coupons()->attach($coupon->id, [
                    'customer_id' => $user->id,
                    'discount_amount' => $couponDiscount,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Increment coupon global counters
                $coupon->increment('used_count');
            }

            // Record initial order status
            OrderStatusHistory::create([
                'user_id' => '2',
                'order_id' => $order->id,
                'status' => 'pending',
                'changed_at' => now(),
            ]);

            // (Optional) Clear customer's cart after checkout
            if ($request->post('payment_method') == "cod") {
                DB::table("users")->where('id', $user->id)->update([
                    'cart' => json_encode([]),
                ]);
            }
            // Mail::to($customer->email)->send(new OrderStatusUpdated($order));

            DB::commit();

            return response()->json([
                'message' => 'Order placed successfully.',
                'order' => $order->load('OrderItem', 'statusHistory', 'coupons'),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to process order.',
                'error' => $e->getMessage()
            ], 500);
        }

    }

    public function validateCoupon(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'coupon_code' => 'required|string|exists:coupons,code',
        ]);

        $cart = $user->cart ?? [];
        if (is_string($cart)) {
            $cart = json_decode($cart, true);
        }

        if (empty($cart)) {
            return response()->json(['message' => 'Your cart is empty.'], 400);
        }

        // Calculate current cart subtotal
        $discountedTotalBeforeCoupon = 0;
        foreach ($cart as $item) {
            $product = Product::find($item['product_id']);
            if (!$product)
                continue;

            $price = $product->price;
            $discountedPrice = $product->offer ?? $price;
            $discountedTotalBeforeCoupon += ($discountedPrice * $item['quantity']);
        }

        $coupon = Coupon::where('code', $request->coupon_code)->first();

        try {
            // Run our standard validation logic (dates, min_spend, usage limits)
            $coupon->isValidFor($user->id, $discountedTotalBeforeCoupon);

            // Calculate exact discount amount (caps percentage coupons automatically)
            $discountAmount = $coupon->calculateDiscount($discountedTotalBeforeCoupon);

            return response()->json([
                'status' => true,
                'message' => 'Coupon applied successfully!',
                'coupon_code' => $coupon->code,
                'discount_amount' => $discountAmount,
                'type' => $coupon->type,
                'value' => $coupon->value
            ], 200);

        } catch (InvalidCouponException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function deletePendingOrderOnFailure(Request $request)
    {
        $orderId = $request->post('order_id');

        $customer = $request->user();

        $order = Order::where('id', $orderId)->where('customer_id', $customer->id)->where('payment_status', 'pending')->first();

        if (!$order) {
            return response()->json(['message' => 'Order not found or not pending.'], 404);
        }

        DB::beginTransaction();
        try {
            // If the order has a coupon attached, decrement its usage back before deleting
            foreach ($order->coupons as $coupon) {
                $coupon->decrement('used_count');
            }

            // Delete associated order items first
            $order->coupons()->detach();
            $order->OrderItem()->delete();

            // Delete the order itself
            $order->delete();

            DB::commit();
            return response()->json(['message' => 'Pending order deleted due to payment failure.'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to revert order records.'], 500);
        }
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

        DB::beginTransaction();
        try {
            // ✅ Create payment entry
            Payment::create([
                'customer_id' => $customer->id,
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

            DB::commit();
            return response()->json(['message' => 'Payment successful, cart cleared, and order updated.'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Payment log sequence error.'], 500);
        }
    }


    public function getOrders(Request $request)
    {
        $customer = $request->user();

        $orders = Order::where('customer_id', $customer->id)
            ->whereIn('payment_status', ['paid', 'cod'])
            ->orderBy('created_at', 'desc')
            ->with('OrderItem.product', 'statusHistory', 'payments', 'coupons')
            ->get();

        return response()->json(['orders' => $orders], 200);
    }

    public function getOrderDetails(Request $request, $orderId)
    {
        $customer = $request->user();

        $order = Order::where('customer_id', $customer->id)
            ->where('id', $orderId)
            ->whereIn('payment_status', ['paid', 'cod'])
            ->with('OrderItem.product', 'statusHistory', 'payments', 'coupons')
            ->first();

        if (!$order) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        return response()->json(['order' => $order], 200);
    }
}
