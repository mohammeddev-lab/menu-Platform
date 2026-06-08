<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('restaurant_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->onDelete('cascade');
            $table->string('name_ar');
            $table->string('name_en');
            $table->string('logo')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('address_ar')->nullable();
            $table->string('address_en')->nullable();
            $table->json('working_hours_ar')->nullable();
            $table->json('working_hours_en')->nullable();
            $table->json('social_links')->nullable();
            
            // Theme Customization
            $table->string('primary_color')->default('#1a3c2f');
            $table->string('secondary_color')->default('#c8a97e');
            $table->string('accent_color')->default('#f5f0e8');
            $table->string('button_style')->default('rounded-lg');
            $table->string('typography_selection')->default('Cairo');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('restaurant_settings');
    }
};
