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
            $table->string('qr_image_url')->nullable()->after('reference');
            $table->string('qr_transaction_id')->nullable()->after('qr_image_url');
            $table->string('payment_number')->nullable()->unique()->after('qr_transaction_id');
            $table->json('qr_response')->nullable()->after('payment_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['qr_image_url', 'qr_transaction_id', 'payment_number', 'qr_response']);
        });
    }
};
