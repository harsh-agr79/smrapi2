<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'customer_id',
        'product_id',
        'quantity',
        'variation',
        'price',
        'discounted_price'
    ];

    protected $casts = [
        'variation' => 'array',
    ];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    protected static function booted()
    {
        static::saved(fn($item) => $item->updateOrderTotals());
        static::deleted(fn($item) => $item->updateOrderTotals());
    }

    // Inside app/Models/OrderItem.php

    public function updateOrderTotals()
    {
        $order = $this->order;
        if (!$order)
            return;

        // 1. Calculate base item totals from the cart items
        $totals = $order->OrderItem()
            ->selectRaw('
            SUM(price * quantity) as total_amount,
            SUM((price - discounted_price) * quantity) as item_discount,
            SUM(discounted_price * quantity) as discounted_total
        ')
            ->first();

        $grossTotal = $totals->discounted_total ?? 0;
        $couponDiscount = 0;

        // 2. IMPORTANT: Loop through coupons attached to this order to keep them in sync
        // If OrderItem creates are happening, $order->coupons might be empty in-memory.
        // We load it fresh from the DB to be safe.
        foreach ($order->coupons()->get() as $coupon) {
            if ($coupon->isValidFor($order->customer_id, $grossTotal)) {
                $discount = $coupon->calculateDiscount($grossTotal);
                $couponDiscount += $discount;

                // Keep the pivot record updated with the exact split
                $order->coupons()->updateExistingPivot($coupon->id, ['discount_amount' => $discount]);
            } else {
                // Remove the coupon if item changes mean they no longer meet requirements
                $order->coupons()->detach($coupon->id);
            }
        }

        $deliveryCharge = $order->delivery_charge ?? 0;
        $totalItemDiscount = $totals->item_discount ?? 0;

        // Combine product markdown discounts + our coupon reductions
        $combinedDiscount = $totalItemDiscount + $couponDiscount;

        // 3. Update the main order row with the final calculation
        $order->update([
            'total_amount' => $totals->total_amount ?? 0,
            'discount' => $combinedDiscount,
            'discounted_total' => $grossTotal - $couponDiscount,
            'net_total' => max(0, ($grossTotal - $couponDiscount) + $deliveryCharge),
        ]);
    }
}
