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
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->string('tracking_number')->unique();
            $table->string('reference_number')->nullable();
            $table->foreignId('warehouse_id')->constrained()->onDelete('cascade');
            $table->string('status')->default('pending'); // pending, in_transit, delivered, exception
            $table->string('origin_address')->nullable();
            $table->string('destination_address')->nullable();
            $table->string('origin_city')->nullable();
            $table->string('destination_city')->nullable();
            $table->decimal('weight', 8, 2)->nullable();
            $table->string('weight_unit')->default('kg');
            $table->decimal('dimensions_length', 8, 2)->nullable();
            $table->decimal('dimensions_width', 8, 2)->nullable();
            $table->decimal('dimensions_height', 8, 2)->nullable();
            $table->string('dimensions_unit')->default('cm');
            $table->decimal('value', 10, 2)->nullable();
            $table->string('currency')->default('USD');
            $table->text('description')->nullable();
            $table->string('carrier')->nullable();
            $table->string('service_type')->nullable();
            $table->timestamp('pickup_date')->nullable();
            $table->timestamp('delivery_date')->nullable();
            $table->timestamp('last_scan_date')->nullable();
            $table->string('last_scan_location')->nullable();
            $table->json('tracking_events')->nullable(); // Array de eventos de tracking
            $table->json('metadata')->nullable(); // Datos adicionales del scraping
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
