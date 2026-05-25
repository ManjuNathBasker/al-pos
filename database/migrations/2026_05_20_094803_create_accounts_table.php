<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('account_name');
            $table->string('account_code')->nullable();
            $table->enum('account_type', ['Asset', 'Liability', 'Equity', 'Income', 'Expense']);
            $table->foreignId('parent_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->decimal('opening_balance', 15, 2)->default(0.00);
            $table->decimal('current_balance', 15, 2)->default(0.00);
            $table->boolean('status')->default(true);
            $table->boolean('is_system')->default(false); // Indicates if this is a default account created by the system
            $table->timestamps();
            $table->softDeletes();
            
            // Ensures tenant safety for duplicate account codes
            $table->unique(['company_id', 'account_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
