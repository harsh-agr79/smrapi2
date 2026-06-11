<?php

namespace App\Models;

use App\Exceptions\InvalidCouponException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Coupon extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'type',
        'value',
        'min_spend',
        'max_discount',
        'usage_limit',
        'usage_limit_per_user',
        'used_count',
        'starts_at',
        'expires_at',
        'is_active'
    ];

    public function orders()
    {
        return $this->belongsToMany(Order::class)->withPivot('discount_amount')->withTimestamps();
    }

    public function isValidFor($customerId, $orderAmount): bool
    {
        if (!$this->is_active) {
            throw new InvalidCouponException('This coupon code is no longer active.');
        }

        if ($this->starts_at && now()->lt($this->starts_at)) {
            throw new InvalidCouponException('This coupon promotion has not started yet.');
        }

        if ($this->expires_at && now()->gt($this->expires_at)) {
            throw new InvalidCouponException('This coupon code has expired.');
        }

        if ($this->min_spend && $orderAmount < $this->min_spend) {
            throw new InvalidCouponException("You must spend at least Rs. {$this->min_spend} to use this coupon.");
        }

        if ($this->usage_limit && $this->used_count >= $this->usage_limit) {
            throw new InvalidCouponException('This coupon has reached its maximum global usage limit.');
        }

        if ($this->usage_limit_per_user && $customerId) {
            $userUsage = $this->orders()
                ->where('coupon_order.customer_id', $customerId)
                ->count();

            if ($userUsage >= $this->usage_limit_per_user) {
                throw new InvalidCouponException('You have already used this coupon code the maximum number of times.');
            }
        }

        return true;
    }

    public function calculateDiscount($orderAmount): float
    {
        if ($this->type === 'percentage') {
            $discount = $orderAmount * ($this->value / 100);

            // If a max discount is set, cap the calculated discount
            if ($this->max_discount) {
                $discount = min($discount, $this->max_discount);
            }

            return round($discount, 2);
        }

        // For 'fixed', return the coupon value, but never more than the order amount itself
        return min($this->value, $orderAmount);
    }
}
