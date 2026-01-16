<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id', 'order_date', 'billing_address',
        'payment_status', 'current_status',
        'total_amount', 'delivery_charge', 'discount', 'discounted_total', 
        'net_total', 'last_status_updated', 'store_id'
    ];

    protected $casts = [
        'billing_address' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::updating(function ($order) {
            if ($order->isDirty('current_status')) { // Check if status has changed
                OrderStatusHistory::create([
                    'order_id'   => $order->id,
                    'status'     => $order->current_status,
                    'changed_at' => now(),
                    'user_id'    => auth()->id() ?? null, // If authenticated, store user ID
                ]);
                
                if ($order->customer && $order->customer->email) {
                    // Mail::to($order->customer->email)->send(new OrderStatusUpdated($order));
                }
            }
           
        });
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }


    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function OrderItem()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function statusHistory()
    {
        return $this->hasMany(OrderStatusHistory::class);
    }
}
