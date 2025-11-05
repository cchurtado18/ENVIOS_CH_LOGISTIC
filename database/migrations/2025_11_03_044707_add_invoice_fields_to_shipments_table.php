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
            // Internal status for admin (different from customer-facing status)
            $table->string('internal_status')->nullable()->after('status');
            
            // Invoice fields
            $table->enum('service_type_billing', ['maritime', 'aerial'])->nullable()->after('service_type');
            $table->decimal('price_per_pound', 8, 2)->nullable()->after('value');
            $table->decimal('invoice_value', 10, 2)->nullable()->after('price_per_pound');
            $table->foreignId('invoice_id')->nullable()->after('invoice_value');
            $table->timestamp('invoiced_at')->nullable()->after('invoice_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropColumn([
                'internal_status',
                'service_type_billing',
                'price_per_pound',
                'invoice_value',
                'invoice_id',
                'invoiced_at',
            ]);
        });
    }
};
