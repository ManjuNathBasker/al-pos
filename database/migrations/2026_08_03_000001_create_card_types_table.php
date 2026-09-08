<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('card_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');                                     // e.g. Standard, Premium, Corporate
            $table->enum('commission_type', ['percentage', 'fixed'])->default('percentage');
            $table->decimal('commission_value', 10, 4)->default(0.0000); // % or flat amount
            $table->enum('commission_handling', [
                'ignore',
                'auto_write_off',
                'settlement_tracking',          // future phase
            ])->default('ignore');
            $table->foreignId('expense_account_id')                    // GL account for commission expense
                  ->nullable()
                  ->constrained('accounts')
                  ->nullOnDelete();
            $table->boolean('status')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('card_types');
    }
};
