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
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('setting_key')->unique();
            $table->text('setting_value');
            $table->string('setting_type')->default('string'); // string, integer, boolean, json
            $table->string('setting_group')->default('general'); // general, efris, email, system
            $table->string('setting_label');
            $table->text('setting_description')->nullable();
            $table->boolean('is_public')->default(false); // whether non-admin users can view
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
}; 