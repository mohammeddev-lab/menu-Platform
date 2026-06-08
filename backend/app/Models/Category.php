<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'restaurant_id',
        'name_ar',
        'name_en',
        'subtitle_ar',
        'subtitle_en',
        'icon',
        'order',
    ];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class)->orderBy('order');
    }
}
