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

        // Validar que el porcentaje esté entre 30 y 100
        if ($percentage < 30 || $percentage > 100) {
            return response()->json([
                'error' => 'El porcentaje debe estar entre 30% y 100% (mínimo requerido: 30%)',
                'success' => false
            ], 400);
        }

        // Calcular el monto basado en el porcentaje
        $paymentAmount = ($booking->total_price * $percentage) / 100;

        // Usar el monto solicitado si está presente, o el calculado
        $finalAmount = $requestedAmount ?? $paymentAmount;

        // Validar que el monto no exceda el saldo pendiente
        $paidAmount = $booking->payments()->where('status', PaymentStatus::PAID)->sum('amount');
        $remainingAmount = $booking->total_price - $paidAmount;

        if ($finalAmount > $remainingAmount) {
            return response()->json([
                'error' => 'El monto no puede exceder el saldo pendiente de la reserva',
                'success' => false
            ], 400);
        }

        // Validar mínimo 30% del total o pagar el saldo restante completo
        $minimumRequired = $booking->total_price * 0.30;
        if ($paidAmount < $minimumRequired && $finalAmount < $minimumRequired) {
            return response()->json([
                'error' => 'El monto mínimo debe ser el 30% del total de la reserva.',
                'success' => false
            ], 422);
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

        // Datos para PagoFacil con MONTO DE PRUEBA
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

        // IMPORTANTE: Verificar que el teléfono esté disponible para que PagoFacil pueda notificar si hay problemas
        if (!$customer->phone_number || $customer->phone_number === '00000000') {
            Log::warning('⚠️ IMPORTANTE: Cliente sin número de teléfono válido', [
                'customer_id' => $customer->id,
                'phone_number' => $paymentData['phone_number'],
                'advertencia' => 'PagoFacil usa el teléfono para notificar si hay problemas con el callback',
            ]);
        }

        Log::info('📝 Datos de pago preparados', [
            'payment_number' => $paymentNumber,
            'amount' => $paymentData['amount'],
            'percentage' => $percentage,
            'client_name' => $paymentData['client_name'],
            'phone_number' => $paymentData['phone_number'],
            'email' => $paymentData['email'],
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
     * Webhook que recibe las notificaciones de PagoFacil cuando se completa un pago
     *
     * URL del Webhook: https://www.tecnoweb.org.bo/inf513/grupo01sc/tecnoweb/public/qr/callback
     *
     * Esta URL debe ser configurada en el panel de PagoFacil para recibir notificaciones
     * de pagos completados, fallidos o cancelados.
     *
     * IMPORTANTE: Esta ruta es pública (sin middleware de autenticación) para que
     * PagoFacil pueda enviar notificaciones.
     */
    public function callback(Request $request)
    {
        Log::info('🔔 PagoFacil Callback recibido', [
            'timestamp' => now()->toDateTimeString(),
            'ip' => $request->ip(),
            'payload' => $request->all(),
        ]);

        try {
            // Obtener los parámetros del request según formato de PagoFacil
            $pedidoId = $request->input('PedidoID');
            $fecha = $request->input('Fecha');
            $hora = $request->input('Hora');
            $estado = $request->input('Estado');
            $metodoPago = $request->input('MetodoPago');

            Log::info('📋 Parámetros recibidos de PagoFacil', [
                'PedidoID' => $pedidoId,
                'Fecha' => $fecha,
                'Hora' => $hora,
                'Estado' => $estado,
                'MetodoPago' => $metodoPago,
            ]);

            // Buscar el pago por ID (PedidoID corresponde al ID del Payment)
            $payment = Payment::find($pedidoId);

            if (!$payment) {
                Log::error('❌ Pago no encontrado en BD', [
                    'PedidoID' => $pedidoId,
                    'callback_data' => $request->all(),
                ]);

                return response()->json([
                    'error' => 1,
                    'status' => 0,
                    'message' => 'Transacción no encontrada',
                    'messageMostrar' => 0,
                    'messageSistema' => '',
                    'values' => false
                ], 200);
            }

            Log::info('✅ Pago encontrado', [
                'payment_id' => $payment->id,
                'booking_id' => $payment->booking_id,
                'current_status' => $payment->status->value,
            ]);

            // Verifica el estado del pago basado en el paquete de PagoFacil
            // Estado 1: En proceso/pendiente
            // Estado 2: Pagado
            // Estado 4: Anulado (no se recibió dinero o el QR caducó)
            // Estado 5: Revisión (si no pudieron notificar a través de la URL callback)

            if ($estado == 2) {
                // ESTADO 2: Pago exitoso
                Log::info('✅ Procesando pago exitoso - Estado 2 (Pagado)');

                try {
                    DB::transaction(function () use ($payment, $request, $fecha, $hora, $metodoPago) {
                        $booking = $payment->booking;

                        // Verificar que el pago no haya sido procesado anteriormente
                        if ($payment->status === PaymentStatus::PAID) {
                            Log::warning('⚠️ Pago ya procesado anteriormente', [
                                'payment_id' => $payment->id,
                                'paid_at' => $payment->paid_at,
                            ]);
                            return;
                        }

                        // Actualizar el pago con información del callback de PagoFacil
                        $payment->update([
                            'status' => PaymentStatus::PAID,
                            'paid_at' => now(),
                            'reference' => $metodoPago,
                            'qr_callback_data' => $request->all(), // Guardar todo el callback para auditoría
                        ]);

                        Log::info('💾 Pago actualizado a PAID', [
                            'payment_id' => $payment->id,
                            'fecha' => $fecha,
                            'hora' => $hora,
                            'metodo_pago' => $metodoPago,
                        ]);

                        // Calcular total pagado incluyendo el que acabamos de actualizar
                        $totalPaid = $booking->payments()->where('status', PaymentStatus::PAID)->sum('amount');
                        $paymentStatus = $totalPaid >= $booking->total_price
                            ? BookingPayment::PAID
                            : BookingPayment::PARTIALLY_PAID;

                        Log::info('💰 Calculando estado de pago', [
                            'total_price' => $booking->total_price,
                            'total_paid' => $totalPaid,
                            'payment_status' => $paymentStatus->value,
                        ]);

                        // Actualizar la reserva
                        $booking->update([
                            'status' => BookingStatus::RESERVED,
                            'payment_status' => $paymentStatus,
                        ]);

                        Log::info('📅 Reserva actualizada', [
                            'booking_id' => $booking->id,
                            'status' => $booking->status->value,
                            'payment_status' => $booking->payment_status->value,
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

                        Log::info('✅ Pago confirmado exitosamente vía PagoFacil', [
                            'payment_id' => $payment->id,
                            'booking_id' => $booking->id,
                            'amount' => $payment->amount,
                            'metodo_pago' => $metodoPago,
                        ]);
                    });

                    // Devuelve un paquete de éxito según formato esperado por PagoFacil
                    return response()->json([
                        'error' => 0,
                        'status' => 1,
                        'message' => 'Pago realizado correctamente',
                        'messageMostrar' => 0,
                        'messageSistema' => '',
                        'values' => true
                    ], 200);

                } catch (\Exception $e) {
                    Log::error('❌ Error al procesar pago exitoso', [
                        'payment_id' => $payment->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);

                    return response()->json([
                        'error' => 1,
                        'status' => 0,
                        'message' => 'Error al procesar el pago',
                        'messageMostrar' => 0,
                        'messageSistema' => $e->getMessage(),
                        'values' => false
                    ], 200);
                }
            } elseif ($estado == 1) {
                // ESTADO 1: En proceso/pendiente
                Log::info('⏳ Pago en proceso - Estado 1 (Pendiente)', [
                    'payment_id' => $payment->id,
                    'callback_data' => $request->all(),
                ]);

                // Guardar los datos del callback pero mantener el estado como pendiente
                $payment->update([
                    'qr_callback_data' => $request->all(),
                ]);

                return response()->json([
                    'error' => 0,
                    'status' => 1,
                    'message' => 'Pago en proceso',
                    'messageMostrar' => 0,
                    'messageSistema' => '',
                    'values' => true
                ], 200);

            } elseif ($estado == 4) {
                // ESTADO 4: Anulado (no se recibió dinero o el QR caducó)
                Log::warning('❌ Pago anulado - Estado 4 (Anulado/QR Caducado)', [
                    'payment_id' => $payment->id,
                    'callback_data' => $request->all(),
                ]);

                $payment->update([
                    'status' => PaymentStatus::FAILED,
                    'qr_callback_data' => $request->all(),
                ]);

                return response()->json([
                    'error' => 0,
                    'status' => 1,
                    'message' => 'Pago anulado',
                    'messageMostrar' => 0,
                    'messageSistema' => '',
                    'values' => true
                ], 200);

            } elseif ($estado == 5) {
                // ESTADO 5: Revisión (si no pudieron notificar a través de la URL callback)
                Log::warning('⚠️ Pago en revisión - Estado 5 (Error en notificación callback)', [
                    'payment_id' => $payment->id,
                    'callback_data' => $request->all(),
                    'advertencia' => 'PagoFacil tuvo problemas notificando el callback. Revisar logs y contactar soporte.',
                ]);

                $payment->update([
                    'qr_callback_data' => $request->all(),
                ]);

                return response()->json([
                    'error' => 0,
                    'status' => 1,
                    'message' => 'Pago en revisión',
                    'messageMostrar' => 0,
                    'messageSistema' => '',
                    'values' => true
                ], 200);

            } else {
                // Estado desconocido
                Log::warning('❓ Estado desconocido recibido', [
                    'payment_id' => $payment->id,
                    'estado' => $estado,
                    'callback_data' => $request->all(),
                ]);

                return response()->json([
                    'error' => 0,
                    'status' => 0,
                    'message' => 'Estado desconocido: ' . $estado,
                    'messageMostrar' => 0,
                    'messageSistema' => '',
                    'values' => false
                ], 200);
            }

        } catch (\Exception $e) {
            // Captura cualquier excepción y devuelve un error
            Log::error('❌ Error en el método callback de PagoFacil', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'error' => 1,
                'status' => 0,
                'message' => 'Ocurrió un error al procesar la transacción',
                'messageMostrar' => 0,
                'messageSistema' => $e->getMessage(),
                'values' => false
            ], 200);
        }
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
