<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        SubscriptionPlan::create([
            'name' => 'Free Plan',
            'price' => 0.00,
            'duration_days' => 30,
            'features' => ['qr_code', 'standard_menu'],
            'limit_categories' => 2,
            'limit_products' => 15,
        ]);

        SubscriptionPlan::create([
            'name' => 'Basic Plan',
            'price' => 19.00,
            'duration_days' => 30,
            'features' => ['qr_code', 'standard_menu', 'basic_analytics', 'png_downloads'],
            'limit_categories' => 10,
            'limit_products' => 50,
        ]);

        SubscriptionPlan::create([
            'name' => 'Premium Plan',
            'price' => 49.00,
            'duration_days' => 30,
            'features' => ['qr_code', 'standard_menu', 'advanced_analytics', 'pdf_downloads', 'theme_customization', 'multilingual'],
            'limit_categories' => 30,
            'limit_products' => 200,
        ]);

        SubscriptionPlan::create([
            'name' => 'Enterprise Plan',
            'price' => 149.00,
            'duration_days' => 365,
            'features' => ['qr_code', 'standard_menu', 'advanced_analytics', 'pdf_downloads', 'theme_customization', 'multilingual', 'custom_domain', 'priority_support'],
            'limit_categories' => 999,
            'limit_products' => 9999,
        ]);
    }
}
