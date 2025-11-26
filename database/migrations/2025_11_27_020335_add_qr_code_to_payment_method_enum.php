<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Eliminar el CHECK constraint existente
        DB::statement('ALTER TABLE payments DROP CONSTRAINT IF EXISTS payments_payment_method_check');

        // Crear un nuevo CHECK constraint que incluya 'qr_code'
        DB::statement("
            ALTER TABLE payments
            ADD CONSTRAINT payments_payment_method_check
            CHECK (payment_method IN ('credit_card', 'cash', 'bank_transfer', 'online', 'qr_code'))
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar el constraint y recrearlo sin 'qr_code'
        DB::statement('ALTER TABLE payments DROP CONSTRAINT IF EXISTS payments_payment_method_check');

        DB::statement("
            ALTER TABLE payments
            ADD CONSTRAINT payments_payment_method_check
            CHECK (payment_method IN ('credit_card', 'cash', 'bank_transfer', 'online'))
        ");
    }
};
