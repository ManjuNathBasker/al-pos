<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Alter enum to include 'closed' status
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending', 'paid', 'refunded', 'cancelled', 'void', 'closed') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert enum change if possible, though reverting enum can be tricky if 'closed' orders exist.
        // For safety, we just leave it or reset to the original if no 'closed' rows exist.
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending', 'paid', 'refunded', 'cancelled', 'void') DEFAULT 'pending'");
    }
};
