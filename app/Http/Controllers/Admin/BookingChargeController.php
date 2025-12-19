<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ChargeType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BookingCharge\StoreChargeRequest;
use App\Models\Booking;
use App\Models\BookingCharge;
use Illuminate\Http\RedirectResponse;

class BookingChargeController extends Controller
{
    public function store(StoreChargeRequest $request, Booking $booking): RedirectResponse
    {
        $this->authorize('addCharge', $booking);

        $data = $request->validated();
        $data['charge_type'] = ChargeType::SERVICE;

        $booking->charges()->create($data);

        // Actualizar el precio total de la reserva
        $this->updateBookingTotal($booking);

        return redirect()->back()
            ->with('flash', [
                'type' => 'success',
                'message' => 'Servicio agregado exitosamente. El total de la reserva se ha actualizado.'
            ]);
    }

    public function destroy(Booking $booking, BookingCharge $charge): RedirectResponse
    {
        $this->authorize('removeCharge', $booking);

        // Solo permitir eliminar cargos de tipo SERVICE
        if ($charge->charge_type !== ChargeType::SERVICE) {
            return redirect()->back()
                ->with('flash', [
                    'type' => 'error',
                    'message' => 'Solo se pueden eliminar cargos de tipo servicio.'
                ]);
        }

        // Verificar que el cargo pertenece a la reserva
        if ($charge->booking_id !== $booking->id) {
            abort(404);
        }

        $charge->delete();

        // Actualizar el precio total de la reserva
        $this->updateBookingTotal($booking);

        return redirect()->back()
            ->with('flash', [
                'type' => 'success',
                'message' => 'Servicio eliminado exitosamente.'
            ]);
    }

    /**
     * Actualizar el precio total de la reserva basado en los cargos
     */
    private function updateBookingTotal(Booking $booking): void
    {
        $totalCharges = $booking->charges()->sum('amount');

        $booking->update([
            'total_price' => $totalCharges,
        ]);

        // Recalcular estado de pago
        $paidAmount = $booking->deposit_amount;

        if ($paidAmount <= 0) {
            $paymentStatus = \App\Enums\BookingPayment::PENDING;
        } elseif ($paidAmount >= $totalCharges) {
            $paymentStatus = \App\Enums\BookingPayment::PAID;
        } else {
            $paymentStatus = \App\Enums\BookingPayment::PARTIALLY_PAID;
        }

        $booking->update([
            'payment_status' => $paymentStatus,
        ]);
    }
}
