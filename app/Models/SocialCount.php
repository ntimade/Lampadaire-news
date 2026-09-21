<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocialCount extends Model
{
    use HasFactory;

    protected $fillable = [
        'language',
        'icon',
        'fan_count',
        'fan_type',
        'button_text',
        'color',
        'url',
        'status',
    ];
}
