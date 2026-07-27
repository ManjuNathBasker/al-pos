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
        Schema::create('cash_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('register_session_id')->constrained('register_sessions')->onDelete('cascade');
            $table->enum('type', [
                'CASH_SALE', 
                'CASH_REFUND', 
                'EXPENSE', 
                'OWNER_WITHDRAWAL', 
                'CASH_DEPOSIT', 
                'OPENING_BALANCE', 
                'CLOSING_BALANCE'
            ]);
            $table->decimal('amount', 12, 2);
            $table->string('payment_method')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_transactions');
    }
};
