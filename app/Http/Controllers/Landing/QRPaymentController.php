<?php

namespace App\Http\Controllers\Landing;

use App\Enums\BookingPayment;
use App\Enums\BookingStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\BookingResource;
use App\Mail\BookingConfirmed;
use App\Models\Booking;
use App\Models\Payment;
use App\Services\MasterQRService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class QRPaymentController extends Controller
{
    protected MasterQRService $masterQRService;

    public function __construct(MasterQRService $masterQRService)
    {
        $this->masterQRService = $masterQRService;
    }

    /**
     * Genera un código QR para el pago de una reserva
     */
    public function generateQR(Booking $booking)
    {
        Log::info('🚀 Iniciando generación de QR', [
            'booking_id' => $booking->id,
            'customer_id' => auth('customer')->id(),
        ]);

        // Verificar que el cliente sea el dueño de la reserva
        if (!$booking->isPayable() || $booking->customer_id !== auth('customer')->id()) {
            Log::error('❌ No autorizado para generar QR', [
                'booking_id' => $booking->id,
                'is_payable' => $booking->isPayable(),
                'booking_customer_id' => $booking->customer_id,
                'auth_customer_id' => auth('customer')->id(),
            ]);
            abort(403, 'No autorizado para generar QR para esta reserva');
        }

        $customer = $booking->customer;

        Log::info('✅ Cliente verificado', [
            'customer_id' => $customer->id,
            'customer_email' => $customer->email,
        ]);

        // Usar monto de prueba en lugar del monto real (para demos/presentaciones)
        $testAmount = config('services.masterqr.test_amount', 0.10);

        Log::info('💰 Usando monto de prueba', [
            'monto_real' => $booking->total_price,
            'monto_prueba' => $testAmount,
        ]);

        // Generar número de pago único
        $paymentNumber = 'HOTEL-' . $booking->id . '-' . time();

        // Preparar detalles de la orden con monto de prueba
        $orderDetail = [
            [
                'serial' => 1,
                'product' => 'Demo - Reserva Hotel #' . $booking->id,
                'quantity' => 1,
                'price' => (float) $testAmount,
                'discount' => 0,
                'total' => (float) $testAmount,
            ]
        ];

        // Datos para MasterQR
        $paymentData = [
            'client_name' => $customer->first_name . ' ' . $customer->last_name,
            'document_type' => 1, // 1 = CI (Cédula de Identidad)
            'document_id' => $customer->national_id ?? '0',
            'phone_number' => $customer->phone_number ?? '00000000',
            'email' => $customer->email,
            'payment_number' => $paymentNumber,
            'amount' => (float) $testAmount, // Monto de prueba
            // La callback URL se obtiene de la configuración (config/services.php)
            'order_detail' => $orderDetail,
        ];

        Log::info('📝 Datos de pago preparados', [
            'payment_number' => $paymentNumber,
            'amount' => $paymentData['amount'],
            'client_name' => $paymentData['client_name'],
        ]);

        // Generar QR
        $qrResponse = $this->masterQRService->generateQR($paymentData);

        Log::info('📡 Respuesta de MasterQR recibida', [
            'response_received' => $qrResponse !== null,
            'response_data' => $qrResponse,
        ]);

        if (!$qrResponse) {
            Log::error('❌ No se pudo generar el código QR', [
                'payment_data' => $paymentData,
            ]);
            return response()->json([
                'error' => 'No se pudo generar el código QR. Por favor, intente nuevamente.',
                'success' => false
            ], 500);
        }

        // Extraer datos de la respuesta (pueden estar en diferentes ubicaciones)
        $qrImageData = $qrResponse['qrImage'] ?? $qrResponse['values']['qrBase64'] ?? null;
        $transactionId = $qrResponse['transactionId'] ?? $qrResponse['values']['transactionId'] ?? null;

        // Actualizar o crear el pago con la información del QR
        $payment = $booking->payments()->first();

        if ($payment) {
            $payment->update([
                'payment_method' => PaymentMethod::QR_CODE,
                'payment_number' => $paymentNumber,
                'qr_image_url' => $qrImageData,
                'qr_transaction_id' => $transactionId,
                'qr_response' => $qrResponse,
            ]);
        } else {
            $payment = $booking->payments()->create([
                'amount' => $booking->total_price,
                'payment_method' => PaymentMethod::QR_CODE,
                'status' => PaymentStatus::PENDING,
                'payment_number' => $paymentNumber,
                'qr_image_url' => $qrImageData,
                'qr_transaction_id' => $transactionId,
                'qr_response' => $qrResponse,
            ]);
        }

        Log::info('💾 Pago guardado en base de datos', [
            'payment_id' => $payment->id,
            'has_qr_data' => !empty($qrImageData),
        ]);

        $responseData = [
            'success' => true,
            'qr_image' => $qrResponse['qrImage'] ?? $qrResponse['values']['qrBase64'] ?? null,
            'transaction_id' => $qrResponse['transactionId'] ?? $qrResponse['values']['transactionId'] ?? null,
            'payment_number' => $paymentNumber,
            'amount' => $testAmount, // Monto de prueba
            'original_amount' => $booking->total_price, // Monto real de la reserva
            'is_test' => true, // Indicar que es un pago de prueba
        ];

        Log::info('✅ QR generado exitosamente', [
            'response_data' => $responseData,
            'has_qr_image' => !empty($responseData['qr_image']),
            'has_transaction_id' => !empty($responseData['transaction_id']),
        ]);

        return response()->json($responseData);
    }

    /**
     * Webhook que recibe las notificaciones de MasterQR cuando se completa un pago
     */
    public function callback(Request $request)
    {
        Log::info('MasterQR Callback received', $request->all());

        // Validar la firma del webhook (implementar según documentación de MasterQR)
        // $isValid = $this->masterQRService->validateWebhookSignature(
        //     $request->all(),
        //     $request->header('X-Signature')
        // );

        // if (!$isValid) {
        //     Log::warning('Invalid webhook signature from MasterQR');
        //     return response()->json(['error' => 'Invalid signature'], 403);
        // }

        // Obtener el número de pago
        $paymentNumber = $request->input('paymentNumber') ?? $request->input('payment_number');

        if (!$paymentNumber) {
            Log::error('Payment number not found in callback', $request->all());
            return response()->json(['error' => 'Payment number required'], 400);
        }

        // Buscar el pago por payment_number
        $payment = Payment::where('payment_number', $paymentNumber)->first();

        if (!$payment) {
            Log::error('Payment not found', ['payment_number' => $paymentNumber]);
            return response()->json(['error' => 'Payment not found'], 404);
        }

        // Verificar el estado del pago desde MasterQR
        $transactionStatus = $request->input('status') ?? $request->input('transactionStatus');

        // Estados posibles de MasterQR: 'completed', 'pending', 'failed', etc.
        // Ajustar según la documentación real de MasterQR
        if (in_array(strtolower($transactionStatus), ['completed', 'success', 'paid'])) {
            DB::transaction(function () use ($payment, $request) {
                $booking = $payment->booking;

                // Actualizar el pago
                $payment->update([
                    'status' => PaymentStatus::PAID,
                    'paid_at' => now(),
                    'reference' => $request->input('transactionId'),
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

                // Enviar email de confirmación
                Mail::to($booking->customer->email)->send(new BookingConfirmed($booking));

                Log::info('Payment confirmed via QR', [
                    'payment_id' => $payment->id,
                    'booking_id' => $booking->id,
                    'amount' => $payment->amount,
                ]);
            });

            return response()->json(['success' => true, 'message' => 'Payment processed']);
        }

        // Si el pago falló
        if (in_array(strtolower($transactionStatus), ['failed', 'rejected', 'cancelled'])) {
            $payment->update([
                'status' => PaymentStatus::FAILED,
            ]);

            Log::warning('Payment failed via QR', [
                'payment_id' => $payment->id,
                'status' => $transactionStatus,
            ]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Consulta el estado de una transacción QR
     */
    public function queryStatus(Booking $booking)
    {
        if ($booking->customer_id !== auth('customer')->id()) {
            abort(403);
        }

        $payment = $booking->payments()->where('payment_method', PaymentMethod::QR_CODE)->first();

        if (!$payment || !$payment->qr_transaction_id) {
            return response()->json(['error' => 'No QR transaction found'], 404);
        }

        $status = $this->masterQRService->queryTransaction($payment->qr_transaction_id);

        if (!$status) {
            return response()->json(['error' => 'Could not query transaction status'], 500);
        }

        return response()->json([
            'status' => $status,
            'payment_status' => $payment->status->value,
        ]);
    }

    /**
     * Página de checkout con opción de QR
     */
    public function checkout(Booking $booking)
    {
        if (!$booking->isPayable() || $booking->customer_id !== auth('customer')->id()) {
            abort(403);
        }

        $booking->load('charges')->loadCount('rooms');
        $roomType = $booking->rooms()->first()->type;
        $roomType->load('media', 'bedTypes');

        return inertia('Landing/CheckoutQR', [
            'booking' => BookingResource::make($booking),
            'roomType' => $roomType,
        ]);
    }
}
