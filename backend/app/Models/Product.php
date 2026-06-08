<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'restaurant_id',
        'name_ar',
        'name_en',
        'description_ar',
        'description_en',
        'price',
        'discount_price',
        'image',
        'is_available',
        'is_featured',
        'is_recommended',
        'tags',
        'order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'discount_price' => 'decimal:2',
        'is_available' => 'boolean',
        'is_featured' => 'boolean',
        'is_recommended' => 'boolean',
        'tags' => 'array',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function productViews()
    {
        return $this->hasMany(ProductView::class);
    }

    public function getActivePriceAttribute()
    {
        return $this->discount_price !== null ? $this->discount_price : $this->price;
    }
}
