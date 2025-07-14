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
        Schema::table('stocks', function (Blueprint $table) {
            $table->string('qrcode_path')->nullable()->after('remarks');
            $table->string('barcode_path')->nullable()->after('qrcode_path');
        });
        Schema::table('efris_goods', function (Blueprint $table) {
            $table->string('qrcode_path')->nullable()->after('eg_description');
            $table->string('barcode_path')->nullable()->after('qrcode_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stocks', function (Blueprint $table) {
            $table->dropColumn(['qrcode_path', 'barcode_path']);
        });
        Schema::table('efris_goods', function (Blueprint $table) {
            $table->dropColumn(['qrcode_path', 'barcode_path']);
        });
    }
};
