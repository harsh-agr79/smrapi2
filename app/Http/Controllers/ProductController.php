<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Image;

class ProductController extends Controller
{
    public function getproduct(Request $request) {
        // Start the product query with category and brand joins
        $query = DB::table('products')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
            ->select('products.*', 'categories.category as category', 'brands.name as brand')
            ->orderBy('ordernum', 'ASC')
            ->where(function($query) {
                $query->where('hide', 0)
                      ->orWhereNull('hide');
            });
    
        // Apply filters based on the request parameters
        if ($request->has('brand')) {
            $query->where('brand_id', $request->get('brand'));
        }
        if ($request->has('category')) {
            $query->where('category_id', $request->get('category'));
        }
        if ($request->has('price_min')) {
            $query->where('price', '>=', $request->get('price_min'));
        }
        if ($request->has('price_max')) {
            $query->where('price', '<=', $request->get('price_max'));
        }
        if ($request->has('stock') && $request->get('stock') == "on") {
            $query->where('stock', '1');
        }
        if ($request->has('featured') &&  $request->get('featured') == "on") {
            $query->where('featured', '1');
        }
        if ($request->has('new') && $request->get('new') == "on") {
            $query->where('new', '1');
        }
        if ($request->has('flash') && $request->get('flash') == "on") {
            $query->where('flash', '1');
        }
        if ($request->has('trending') && $request->get('trending') == "on") {
            $query->where('trending', '1');
        }
    
        // Retrieve the authenticated user and their wishlist
        $user = auth('sanctum')->user();
        $wishlistProductIds = [];
    
        if ($user && !empty($user->wishlist)) {
            $wishlist = json_decode(json_encode($user->wishlist), true);
            if (is_array($wishlist)) {
                // Extract the product_ids from the wishlist
                $wishlistProductIds = array_column($wishlist, 'product_id');
            }
        }
    
        // Execute the query and get the results
        $products = $query->get();
    
        // Add the wishlist field to each product and decode variations
        $products->transform(function($product) use ($wishlistProductIds) {
            $product->variations = json_decode($product->variations, true); // Decode JSON to associative array
            $product->wishlist = in_array($product->id, $wishlistProductIds);
            $images = json_decode($product->images, true);
            $product->images = is_array($images) ? implode('|', $images) : $product->images;
    
            $booleanFields = ['hide', 'featured', 'stock', 'trending', 'flash', 'new'];
            foreach ($booleanFields as $field) {
                $product->{$field} = isset($product->{$field}) && $product->{$field} == 1 ? 'on' : NULL;
            }
    
            return $product;
        });
    
        // Return the results as a JSON response
        return response()->json($products);
    }
    
    

    public function getproduct2(Request $request) {
        // Start the product query with category and brand joins
        $query = DB::table('products')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
            ->select('products.*', 'categories.category as category', 'brands.name as brand')
            ->where(function($query) {
                $query->where('hide', 0)
                      ->orWhereNull('hide');
            })
            ->orderBy('ordernum', 'ASC');
    
        // Apply filters based on the request parameters
        if ($request->has('brand')) {
            $brandIds = $request->get('brand');
            if (is_string($brandIds) && preg_match('/^\[.*\]$/', $brandIds)) {
                $brandIds = json_decode($brandIds, true);
            }
            if (is_array($brandIds)) {
                $query->whereIn('brand_id', $brandIds);
            } else {
                $query->where('brand_id', $brandIds);
            }
        }
    
        if ($request->has('category')) {
            $categoryIds = $request->get('category');
            if (is_string($categoryIds) && preg_match('/^\[.*\]$/', $categoryIds)) {
                $categoryIds = json_decode($categoryIds, true);
            }
            if (is_array($categoryIds)) {
                $query->whereIn('category_id', $categoryIds);
            } else {
                $query->where('category_id', $categoryIds);
            }
        }
    
        if ($request->has('price_min')) {
            $query->where('price', '>=', $request->get('price_min'));
        }
        if ($request->has('price_max')) {
            $query->where('price', '<=', $request->get('price_max'));
        }
    
        if ($request->has('stock') && $request->get('stock') == "on") {
            $query->where('stock', '1');
        }
        if ($request->has('featured') && $request->get('featured') == "on") {
            $query->where('featured', '1');
        }
        if ($request->has('new') && $request->get('new') == "on") {
            $query->where('new', '1');
        }
        if ($request->has('flash') && $request->get('flash') == "on") {
            $query->where('flash', '1');
        }
        if ($request->has('trending') && $request->get('trending') == "on") {
            $query->where('trending', '1');
        }
    
        // Retrieve the authenticated user and their wishlist
        $user = auth('sanctum')->user();
        $wishlistProductIds = [];
    
        if ($user && !empty($user->wishlist)) {
            $wishlist = json_decode(json_encode($user->wishlist), true);
            if (is_array($wishlist)) {
                $wishlistProductIds = array_column($wishlist, 'product_id');
            }
        }
    
        // Execute the query and paginate results
        $results = $query->paginate(20);
        $results->getCollection()->transform(function($product) use ($wishlistProductIds) {
            $product->variations = json_decode($product->variations, true);
            $product->wishlist = in_array($product->id, $wishlistProductIds);
            $images = json_decode($product->images, true);
            $product->images = is_array($images) ? implode('|', $images) : $product->images;
    
            $booleanFields = ['hide', 'featured', 'stock', 'trending', 'flash', 'new'];
            foreach ($booleanFields as $field) {
                $product->{$field} = isset($product->{$field}) && $product->{$field} == 1 ? 'on' : NULL;
            }
    
            return $product;
        });
    
        return response()->json($results);
    }
    
    

    public function getProductDetail(Request $request, $id)
    {
        // Retrieve the product with category and brand details
        $product = DB::table('products')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
            ->select('products.*', 'categories.category as category', 'brands.name as brand')
            ->where('products.id', $id)
            ->first();
    
        // Check if the product exists
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }
    
        // Decode the product variations
        $product->variations = json_decode($product->variations);
        $product->reviews = DB::table('product_reviews')
            ->leftJoin('users', 'product_reviews.user_id', '=', 'users.id')
            ->select('product_reviews.*', 'users.name as user_name')
            ->orderBy('product_reviews.created_at', 'DESC')
            ->where('product_reviews.product_id', $id)
            ->get();
    
        // Retrieve authenticated user's wishlist
        $user = auth('sanctum')->user();
        $wishlistProductIds = [];
    
        if ($user && !empty($user->wishlist)) {
            $wishlist = json_decode(json_encode($user->wishlist), true);
            if (is_array($wishlist)) {
                // Extract the product_ids from the wishlist
                $wishlistProductIds = array_column($wishlist, 'product_id');
            }
        }
    
        // Check if the product is in the user's wishlist
        $product->wishlist = in_array($id, $wishlistProductIds);
    
        // Handle product images
        $images = json_decode($product->images, true);
        $product->images = is_array($images) ? implode('|', $images) : $product->images;
    
        // Convert boolean fields to 'on' or NULL
        $booleanFields = ['hide', 'featured', 'stock', 'trending', 'flash', 'new'];
        foreach ($booleanFields as $field) {
            $product->{$field} = isset($product->{$field}) && $product->{$field} == 1 ? 'on' : NULL;
        }
    
        // Return the product details with the wishlist status and category/brand names
        return response()->json($product);
    }
    


    public function maxDiscount() {
        // Retrieve price and offer values from the database
        $products = DB::table('products')->select('price', 'offer')->get();
    
        // Calculate discount percentages
        $discounts = $products->map(function($product) {
            if ($product->price > 0 && $product->offer != NULL) {
                return (($product->price - $product->offer) / $product->price) * 100;
            }
            return 0; // Avoid division by zero for products with price 0
        });
    
        // Get the maximum discount percentage
        $maxDiscountPercentage = $discounts->max();
    
        // Return the result as JSON
        return response()->json($maxDiscountPercentage);
    }

    public function maxPrice(){
        $maxPrice = DB::table('products')->max('price');
    
        return response()->json($maxPrice);
    }
}
