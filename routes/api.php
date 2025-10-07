<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\FrontController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\BillingAddressController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\FAQController;
use App\Http\Controllers\ReviewController;


Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
->middleware(['signed', 'throttle:6,1'])
->name('verification.verify');

Route::group(['middleware'=>'api_key'], function () { 

    Route::post('/login', [AuthController::class, 'login']); //done
    Route::post('/register', [AuthController::class, 'register']); //done
    
    Route::get('/products', [ProductController::class, 'getproduct']); //done
    Route::get('/products2', [ProductController::class, 'getproduct2']); //done
    Route::get('/product/{id}', [ProductController::class, 'getproductdetail']); //done
    
    Route::get('/brands', [BrandController::class, 'getBrands']); //done
    Route::get('/category', [CategoryController::class, 'getCategoryApi']); //done
    
    Route::get('/maxPrice', [ProductController::class, 'maxPrice']); //done
    
    Route::get('/sliderimgs', [FrontController::class, 'sliderimgs']); //done
    Route::get('/banners', [FrontController::class, 'getBanners']); //done
    
    Route::get('maxDiscount', [ProductController::class, 'maxDiscount']); //done
    
    Route::get('terms', [FrontController::class, 'getTerms']); //done
    Route::get('policy', [FrontController::class, 'getPolicy']); //done

    Route::get('/faqs', [FAQController::class, 'getFaq']);
    
    Route::post('/forgotpwd', [AuthController::class, 'sendResetLinkEmail']); //done
    Route::post('/resetpwd/validatecredentials', [AuthController::class, 'rp_validateCreds']); //done
    Route::post('/resetpwd/newpwd', [AuthController::class, 'set_newpass']); //done

    Route::get('/homecategory', [CategoryController::class, 'homeCategory']); //done


    Route::get('/getBlogs', [BlogController::class, 'getBlog']);
    Route::get('/getBlog/{id}', [BlogController::class, 'getBlogContent']);

   

    Route::post('/email/resend', [AuthController::class, 'resendVerificationEmail'])
    ->middleware(['auth:sanctum', 'throttle:6,1'])
    ->name('verification.resend');

    Route::middleware(['auth:sanctum', 'verified'])->group(function () {
        Route::get('/user', function (Request $request) {
            return $request->user();
        });

        Route::post('/submit-review', [ReviewController::class, 'submitReview']); //new

        Route::post('/cart/add', [CartController::class, 'addToCart']); //done
        Route::post('/cart/update', [CartController::class, 'updateCart']); //done
        Route::post('/cart/remove', [CartController::class, 'removeFromCart']); //done
        Route::post('/cart/reduce', [CartController::class, 'reduceQuantity']); //done
        Route::get('/cart', [CartController::class, 'getCart']); //done

        // Add to Wishlist
        Route::post('/wishlist/toggle', [CartController::class, 'toggleWishlist']); //done
        Route::get('/wishlist', [CartController::class, 'getWishlist']); //done


        //Billing address crud
        Route::post('/billing-address', [BillingAddressController::class, 'addBillingAddress']); //done
        Route::put('/billing-address/{index}', [BillingAddressController::class, 'updateBillingAddress']); //done
        Route::delete('/billing-address/{index}', [BillingAddressController::class, 'deleteBillingAddress']); //done
        Route::get('/billing-address', [BillingAddressController::class, 'getBillingAddresses']); //done 
        Route::get('/billing-address/default', [BillingAddressController::class, 'getDefaultBillingAddress']); //done
        Route::put('/billing-address/default/{index}', [BillingAddressController::class, 'switchDefaultBillingAddress']); //done

        Route::post('/orders/checkout', [OrderController::class, 'checkout']); //done
        Route::post('/orders/payment-success', [OrderController::class, 'handlePaymentSuccess']);
        Route::post('/orders/delete-on-failure', [OrderController::class, 'deletePendingOrderOnFailure']);

        Route::get('/orders', [OrderController::class, 'getOrders']);
        Route::get('/orders/{orderId}', [OrderController::class, 'getOrderDetails']);

        Route::get('/provinces', [FrontController::class, 'getProvinces']); //done
        Route::post('/districts', [FrontController::class, 'getDistrictsByProvince']); //done
        Route::post('/municipalities', [FrontController::class, 'getMunicipalitiesByDistrict']); //done
    });
});



