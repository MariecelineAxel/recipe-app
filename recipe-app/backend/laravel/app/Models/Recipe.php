<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    protected $fillable = [
        'title',
        'description',
        'ingredients', // JSON
    ];

    protected $casts = [
        'ingredients' => 'array',
    ];
}
