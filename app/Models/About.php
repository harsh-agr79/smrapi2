<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class About extends Model
{
    use HasFactory;

    protected $fillable = [
        'meta_title',
        'meta_description',
        'meta_image',
        'hero_text_above_title',
        'hero_title',
        'hero_description',
        'statistics',
        'who_title',
        'who_description',
        'mission_text',
        'vision_text',
        'team_quote',
        'why_choose_title',
        'why_choose_cards',
        'cta_title',
        'cta_description',
        'cta_button_text',
        'cta_button_text2',
        'cta_button_link',
        'cta_button_link2'
    ];

    protected $casts = [
        'statistics' => 'array',
        'why_choose_cards' => 'array'
    ];
}
