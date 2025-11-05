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
        Schema::create('pending_trackings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('tracking_number');
            $table->enum('status', ['waiting', 'found', 'failed'])->default('waiting');
            $table->integer('attempts')->default(0);
            $table->timestamp('found_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
            
            // Unique constraint to prevent duplicate pending trackings per user
            $table->unique(['user_id', 'tracking_number', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pending_trackings');
    }
};
