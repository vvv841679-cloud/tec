<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\PageView;
use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->input('q', '');

        if (empty($query)) {
            return Inertia::render('Customer/Search/Index', [
                'query' => '',
                'results' => [
                    'roomTypes' => [],
                    'rooms' => [],
                    'bookings' => [],
                    'pageViews' => [],
                ],
                'totalResults' => 0,
                'quickLinks' => [],
            ]);
        }

        // Buscar en tipos de habitaciones (case-insensitive)
        $searchTerm = strtolower($query);
        $quickLinks = $this->getQuickLinks($searchTerm, true);

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
                'url' => route('roomTypes.show', $roomType->slug),
                'image' => $roomType->getFirstMediaUrl('main', 'thumb'),
            ];
        });

        // Buscar en habitaciones (case-insensitive) - PostgreSQL compatible
        $rooms = Room::where(function($q) use ($searchTerm) {
            $q->whereRaw('LOWER(CAST(room_number AS TEXT)) like ?', ["%{$searchTerm}%"])
              ->orWhereRaw('LOWER(CAST(floor_number AS TEXT)) like ?', ["%{$searchTerm}%"]);
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
                'url' => route('roomTypes.show', $room->type->slug ?? '#'),
                'image' => $room->type?->getFirstMediaUrl('main', 'thumb'),
            ];
        });

        // Buscar en reservas del cliente actual (case-insensitive)
        $bookings = Booking::where('customer_id', auth('customer')->id())
            ->where(function($q) use ($searchTerm) {
                $q->whereRaw('LOWER(ref_number) like ?', ["%{$searchTerm}%"])
                  ->orWhereRaw('LOWER(special_requests) like ?', ["%{$searchTerm}%"]);
            })
            ->with(['rooms.type', 'mealPlan'])
            ->limit(10)
            ->get()
            ->map(function ($booking) {
                return [
                    'id' => $booking->id,
                    'type' => 'booking',
                    'title' => "Reserva #{$booking->ref_number}",
                    'description' => "Check-in: {$booking->check_in->format('d/m/Y')} | Check-out: {$booking->check_out->format('d/m/Y')}",
                    'subtitle' => "Estado: {$booking->status->value} | Total: Bs. {$booking->total_price}",
                    'url' => route('customer.bookings.index'),
                    'badge' => $booking->status->value,
                ];
            });

        // Buscar en páginas vistas (case-insensitive)
        $pageViews = PageView::where(function($q) use ($searchTerm) {
            $q->whereRaw('LOWER(url) like ?', ["%{$searchTerm}%"])
              ->orWhereRaw('LOWER(page_title) like ?', ["%{$searchTerm}%"]);
        })
        ->orderBy('visit_count', 'desc')
        ->limit(10)
        ->get()
        ->map(function ($pageView) {
            return [
                'id' => $pageView->id,
                'type' => 'page',
                'title' => $pageView->page_title ?? $pageView->url,
                'description' => "URL: /{$pageView->url}",
                'subtitle' => "Visitas: {$pageView->visit_count}",
                'url' => url($pageView->url),
                'badge' => "{$pageView->visit_count} visitas",
            ];
        });

        $totalResults = $roomTypes->count() + $rooms->count() + $bookings->count() + $pageViews->count();

        return Inertia::render('Customer/Search/Index', [
            'query' => $query,
            'results' => [
                'roomTypes' => $roomTypes,
                'rooms' => $rooms,
                'bookings' => $bookings,
                'pageViews' => $pageViews,
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
        $quickLinks = $this->getQuickLinks($searchTerm, true);

        // Buscar en tipos de habitaciones (case-insensitive)
        $roomTypes = RoomType::whereRaw('LOWER(name) like ?', ["%{$searchTerm}%"])
            ->limit(5)
            ->get(['id', 'name', 'slug'])
            ->map(fn($rt) => [
                'label' => $rt->name,
                'value' => $rt->slug,
                'type' => 'Tipo de habitación',
                'url' => route('roomTypes.show', $rt->slug),
            ]);

        // Buscar en habitaciones (case-insensitive) - PostgreSQL compatible
        $rooms = Room::whereRaw('LOWER(CAST(room_number AS TEXT)) like ?', ["%{$searchTerm}%"])
            ->with('type:id,name,slug')
            ->limit(5)
            ->get()
            ->map(fn($room) => [
                'label' => "Habitación #{$room->room_number}",
                'value' => $room->id,
                'type' => 'Habitación',
                'url' => route('roomTypes.show', $room->type->slug ?? '#'),
            ]);

        // Buscar en reservas (case-insensitive)
        $bookings = Booking::where('customer_id', auth('customer')->id())
            ->whereRaw('LOWER(ref_number) like ?', ["%{$searchTerm}%"])
            ->limit(5)
            ->get(['id', 'ref_number'])
            ->map(fn($booking) => [
                'label' => "Reserva #{$booking->ref_number}",
                'value' => $booking->id,
                'type' => 'Reserva',
                'url' => route('customer.bookings.index'),
            ]);

        $suggestions = collect()
            ->merge($roomTypes)
            ->merge($rooms)
            ->merge($bookings)
            ->take(10);

        return response()->json([
            'suggestions' => $suggestions,
            'quickLinks' => $quickLinks
        ]);
    }

    /**
     * Búsqueda pública para visitantes no autenticados
     */
    public function publicIndex(Request $request)
    {
        $query = $request->input('q', '');

        if (empty($query)) {
            return Inertia::render('Landing/Search', [
                'query' => '',
                'results' => [
                    'roomTypes' => [],
                    'rooms' => [],
                    'pageViews' => [],
                ],
                'totalResults' => 0,
                'quickLinks' => [],
            ]);
        }

        // Buscar en tipos de habitaciones (case-insensitive)
        $searchTerm = strtolower($query);
        $quickLinks = $this->getQuickLinks($searchTerm, false);

        $roomTypes = RoomType::where(function($q) use ($searchTerm) {
            $q->whereRaw('LOWER(name) like ?', ["%{$searchTerm}%"])
              ->orWhereRaw('LOWER(description) like ?', ["%{$searchTerm}%"])
              ->orWhereRaw('LOWER(view) like ?', ["%{$searchTerm}%"]);
        })
        ->active()
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
                'url' => route('roomTypes.show', $roomType->slug),
                'image' => $roomType->getFirstMediaUrl('main', 'thumb'),
            ];
        });

        // Buscar en habitaciones (case-insensitive) - PostgreSQL compatible
        $rooms = Room::where(function($q) use ($searchTerm) {
            $q->whereRaw('LOWER(CAST(room_number AS TEXT)) like ?', ["%{$searchTerm}%"])
              ->orWhereRaw('LOWER(CAST(floor_number AS TEXT)) like ?', ["%{$searchTerm}%"]);
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
                'url' => route('roomTypes.show', $room->type->slug ?? '#'),
                'image' => $room->type?->getFirstMediaUrl('main', 'thumb'),
            ];
        });

        // Buscar en páginas vistas (case-insensitive)
        $pageViews = PageView::where(function($q) use ($searchTerm) {
            $q->whereRaw('LOWER(url) like ?', ["%{$searchTerm}%"])
              ->orWhereRaw('LOWER(page_title) like ?', ["%{$searchTerm}%"]);
        })
        ->orderBy('visit_count', 'desc')
        ->limit(10)
        ->get()
        ->map(function ($pageView) {
            return [
                'id' => $pageView->id,
                'type' => 'page',
                'title' => $pageView->page_title ?? $pageView->url,
                'description' => "URL: /{$pageView->url}",
                'subtitle' => "Visitas: {$pageView->visit_count}",
                'url' => url($pageView->url),
                'badge' => "{$pageView->visit_count} visitas",
            ];
        });

        $totalResults = $roomTypes->count() + $rooms->count() + $pageViews->count();

        return Inertia::render('Landing/Search', [
            'query' => $query,
            'results' => [
                'roomTypes' => $roomTypes,
                'rooms' => $rooms,
                'pageViews' => $pageViews,
            ],
            'totalResults' => $totalResults,
            'quickLinks' => $quickLinks,
        ]);
    }

    /**
     * API endpoint para búsqueda en tiempo real (pública)
     */
    public function publicSearch(Request $request)
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
        $quickLinks = $this->getQuickLinks($searchTerm, false);

        // Buscar en tipos de habitaciones (case-insensitive)
        $roomTypes = RoomType::whereRaw('LOWER(name) like ?', ["%{$searchTerm}%"])
            ->active()
            ->limit(5)
            ->get(['id', 'name', 'slug'])
            ->map(fn($rt) => [
                'label' => $rt->name,
                'value' => $rt->slug,
                'type' => 'Tipo de habitación',
                'url' => route('roomTypes.show', $rt->slug),
            ]);

        // Buscar en habitaciones (case-insensitive) - PostgreSQL compatible
        $rooms = Room::whereRaw('LOWER(CAST(room_number AS TEXT)) like ?', ["%{$searchTerm}%"])
            ->with('type:id,name,slug')
            ->limit(5)
            ->get()
            ->map(fn($room) => [
                'label' => "Habitación #{$room->room_number}",
                'value' => $room->id,
                'type' => 'Habitación',
                'url' => route('roomTypes.show', $room->type->slug ?? '#'),
            ]);

        $suggestions = collect()
            ->merge($roomTypes)
            ->merge($rooms)
            ->take(10);

        return response()->json([
            'suggestions' => $suggestions,
            'quickLinks' => $quickLinks
        ]);
    }

    /**
     * Obtener enlaces rápidos basados en palabras clave
     */
    private function getQuickLinks($searchTerm, $isAuthenticated = false)
    {
        $links = [];

        // Palabras clave para habitaciones
        if (preg_match('/(habitacion|cuarto|room|suite|dormir|hosped)/i', $searchTerm)) {
            $links[] = [
                'label' => 'Ver todas las habitaciones',
                'icon' => 'bi-door-open',
                'url' => route('roomTypes.index'),
                'type' => 'quick_link'
            ];
        }

        if ($isAuthenticated) {
            // Enlaces para usuarios autenticados

            // Palabras clave para reservas
            if (preg_match('/(reserva|reservar|booking|book)/i', $searchTerm)) {
                $links[] = [
                    'label' => 'Mis reservas',
                    'icon' => 'bi-calendar-check',
                    'url' => route('customer.bookings.index'),
                    'type' => 'quick_link'
                ];
                $links[] = [
                    'label' => 'Hacer una nueva reserva',
                    'icon' => 'bi-plus-circle',
                    'url' => route('roomTypes.index'),
                    'type' => 'quick_link'
                ];
            }

            // Palabras clave para pagos
            if (preg_match('/(pago|pagar|payment|factura)/i', $searchTerm)) {
                $links[] = [
                    'label' => 'Ver mis pagos',
                    'icon' => 'bi-wallet2',
                    'url' => route('customer.payments.index'),
                    'type' => 'quick_link'
                ];
            }

            // Palabras clave para perfil
            if (preg_match('/(perfil|cuenta|profile|usuario)/i', $searchTerm)) {
                $links[] = [
                    'label' => 'Mi perfil',
                    'icon' => 'bi-person',
                    'url' => route('customer.profile.edit'),
                    'type' => 'quick_link'
                ];
            }
        } else {
            // Enlaces para visitantes

            // Palabras clave para login/registro
            if (preg_match('/(login|iniciar|sesion|cuenta|registr)/i', $searchTerm)) {
                $links[] = [
                    'label' => 'Iniciar sesión',
                    'icon' => 'bi-box-arrow-in-right',
                    'url' => route('login'),
                    'type' => 'quick_link'
                ];
                $links[] = [
                    'label' => 'Crear cuenta',
                    'icon' => 'bi-person-plus',
                    'url' => route('register'),
                    'type' => 'quick_link'
                ];
            }
        }

        return $links;
    }
}
