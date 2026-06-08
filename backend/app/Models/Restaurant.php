<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Restaurant extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'slug',
        'status',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function settings()
    {
        return $this->hasOne(RestaurantSetting::class);
    }

    public function categories()
    {
        return $this->hasMany(Category::class)->orderBy('order');
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function offers()
    {
        return $this->hasMany(Offer::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function activeSubscription()
    {
        return $this->hasOne(Subscription::class)
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>', now());
            })
            ->latestOfMany();
    }

    public function menuViews()
    {
        return $this->hasMany(MenuView::class);
    }

    public function productViews()
    {
        return $this->hasMany(ProductView::class);
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }
}
