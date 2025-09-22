<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FAQ;
use App\Models\MetaTag;


class FAQController extends Controller
{
    public function getFaq(Request $request){
        $faq = FAQ::orderBy('order', 'ASC')->get();
        return response()->json(["faqs" => $faq, 'meta_tags' => MetaTag::where('slug', 'faqs')->first()], 200);
    }
}
