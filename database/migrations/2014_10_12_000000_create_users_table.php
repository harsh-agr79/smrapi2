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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();

            $table->string('email_enc')->nullable(); // Encrypted email
            $table->string('token_fp')->nullable(); // Forgot password token
            $table->timestamp('fp_at')->nullable(); // Forgot password timestamp
            $table->json('cart')->nullable(); // Cart data in JSON format
            $table->json('wishlist')->nullable(); // Wishlist data in JSON format
            $table->json('billing_address')->nullable(); // Billing address
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
