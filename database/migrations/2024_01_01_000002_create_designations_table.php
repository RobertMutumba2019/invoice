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
        Schema::create('designations', function (Blueprint $table) {
            $table->id('designation_id');
            $table->string('designation_name');
            $table->text('designation_description')->nullable();
            $table->boolean('designation_active')->default(true);
            $table->unsignedBigInteger('designation_added_by')->nullable();
            $table->timestamp('designation_date_added')->useCurrent();
            $table->timestamps();
            
            $table->foreign('designation_added_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('designations');
    }
}; 