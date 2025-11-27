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
    public function generateQR(Request $request, Booking $booking)
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

        // Obtener porcentaje y monto del request
        $percentage = $request->input('percentage', 100);
        $requestedAmount = $request->input('amount');

        // Validar que el porcentaje esté entre 1 y 100
        if ($percentage < 1 || $percentage > 100) {
            return response()->json([
                'error' => 'El porcentaje debe estar entre 1% y 100%',
                'success' => false
            ], 400);
        }

        // Calcular el monto basado en el porcentaje
        $paymentAmount = ($booking->total_price * $percentage) / 100;

        // Usar el monto solicitado si está presente, o el calculado
        $finalAmount = $requestedAmount ?? $paymentAmount;

        // Validar que el monto no exceda el total
        if ($finalAmount > $booking->total_price) {
            return response()->json([
                'error' => 'El monto no puede exceder el total de la reserva',
                'success' => false
            ], 400);
        }

        Log::info('💰 Monto a pagar calculado', [
            'total_booking' => $booking->total_price,
            'percentage' => $percentage,
            'calculated_amount' => $paymentAmount,
            'final_amount' => $finalAmount,
        ]);

        // MODO PRUEBA: Usar monto de prueba en lugar del monto real
        $testAmount = config('services.masterqr.test_amount', 0.10);
        $isTestMode = true; // Puedes cambiar esto a false cuando quieras usar montos reales

        Log::info('🧪 MODO PRUEBA ACTIVADO', [
            'monto_real_calculado' => $finalAmount,
            'monto_prueba_qr' => $testAmount,
            'razon' => 'Evitar cobros altos durante desarrollo/pruebas',
        ]);

        // Generar número de pago único
        $paymentNumber = 'HOTEL-' . $booking->id . '-' . time();

        // Preparar descripción según el porcentaje
        $productDescription = $percentage == 100
            ? "DEMO - Reserva Hotel #" . $booking->id
            : "DEMO - Pago {$percentage}% - Reserva Hotel #" . $booking->id;

        // Preparar detalles de la orden con MONTO DE PRUEBA
        $orderDetail = [
            [
                'serial' => 1,
                'product' => $productDescription,
                'quantity' => 1,
                'price' => (float) $testAmount,
                'discount' => 0,
                'total' => (float) $testAmount,
            ]
        ];

        // Datos para MasterQR con MONTO DE PRUEBA
        $paymentData = [
            'client_name' => $customer->first_name . ' ' . $customer->last_name,
            'document_type' => 1, // 1 = CI (Cédula de Identidad)
            'document_id' => $customer->national_id ?? '0',
            'phone_number' => $customer->phone_number ?? '00000000',
            'email' => $customer->email,
            'payment_number' => $paymentNumber,
            'amount' => (float) $testAmount, // MONTO DE PRUEBA
            // La callback URL se obtiene de la configuración (config/services.php)
            'order_detail' => $orderDetail,
        ];

        Log::info('📝 Datos de pago preparados', [
            'payment_number' => $paymentNumber,
            'amount' => $paymentData['amount'],
            'percentage' => $percentage,
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
                'amount' => $finalAmount,
                'payment_method' => PaymentMethod::QR_CODE,
                'payment_number' => $paymentNumber,
                'qr_image_url' => $qrImageData,
                'qr_transaction_id' => $transactionId,
                'qr_response' => $qrResponse,
            ]);
        } else {
            $payment = $booking->payments()->create([
                'amount' => $finalAmount,
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
            'amount' => $finalAmount,
            'percentage' => $percentage,
            'has_qr_data' => !empty($qrImageData),
        ]);

        $responseData = [
            'success' => true,
            'qr_image' => $qrResponse['qrImage'] ?? $qrResponse['values']['qrBase64'] ?? null,
            'transaction_id' => $qrResponse['transactionId'] ?? $qrResponse['values']['transactionId'] ?? null,
            'payment_number' => $paymentNumber,
            'amount' => $testAmount, // MONTO DE PRUEBA en el QR
            'real_amount' => $finalAmount, // MONTO REAL calculado
            'percentage' => $percentage,
            'original_amount' => $booking->total_price,
            'is_partial' => $percentage < 100,
            'is_test' => $isTestMode,
            'test_reason' => 'Monto de prueba para evitar cobros altos durante desarrollo',
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
     *
     * URL del Webhook: https://tu-dominio.com/qr/callback
     *
     * Esta URL debe ser configurada en el panel de MasterQR para recibir notificaciones
     * de pagos completados, fallidos o cancelados.
     *
     * IMPORTANTE: Esta ruta es pública (sin middleware de autenticación) para que
     * MasterQR pueda enviar notificaciones.
     */
    public function callback(Request $request)
    {
        Log::info('🔔 MasterQR Callback recibido', [
            'timestamp' => now()->toDateTimeString(),
            'ip' => $request->ip(),
            'headers' => $request->headers->all(),
            'payload' => $request->all(),
        ]);

        // Validar la firma del webhook (implementar según documentación de MasterQR)
        // IMPORTANTE: Descomentar esto en producción para mayor seguridad
        // $isValid = $this->masterQRService->validateWebhookSignature(
        //     $request->all(),
        //     $request->header('X-Signature')
        // );
        //
        // if (!$isValid) {
        //     Log::warning('❌ Firma de webhook inválida de MasterQR', [
        //         'ip' => $request->ip(),
        //         'signature' => $request->header('X-Signature'),
        //     ]);
        //     return response()->json(['error' => 'Invalid signature'], 403);
        // }

        // Obtener el número de pago (probar diferentes campos que MasterQR puede enviar)
        $paymentNumber = $request->input('paymentNumber')
                      ?? $request->input('payment_number')
                      ?? $request->input('PaymentNumber')
                      ?? $request->input('externalId');

        if (!$paymentNumber) {
            Log::error('❌ Número de pago no encontrado en callback', [
                'all_inputs' => $request->all(),
                'ip' => $request->ip(),
            ]);
            return response()->json(['error' => 'Payment number required'], 400);
        }

        Log::info('🔍 Buscando pago', ['payment_number' => $paymentNumber]);

        // Buscar el pago por payment_number
        $payment = Payment::where('payment_number', $paymentNumber)->first();

        if (!$payment) {
            Log::error('❌ Pago no encontrado en BD', [
                'payment_number' => $paymentNumber,
                'callback_data' => $request->all(),
            ]);
            return response()->json(['error' => 'Payment not found'], 404);
        }

        Log::info('✅ Pago encontrado', [
            'payment_id' => $payment->id,
            'booking_id' => $payment->booking_id,
            'current_status' => $payment->status->value,
        ]);

        // Verificar el estado del pago desde MasterQR (probar diferentes campos)
        $transactionStatus = $request->input('status')
                          ?? $request->input('transactionStatus')
                          ?? $request->input('Status')
                          ?? $request->input('paymentStatus');

        Log::info('📋 Estado de transacción recibido', [
            'status' => $transactionStatus,
            'payment_id' => $payment->id,
        ]);

        // Estados posibles de MasterQR: 'completed', 'success', 'paid', etc.
        // Ajustar según la documentación real de MasterQR
        if (in_array(strtolower($transactionStatus), ['completed', 'success', 'paid', 'approved'])) {
            Log::info('✅ Procesando pago exitoso');

            try {
                DB::transaction(function () use ($payment, $request) {
                    $booking = $payment->booking;

                    // Verificar que el pago no haya sido procesado anteriormente
                    if ($payment->status === PaymentStatus::PAID) {
                        Log::warning('⚠️ Pago ya procesado anteriormente', [
                            'payment_id' => $payment->id,
                            'paid_at' => $payment->paid_at,
                        ]);
                        return;
                    }

                    // Actualizar el pago con información del callback
                    $payment->update([
                        'status' => PaymentStatus::PAID,
                        'paid_at' => now(),
                        'reference' => $request->input('transactionId')
                                    ?? $request->input('transaction_id')
                                    ?? $request->input('TransactionId'),
                        'qr_callback_data' => $request->all(), // Guardar todo el callback para auditoría
                    ]);

                    Log::info('💾 Pago actualizado a PAID', [
                        'payment_id' => $payment->id,
                        'reference' => $payment->reference,
                    ]);

                    // Actualizar la reserva
                    $booking->update([
                        'status' => BookingStatus::RESERVED,
                        'payment_status' => BookingPayment::PAID,
                    ]);

                    Log::info('📅 Reserva actualizada', [
                        'booking_id' => $booking->id,
                        'status' => $booking->status->value,
                    ]);

                    // Crear estado de reserva
                    $booking->statuses()->create([
                        'status' => BookingStatus::RESERVED,
                    ]);

                    // Enviar email de confirmación
                    try {
                        Mail::to($booking->customer->email)->send(new BookingConfirmed($booking));
                        Log::info('📧 Email de confirmación enviado', [
                            'customer_email' => $booking->customer->email,
                        ]);
                    } catch (\Exception $e) {
                        Log::error('❌ Error al enviar email de confirmación', [
                            'error' => $e->getMessage(),
                            'customer_email' => $booking->customer->email,
                        ]);
                        // No fallar la transacción si el email falla
                    }

                    Log::info('✅ Pago confirmado exitosamente vía QR', [
                        'payment_id' => $payment->id,
                        'booking_id' => $booking->id,
                        'amount' => $payment->amount,
                        'reference' => $payment->reference,
                    ]);
                });

                return response()->json([
                    'success' => true,
                    'message' => 'Payment processed successfully'
                ]);

            } catch (\Exception $e) {
                Log::error('❌ Error al procesar pago exitoso', [
                    'payment_id' => $payment->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                return response()->json([
                    'error' => 'Error processing payment',
                    'message' => $e->getMessage()
                ], 500);
            }
        }

        // Si el pago falló, fue rechazado o cancelado
        if (in_array(strtolower($transactionStatus), ['failed', 'rejected', 'cancelled', 'error', 'denied'])) {
            Log::warning('⚠️ Pago fallido/rechazado', [
                'payment_id' => $payment->id,
                'status' => $transactionStatus,
                'callback_data' => $request->all(),
            ]);

            $payment->update([
                'status' => PaymentStatus::FAILED,
                'qr_callback_data' => $request->all(), // Guardar datos para análisis
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment marked as failed'
            ]);
        }

        // Estado desconocido o pendiente
        Log::info('ℹ️ Estado de pago no reconocido o pendiente', [
            'payment_id' => $payment->id,
            'status' => $transactionStatus,
            'callback_data' => $request->all(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Callback received, status: ' . $transactionStatus
        ]);
    }

    /**
     * Consulta el estado de una transacción QR
     */
    public function queryStatus(Booking $booking)
    {
        Log::info('🔍 Consultando estado de pago QR', [
            'booking_id' => $booking->id,
            'customer_id' => auth('customer')->id(),
        ]);

        if ($booking->customer_id !== auth('customer')->id()) {
            Log::error('❌ Cliente no autorizado', [
                'booking_customer_id' => $booking->customer_id,
                'auth_customer_id' => auth('customer')->id(),
            ]);
            abort(403);
        }

        $payment = $booking->payments()->where('payment_method', PaymentMethod::QR_CODE)->first();

        if (!$payment) {
            Log::warning('⚠️ No se encontró pago QR', [
                'booking_id' => $booking->id,
            ]);
            return response()->json([
                'error' => 'No QR transaction found',
                'payment_status' => 'pending'
            ], 404);
        }

        Log::info('✅ Estado del pago', [
            'payment_id' => $payment->id,
            'status' => $payment->status->value,
            'has_transaction_id' => !empty($payment->qr_transaction_id),
        ]);

        // Solo devolver el estado actual del pago en nuestra base de datos
        // El callback de MasterQR actualizará este estado cuando el pago se complete
        return response()->json([
            'payment_status' => $payment->status->value,
            'booking_status' => $booking->status->value,
            'payment_id' => $payment->id,
            'transaction_id' => $payment->qr_transaction_id,
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
