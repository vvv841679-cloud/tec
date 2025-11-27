<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->input('q', '');

        if (empty($query)) {
            return Inertia::render('Admin/Search/Index', [
                'query' => '',
                'results' => [
                    'bookings' => [],
                    'customers' => [],
                    'rooms' => [],
                    'users' => [],
                    'payments' => [],
                    'roomTypes' => [],
                ],
                'totalResults' => 0,
                'quickLinks' => [],
            ]);
        }

        // Buscar en reservas (case-insensitive)
        $searchTerm = strtolower($query);
        $quickLinks = $this->getQuickLinks($searchTerm);

        $bookings = Booking::where(function($q) use ($searchTerm) {
            $q->whereRaw('LOWER(ref_number) like ?', ["%{$searchTerm}%"])
              ->orWhereRaw('LOWER(special_requests) like ?', ["%{$searchTerm}%"]);
        })
        ->orWhereHas('customer', function($q) use ($searchTerm) {
            $q->whereRaw('LOWER(first_name) like ?', ["%{$searchTerm}%"])
              ->orWhereRaw('LOWER(last_name) like ?', ["%{$searchTerm}%"]);
        })
        ->with(['customer', 'rooms.type'])
        ->latest()
        ->limit(10)
        ->get()
        ->map(function ($booking) {
            return [
                'id' => $booking->id,
                'type' => 'booking',
                'title' => "Reserva #{$booking->ref_number}",
                'description' => $booking->customer ? "{$booking->customer->full_name}" : 'Sin cliente',
                'subtitle' => "Check-in: {$booking->check_in->format('d/m/Y')} | Estado: {$booking->status->value} | Total: Bs. {$booking->total_price}",
                'url' => route('admin.bookings.show', $booking),
                'badge' => $booking->status->value,
            ];
        });

        // Buscar en clientes (case-insensitive)
        $customers = Customer::where(function($q) use ($searchTerm) {
            $q->whereRaw('LOWER(first_name) like ?', ["%{$searchTerm}%"])
              ->orWhereRaw('LOWER(last_name) like ?', ["%{$searchTerm}%"])
              ->orWhereRaw('LOWER(email) like ?', ["%{$searchTerm}%"])
              ->orWhereRaw('LOWER(phone_number) like ?', ["%{$searchTerm}%"])
              ->orWhereRaw('LOWER(document_number) like ?', ["%{$searchTerm}%"]);
        })
        ->limit(10)
        ->get()
        ->map(function ($customer) {
            return [
                'id' => $customer->id,
                'type' => 'customer',
                'title' => $customer->full_name,
                'description' => $customer->email,
                'subtitle' => "Tel: {$customer->phone_number} | Doc: {$customer->document_number}",
                'url' => route('admin.customers.show', $customer),
                'badge' => $customer->is_verified ? 'Verificado' : 'No verificado',
            ];
        });

        // Buscar en habitaciones (case-insensitive)
        $rooms = Room::where(function($q) use ($searchTerm) {
            $q->whereRaw('LOWER(CAST(room_number AS TEXT)) like ?', ["%{$searchTerm}%"])
              ->orWhereRaw('LOWER(CAST(floor_number AS TEXT)) like ?', ["%{$searchTerm}%"]);
        })
        ->orWhereHas('type', function($q) use ($searchTerm) {
            $q->whereRaw('LOWER(name) like ?', ["%{$searchTerm}%"]);
        })
        ->with('type')
        ->limit(10)
        ->get()
        ->map(function ($room) {
            return [
                'id' => $room->id,
                'type' => 'room',
                'title' => "Habitación #{$room->room_number}",
                'description' => $room->type->name ?? 'Sin tipo',
                'subtitle' => "Piso: {$room->floor_number} | Estado: {$room->status->value}",
                'url' => route('admin.rooms.update', $room),
                'badge' => $room->status->value,
            ];
        });

        // Buscar en usuarios/administradores (case-insensitive)
        $users = User::where(function($q) use ($searchTerm) {
            $q->whereRaw('LOWER(first_name) like ?', ["%{$searchTerm}%"])
              ->orWhereRaw('LOWER(last_name) like ?', ["%{$searchTerm}%"])
              ->orWhereRaw('LOWER(email) like ?', ["%{$searchTerm}%"]);
        })
        ->with('roles')
        ->limit(10)
        ->get()
        ->map(function ($user) {
            return [
                'id' => $user->id,
                'type' => 'user',
                'title' => $user->full_name,
                'description' => $user->email,
                'subtitle' => "Roles: " . $user->roles->pluck('name')->join(', '),
                'url' => route('admin.users.update', $user),
                'badge' => $user->roles->first()?->name ?? 'Sin rol',
            ];
        });

        // Buscar en pagos (case-insensitive)
        $payments = Payment::where(function($q) use ($searchTerm) {
            $q->whereRaw('LOWER(transaction_number) like ?', ["%{$searchTerm}%"])
              ->orWhereRaw('LOWER(CAST(amount AS TEXT)) like ?', ["%{$searchTerm}%"]);
        })
        ->orWhereHas('booking', function($q) use ($searchTerm) {
            $q->whereRaw('LOWER(ref_number) like ?', ["%{$searchTerm}%"]);
        })
        ->with(['booking.customer', 'method'])
        ->latest()
        ->limit(10)
        ->get()
        ->map(function ($payment) {
            return [
                'id' => $payment->id,
                'type' => 'payment',
                'title' => "Pago #{$payment->transaction_number}",
                'description' => "Reserva: {$payment->booking->ref_number} | Cliente: {$payment->booking->customer->full_name}",
                'subtitle' => "Monto: Bs. {$payment->amount} | Método: {$payment->method->name} | Estado: {$payment->status->value}",
                'url' => route('admin.bookings.show', $payment->booking),
                'badge' => $payment->status->value,
            ];
        });

        // Buscar en tipos de habitaciones (case-insensitive)
        $roomTypes = RoomType::where(function($q) use ($searchTerm) {
            $q->whereRaw('LOWER(name) like ?', ["%{$searchTerm}%"])
              ->orWhereRaw('LOWER(description) like ?', ["%{$searchTerm}%"])
              ->orWhereRaw('LOWER(view) like ?', ["%{$searchTerm}%"]);
        })
        ->with(['facilities', 'bedTypes'])
        ->limit(10)
        ->get()
        ->map(function ($roomType) {
            return [
                'id' => $roomType->id,
                'type' => 'room_type',
                'title' => $roomType->name,
                'description' => $roomType->description,
                'subtitle' => "Vista: {$roomType->view} | Tamaño: {$roomType->size}m² | Precio: Bs. {$roomType->price}",
                'url' => route('admin.room-types.update', $roomType),
                'image' => $roomType->getFirstMediaUrl('main', 'thumb'),
                'badge' => $roomType->is_active ? 'Activo' : 'Inactivo',
            ];
        });

        $totalResults = $bookings->count() + $customers->count() + $rooms->count() +
                       $users->count() + $payments->count() + $roomTypes->count();

        return Inertia::render('Admin/Search/Index', [
            'query' => $query,
            'results' => [
                'bookings' => $bookings,
                'customers' => $customers,
                'rooms' => $rooms,
                'users' => $users,
                'payments' => $payments,
                'roomTypes' => $roomTypes,
            ],
            'totalResults' => $totalResults,
            'quickLinks' => $quickLinks,
        ]);
    }

    /**
     * API endpoint para búsqueda en tiempo real
     */
    public function search(Request $request)
    {
        $query = $request->input('q', '');

        if (strlen($query) < 2) {
            return response()->json([
                'suggestions' => [],
                'quickLinks' => []
            ]);
        }

        $searchTerm = strtolower($query);

        // Enlaces rápidos basados en palabras clave
        $quickLinks = $this->getQuickLinks($searchTerm);

        $suggestions = collect();

        try {
            // Buscar en reservas (case-insensitive) - Más eficiente
            $bookings = Booking::whereRaw('LOWER(ref_number) like ?', ["%{$searchTerm}%"])
                ->with('customer:id,first_name,last_name')
                ->limit(2)
                ->get(['id', 'ref_number', 'customer_id'])
                ->map(fn($booking) => [
                    'label' => "Reserva #{$booking->ref_number}",
                    'value' => $booking->id,
                    'type' => 'Reserva',
                    'subtitle' => $booking->customer?->full_name,
                    'url' => route('admin.bookings.show', $booking),
                ]);
            $suggestions = $suggestions->merge($bookings);
        } catch (\Exception $e) {
            \Log::error('Error searching bookings: ' . $e->getMessage());
        }

        try {
            // Buscar en clientes (case-insensitive)
            $customers = Customer::where(function($q) use ($searchTerm) {
                $q->whereRaw('LOWER(first_name) like ?', ["%{$searchTerm}%"])
                  ->orWhereRaw('LOWER(last_name) like ?', ["%{$searchTerm}%"])
                  ->orWhereRaw('LOWER(email) like ?', ["%{$searchTerm}%"]);
            })
            ->limit(2)
            ->get(['id', 'first_name', 'last_name', 'email'])
            ->map(fn($customer) => [
                'label' => $customer->full_name,
                'value' => $customer->id,
                'type' => 'Cliente',
                'subtitle' => $customer->email,
                'url' => route('admin.customers.show', $customer),
            ]);
            $suggestions = $suggestions->merge($customers);
        } catch (\Exception $e) {
            \Log::error('Error searching customers: ' . $e->getMessage());
        }

        try {
            // Buscar en habitaciones (case-insensitive)
            $rooms = Room::whereRaw('LOWER(CAST(room_number AS TEXT)) like ?', ["%{$searchTerm}%"])
                ->with('type:id,name')
                ->limit(2)
                ->get(['id', 'room_number', 'room_type_id'])
                ->map(fn($room) => [
                    'label' => "Habitación #{$room->room_number}",
                    'value' => $room->id,
                    'type' => 'Habitación',
                    'subtitle' => $room->type?->name,
                    'url' => route('admin.rooms.update', $room),
                ]);
            $suggestions = $suggestions->merge($rooms);
        } catch (\Exception $e) {
            \Log::error('Error searching rooms: ' . $e->getMessage());
        }

        try {
            // Buscar en usuarios (case-insensitive)
            $users = User::where(function($q) use ($searchTerm) {
                $q->whereRaw('LOWER(first_name) like ?', ["%{$searchTerm}%"])
                  ->orWhereRaw('LOWER(last_name) like ?', ["%{$searchTerm}%"])
                  ->orWhereRaw('LOWER(email) like ?', ["%{$searchTerm}%"]);
            })
            ->limit(2)
            ->get(['id', 'first_name', 'last_name', 'email'])
            ->map(fn($user) => [
                'label' => $user->full_name,
                'value' => $user->id,
                'type' => 'Usuario',
                'subtitle' => $user->email,
                'url' => route('admin.users.update', $user),
            ]);
            $suggestions = $suggestions->merge($users);
        } catch (\Exception $e) {
            \Log::error('Error searching users: ' . $e->getMessage());
        }

        try {
            // Buscar en tipos de habitaciones si hay espacio
            if ($suggestions->count() < 8) {
                $roomTypes = RoomType::whereRaw('LOWER(name) like ?', ["%{$searchTerm}%"])
                    ->limit(2)
                    ->get(['id', 'name'])
                    ->map(fn($rt) => [
                        'label' => $rt->name,
                        'value' => $rt->id,
                        'type' => 'Tipo de habitación',
                        'subtitle' => null,
                        'url' => route('admin.room-types.update', $rt),
                    ]);
                $suggestions = $suggestions->merge($roomTypes);
            }
        } catch (\Exception $e) {
            \Log::error('Error searching room types: ' . $e->getMessage());
        }

        return response()->json([
            'suggestions' => $suggestions->take(10)->values(),
            'quickLinks' => $quickLinks
        ]);
    }

    /**
     * Obtener enlaces rápidos basados en palabras clave
     */
    private function getQuickLinks($searchTerm)
    {
        $links = [];

        // Palabras clave para reservas
        if (preg_match('/(reserva|booking|book)/i', $searchTerm)) {
            $links[] = [
                'label' => 'Todas las reservas',
                'icon' => 'IconCalendarCheck',
                'url' => route('admin.bookings.index'),
                'type' => 'quick_link'
            ];
            $links[] = [
                'label' => 'Nueva reserva',
                'icon' => 'IconCalendarWeek',
                'url' => route('admin.bookings.create'),
                'type' => 'quick_link'
            ];
        }

        // Palabras clave para clientes
        if (preg_match('/(cliente|customer|client)/i', $searchTerm)) {
            $links[] = [
                'label' => 'Todos los clientes',
                'icon' => 'IconUsers',
                'url' => route('admin.customers.index'),
                'type' => 'quick_link'
            ];
            $links[] = [
                'label' => 'Nuevo cliente',
                'icon' => 'IconUserCircle',
                'url' => route('admin.customers.create'),
                'type' => 'quick_link'
            ];
        }

        // Palabras clave para habitaciones
        if (preg_match('/(habitacion|room|cuarto)/i', $searchTerm)) {
            $links[] = [
                'label' => 'Todas las habitaciones',
                'icon' => 'IconBed',
                'url' => route('admin.rooms.index'),
                'type' => 'quick_link'
            ];
            $links[] = [
                'label' => 'Tipos de habitaciones',
                'icon' => 'IconBuildingBurjAlArab',
                'url' => route('admin.room-types.index'),
                'type' => 'quick_link'
            ];
        }

        // Palabras clave para pagos
        if (preg_match('/(pago|payment|factura)/i', $searchTerm)) {
            $links[] = [
                'label' => 'Todos los pagos',
                'icon' => 'IconBrandMastercard',
                'url' => route('admin.payments.index'),
                'type' => 'quick_link'
            ];
        }

        // Palabras clave para usuarios
        if (preg_match('/(usuario|user|admin)/i', $searchTerm)) {
            $links[] = [
                'label' => 'Todos los usuarios',
                'icon' => 'IconUser',
                'url' => route('admin.users.index'),
                'type' => 'quick_link'
            ];
            $links[] = [
                'label' => 'Nuevo usuario',
                'icon' => 'IconUserCircle',
                'url' => route('admin.users.create'),
                'type' => 'quick_link'
            ];
        }

        // Palabras clave para dashboard
        if (preg_match('/(dashboard|inicio|home|panel)/i', $searchTerm)) {
            $links[] = [
                'label' => 'Dashboard',
                'icon' => 'IconHome',
                'url' => route('admin.dashboard'),
                'type' => 'quick_link'
            ];
        }

        return $links;
    }
}
