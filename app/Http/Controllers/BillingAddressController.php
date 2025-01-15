<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BillingAddressController extends Controller
{
    public function addBillingAddress( Request $request ) {
        $user = $request->user();
        $billingAddresses = $user->billing_address ?? [];

        if ( is_string( $billingAddresses ) ) {
            $billingAddresses = json_decode( $billingAddresses, true );
        }

        // Generate unique ID for the new address based on max current ID
        $newId = collect( $billingAddresses )->max( 'id' ) + 1;

        $newAddress = [
            'id' => $newId,
            'province' => $request->input( 'province' ),
            'district' => $request->input( 'district' ),
            'municipality' => $request->input( 'municipality' ),
            'address' => $request->input( 'address' ),
            'nearest_landmark' => $request->input( 'nearest_landmark' ),
            'zipcode' => $request->input( 'zipcode' ),
            'wardno' => $request->input( 'wardno' ),
            'is_default' => $request->input( 'is_default', false ),
        ];

        // Ensure only one default address
        if ( $newAddress[ 'is_default' ] ) {
            foreach ( $billingAddresses as &$address ) {
                $address[ 'is_default' ] = false;
            }
        } elseif ( count( $billingAddresses ) == 0 ) {
            $newAddress[ 'is_default' ] = true;
            // First address added is default
        }

        $billingAddresses[] = $newAddress;
        $user->billing_address = json_encode( $billingAddresses );
        $user->save();

        return response()->json( [ 'message' => 'Billing address added successfully', 'billing_address' => $billingAddresses ] );
    }

    public function updateBillingAddress( Request $request, $id ) {
        $user = $request->user();
        $billingAddresses = $user->billing_address ?? [];

        if ( is_string( $billingAddresses ) ) {
            $billingAddresses = json_decode( $billingAddresses, true );
        }

        // Find the address by ID
        $index = collect( $billingAddresses )->search( fn( $address ) => $address[ 'id' ] == $id );

        if ( $index === false ) {
            return response()->json( [ 'message' => 'Billing address not found' ], 404 );
        }

        $billingAddresses[ $index ][ 'province' ] = $request->input( 'province', $billingAddresses[ $index ][ 'province' ] );
        $billingAddresses[ $index ][ 'district' ] = $request->input( 'district', $billingAddresses[ $index ][ 'district' ] );
        $billingAddresses[ $index ][ 'municipality' ] = $request->input( 'municipality', $billingAddresses[ $index ][ 'municipality' ] );
        $billingAddresses[ $index ][ 'address' ] = $request->input( 'address', $billingAddresses[ $index ][ 'address' ] );
        $billingAddresses[ $index ][ 'nearest_landmark' ] = $request->input( 'nearest_landmark', $billingAddresses[ $index ][ 'nearest_landmark' ] );
        $billingAddresses[ $index ][ 'zipcode' ] = $request->input( 'zipcode', $billingAddresses[ $index ][ 'zipcode' ] );
        $billingAddresses[ $index ][ 'wardno' ] = $request->input( 'wardno', $billingAddresses[ $index ][ 'wardno' ] );
        $billingAddresses[ $index ][ 'is_default' ] = $request->input( 'is_default', $billingAddresses[ $index ][ 'is_default' ] );

        if ( $billingAddresses[ $index ][ 'is_default' ] ) {
            foreach ( $billingAddresses as &$address ) {
                $address[ 'is_default' ] = false;
            }
            $billingAddresses[ $index ][ 'is_default' ] = true;
        }

        $user->billing_address = json_encode( $billingAddresses );
        $user->save();

        return response()->json( [ 'message' => 'Billing address updated successfully', 'billing_address' => $billingAddresses ] );
    }

    public function deleteBillingAddress( Request $request, $id ) {
        $user = $request->user();
        $billingAddresses = $user->billing_address ?? [];

        if ( is_string( $billingAddresses ) ) {
            $billingAddresses = json_decode( $billingAddresses, true );
        }

        $index = collect( $billingAddresses )->search( fn( $address ) => $address[ 'id' ] == $id );

        if ( $index === false ) {
            return response()->json( [ 'message' => 'Billing address not found' ], 404 );
        }

        $isDefault = $billingAddresses[ $index ][ 'is_default' ] ?? false;
        array_splice( $billingAddresses, $index, 1 );

        if ( $isDefault && count( $billingAddresses ) > 0 ) {
            $billingAddresses[ array_key_last( $billingAddresses ) ][ 'is_default' ] = true;
        }

        $user->billing_address = json_encode( $billingAddresses );
        $user->save();

        return response()->json( [ 'message' => 'Billing address deleted successfully', 'billing_address' => $billingAddresses ] );
    }

    public function getBillingAddresses( Request $request ) {
        $user = $request->user();
        $billingAddresses = $user->billing_address ?? [];

        if ( is_string( $billingAddresses ) ) {
            $billingAddresses = json_decode( $billingAddresses, true );
        }

        return response()->json( [ 'billing_address' => $billingAddresses ] );
    }

    public function getDefaultBillingAddress( Request $request ) {
        $user = $request->user();
        $billingAddresses = $user->billing_address ?? [];

        if ( is_string( $billingAddresses ) ) {
            $billingAddresses = json_decode( $billingAddresses, true );
        }

        $defaultAddress = collect( $billingAddresses )->firstWhere( 'is_default', true );

        if ( !$defaultAddress ) {
            return response()->json( [ 'message' => 'Default billing address not found' ], 404 );
        }

        return response()->json( [ 'default_billing_address' => $defaultAddress ] );
    }

    public function switchDefaultBillingAddress( Request $request, $id ) {
        $user = $request->user();
        $billingAddresses = $user->billing_address ?? [];

        if ( is_string( $billingAddresses ) ) {
            $billingAddresses = json_decode( $billingAddresses, true );
        }

        $index = collect( $billingAddresses )->search( fn( $address ) => $address[ 'id' ] == $id );

        if ( $index === false ) {
            return response()->json( [ 'message' => 'Billing address not found' ], 404 );
        }

        foreach ( $billingAddresses as &$address ) {
            $address[ 'is_default' ] = false;
        }

        $billingAddresses[ $index ][ 'is_default' ] = true;

        $user->billing_address = json_encode( $billingAddresses );
        $user->save();

        return response()->json( [ 'message' => 'Default billing address updated successfully', 'billing_address' => $billingAddresses ] );
    }
}
