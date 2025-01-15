<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'brand_id',
        'brand',
        'category_id',
        'category',
        'stock',
        'hide',
        'price',
        'featured',
        'net',
        'details',
        'images',
        'ordernum',
        'offer',
        'trending',
        'flash',
        'new',
        'variations',
    ];

    protected $casts = [
        'variations' => 'array',
        'images' => 'array',
        'hide' => 'boolean',
        'featured' => 'boolean',
        'stock' => 'boolean',
        'offer' => 'string',
        'trending' => 'boolean',
        'flash' => 'boolean',
        'new' => 'boolean',
    ];

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function setVariationsAttribute($value)
    {
        $this->attributes['variations'] = json_encode($value);
    }
}
