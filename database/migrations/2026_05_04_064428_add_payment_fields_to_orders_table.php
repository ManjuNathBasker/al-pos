<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('customer_id')->nullable()->after('user_id')->constrained('customers')->nullOnDelete();
            $table->decimal('cash_amount', 12, 2)->default(0)->after('change_due');
            $table->decimal('upi_amount', 12, 2)->default(0)->after('cash_amount');
            $table->decimal('card_amount', 12, 2)->default(0)->after('upi_amount');
            $table->decimal('wallet_used', 12, 2)->default(0)->after('card_amount');
            $table->decimal('change_returned', 12, 2)->default(0)->after('wallet_used');
            $table->decimal('total_paid', 12, 2)->default(0)->after('change_returned');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropColumn([
                'customer_id',
                'cash_amount',
                'upi_amount',
                'card_amount',
                'wallet_used',
                'change_returned',
                'total_paid'
            ]);
        });
    }
};
