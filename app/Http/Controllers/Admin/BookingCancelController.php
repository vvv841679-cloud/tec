<?php

namespace App\Http\Controllers\Admin;

use App\Attributes\Authorize;
use App\Enums\BookingStatus;
use App\Enums\ChargeType;
use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\CancellationRule;
use DB;
use Illuminate\Http\Request;

class BookingCancelController extends Controller
{
    #[Authorize('cancel', 'booking')]
    public function cancellationFee(Booking $booking)
    {
        $rule = CancellationRule::where('min_days_before', '<=', $booking->daysUntilCheckIn())
            ->where('max_days_before', '>=', $booking->daysUntilCheckIn())
            ->first();

        if (!$rule) {
            return response()->json([
                'amount' => 0,
            ]);
        }

        $amount = ($booking->total_price * $rule->penalty_percent) / 100;
        $amount = round($amount, 2);

        return response()->json([
            'amount' => $amount,
        ]);
    }

    #[Authorize('cancel', 'booking')]
    public function cancel(Request $request, Booking $booking)
    {
        $data = $request->validate([
            'amount' => 'required|numeric|min:0|max:' . $booking->total_price,
            'description' => 'nullable|string',
        ]);

        if(!in_array($booking->status, [BookingStatus::RESERVED, BookingStatus::CHECK_IN])) {
            return redirect()->back()->with([
                'message' => 'Esta reserva no puede ser cancelada en este momento. Por favor, revisa el estado de la reserva.',
                'type' => 'error'
            ]);
        }

        DB::Transaction(function () use ($booking, $data) {
            $refundAmount = $booking->deposit_amount - $data['amount'];

            if($refundAmount > 0) {
                $booking->payments()->create([
                    'amount' => $refundAmount,
                    'type' => PaymentType::REFUND,
                    'payment_method' => 'bank_transfer',
                    'status' => PaymentStatus::PAID,
                    'payment_date' => now(),
                ]);
            }

            $booking->charges()->create([
                'amount' => $data['amount'],
                'charge_type' => ChargeType::CANCELLATION_FEE,
            ]);

            $booking->statuses()->create([
                'status' => BookingStatus::CANCELLED,
                'description' => $data['description'],
            ]);

            $booking->update([
                'status' => BookingStatus::CANCELLED,
            ]);
        });

        return redirect()->back()->with([
            'message' => 'Tu reserva ha sido cancelada exitosamente. Se aplicó reembolso y cargo por cancelación.',
        ]);
    }
}
