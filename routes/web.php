<?php

use Illuminate\Support\Facades\Route;


use App\Models\Order;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect('/admin');
});

Route::get('/orders/{order}/print-invoice', function (Order $order) {
    // Eager load relationships to prevent N+1 queries
    $order->load(['customer', 'OrderItem.product', 'store']);
    
    return view('invoices.print', compact('order'));
})->name('order.invoice.print')->middleware(['web', 'auth']);
