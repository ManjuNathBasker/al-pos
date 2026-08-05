<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add card commission fields to orders table
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('card_type_id')
                  ->nullable()
                  ->after('register_session_id')
                  ->constrained('card_types')
                  ->nullOnDelete();
            $table->decimal('card_commission_amount', 12, 4)->default(0.0000)->after('card_type_id');
            $table->decimal('card_commission_tax_amount', 12, 4)->default(0.0000)->after('card_commission_amount');
            $table->decimal('card_commission_total_deduction', 12, 4)->default(0.0000)->after('card_commission_tax_amount');
            $table->decimal('card_net_received', 12, 4)->default(0.0000)->after('card_commission_total_deduction');
        });

        // Add is_card_account flag to accounts table (explicit, no auto-detection)
        Schema::table('accounts', function (Blueprint $table) {
            $table->boolean('is_card_account')->default(false)->after('show_in_pos');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['card_type_id']);
            $table->dropColumn([
                'card_type_id',
                'card_commission_amount',
                'card_commission_tax_amount',
                'card_commission_total_deduction',
                'card_net_received',
            ]);
        });

        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn('is_card_account');
        });
    }
};
