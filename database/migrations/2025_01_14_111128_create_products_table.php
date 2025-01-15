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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('brand_id');
            $table->string('brand')->nullable();
            $table->unsignedBigInteger('category_id');
            $table->string('category')->nullable();
            $table->integer('stock')->nullable();
            $table->boolean('hide')->nullable(); // Allow NULL values
            $table->decimal('price', 10, 2);
            $table->boolean('featured')->nullable(); // Allow NULL values
            $table->decimal('net', 10, 2)->nullable();
            $table->longText('details')->nullable();
            $table->text('images')->nullable();
            $table->integer('ordernum')->default(0);
            $table->boolean('offer')->nullable(); // Allow NULL values
            $table->boolean('trending')->nullable(); // Allow NULL values
            $table->boolean('flash')->nullable(); // Allow NULL values
            $table->boolean('new')->nullable(); // Allow NULL values
            $table->json('variations')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
