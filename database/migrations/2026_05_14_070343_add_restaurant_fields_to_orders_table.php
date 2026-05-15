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
            $table->foreignId('table_id')->nullable()->constrained('restaurant_tables')->nullOnDelete();
            $table->enum('service_type', ['retail', 'dine_in', 'takeaway', 'delivery'])->default('retail');
            $table->enum('kitchen_status', ['pending', 'preparing', 'ready', 'served', 'none'])->default('none');
            $table->foreignId('waiter_id')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['table_id']);
            $table->dropForeign(['waiter_id']);
            $table->dropColumn(['table_id', 'service_type', 'kitchen_status', 'waiter_id']);
        });
    }
};
