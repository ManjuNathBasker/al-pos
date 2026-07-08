<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Branches Table
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->nullable();
            $table->string('address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Cards Table (Card Master)
        Schema::create('cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('bank_name');
            $table->string('card_network'); // Visa, Mastercard, RuPay, Amex, etc.
            $table->string('card_type'); // Credit, Debit, EMI, Gift, Custom, etc.
            $table->foreignId('settlement_account_id')->constrained('accounts')->cascadeOnDelete();
            $table->decimal('service_charge', 5, 2)->default(0.00); // customer service charge %
            $table->decimal('mdr', 5, 2)->default(0.00); // merchant mdr %
            $table->decimal('processing_fee', 10, 2)->default(0.00); // flat transaction fee
            $table->integer('settlement_days')->default(1);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        // 3. Bank Offers Table
        Schema::create('bank_offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('min_purchase', 12, 2)->default(0.00);
            $table->decimal('max_discount', 12, 2)->default(0.00);
            $table->enum('discount_type', ['percent', 'flat'])->default('percent');
            $table->decimal('discount_value', 12, 2)->default(0.00);
            $table->decimal('cashback', 12, 2)->default(0.00);
            $table->boolean('is_emi_offer')->default(false);
            $table->integer('usage_limit')->default(0);
            $table->integer('used_count')->default(0);
            $table->decimal('merchant_contribution', 5, 2)->default(100.00); // % cost merchant bears
            $table->decimal('bank_contribution', 5, 2)->default(0.00); // % cost bank bears
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        // 4. Offer Pivot Tables
        Schema::create('bank_offer_card', function (Blueprint $table) {
            $table->foreignId('bank_offer_id')->constrained('bank_offers')->cascadeOnDelete();
            $table->foreignId('card_id')->constrained('cards')->cascadeOnDelete();
            $table->primary(['bank_offer_id', 'card_id']);
        });

        Schema::create('bank_offer_product', function (Blueprint $table) {
            $table->foreignId('bank_offer_id')->constrained('bank_offers')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->primary(['bank_offer_id', 'product_id']);
        });

        Schema::create('bank_offer_category', function (Blueprint $table) {
            $table->foreignId('bank_offer_id')->constrained('bank_offers')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->primary(['bank_offer_id', 'category_id']);
        });

        Schema::create('bank_offer_customer', function (Blueprint $table) {
            $table->foreignId('bank_offer_id')->constrained('bank_offers')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->primary(['bank_offer_id', 'customer_id']);
        });

        Schema::create('bank_offer_branch', function (Blueprint $table) {
            $table->foreignId('bank_offer_id')->constrained('bank_offers')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->primary(['bank_offer_id', 'branch_id']);
        });

        // 5. Card Transactions Table
        Schema::create('card_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('card_id')->constrained('cards')->cascadeOnDelete();
            $table->string('bank_name');
            $table->decimal('gross_amount', 12, 2);
            $table->decimal('discount_amount', 12, 2)->default(0.00);
            $table->decimal('cashback_amount', 12, 2)->default(0.00);
            $table->decimal('service_charge_amount', 12, 2)->default(0.00);
            $table->decimal('mdr_amount', 12, 2)->default(0.00);
            $table->decimal('processing_fee_amount', 12, 2)->default(0.00);
            $table->decimal('net_settlement_amount', 12, 2);
            $table->decimal('merchant_discount_share', 12, 2)->default(0.00);
            $table->decimal('bank_discount_share', 12, 2)->default(0.00);
            $table->foreignId('bank_offer_id')->nullable()->constrained('bank_offers')->nullOnDelete();
            $table->enum('settlement_status', ['pending', 'completed', 'failed'])->default('pending');
            $table->date('settlement_date')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        // 6. Bank Settlements Table
        Schema::create('bank_settlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('card_transaction_id')->constrained('card_transactions')->cascadeOnDelete();
            $table->string('bank_statement_reference')->nullable();
            $table->decimal('expected_settlement_amount', 12, 2);
            $table->decimal('actual_settlement_amount', 12, 2);
            $table->decimal('settlement_difference', 12, 2)->default(0.00);
            $table->decimal('bank_charges', 12, 2)->default(0.00);
            $table->decimal('processing_charges', 12, 2)->default(0.00);
            $table->foreignId('adjustment_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();
            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending');
            $table->date('settlement_date');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_settlements');
        Schema::dropIfExists('card_transactions');
        Schema::dropIfExists('bank_offer_branch');
        Schema::dropIfExists('bank_offer_customer');
        Schema::dropIfExists('bank_offer_category');
        Schema::dropIfExists('bank_offer_product');
        Schema::dropIfExists('bank_offer_card');
        Schema::dropIfExists('bank_offers');
        Schema::dropIfExists('cards');
        Schema::dropIfExists('branches');
    }
};
