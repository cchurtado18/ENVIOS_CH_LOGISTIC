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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('invoice_number')->unique();
            $table->integer('act_number')->nullable();
            $table->date('invoice_date');
            
            // Sender info
            $table->string('sender_name')->nullable();
            $table->string('sender_location')->nullable();
            $table->string('sender_phone')->nullable();
            
            // Recipient info
            $table->string('recipient_name')->nullable();
            $table->string('recipient_location')->nullable();
            $table->string('recipient_phone')->nullable();
            
            // Financial data
            $table->decimal('subtotal_maritime', 10, 2)->default(0);
            $table->decimal('subtotal_aerial', 10, 2)->default(0);
            $table->decimal('total_maritime_lbs', 8, 2)->default(0);
            $table->decimal('total_aerial_lbs', 8, 2)->default(0);
            $table->integer('package_count')->default(0);
            $table->decimal('delivery_cost', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
