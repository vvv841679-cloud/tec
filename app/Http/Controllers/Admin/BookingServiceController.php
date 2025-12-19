<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BookingPayment;
use App\Enums\ChargeType;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingCharge;
use Illuminate\Http\Request;

class BookingServiceController extends Controller
{
    /**
     * Agregar un servicio adicional a una reserva existente
     */
    public function store(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
        ]);

        // Crear el cargo adicional
        $charge = $booking->charges()->create([
            'charge_type' => ChargeType::SERVICE,
            'description' => $validated['description'],
            'amount' => $validated['amount'],
        ]);

        // Actualizar el precio total de la reserva
        $totalCharges = $booking->charges()->sum('amount');
        $booking->update([
            'total_price' => $totalCharges,
        ]);

        // Recalcular el estado de pago
        $this->updatePaymentStatus($booking);

        return redirect()->back()->with('flash', [
            'type' => 'success',
            'message' => 'Servicio adicional agregado correctamente. El total de la reserva se ha actualizado.'
        ]);
    }

    /**
     * Eliminar un servicio adicional
     */
    public function destroy(Booking $booking, BookingCharge $charge)
    {
        // Verificar que el cargo pertenece a la reserva
        if ($charge->booking_id !== $booking->id) {
            abort(403);
        }

        // Solo permitir eliminar cargos de tipo servicio
        if ($charge->charge_type !== ChargeType::SERVICE) {
            return redirect()->back()->with('flash', [
                'type' => 'error',
                'message' => 'Solo se pueden eliminar servicios adicionales.'
            ]);
        }

        $charge->delete();

        // Recalcular el precio total
        $totalCharges = $booking->charges()->sum('amount');
        $booking->update([
            'total_price' => $totalCharges,
        ]);

        // Recalcular el estado de pago
        $this->updatePaymentStatus($booking);

        return redirect()->back()->with('flash', [
            'type' => 'success',
            'message' => 'Servicio eliminado correctamente.'
        ]);
    }

    /**
     * Actualizar el estado de pago basado en el nuevo total
     */
    private function updatePaymentStatus(Booking $booking)
    {
        $paidAmount = $booking->deposit_amount;
        $totalPrice = $booking->total_price;

        if ($paidAmount <= 0) {
            $paymentStatus = BookingPayment::PENDING;
        } elseif ($paidAmount >= $totalPrice) {
            $paymentStatus = BookingPayment::PAID;
        } else {
            $paymentStatus = BookingPayment::PARTIALLY_PAID;
        }

        $booking->update([
            'payment_status' => $paymentStatus,
        ]);
    }
}
