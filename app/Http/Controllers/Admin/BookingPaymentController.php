<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Booking\Payment\CreateRequest;
use App\Http\Requests\Admin\Booking\Payment\EditRequest;
use App\Http\Resources\BookingResource;
use App\Http\Resources\PaymentResource;
use App\Models\Booking;
use App\Models\Payment;
use App\Services\MasterQRService;
use DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class BookingPaymentController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource('App\Models\Payment,booking', 'payment,booking');
    }

    public function index(Booking $booking)
    {
        $user = auth()->user();
        $booking->load('customer');
        $payments = $booking->payments()->latest()->get();

        $payments->map(fn ($payment) => $payment->setAttribute('access', [
            'edit' => $user->can('update', $payment),
        ]));

        return inertia('Admin/Booking/Payment/List', [
            'booking' => BookingResource::make($booking),
            'types' => PaymentType::asSelect(),
            'methods' => PaymentMethod::asSelect(),
            'statuses' => PaymentStatus::asSelect(),
            'payments' => PaymentResource::collection($payments),
            'access' => [
                'createPayment' => $user->can('create', [Payment::class, $booking]),
            ]
        ]);
    }

    /**
     * @throws Throwable
     */
    public function store(CreateRequest $request, Booking $booking, MasterQRService $masterQRService)
    {
        $data = $request->validated();

        $data['paid_at'] = $data['status'] === PaymentStatus::PAID->value ? now() : null;
        $data['type'] = PaymentType::DEPOSIT;

        // Si el método es QR_CODE, generar el código QR automáticamente
        if ($data['payment_method'] === PaymentMethod::QR_CODE->value) {
            try {
                $customer = $booking->customer;
                $amount = $data['amount'];

                // Usar monto de prueba en desarrollo
                $testAmount = config('services.masterqr.test_amount', 0.10);
                $isTestMode = true;

                // Generar número de pago único
                $paymentNumber = 'HOTEL-ADM-' . $booking->id . '-' . time();

                // Preparar detalles de la orden
                $orderDetail = [
                    [
                        'serial' => 1,
                        'product' => "DEMO - Admin - Reserva Hotel #" . $booking->id,
                        'quantity' => 1,
                        'price' => (float) $testAmount,
                        'discount' => 0,
                        'total' => (float) $testAmount,
                    ]
                ];

                // Datos para PagoFacil
                $paymentData = [
                    'client_name' => $customer->first_name . ' ' . $customer->last_name,
                    'document_type' => 1,
                    'document_id' => $customer->national_id ?? '0',
                    'phone_number' => $customer->phone_number ?? '00000000',
                    'email' => $customer->email,
                    'payment_number' => $paymentNumber,
                    'amount' => (float) $testAmount,
                    'order_detail' => $orderDetail,
                ];

                // Generar QR
                $qrResponse = $masterQRService->generateQR($paymentData);

                if ($qrResponse) {
                    // Agregar datos del QR al payment
                    $data['qr_image_url'] = $qrResponse['qrImage'] ?? $qrResponse['values']['qrBase64'] ?? null;
                    $data['qr_transaction_id'] = $qrResponse['transactionId'] ?? $qrResponse['values']['transactionId'] ?? null;
                    $data['payment_number'] = $paymentNumber;
                    $data['qr_response'] = $qrResponse;

                    Log::info('✅ QR generado desde Admin', [
                        'booking_id' => $booking->id,
                        'payment_number' => $paymentNumber,
                        'amount' => $amount,
                        'test_amount' => $testAmount,
                    ]);
                } else {
                    Log::error('❌ Error al generar QR desde Admin', [
                        'booking_id' => $booking->id,
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('❌ Excepción al generar QR desde Admin', [
                    'error' => $e->getMessage(),
                    'booking_id' => $booking->id,
                ]);
            }
        }

        DB::Transaction(fn () => $booking->payments()->create($data));

        return redirect()->back()->with('message', 'Pago creado.' . (isset($data['qr_image_url']) ? ' Código QR generado exitosamente.' : ''));
    }

    /**
     * @throws Throwable
     */
    public function update(EditRequest $request, Payment $payment)
    {
        $data = $request->validated();

        $payment->fill($data);

        if($payment->isDirty('status')) {
            $payment->paid_at = $data['status'] === PaymentStatus::PAID->value
                ? now()
                : null;
        }

        DB::Transaction(fn () => $payment->save());

        return redirect()->back()->with('message', 'Pago actualizado.');
    }
}
