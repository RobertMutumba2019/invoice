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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            
            // EFRIS specific fields
            $table->string('user_name')->unique(); // Username for login
            $table->string('user_surname');
            $table->string('user_othername')->nullable();
            $table->string('user_phone')->nullable();
            $table->unsignedBigInteger('user_department_id')->nullable();
            $table->unsignedBigInteger('user_designation')->nullable();
            $table->boolean('user_active')->default(true);
            $table->boolean('user_online')->default(false);
            $table->boolean('user_forgot_password')->default(false);
            $table->timestamp('user_last_logged_in')->nullable();
            $table->timestamp('user_last_active')->nullable();
            $table->timestamp('user_last_changed')->nullable();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
