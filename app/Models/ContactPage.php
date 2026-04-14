<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactPage extends Model
{
    use HasFactory;

    protected $fillable = [
        'meta_title',
        'meta_description',
        'meta_image',
        'hero_text_above_title',
        'hero_title',
        'hero_description',
        'contact_info'
    ];

    protected $casts = [
        'contact_info' => 'array'
    ];
}
