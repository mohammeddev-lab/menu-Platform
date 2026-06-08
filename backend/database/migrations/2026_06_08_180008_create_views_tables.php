<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->onDelete('cascade');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->enum('device_type', ['mobile', 'tablet', 'desktop'])->default('desktop');
            $table->timestamp('viewed_at')->useCurrent();

            $table->index(['restaurant_id', 'viewed_at']);
        });

        Schema::create('product_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('restaurant_id')->constrained()->onDelete('cascade');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->enum('device_type', ['mobile', 'tablet', 'desktop'])->default('desktop');
            $table->timestamp('viewed_at')->useCurrent();

            $table->index(['product_id', 'viewed_at']);
            $table->index(['restaurant_id', 'viewed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_views');
        Schema::dropIfExists('menu_views');
    }
};
