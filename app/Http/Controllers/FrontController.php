<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class FrontController extends Controller
{
    public function getTerms() {
        $terms =  DB::table( 'terms' )->where( 'id', 1 )->first();
    
        return response()->json( $terms->terms, 200 );
    }

    public function getPolicy() {
        $policy =  DB::table( 'policies' )->where( 'id', 1 )->first();
    
        return response()->json( $policy->policy, 200 );
    }

    public function sliderimgs() {
        $res = DB::table( 'fronts' )
        ->where( 'type', 'image' )
        ->orderBy( 'id', 'DESC' )
        ->get( [ 'id', 'image' ] )
        ->map( function ( $item ) {
            return [
                'id' => $item->id,
                'path' => $item->image, // Replace 'image' with 'path'
            ];
        }
    );
    return response()->json( $res, 200 );
    }
    public function getBanners()
    {
        // Retrieve all data from the banners table
        $banners = DB::table('banners' )->get();

        // Return the data as a JSON response
        return response()->json( $banners );
    }

    public function getProvinces()
    {
        // Get distinct provinces (states) from the locations table
        $provinces = DB::table('locations')->distinct()->pluck('province');

        return response()->json($provinces);
    }

    // 2. Get districts by province name
    public function getDistrictsByProvince(Request $request)
    {
        $provinceName = $request->input('province');

        if (empty($provinceName)) {
            return response()->json(['error' => 'Province name is required'], 400);
        }

        // Get distinct districts for the given province
        $districts = DB::table('locations')->where('province', $provinceName)
                             ->distinct()
                             ->pluck('district');

        return response()->json($districts);
    }

    // 3. Get municipalities by district name
    public function getMunicipalitiesByDistrict(Request $request)
    {
        $districtName = $request->input('district');

        if (empty($districtName)) {
            return response()->json(['error' => 'District name is required'], 400);
        }

        // Get municipalities for the given district
        $municipalities = DB::table('locations')->where('district', $districtName)
                                  ->distinct()
                                  ->pluck('municipality');

        return response()->json($municipalities);
    }
}
