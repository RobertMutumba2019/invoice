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
        Schema::create('stock_decreases', function (Blueprint $table) {
            $table->id();
            $table->string('sun_reference')->nullable(); // SUN Systems reference
            $table->string('item_code'); // EFRIS goods code
            $table->decimal('quantity', 15, 2); // Stock quantity to decrease
            $table->string('reference')->nullable(); // EFRIS stock reference
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->text('remarks')->nullable();
            $table->string('decrease_reason')->nullable(); // Reason for stock decrease
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            // You may want to add a foreign key for item_code if you have a goods table
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_decreases');
    }
};
