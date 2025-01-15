<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Brand;
use Illuminate\Support\Facades\DB;

class BrandController extends Controller
{
    public function getBrands(){
        $brand = Brand::get();
        return response()->json($brand, 200);
    }
}
