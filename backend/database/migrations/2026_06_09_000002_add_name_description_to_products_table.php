<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('name')->nullable()->after('restaurant_id');
            $table->text('description')->nullable()->after('name');
        });

        DB::statement("UPDATE products SET name = COALESCE(NULLIF(name_en, ''), name_ar) WHERE name IS NULL");
        DB::statement("UPDATE products SET description = COALESCE(NULLIF(description_en, ''), description_ar) WHERE description IS NULL AND (description_en IS NOT NULL OR description_ar IS NOT NULL)");
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['name', 'description']);
        });
    }
};
