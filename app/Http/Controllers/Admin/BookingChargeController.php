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

        return redirect()->back()
            ->with('success', 'Servicio agregado exitosamente.');
    }

    public function destroy(Booking $booking, BookingCharge $charge): RedirectResponse
    {
        $this->authorize('removeCharge', $booking);

        // Solo permitir eliminar cargos de tipo SERVICE
        if ($charge->charge_type !== ChargeType::SERVICE) {
            return redirect()->back()
                ->with('error', 'Solo se pueden eliminar cargos de tipo servicio.');
        }

        // Verificar que el cargo pertenece a la reserva
        if ($charge->booking_id !== $booking->id) {
            abort(404);
        }

        $charge->delete();

        return redirect()->back()
            ->with('success', 'Servicio eliminado exitosamente.');
    }
}
