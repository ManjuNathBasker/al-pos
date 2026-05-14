<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = ['categories', 'products', 'customers', 'orders', 'coupons', 'users', 'order_items', 'wallet_transactions'];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $row) use ($table) {
                if ($table !== 'users') {
                    $row->foreignId('company_id')->nullable()->constrained()->onDelete('cascade');
                } else {
                    $row->foreignId('company_id')->after('id')->nullable()->constrained()->onDelete('cascade');
                }
                
                if (!in_array($table, ['users', 'order_items'])) {
                    $row->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
                    $row->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
                }
            });
        }
    }

    public function down(): void
    {
        $tables = ['categories', 'products', 'customers', 'orders', 'coupons', 'users', 'order_items', 'wallet_transactions'];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $row) use ($table) {
                $row->dropForeign([$table === 'users' ? 'company_id' : 'company_id']);
                $row->dropColumn('company_id');
                
                if (!in_array($table, ['users', 'order_items'])) {
                    $row->dropForeign(['created_by']);
                    $row->dropForeign(['updated_by']);
                    $row->dropColumn(['created_by', 'updated_by']);
                }
            });
        }
    }
};
