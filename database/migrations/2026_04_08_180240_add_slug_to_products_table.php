<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // 1. Find all products with an empty slug and give them a temporary unique slug

        $table->string('slug')->nullable()->after('name'); // Add slug column if it doesn't exist
        
        $products = DB::table('products')->where('slug', '')->orWhereNull('slug')->get();
        
        foreach ($products as $product) {
            DB::table('products')
                ->where('id', $product->id)
                ->update([
                    // Generates something like: existing-name-12, or just temp-slug-12
                    'slug' => 'temp-slug-' . $product->id 
                ]);
        }

        // 2. Now that there are no duplicates, it is safe to apply the unique constraint
        Schema::table('products', function (Blueprint $table) {
            $table->unique('slug');
        });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            //
        });
    }
};
