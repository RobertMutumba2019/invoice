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
        Schema::create('credit_notes', function (Blueprint $table) {
            $table->id('cn_id');
            $table->string('cn_no')->nullable()->comment('Credit Note Number');
            $table->unsignedBigInteger('original_invoice_id');
            $table->string('original_invoice_no');
            $table->string('buyer_name');
            $table->string('buyer_tin')->nullable();
            $table->string('buyer_address')->nullable();
            $table->string('buyer_phone')->nullable();
            $table->string('buyer_email')->nullable();
            $table->decimal('invoice_amount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->string('currency', 3)->default('UGX');
            $table->text('reason')->nullable();
            $table->string('reason_code')->nullable();
            $table->enum('status', ['DRAFT', 'SUBMITTED', 'APPROVED', 'CANCELLED'])->default('DRAFT');
            $table->string('efris_cn_no')->nullable()->comment('EFRIS Credit Note Number');
            $table->string('fdn')->nullable()->comment('EFRIS FDN');
            $table->string('qr_code')->nullable()->comment('QR Code');
            $table->text('efris_response')->nullable()->comment('EFRIS API Response');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('original_invoice_id')->references('invoice_id')->on('invoices')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');

            $table->index(['cn_no', 'status']);
            $table->index('original_invoice_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_notes');
    }
}; 