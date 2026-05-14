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
        $tables = ['units', 'product_images'];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $row) {
                $row->foreignId('company_id')->nullable()->constrained()->onDelete('cascade');
                $row->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
                $row->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            });
        }
    }

    public function down(): void
    {
        $tables = ['units', 'product_images'];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $row) {
                $row->dropForeign(['company_id']);
                $row->dropForeign(['created_by']);
                $row->dropForeign(['updated_by']);
                $row->dropColumn(['company_id', 'created_by', 'updated_by']);
            });
        }
    }
};
