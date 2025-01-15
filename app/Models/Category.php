<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'category',
        'image',
        'ordernum',
    ];

    // Dates that should be automatically converted to Carbon instances
    protected $dates = ['created_at', 'updated_at'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
