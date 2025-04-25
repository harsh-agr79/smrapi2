<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Blog;

class BlogController extends Controller
{
    public function getBlog(Request $request)
    {
        $pinnedBlog = Blog::where('pinned', true)->first();
        $otherBlogs = Blog::orderBy('published_on', 'DESC')->get();
    
        return response()->json([
            'pinned_blog' => $pinnedBlog,
            'blogs' => $otherBlogs
        ], 200);
    }
    public function getBlogContent(Request $request, $id)
    {
        $blog = Blog::where('id', $id)->first();
    
        if (!$blog) {
            $blog = Blog::where('slug', $id)->first();
        }
    
        if (!$blog) {
            return response()->json(['message' => 'Blog not found'], 404);
        }
    
        return response()->json(['blog' => $blog], 200);
    }
}
