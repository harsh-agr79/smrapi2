<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    public function getCategoryApi(){
        $category = Category::orderBy('ordernum', 'ASC')->get();
        return response()->json($category, 200);
    }

    public function homeCategory() {
        // Fetch categories where image is not null
        $categories = Category::orderBy('ordernum', 'ASC')->whereNotNull('image')->get();
        
        // Add the prefix 'categories/' to each image value
        // $categories->map(function ($category) {
        //     if ($category->image) {
        //         $category->image = 'categories/' . $category->image;
        //     }
        //     return $category;
        // });
    
        // Return the updated categories with the prefixed image path
        return response()->json($categories, 200);
    }
}
