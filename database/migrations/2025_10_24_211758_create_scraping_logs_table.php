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
        Schema::create('scraping_logs', function (Blueprint $table) {
            $table->id();
            $table->string('source_url');
            $table->string('scraper_type'); // everest_warehouse, everest_shipment, etc.
            $table->string('status'); // success, failed, partial
            $table->integer('records_found')->default(0);
            $table->integer('records_processed')->default(0);
            $table->integer('records_errors')->default(0);
            $table->text('error_message')->nullable();
            $table->json('response_data')->nullable(); // Datos de respuesta del scraping
            $table->integer('response_time_ms')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scraping_logs');
    }
};
