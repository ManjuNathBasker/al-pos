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
        // 1. Inventory Items (Ingredients / Raw Materials)
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable(); // Future-ready
            $table->string('name');
            $table->string('code')->nullable();
            $table->enum('unit_type', ['kg', 'gram', 'liter', 'ml', 'piece']);
            $table->decimal('current_stock', 12, 3)->default(0);
            $table->decimal('minimum_stock', 12, 3)->default(0);
            $table->decimal('cost_price', 12, 2)->default(0);
            $table->foreignId('supplier_id')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'status']);
        });

        // 2. Recipe Items (Linking Products to Inventory Items)
        Schema::create('recipe_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inventory_item_id')->constrained()->cascadeOnDelete();
            $table->decimal('quantity', 12, 3);
            $table->timestamps();

            $table->index(['product_id', 'inventory_item_id']);
        });

        // 3. Inventory Transactions (Logs)
        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inventory_item_id')->constrained()->cascadeOnDelete();
            $table->enum('transaction_type', ['deduction', 'purchase', 'adjustment', 'wastage', 'restoration']);
            $table->decimal('quantity', 12, 3);
            $table->string('reference_type')->nullable(); // e.g., 'Order', 'Purchase'
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->index(['inventory_item_id', 'transaction_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_transactions');
        Schema::dropIfExists('recipe_items');
        Schema::dropIfExists('inventory_items');
    }
};
