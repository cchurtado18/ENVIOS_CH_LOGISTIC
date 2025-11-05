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
        // For SQLite, we need to drop and recreate the foreign key
        Schema::table('shipments', function (Blueprint $table) {
            // Drop the foreign key constraint
            $table->dropForeign(['warehouse_id']);
        });
        
        Schema::table('shipments', function (Blueprint $table) {
            // Change warehouse_id to nullable
            $table->unsignedBigInteger('warehouse_id')->nullable()->change();
        });
        
        Schema::table('shipments', function (Blueprint $table) {
            // Recreate the foreign key constraint
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('cascade');
        });
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
