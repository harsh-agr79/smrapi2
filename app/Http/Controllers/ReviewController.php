<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductReview;
use App\Models\Product;
use App\Models\User;

class ReviewController extends Controller
{
    public function submitReview(Request $request){

        $user = $request->user();

        $request->validate([
            'product_id' => 'required|exists:products,id',
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'required|string|max:1000',
        ]);

        $review = ProductReview::create([
            'product_id' => $request->product_id,
            'user_id' => $user->id,
            'rating' => $request->rating,
            'review' => $request->review,
        ]);

        return response()->json(['message' => 'Review submitted successfully', 'review' => $review], 201);
    }
}
