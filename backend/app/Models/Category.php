<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'restaurant_id',
        'name',
        'name_ar',
        'name_en',
        'description',
        'subtitle_ar',
        'subtitle_en',
        'icon',
        'icon_type',
        'icon_value',
        'order',
    ];

    protected static function booted(): void
    {
        static::creating(function (Category $category) {
            if (!$category->name_ar && $category->name) {
                $category->name_ar = $category->name;
                $category->name_en = $category->name;
            }
            if (!$category->subtitle_ar && $category->description) {
                $category->subtitle_ar = $category->description;
                $category->subtitle_en = $category->description;
            }
        });
    }

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class)->orderBy('order');
    }
}
