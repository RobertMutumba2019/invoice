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
        Schema::create('credit_note_items', function (Blueprint $table) {
            $table->id('cni_id');
            $table->unsignedBigInteger('credit_note_id');
            $table->unsignedBigInteger('original_item_id')->nullable();
            $table->string('item_name');
            $table->string('item_code');
            $table->decimal('quantity', 10, 2);
            $table->decimal('unit_price', 15, 2);
            $table->string('uom', 10);
            $table->string('tax_category', 10);
            $table->decimal('tax_rate', 5, 2);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->timestamps();

            $table->foreign('credit_note_id')->references('cn_id')->on('credit_notes')->onDelete('cascade');
            $table->foreign('original_item_id')->references('id')->on('invoice_items');

            $table->index('credit_note_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_note_items');
    }
}; 