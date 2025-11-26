<template>
    <div class="container">
        <Head title="panel de control" />
        <div class="g-2 align-items-center mt-4">
            <div class="page-pretitle">Resumen</div>
            <h2 class="page-title pt-1">Panel de Control</h2>
        </div>

        <div class="row g-3 mt-3">

            <div class="col-md-3">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                        <span class="bg-success text-white avatar ml-0 rounded" style="padding: 12px">
                           <IconCalendar class="icon"/>
                        </span>
                            </div>
                            <div class="col">
                                <h6 class="font-weight-medium mb-1">Próximas Estancias</h6>
                                <h4 class="text-secondary mb-0">{{ upcoming_count }} reserva(s)</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                        <span class="bg-danger text-white avatar ml-0 rounded" style="padding: 12px">
                           <IconDoorExit class="icon"/>
                        </span>
                            </div>
                            <div class="col">
                                <h6 class="font-weight-medium mb-1">Reservas Pasadas</h6>
                                <h4 class="text-secondary mb-0">{{ past_count }} completada(s)</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                        <span class="bg-warning text-white avatar ml-0 rounded" style="padding: 12px">
                           <IconHistory class="icon"/>
                        </span>
                            </div>
                            <div class="col">
                                <h6 class="font-weight-medium mb-1">Pagos Totales</h6>
                                <h4 class="text-secondary mb-0">{{ money_format(total_paid) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                        <span class="bg-primary text-white avatar ml-0 rounded" style="padding: 12px">
                           <IconBan class="icon"/>
                        </span>
                            </div>
                            <div class="col">
                                <h6 class="font-weight-medium mb-1">Cancelaciones</h6>
                                <h4 class="text-secondary mb-0">{{ cancellation_count }} cancelada(s)</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="card mb-4 shadow-sm mt-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><IconCalendarEvent class="icon" /> Su Próxima Reserva</h5>
            </div>

            <div class="card-body">
                <p class="text-muted" v-if="!upcoming_booking">
                    Usted no tiene próximas reservas.
                </p>

                <div class="room-details" v-else>
                    <h5>Habitación: {{ upcoming_booking.rooms[0].type.name }}</h5>
                    <p class="mb-2 mt-3">
                        <IconCalendarStats class="icon" />
                        {{ upcoming_booking.check_in }} → {{ upcoming_booking.check_out }}
                    </p>
                    <p class="mb-2">
                        <IconCurrencyDollar class="icon" />
                        Precio Total: <strong>{{ money_format(upcoming_booking.total_price) }}</strong>
                    </p>

                    <a :href="route('bookings.success', upcoming_booking.id)"
                       class="btn btn-book-now px-3 py-2 fs-6">
                        <IconEye class="icon me-1" /> Ver Reserva
                    </a>
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header border-0">
                <h5 class="mb-0">Reservas Recientes</h5>
            </div>
            <div class="card-table table-responsive p-0">
                <table class="table table-vcenter">
                    <thead>
                    <tr>
                        <th>#ID</th>
                        <th>Huéspedes</th>
                        <th>Entrada</th>
                        <th>Salida</th>
                        <th>Estado</th>
                        <th>Total</th>
                        <th>Monto Pagado</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="booking in recent_bookings" v-if="recent_bookings.length">
                        <td><Link :href="route('bookings.success', booking.id)"> {{ booking.ref_number }}</Link></td>
                        <th>{{booking.adults + booking.children}}</th>
                        <td>{{ booking.check_in }}</td>
                        <td>{{ booking.check_out }}</td>
                        <td>
                        <span class="badge" :class="displayStatus(booking.status).bgClass">
                                {{ displayStatus(booking.status).label }}
                        </span>
                        </td>
                        <td>{{ money_format(booking.total_price) }}</td>
                        <td>{{ money_format(booking.deposit_amount) }}</td>
                    </tr>
                    <tr v-else>
                        <td colspan="7" class="text-center py-3 text-muted">
                            No se encontraron reservas.
                        </td>
                    </tr>
                    </tbody>
                </table>

            </div>
        </div>
    </div>
</template>


<script setup>
import {IconCalendar, IconBan, IconDoorExit, IconHistory, IconCalendarEvent, IconCalendarStats, IconCurrencyDollar, IconEye} from "@tabler/icons-vue";
import {money_format} from "../../Utils/helper.js";
import {useEnum} from "../../Composables/useEnum.js";

const {statuses} = defineProps({
    upcoming_count: Number,
    past_count: Number,
    total_paid: Number,
    cancellation_count: Number,
    upcoming_booking: Number,
    recent_bookings: Number,
    'statuses': Object
})

const {display: displayStatus} = useEnum(statuses)

</script>

<style scoped>
.icon {
    vertical-align: middle !important;
}
</style>
