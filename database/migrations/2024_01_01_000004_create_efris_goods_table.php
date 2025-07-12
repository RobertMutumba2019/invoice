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
        Schema::create('efris_goods', function (Blueprint $table) {
            $table->id('eg_id');
            $table->string('eg_name');
            $table->string('eg_code')->unique();
            $table->text('eg_description')->nullable();
            $table->decimal('eg_price', 15, 2)->default(0);
            $table->string('eg_uom')->default('UNIT'); // Unit of Measure
            $table->string('eg_tax_category')->default('V'); // V=VAT, Z=Zero, E=Exempt
            $table->decimal('eg_tax_rate', 5, 2)->default(18.00);
            $table->boolean('eg_active')->default(true);
            $table->unsignedBigInteger('eg_added_by')->nullable();
            $table->timestamp('eg_date_added')->useCurrent();
            $table->timestamps();
            
            $table->foreign('eg_added_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('efris_goods');
    }
}; 