<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MetaTag extends Model
{
    use HasFactory;

     protected $fillable = [
        'slug',
        'meta_title',
        'meta_description',
        'meta_image',
        'meta_image_alt',
    ];
}
