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
        Schema::table('payments', function (Blueprint $table) {
            // Cambiar qr_image_url de VARCHAR(255) a TEXT para soportar base64 largo
            $table->text('qr_image_url')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Revertir a VARCHAR(255)
            $table->string('qr_image_url', 255)->nullable()->change();
        });
    }
};
