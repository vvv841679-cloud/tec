<?php

namespace App\Console\Commands;

use App\Enums\BookingPayment;
use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Mail\BookingConfirmed;
use App\Models\Payment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SimulateMasterQRCallback extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'masterqr:simulate-callback {payment_number}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Simula un callback exitoso de MasterQR para testing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $paymentNumber = $this->argument('payment_number');

        $this->info("🔍 Buscando pago con número: {$paymentNumber}");

        // Buscar el pago por payment_number
        $payment = Payment::where('payment_number', $paymentNumber)->first();

        if (!$payment) {
            $this->error("❌ No se encontró ningún pago con el número: {$paymentNumber}");
            return 1;
        }

        $this->info("✅ Pago encontrado:");
        $this->info("   - ID: {$payment->id}");
        $this->info("   - Booking ID: {$payment->booking_id}");
        $this->info("   - Monto: Bs. {$payment->amount}");
        $this->info("   - Estado actual: {$payment->status->value}");

        if ($payment->status === PaymentStatus::PAID) {
            $this->warn("⚠️ El pago ya está marcado como pagado");
            return 0;
        }

        $this->info("\n💳 Simulando callback de MasterQR...");

        DB::transaction(function () use ($payment) {
            $booking = $payment->booking;

            // Actualizar el pago
            $payment->update([
                'status' => PaymentStatus::PAID,
                'paid_at' => now(),
                'reference' => 'SIMULATED-' . time(),
            ]);

            // Actualizar la reserva
            $booking->update([
                'status' => BookingStatus::RESERVED,
                'payment_status' => BookingPayment::PAID,
            ]);

            // Crear estado de reserva
            $booking->statuses()->create([
                'status' => BookingStatus::RESERVED,
            ]);

            // Enviar email de confirmación (comentado para testing)
            // Mail::to($booking->customer->email)->send(new BookingConfirmed($booking));

            Log::info('✅ Pago simulado como completado', [
                'payment_id' => $payment->id,
                'booking_id' => $booking->id,
                'amount' => $payment->amount,
            ]);
        });

        $this->info("\n✅ Callback simulado exitosamente!");
        $this->info("   - Estado del pago: PAID");
        $this->info("   - Estado de la reserva: RESERVED");
        $this->info("\n💡 Ahora el frontend detectará el cambio en el próximo polling");

        return 0;
    }
}
