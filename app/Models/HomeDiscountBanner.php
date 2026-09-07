<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomeDiscountBanner extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_title',
        'discount_amount',
    ];
}
