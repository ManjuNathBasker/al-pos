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

            // Relationships
            $table->foreignId('category_id')
                  ->constrained('categories')
                  ->restrictOnDelete();

            $table->foreignId('unit_id')
                  ->nullable()
                  ->constrained('units')
                  ->nullOnDelete();

            // Identity
            $table->string('name');
            $table->string('slug')->unique()->nullable();
            $table->string('sku')->unique()->nullable()->comment('Stock Keeping Unit');
            $table->string('barcode')->unique()->nullable()->comment('EAN / UPC barcode');
            $table->text('description')->nullable();

            // Media
            $table->string('image')->nullable()->comment('Primary product image path');

            // Pricing
            $table->decimal('price', 12, 2)->comment('Selling price');
            $table->decimal('cost_price', 12, 2)->nullable()->comment('Purchase / cost price');
            $table->decimal('compare_price', 12, 2)->nullable()->comment('Original price for strike-through display');
            $table->unsignedTinyInteger('tax_rate')->default(0)->comment('Tax percentage, e.g. 8 = 8%');

            // Inventory
            $table->integer('stock_qty')->default(0);
            $table->integer('low_stock_threshold')->default(5);
            $table->boolean('track_stock')->default(true);
            $table->boolean('allow_backorder')->default(false);

            // Status
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);

            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            // Indexes for POS queries
            $table->index(['is_active', 'category_id']);
            $table->index(['is_active', 'is_featured']);
            $table->index('sku');
            $table->index('barcode');
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
