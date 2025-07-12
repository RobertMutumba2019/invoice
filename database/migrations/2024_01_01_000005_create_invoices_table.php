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
            $table->id('invoice_id');
            $table->string('invoice_no')->unique();
            $table->string('efris_invoice_no')->nullable();
            $table->string('buyer_tin')->nullable();
            $table->string('buyer_name');
            $table->string('buyer_address')->nullable();
            $table->string('buyer_phone')->nullable();
            $table->string('buyer_email')->nullable();
            $table->decimal('invoice_amount', 15, 2);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2);
            $table->string('currency', 3)->default('UGX');
            $table->enum('invoice_type', ['LOCAL', 'EXPORT', 'CONTRACT', 'AUCTION'])->default('LOCAL');
            $table->enum('status', ['DRAFT', 'SUBMITTED', 'APPROVED', 'REJECTED', 'CANCELLED'])->default('DRAFT');
            $table->text('remarks')->nullable();
            $table->string('qr_code')->nullable();
            $table->string('fdn')->nullable(); // Fiscal Device Number
            $table->unsignedBigInteger('created_by');
            $table->timestamp('invoice_date');
            $table->timestamps();
            
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
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