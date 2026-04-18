<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    /**
     * Helper: Base query specifically for LISTS (Highly optimized memory footprint)
     * Only selects exactly what is needed for the Product Card
     */
    private function getBaseListQuery()
    {
        return Product::select('id', 'name', 'price', 'offer', 'images', 'category_id', 'brand_id', 'slug', 'stock')
            ->with(['category:id,category', 'brand:id,name'])
            ->where(function ($query) {
                $query->where('hide', 0)->orWhereNull('hide');
            });
    }

    /**
     * Helper: Base query for details (Fetches everything like variations, details, etc.)
     */
    private function getBaseDetailQuery()
    {
        return Product::with(['category:id,category', 'brand:id,name'])
            ->where(function ($query) {
                $query->where('hide', 0)->orWhereNull('hide');
            });
    }

    /**
     * Helper: Applies all request filters consistently
     */
    private function applyFilters($query, Request $request)
    {
        if ($request->has('brand')) {
            $brandIds = $request->get('brand');
            if (is_string($brandIds) && preg_match('/^\[.*\]$/', $brandIds)) {
                $brandIds = json_decode($brandIds, true);
            }
            is_array($brandIds) ? $query->whereIn('brand_id', $brandIds) : $query->where('brand_id', $brandIds);
        }

        if ($request->has('category')) {
            $categoryIds = $request->get('category');
            if (is_string($categoryIds) && preg_match('/^\[.*\]$/', $categoryIds)) {
                $categoryIds = json_decode($categoryIds, true);
            }
            is_array($categoryIds) ? $query->whereIn('category_id', $categoryIds) : $query->where('category_id', $categoryIds);
        }

        if ($request->has('price_min')) {
            $query->where('price', '>=', $request->get('price_min'));
        }
        if ($request->has('price_max')) {
            $query->where('price', '<=', $request->get('price_max'));
        }

        // Note: You can still filter by these columns even if they aren't in the select() statement
        $flags = ['stock', 'featured', 'new', 'flash', 'trending'];
        foreach ($flags as $flag) {
            if ($request->get($flag) == "on") {
                $query->where($flag, true); 
            }
        }

        return $query;
    }

    private function getWishlistIds()
    {
        $user = auth('sanctum')->user();
        if (!$user || empty($user->wishlist)) {
            return [];
        }

        return collect(is_string($user->wishlist) ? json_decode($user->wishlist, true) : $user->wishlist)
            ->pluck('product_id')
            ->toArray();
    }

    /**
     * Helper: Formats ONLY the required fields for the product card list payload
     */
    private function formatListProduct(Product $product, array $wishlistProductIds)
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'price' => $product->price,
            'offer' => $product->offer,
            'brand_id' => $product->brand_id,
            'category_id' => $product->category_id,
            'images' => is_array($product->images) ? implode('|', $product->images) : $product->images,
            'category' => $product->category ? $product->category->category : null,
            'brand' => $product->brand ? $product->brand->name : null,
            'wishlist' => in_array($product->id, $wishlistProductIds),
            'stock' => $product->stock ? 'on' : null, // Usually needed to disable the Add button if out of stock
        ];
    }

    /**
     * Helper: Formats the full product for detail pages
     */
    private function formatDetailProduct(Product $product, array $wishlistProductIds)
    {
        $data = $product->toArray();
        // $data['category'] = $product->category ? $product->category->category : null;
        // $data['brand'] = $product->brand ? $product->brand->name : null;
        $data['wishlist'] = in_array($product->id, $wishlistProductIds);
        $data['images'] = is_array($product->images) ? implode('|', $product->images) : $product->images;

        $booleanFields = ['hide', 'featured', 'stock', 'trending', 'flash', 'new'];
        foreach ($booleanFields as $field) {
            $data[$field] = $product->{$field} ? 'on' : null;
        }

        return $data;
    }

    private function getProductReviews($productId)
    {
        return DB::table('product_reviews')
            ->leftJoin('users', 'product_reviews.user_id', '=', 'users.id')
            ->select('product_reviews.*', 'users.name as user_name')
            ->where('product_reviews.product_id', $productId)
            ->orderBy('product_reviews.created_at', 'DESC')
            ->get();
    }


    // =========================================================================
    // PUBLIC ROUTES
    // =========================================================================

    public function getproduct(Request $request)
    {
        // Use List Query
        $query = $this->getBaseListQuery()->orderBy('ordernum', 'ASC');
        $query = $this->applyFilters($query, $request);
        
        $wishlistIds = $this->getWishlistIds();

        $products = $query->get()->transform(function ($product) use ($wishlistIds) {
            return $this->formatListProduct($product, $wishlistIds); // Format purely for cards
        });

        return response()->json($products);
    }

    public function getemiproduct(Request $request)
    {
        // Use List Query
        $query = $this->getBaseListQuery()
            ->where('is_emi_available', true)
            ->orderBy('ordernum', 'ASC');
            
        $query = $this->applyFilters($query, $request);
        
        $wishlistIds = $this->getWishlistIds();

        $products = $query->get()->transform(function ($product) use ($wishlistIds) {
            return $this->formatListProduct($product, $wishlistIds);
        });

        return response()->json($products);
    }

    public function getproduct2(Request $request)
    {
        // Use List Query
        $query = $this->getBaseListQuery()->orderBy('ordernum', 'ASC');
        $query = $this->applyFilters($query, $request);

        $wishlistIds = $this->getWishlistIds();
        
        $results = $query->paginate(20);
        $results->getCollection()->transform(function ($product) use ($wishlistIds) {
            return $this->formatListProduct($product, $wishlistIds);
        });

        return response()->json($results);
    }

    public function getProductDetail(Request $request, $id)
    {
        // Use Detail Query (fetches all columns)
        $product = $this->getBaseDetailQuery()->where('id', $id)->first();

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $formattedProduct = $this->formatDetailProduct($product, $this->getWishlistIds());
        $formattedProduct['reviews'] = $this->getProductReviews($product->id);
        
        return response()->json($formattedProduct);
    }

    public function getProductDetailSlug(Request $request, $slug)
    {
        // Use Detail Query (fetches all columns)
        $product = $this->getBaseDetailQuery()->where('slug', $slug)->first();

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $formattedProduct = $this->formatDetailProduct($product, $this->getWishlistIds());
        $formattedProduct['reviews'] = $this->getProductReviews($product->id);

        return response()->json($formattedProduct);
    }

    public function maxDiscount()
    {
        $maxDiscountPercentage = Product::where('price', '>', 0)
            ->whereNotNull('offer')
            ->max(DB::raw('((price - offer) / price) * 100'));

        return response()->json((float) $maxDiscountPercentage);
    }

    public function maxPrice()
    {
        $maxPrice = Product::max('price');
        return response()->json($maxPrice);
    }
}