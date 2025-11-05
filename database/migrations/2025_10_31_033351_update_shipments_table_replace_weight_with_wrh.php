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
        Schema::table('shipments', function (Blueprint $table) {
            // Drop old weight columns
            $table->dropColumn(['weight', 'weight_unit']);
            // Add new wrh column
            $table->string('wrh')->nullable()->after('destination_city');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            // Drop wrh column
            $table->dropColumn('wrh');
            // Add back old weight columns
            $table->decimal('weight', 8, 2)->nullable()->after('destination_city');
            $table->string('weight_unit', 10)->default('kg')->after('weight');
        });
    }
};
