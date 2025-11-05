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
            // Check if metadata column exists before adding it
            if (!Schema::hasColumn('shipments', 'metadata')) {
                $table->json('metadata')->nullable()->after('tracking_events');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            // Only drop if column exists
            if (Schema::hasColumn('shipments', 'metadata')) {
                $table->dropColumn('metadata');
            }
        });
    }
};
