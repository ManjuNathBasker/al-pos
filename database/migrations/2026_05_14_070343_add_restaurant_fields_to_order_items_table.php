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
        Schema::table('order_items', function (Blueprint $table) {
            $table->enum('kitchen_status', ['pending', 'preparing', 'ready', 'served'])->default('pending');
            $table->string('item_note')->nullable(); // e.g. "No onions", "Extra spicy"
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['kitchen_status', 'item_note']);
        });
    }
};
