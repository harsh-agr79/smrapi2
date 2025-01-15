<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;


    protected $fillable = [
        'user_id',
        'order_number',
        'status',
        'total_amount',
        'subtotal',
        'tax',
        'discount',
        'shipping_fee',
        'payment_method',
        'payment_status',
        'transaction_id',
        'billing_address',
        'shipping_method',
        'order_notes',
        'placed_at',
        'completed_at',
        'cancelled_at',
    ];

    protected $casts = [
        'billing_address' => 'array',
        'placed_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function history()
    {
        return $this->hasMany(OrderHistory::class);
    }

}
