<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Offer;
use App\Models\Product;
use App\Models\Restaurant;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Policies\CategoryPolicy;
use App\Policies\OfferPolicy;
use App\Policies\ProductPolicy;
use App\Policies\RestaurantPolicy;
use App\Policies\SubscriptionPlanPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    protected $policies = [
        Restaurant::class => RestaurantPolicy::class,
        Category::class => CategoryPolicy::class,
        Product::class => ProductPolicy::class,
        Offer::class => OfferPolicy::class,
        SubscriptionPlan::class => SubscriptionPlanPolicy::class,
        User::class => UserPolicy::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
