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
            $table->string('currency_code', 10)->nullable()->after('total_paid');
            $table->string('currency_symbol', 10)->nullable()->after('currency_code');
            $table->string('currency_symbol_position', 10)->default('before')->after('currency_symbol');
            $table->unsignedTinyInteger('currency_decimal_places')->default(2)->after('currency_symbol_position');
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->string('currency_code', 10)->nullable()->after('total_amount');
            $table->string('currency_symbol', 10)->nullable()->after('currency_code');
            $table->string('currency_symbol_position', 10)->default('before')->after('currency_symbol');
            $table->unsignedTinyInteger('currency_decimal_places')->default(2)->after('currency_symbol_position');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'currency_code',
                'currency_symbol',
                'currency_symbol_position',
                'currency_decimal_places',
            ]);
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn([
                'currency_code',
                'currency_symbol',
                'currency_symbol_position',
                'currency_decimal_places',
            ]);
        });
    }
};
