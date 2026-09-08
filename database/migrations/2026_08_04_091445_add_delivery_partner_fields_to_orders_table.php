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
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('delivery_partner_id')->nullable()->constrained('delivery_partners')->nullOnDelete();
            $table->decimal('delivery_commission_amount', 10, 4)->default(0);
            $table->string('settlement_status')->nullable(); // e.g. pending, settled
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['delivery_partner_id']);
            $table->dropColumn(['delivery_partner_id', 'delivery_commission_amount', 'settlement_status']);
        });
    }
};
