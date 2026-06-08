<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestaurantSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'restaurant_id',
        'name_ar',
        'name_en',
        'logo',
        'cover_image',
        'contact_email',
        'contact_phone',
        'address_ar',
        'address_en',
        'working_hours_ar',
        'working_hours_en',
        'social_links',
        'primary_color',
        'secondary_color',
        'accent_color',
        'button_style',
        'typography_selection',
    ];

    protected $casts = [
        'working_hours_ar' => 'array',
        'working_hours_en' => 'array',
        'social_links' => 'array',
    ];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }
}
