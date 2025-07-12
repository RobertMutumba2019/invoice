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
        Schema::create('departments', function (Blueprint $table) {
            $table->id('dept_id');
            $table->string('dept_name');
            $table->text('dept_description')->nullable();
            $table->boolean('dept_active')->default(true);
            $table->unsignedBigInteger('dept_added_by')->nullable();
            $table->timestamp('dept_date_added')->useCurrent();
            $table->timestamps();
            
            $table->foreign('dept_added_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
}; 