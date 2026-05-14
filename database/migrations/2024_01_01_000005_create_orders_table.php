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
            $table->id();

            // Reference
            $table->string('order_number')->unique()->comment('Human-readable order ID e.g. ORD-00042');

            // Cashier / user who created the order
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            // Financials
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('amount_paid', 12, 2)->default(0);
            $table->decimal('change_due', 12, 2)->default(0);

            // Payment
            $table->enum('payment_method', ['cash', 'card', 'qr', 'mixed', 'other'])->default('cash');
            $table->string('payment_reference')->nullable()->comment('Card auth code, transaction ID, etc.');

            // Status
            $table->enum('status', ['pending', 'paid', 'refunded', 'cancelled', 'void'])->default('pending');

            // Notes
            $table->text('note')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'created_at']);
            $table->index('user_id');
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
