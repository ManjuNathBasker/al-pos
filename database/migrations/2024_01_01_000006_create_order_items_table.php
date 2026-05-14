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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                  ->constrained('orders')
                  ->cascadeOnDelete();

            // Snapshot of product at time of sale (product may change later)
            $table->foreignId('product_id')
                  ->nullable()
                  ->constrained('products')
                  ->nullOnDelete();

            $table->string('product_name')->nullable()->comment('Snapshot of name at time of sale');
            $table->string('product_sku')->nullable()->comment('Snapshot of SKU');

            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 12, 2)->comment('Price per unit at time of sale');
            $table->decimal('cost_price', 12, 2)->nullable()->comment('Cost per unit at time of sale');
            $table->decimal('discount_amount', 12, 2)->default(0)->comment('Per-line discount');
            $table->decimal('tax_amount', 12, 2)->default(0)->comment('Per-line tax');
            $table->decimal('subtotal', 12, 2)->comment('unit_price * quantity - discount + tax');

            $table->timestamps();

            $table->index('order_id');
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
