<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    use HasFactory;

    protected $fillable = [
        'heading', 
        'subheading', 
        'cover_photo', 
        'published_on', 
        'content',
        'meta_title',
        'meta_description',
        'pinned',
        'slug'
    ];

    public function scopePinned($query)
    {
        return $query->where('pinned', true);
    }
}
