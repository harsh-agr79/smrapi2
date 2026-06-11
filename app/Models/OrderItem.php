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

    public function updateOrderTotals()
    {
        $order = $this->order; // Get parent order
        if (!$order)
            return;

        $totals = $order->OrderItem()
            ->selectRaw('
                SUM(price * quantity) as total_amount,
                SUM((price - discounted_price) * quantity) as item_discount,
                SUM(discounted_price * quantity) as discounted_total
            ')
            ->first();

        $grossTotal = $totals->discounted_total ?? 0;
        $couponDiscount = 0;

        foreach ($order->coupons as $coupon) {
            // Double check validity against the new item totals
            if ($coupon->isValidFor($order->customer_id, $grossTotal)) {
                $discount = $coupon->calculateDiscount($grossTotal);
                $couponDiscount += $discount;

                // Update pivot table record for accuracy
                $order->coupons()->updateExistingPivot($coupon->id, ['discount_amount' => $discount]);
            } else {
                // Detach if it's no longer valid due to item removal/quantity drop
                $order->coupons()->detach($coupon->id);
            }
        }

        $deliveryCharge = $order->delivery_charge ?? 0; // Get delivery charge from the order
        $totalItemDiscount = $totals->item_discount ?? 0;

        $combinedDiscount = $totalItemDiscount + $couponDiscount;

        $order->update([
            'total_amount' => $totals->total_amount ?? 0,
            'discount' => $combinedDiscount,
            'discounted_total' => $grossTotal - $couponDiscount,
            'net_total' => max(0, ($grossTotal - $couponDiscount) + $deliveryCharge), // Add delivery charge
        ]);
    }
}
