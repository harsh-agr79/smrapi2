<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Front extends Model
{
    use HasFactory;

    protected $fillable = ['image', 'type', 'message', 'ordernum', 'extra1'];

    protected $attributes = [
        'type' => 'image',
    ];
}
