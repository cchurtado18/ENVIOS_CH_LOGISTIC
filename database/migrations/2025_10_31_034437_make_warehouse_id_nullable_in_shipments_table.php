<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if foreign key exists before trying to drop it
        $foreignKeys = \Illuminate\Support\Facades\DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'shipments' 
            AND COLUMN_NAME = 'warehouse_id' 
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ");
        
        if (!empty($foreignKeys)) {
            Schema::table('shipments', function (Blueprint $table) {
                // Drop the foreign key constraint if it exists
                $table->dropForeign(['warehouse_id']);
            });
        }
        
        Schema::table('shipments', function (Blueprint $table) {
            // Change warehouse_id to nullable
            $table->unsignedBigInteger('warehouse_id')->nullable()->change();
        });
        
        // Only recreate foreign key if warehouses table exists
        if (Schema::hasTable('warehouses')) {
            Schema::table('shipments', function (Blueprint $table) {
                // Recreate the foreign key constraint
                $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropForeign(['warehouse_id']);
        });
        
        Schema::table('shipments', function (Blueprint $table) {
            $table->unsignedBigInteger('warehouse_id')->nullable(false)->change();
        });
        
        Schema::table('shipments', function (Blueprint $table) {
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('cascade');
        });
    }
};
