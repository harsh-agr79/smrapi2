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
        Schema::create('orders', function (Blueprint $table) {
            $table->id(); // order_id
            $table->foreignId('customer_id')->constrained('users')->onDelete('cascade');
            $table->timestamp('order_date')->useCurrent();
            $table->text('billing_address');
            $table->string('current_status')->default('pending');
            $table->decimal('total_amount', 10, 2);
            $table->decimal('delivery_charge', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('discounted_total', 10, 2);
            $table->decimal('net_total', 10, 2);
            $table->enum('payment_status', ['pending', 'paid', 'cod'])->default('pending');
            $table->timestamp('last_status_updated')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
