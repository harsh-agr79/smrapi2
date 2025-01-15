<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CartController extends Controller
{
    public function getCart(Request $request)
    {
        $user = $request->user();
        $cart = $user->cart ?? [];
    
        // Check if the cart is a JSON string and decode it
        if (is_string($cart)) {
            $cart = json_decode($cart, true);  // Decode JSON string to array
        }
    
        // If the cart is empty after decoding, return an empty array
        if (empty($cart)) {
            return response()->json(['cart' => [], 'total' => 0, 'discounted_total' => 0, 'discount' => 0]);
        }
    
        // Extract product IDs from the cart
        $productIds = array_column($cart, 'product_id');
    
        // Fetch product details from the database
        $products = DB::table('products')->whereIn('id', $productIds)->get();
    
        // Map products by their IDs for easier access
        $productsById = $products->keyBy('id');
    
        // Initialize totals
        $total = 0;
        $discountedTotal = 0;
    
        // Combine cart items with product data
        $fullCart = array_map(function ($item) use ($productsById, &$total, &$discountedTotal) {
            $product = $productsById->get($item['product_id']);
    
            // Ensure that product exists before proceeding
            if ($product) {
                $basePrice = $product->price;
                $offerPrice = $product->offer ?? $basePrice;  // Use offer if available, otherwise regular price
    
                // Determine the price based on the variation
                if (!empty($item['variation']) && isset($item['variation']['price'])) {
                    $variationPrice = $item['variation']['price'];
                    $basePrice = $variationPrice;  // Override base price with variation price if provided
                    $offerPrice = min($offerPrice, $variationPrice);  // Ensure the offer price is the lowest available
                }
    
                // Calculate totals based on quantity
                $itemTotal = $basePrice * $item['quantity'];
                $itemDiscountedTotal = $offerPrice * $item['quantity'];
    
                $total += $itemTotal;
                $discountedTotal += $itemDiscountedTotal;
    
                return [
                    'product' => $product,  // Include all product details
                    'quantity' => $item['quantity'],
                    'variation' => $item['variation'],
                    'price' => $basePrice,
                    'offer' => $offerPrice,
                    'item_total' => $itemTotal,
                    'item_discounted_total' => $itemDiscountedTotal
                ];
            }
            return null;  // Handle cases where the product is not found
        }, $cart);
    
        // Filter out null entries (in case any product was not found)
        $fullCart = array_filter($fullCart);
    
        // Calculate the overall discount amount
        $discount = $total - $discountedTotal;
    
        return response()->json([
            'cart' => array_values($fullCart),
            'total' => $total,
            'discounted_total' => $discountedTotal,
            'discount' => $discount
        ]);
    }    
    


    public function addToCart(Request $request)
    {
        $user = $request->user();
        $cart = $user->cart ?? [];
    
        // Check if the cart is a JSON string and decode it
        if (is_string($cart)) {
            $cart = json_decode($cart, true);  // Decode JSON string to array
        }
    
        // Get the product and other optional data from the request
        $productId = $request->input('product_id');
        $quantity = $request->input('quantity', 1);  // Default quantity is 1
        $variation = $request->input('variation', null);  // Variation is optional
    
        // Check if the product exists in the products table
        $product = DB::table('products')->where('id', $productId)->first();
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }
    
        // Check if the product already exists in the cart
        $found = false;
        foreach ($cart as &$item) {
            if ($item['product_id'] == $productId) {
                // Update quantity if product exists in cart
                $item['quantity'] += $quantity;
                if ($variation) {
                    $item['variation'] = $variation;  // Update variation if provided
                }
                $found = true;
                break;
            }
        }
    
        // If the product does not exist in the cart, add it
        if (!$found) {
            $cart[] = [
                'product_id' => $productId,
                'quantity' => $quantity,
                'variation' => $variation  // Can be null if not provided
            ];
        }
    
        // Save the updated cart to the user
        $user->cart = json_encode($cart);  // Store the cart as JSON
        $user->save();
    
        return response()->json(['message' => 'Product added to cart successfully', 'cart' => $cart]);
    }
    
    

    public function updateCart(Request $request)
    {
        $user = $request->user();
        $cart = $user->cart ?? [];

        // Check if the cart is a JSON string and decode it
        if (is_string($cart)) {
            $cart = json_decode($cart, true);  // Decode JSON string to array
        }

        // Get the product ID and new quantity/variation from the request
        $productId = $request->input('product_id');
        $quantity = $request->input('quantity', 1);  // Default quantity is 1
        $variation = $request->input('variation', null);  // Variation is optional

        // Update the cart
        foreach ($cart as &$item) {
            if ($item['product_id'] == $productId) {
                // Update quantity and variation if provided
                $item['quantity'] = $quantity;
                if ($variation) {
                    $item['variation'] = $variation;
                }
                break;
            }
        }

        // Save the updated cart to the user
        $user->cart = $cart;
        $user->save();

        return response()->json(['message' => 'Cart updated successfully', 'cart' => $cart]);
    }

    

    public function removeFromCart(Request $request)
    {
        $user = $request->user();
        $cart = $user->cart ?? [];

        // Check if the cart is a JSON string and decode it
        if (is_string($cart)) {
            $cart = json_decode($cart, true);  // Decode JSON string to array
        }

        // Get the product ID to remove
        $productId = $request->input('product_id');

        // Filter out the product from the cart
        $cart = array_filter($cart, function($item) use ($productId) {
            return $item['product_id'] != $productId;
        });

        // Save the updated cart to the user
        $user->cart = $cart;
        $user->save();

        return response()->json(['message' => 'Product removed from cart successfully', 'cart' => $cart]);
    }

        public function reduceQuantity(Request $request)
        {
            $user = $request->user();
            $cart = $user->cart ?? [];

            // Check if the cart is a JSON string and decode it
            if (is_string($cart)) {
                $cart = json_decode($cart, true);  // Decode JSON string to array
            }

            // Get the product ID from the request
            $productId = $request->input('product_id');
            $decreaseBy = $request->input('quantity', 1);  // Default to decreasing by 1

            // Flag to check if product exists
            $found = false;

            // Loop through the cart and reduce the quantity
            foreach ($cart as &$item) {
                if ($item['product_id'] == $productId) {
                    $found = true;

                    // Reduce the quantity
                    $item['quantity'] -= $decreaseBy;

                    // If the quantity is 0 or less, you can either remove the item or set a minimum quantity
                    if ($item['quantity'] <= 0) {
                        // Remove the item from the cart
                        $cart = array_filter($cart, function($item) use ($productId) {
                            return $item['product_id'] != $productId;
                        });
                    }
                    break;
                }
            }

            // If the product was found, save the updated cart
            if ($found) {
                $user->cart = $cart;
                $user->save();
                return response()->json(['message' => 'Product quantity reduced successfully', 'cart' => $cart]);
            } else {
                return response()->json(['message' => 'Product not found in cart'], 404);
            }
        }

        public function toggleWishlist(Request $request)
    {
        $user = $request->user();
        $wishlist = $user->wishlist ?? [];

        // Check if the wishlist is a JSON string and decode it
        if (is_string($wishlist)) {
            $wishlist = json_decode($wishlist, true);  // Decode JSON string to array
        }

        // Get the product ID from the request
        $productId = $request->input('product_id');

        // Check if the product exists in the products table
        $product = DB::table('products')->where('id', $productId)->first();
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        // Check if the product is already in the wishlist
        $existingKey = array_search($productId, array_column($wishlist, 'product_id'));

        if ($existingKey !== false) {
            // If the product exists in the wishlist, remove it
            unset($wishlist[$existingKey]);
            $message = 'Product removed from wishlist successfully';
        } else {
            // If the product doesn't exist, add it to the wishlist
            $wishlist[] = ['product_id' => $productId];
            $message = 'Product added to wishlist successfully';
        }

        // Save the updated wishlist to the user
        $user->wishlist = json_encode(array_values($wishlist));  // Re-index the array and save as JSON
        $user->save();

        return response()->json(['message' => $message, 'wishlist' => $wishlist]);
    }

        

    public function getWishlist(Request $request)
    {
        $user = $request->user();
        $wishlist = $user->wishlist ?? [];

        // Check if the wishlist is a JSON string and decode it
        if (is_string($wishlist)) {
            $wishlist = json_decode($wishlist, true);  // Decode JSON string to array
        }

        // Fetch all product data for each product in the wishlist
        $productIds = array_column($wishlist, 'product_id');
        $products = DB::table('products')->whereIn('id', $productIds)->get();

        // Add wishlist:true to each product
        $products->transform(function($product) {
            $product->wishlist = true;
            return $product;
        });

        return response()->json(['wishlist' => $products]);
    }
}
