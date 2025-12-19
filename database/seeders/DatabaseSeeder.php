<?php

namespace Database\Seeders;

use App\Enums\BookingStatus;
use App\Enums\ChargeType;
use App\Enums\RoomStatus;
use App\Enums\Sex;
use App\Models\BedType;
use App\Models\Booking;
use App\Models\BookingChildren;
use App\Models\CancellationRule;
use App\Models\Country;
use App\Models\Customer;
use App\Models\Facility;
use App\Models\MealPlan;
use App\Models\Menu;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;
use App\Services\Permission\PermissionService;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $defaultRoles = ['admin', 'reception', 'manager'];
        array_map(fn($name) => Role::create(['name' => $name]), $defaultRoles);

        User::factory()->create([
            'first_name' => 'Rasoul',
            'last_name' => 'Zinati',
            'email' => 'test@example.com',
            'password' => '1234',
            'sex' => Sex::Male,
            'is_super_admin' => true,
        ]);

        $users = User::factory(100)->create();

        $users->each(fn($user) => $user->assignRole(fake()->randomElements($defaultRoles), mt_rand(1, 3)));

        PermissionService::syncBaseOnPolicies();

        // Seed Menus
        $menuInicio = Menu::create([
            'name' => 'Inicio',
            'route_name' => 'admin.dashboard',
            'icon' => 'IconHome',
            'order' => 1,
            'active' => true,
        ]);

        $menuPersonas = Menu::create([
            'name' => 'Personas',
            'route_name' => '#',
            'icon' => 'IconUsers',
            'order' => 2,
            'active' => true,
        ]);

        Menu::create([
            'parent_id' => $menuPersonas->id,
            'name' => 'Usuarios',
            'route_name' => 'admin.users.index',
            'icon' => 'IconUser',
            'order' => 1,
            'active' => true,
        ]);

        Menu::create([
            'parent_id' => $menuPersonas->id,
            'name' => 'Clientes',
            'route_name' => 'admin.customers.index',
            'icon' => 'IconUserCircle',
            'order' => 2,
            'active' => true,
        ]);

        Menu::create([
            'parent_id' => $menuPersonas->id,
            'name' => 'Roles',
            'route_name' => 'admin.roles.index',
            'icon' => 'IconShieldLock',
            'order' => 3,
            'active' => true,
        ]);

        $menuHotel = Menu::create([
            'name' => 'Hotel',
            'route_name' => '#',
            'icon' => 'IconBuildingSkyscraper',
            'order' => 3,
            'active' => true,
        ]);

        Menu::create([
            'parent_id' => $menuHotel->id,
            'name' => 'Países',
            'route_name' => 'admin.countries.index',
            'icon' => 'IconWorld',
            'order' => 1,
            'active' => true,
        ]);

        Menu::create([
            'parent_id' => $menuHotel->id,
            'name' => 'Tipos de Cama',
            'route_name' => 'admin.bedTypes.index',
            'icon' => 'IconBed',
            'order' => 2,
            'active' => true,
        ]);

        Menu::create([
            'parent_id' => $menuHotel->id,
            'name' => 'Instalaciones',
            'route_name' => 'admin.facilities.index',
            'icon' => 'IconClipboardData',
            'order' => 3,
            'active' => true,
        ]);

        Menu::create([
            'parent_id' => $menuHotel->id,
            'name' => 'Tipos de Habitación',
            'route_name' => 'admin.roomTypes.index',
            'icon' => 'IconSofa',
            'order' => 4,
            'active' => true,
        ]);

        Menu::create([
            'parent_id' => $menuHotel->id,
            'name' => 'Habitaciones',
            'route_name' => 'admin.rooms.index',
            'icon' => 'IconBuildingBurjAlArab',
            'order' => 5,
            'active' => true,
        ]);

        Menu::create([
            'parent_id' => $menuHotel->id,
            'name' => 'Planes de Comida',
            'route_name' => 'admin.mealPlans.index',
            'icon' => 'IconMeat',
            'order' => 6,
            'active' => true,
        ]);

        Menu::create([
            'parent_id' => $menuHotel->id,
            'name' => 'Reglas de Cancelación',
            'route_name' => 'admin.cancellationRules.index',
            'icon' => 'IconScale',
            'order' => 7,
            'active' => true,
        ]);

        $menuReservas = Menu::create([
            'name' => 'Reservas',
            'route_name' => '#',
            'icon' => 'IconCalendarWeek',
            'order' => 4,
            'active' => true,
        ]);

        Menu::create([
            'parent_id' => $menuReservas->id,
            'name' => 'Reservas',
            'route_name' => 'admin.bookings.index',
            'icon' => 'IconCalendarCheck',
            'order' => 1,
            'active' => true,
        ]);

        Menu::create([
            'parent_id' => $menuReservas->id,
            'name' => 'Pagos',
            'route_name' => 'admin.payments.index',
            'icon' => 'IconBrandMastercard',
            'order' => 2,
            'active' => true,
        ]);

        $countries = Country::factory(50)->create();

        $bedTypes = [
            'Individual' => 1,
            'Estándar' => 2,
            'King' => 2,
            'Real' => 2
        ];

        $bedTypes = array_map(fn($name, $quantity) => BedType::create(['name' => $name, 'capacity' => $quantity]),
            array_keys($bedTypes), $bedTypes);

        $facilities = [
            'Baño privado',
            'TV de pantalla plana',
            'Terraza',
            'Wifi',
            'Artículos de aseo gratuitos',
            'Ducha',
            'Inodoro',
            'Suelos de madera o parquet',
            'Toallas',
            'Zona comercial'
        ];

        $facilities = array_map(fn($name) => Facility::create([
            'name' => $name,
        ]), $facilities);

        $roomTypes = [
            'Habitación Individual',

            'Habitación Doble',

            'Habitación Twin',

            'Habitación Triple',

            'Habitación Cuádruple',

            'Habitación Familiar',

            'Habitación King',

            'Habitación Queen',

            'Habitación Estudio',

            'Habitación Deluxe',

            'Habitación Superior',

            'Habitación Ejecutiva',

            'Suite Junior',

            'Suite',
            'Suite Presidencial',

            'Habitación Comunicada',

            'Habitación Adyacente',

            'Habitación Accesible',

            'Habitación para Fumadores',

            'Habitación para No Fumadores',
        ];

        $roomTypes = collect($roomTypes)->map(function ($name) {
            return RoomType::factory()->create(['name' => $name, 'slug' => \Str::slug($name,)]);
        });

        $roomTypes->map(function ($roomType) use ($bedTypes, $facilities) {
            $roomType->bedTypes()->sync([fake()->randomElement($bedTypes)->id => ['quantity' => mt_rand(1, 2)]]);
            $roomType->facilities()->sync(fake()->randomElements($facilities, mt_rand(5, 10)));
        });

        $roomTypes->each(function ($roomType) {
            Room::factory(mt_rand(10, 30))->create(['room_type_id' => $roomType->id]);
        });


        $mealPlans = [
            ['code' => 'RO', 'name' => 'Solo Habitación', 'description' => 'Sin comidas incluidas', 'adult_price' => 0.00, 'child_price' => 0.00, 'infant_price' => 0.00],
            ['code' => 'BB', 'name' => 'Cama y Desayuno', 'description' => 'Desayuno incluido', 'adult_price' => 10.00, 'child_price' => 8.00, 'infant_price' => 0.00],
            ['code' => 'HB', 'name' => 'Media Pensión', 'description' => 'Desayuno + Cena', 'adult_price' => 25.00, 'child_price' => 20, 'infant_price' => 0.00],
            ['code' => 'FB', 'name' => 'Pensión Completa', 'description' => 'Desayuno + Almuerzo + Cena', 'adult_price' => 40.00, 'child_price' => 30, 'infant_price' => 5.00],
            ['code' => 'AI', 'name' => 'Todo Incluido', 'description' => 'Todas las comidas + bebidas', 'adult_price' => 65.00, 'child_price' => 50.00, 'infant_price' => 10.00]
        ];

        foreach ($mealPlans as $mealPlan) {
            MealPlan::create($mealPlan);
        }

        $cancellationRules = [
            [
                'min_days_before' => 0,
                'max_days_before' => 1,
                'penalty_percent' => 100,
                'description' => 'Sin reembolso para cancelaciones realizadas dentro de 1 día antes del check-in',
            ],
            [
                'min_days_before' => 2,
                'max_days_before' => 3,
                'penalty_percent' => 50,
                'description' => '50% de reembolso para cancelaciones realizadas 2-3 días antes del check-in',
            ],
            [
                'min_days_before' => 4,
                'max_days_before' => 7,
                'penalty_percent' => 25,
                'description' => '25% de penalización para cancelaciones 4-7 días antes del check-in',
            ],
            [
                'min_days_before' => 8,
                'max_days_before' => 999,
                'penalty_percent' => 0,
                'description' => 'Cancelación gratuita para reservas canceladas 8 o más días antes del check-in',
            ],
        ];

        foreach ($cancellationRules as $cancellationRule) {
            CancellationRule::create($cancellationRule);
        }

        $customers = Customer::factory(200)
            ->state(fn() => ['national_id' => $countries->random()->id])
            ->create();


        $roomTypes = $roomTypes->random(mt_rand(2, 5));
        $allRooms = Room::whereHas('type', function ($query) use ($roomTypes) {
            $query->whereIn('name', $roomTypes->map(fn($r) => $r->name)->toArray());
        })
            ->whereNot('status', RoomStatus::Maintenance)
            ->get();

        $rooms = Room::with('type')->whereNot('status', RoomStatus::Maintenance)->inRandomOrder()->limit(100)->get();

        $rooms = $rooms->concat($allRooms);

        $rooms->each(function ($room) use ($customers) {
            $customer = $customers->random(1)->first();

            $now = now();
            $booking = Booking::create([
                'customer_id' => $customer->id,
                'adults' => $room->type->max_adult,
                'children' => mt_rand(0, $room->type->max_children),
                'check_in' => $now->addDays(mt_rand(0, 30)),
                'check_out' => $now->clone()->addDays(mt_rand(1, 14)),
                'smoking_preference' => $room->smoking_preference,
                'status' => BookingStatus::RESERVED,
                'meal_plan_id' => 1,
            ]);

            $booking->rooms()->sync($room->id);

            $booking->statuses()->create([
                'status' => BookingStatus::PENDING,
            ]);

            $booking->statuses()->create([
                'status' => BookingStatus::RESERVED,
            ]);


            $mealPlanPrice = 0;
            $booking->charges()->create([
                'charge_type' => ChargeType::MEAL_PLAN,
                'amount' => $mealPlanPrice
            ]);

            $numberOfDays = $booking->check_in->diffInDays($booking->check_out);

            $roomsPrice = $room->type->price * $numberOfDays;
            $booking->charges()->create([
                'charge_type' => ChargeType::ROOM,
                'amount' => $roomsPrice,
            ]);

            $tax = ($roomsPrice + $mealPlanPrice) * config('hotel.tax_rate');
            $booking->charges()->create([
                'charge_type' => ChargeType::TAX,
                'amount' => $tax
            ]);

            BookingChildren::factory($booking->children)->create(['booking_id' => $booking->id]);

            $booking->update(['total_price' => $roomsPrice + $mealPlanPrice + $tax]);
        });
    }
}
