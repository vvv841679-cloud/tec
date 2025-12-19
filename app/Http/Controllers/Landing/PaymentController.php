<?php

namespace App\Http\Controllers\Landing;

use App\Enums\BookingPayment;
use App\Enums\BookingStatus;
use App\Enums\ChargeType;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use App\Enums\SmokingPreference;
use App\Http\Controllers\Controller;
use App\Http\Resources\BookingResource;
use App\Http\Resources\RoomTypeResource;
use App\Mail\BookingConfirmed;
use App\Models\Booking;
use App\Models\RoomType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Mail;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;
use Stripe\PaymentIntent;

class PaymentController extends Controller
{
    public function create(Booking $booking)
    {
        if (!$booking->isPayable() || $booking->customer_id !== auth('customer')->id()) {
            abort(403);
        }

        $booking->load('charges')->loadCount('rooms');

        $roomType = $booking->rooms()->first()->type;
        $roomType->load('media', 'bedTypes');

        return inertia('Landing/Checkout', [
            'booking' => BookingResource::make($booking),
            'roomType' => RoomTypeResource::make($roomType),
            'stripeKey' => config('services.stripe.key'),
            'charges' => ChargeType::asSelect(),
        ]);
    }

    /**
     * @throws ApiErrorException
     */
    public function store(Request $request, Booking $booking)
    {
        if (!$booking->isPayable() || $booking->customer_id !== auth('customer')->id()) {
            abort(403);
        }

        // Validar el porcentaje y monto
        $request->validate([
            'percentage' => 'nullable|numeric|min:30|max:100',
            'amount' => 'nullable|numeric|min:0',
        ]);

        Stripe::setApiKey(config('services.stripe.secret'));

        // Calcular el monto a pagar según el porcentaje o monto especificado
        $percentage = $request->input('percentage', 100);
        $amountToPay = $request->input('amount', ($booking->total_price * $percentage) / 100);

        // Validar que el monto no exceda el saldo pendiente
        $paidAmount = $booking->payments()->where('status', PaymentStatus::PAID)->sum('amount');
        $remainingAmount = $booking->total_price - $paidAmount;

        if ($amountToPay > $remainingAmount) {
            $amountToPay = $remainingAmount;
        }

        // Validar mínimo 30% del total o pagar el saldo restante completo
        $minimumRequired = $booking->total_price * 0.30;
        if ($paidAmount < $minimumRequired && $amountToPay < $minimumRequired) {
            return response()->json([
                'message' => 'El monto mínimo debe ser el 30% del total de la reserva.'
            ], 422);
        }

        $intent = PaymentIntent::create([
            'amount' => (int) ($amountToPay * 100),
            'currency' => 'bob',
            'metadata' => [
                'user_id' => auth('customer')->id(),
                'booking_id' => $booking->id,
                'percentage' => $percentage,
            ],
            'description' => 'Pago de Reserva de Hotel',
            'automatic_payment_methods' => ['enabled' => true],
        ]);

        // Crear o actualizar el registro de pago
        $payment = $booking->payments()->updateOrCreate(
            ['reference' => $intent->id],
            [
                'amount' => $amountToPay,
                'type' => PaymentType::DEPOSIT,
                'payment_method' => PaymentMethod::CREDIT_CARD,
                'status' => PaymentStatus::PENDING,
                'reference' => $intent->id,
            ]
        );

        return response()->json(['client_secret' => $intent->client_secret]);
    }

    public function confirmPayment(Request $request)
    {
        $paymentIntentId = $request->input('payment_intent');

        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            $intent = PaymentIntent::retrieve($paymentIntentId);
        } catch (\Exception $e) {
            return redirect()->route('home');
        }

        $bookingId = $intent['metadata']['booking_id'] ?? null;
        $booking = $bookingId ? Booking::find($bookingId) : null;

        if (!$booking) {
            return redirect()->route('home');
        }

        if ($intent->status === 'succeeded') {
            DB::Transaction(function () use ($intent, $booking) {
                $payment = $booking->payments()->where('reference', $intent->id)->first();
                
                if ($payment) {
                    $payment->update([
                        'status' => PaymentStatus::PAID,
                        'paid_at' => now(),
                    ]);
                }

                // Calculate total paid including the one just updated
                $totalPaid = $booking->payments()->where('status', PaymentStatus::PAID)->sum('amount');
                $paymentStatus = $totalPaid >= $booking->total_price ? BookingPayment::PAID : BookingPayment::PARTIALLY_PAID;

                $booking->update([
                    'status' => BookingStatus::RESERVED,
                    'payment_status' => $paymentStatus,
                ]);

                $booking->statuses()->create([
                    'status' => BookingStatus::RESERVED,
                ]);
            });

            Mail::to($booking->customer->email)->send(new BookingConfirmed($booking));

            return redirect()->intended(route('bookings.success', $booking));
        }

        return redirect()->intended(route('bookings.failed', $booking));
    }

    public function success(Booking $booking)
    {
        if (
            !$booking->hasPaidMinimumPercentage(0.30) ||
            $booking->customer_id !== auth('customer')->id()
        ) {
            abort(403);
        }

        $roomType = RoomType::whereHas('rooms.bookings', fn($query) => $query->whereKey($booking->id))->first();
        $roomType->load('media', 'bedTypes');

        $booking->load(['charges', 'mealPlan', 'customer', 'rooms', 'payments'])->loadCount('rooms');
        return inertia('Landing/Success', [
            'booking' => BookingResource::make($booking),
            'roomType' => RoomTypeResource::make($roomType),
            'charges' => ChargeType::asSelect(),
            'statuses' => BookingStatus::asSelect(),
            'bookingPayments' => BookingPayment::asSelect(),
            'smokings' => SmokingPreference::asSelect(),
            'types' => PaymentType::asSelect(),
            'methods' => PaymentMethod::asSelect(),
            'paymentStatuses' => PaymentStatus::asSelect(),
        ]);
    }

    public function failed(Booking $booking)
    {
        if (!$booking->isPayable() || $booking->customer_id !== auth('customer')->id()) {
            abort(403);
        }

        return inertia('Landing/Failed', [
            'booking' => BookingResource::make($booking),
        ]);
    }
}
