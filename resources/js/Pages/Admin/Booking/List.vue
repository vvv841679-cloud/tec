<template>
    <Head title="Reservas"/>
    <div class="row g-2 align-items-center mb-4">
        <div class="col">
            <h2 class="page-title">Reservas</h2>
        </div>
        <div class="col-auto ms-auto d-print-none">
            <div class="btn-list">

                <Link :href="route('admin.bookings.create')" class="btn btn-primary btn-5 d-none d-sm-inline-block"
                      v-if="access.createBookings"
                      @click="openModal = !openModal">
                    <IconPlus class="icon icon-2"/>
                    Nuevo Registro
                </Link>
            </div>
            </div>
    </div>

    <div class="card">
        <div class="card-table">
            <div class="card-header d-block">
                <div class="row">
                    <h3 class="card-title mb-0">Reservas</h3>
                    <p class="text-secondary m-0">Lista de Reservas.</p>
                </div>
                <div class="row mt-3">
                    <div class="col-3">
                        <select-box
                            placeholder="Todos los Clientes"
                            v-model="filters.customer_id"
                            :options="customers"/>
                    </div>
                    <div class="input-group input-group-flat w-auto col-2">
                        <input id="advanced-table-search" type="number"
                               placeholder="Número de Referencia"
                               class="form-control" autocomplete="off" v-model="filters.ref_number">
                    </div>
                    <div class="input-group input-group-flat w-auto col-2">
                        <input id="advanced-table-search" type="number"
                               placeholder="Número de Habitación"
                               class="form-control" autocomplete="off" v-model="filters.room_number">
                    </div>
                    <div class="col-2">
                        <DatePicker
                            v-model="filters.check_in"
                            placeholder="Fecha de Entrada"
                            range multi-calendars
                            :time-config="{ enableTimePicker: false }"
                        />
                    </div>
                    <div class="col-2">
                        <DatePicker
                            v-model="filters.check_out"
                            placeholder="Fecha de Salida"
                            range multi-calendars
                            :time-config="{ enableTimePicker: false }"
                        />
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-2">
                        <select-box
                            placeholder="Estado de Pago"
                            v-model="filters.payment_status"
                            :options="selectPaymentStatuses"/>
                    </div>
                    <div class="col-2">
                        <select-box
                            placeholder="Todos los Estados"
                            v-model="filters.status"
                            :options="selectStatuses"/>
                    </div>
                    <Link :href="route('admin.bookings.index')" class="btn btn-primary w-auto">
                        <IconRestore class="icon m-0"/>
                        </Link>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-selectable card-table table-vcenter text-nowrap datatable">
                    <thead>
                    <tr>
                        <th class="w-1"></th>
                        <sort-head name="ref_number" v-model="sorts" label="Número de Ref."/>
                        <sort-head name="full_name" v-model="sorts" label="Cliente"/>
                        <th>Huéspedes</th>
                        <SortHead name="check_in" v-model="sorts" label="Entrada"/>
                        <SortHead name="check_out" v-model="sorts" label="Salida"/>
                        <th>Habitación</th>
                        <SortHead name="total_price" v-model="sorts" label="Precio Total"/>
                        <SortHead name="deposit_amount" v-model="sorts" label="Monto Pagado"/>
                        <SortHead name="status" v-model="sorts" label="Estado"/>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody class="table-tbody">
                    <tr v-for="booking in bookings.data" :key="booking.id">
                        <td>
                            <input class="form-check-input m-0 align-middle table-selectable-check" type="checkbox"
                                   aria-label="Seleccionar factura" value="true">
                        </td>
                        <td>{{ booking.ref_number }}</td>
                        <td>{{ booking.customer.full_name }}</td>
                        <td>{{ booking.adults }} - {{ booking.children }}</td>
                        <td>{{ booking.check_in }}</td>
                        <td>{{ booking.check_out }}</td>
                        <td>
                            <span :title="booking.rooms.map(r => r.room_number).join(', ')">
                                {{ booking.rooms.length }} {{ booking.rooms.length === 1 ? 'Habitación' : 'Habitaciones' }}
                            </span>
                        </td>
                        <td>{{ money_format(booking.total_price) }}</td>
                        <td>{{ money_format(booking.deposit_amount) }}</td>
                        <td>
                            <span class="badge" :class="displayStatus(booking.status).bgClass">
                                {{ displayStatus(booking.status).label }}
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="dropdown" v-if="Object.values(booking.access).some(per => per)">
                                <button class="btn dropdown-toggle align-text-top" data-bs-boundary="viewport"
                                        data-bs-toggle="dropdown" aria-expanded="true">
                                    Acciones
                                </button>
                                <div class="dropdown-menu dropdown-menu-end" data-popper-placement="bottom-end">
                                    <Link :href="route('admin.bookings.show', booking.id)" class="dropdown-item"
                                          v-if="booking.access.show">
                                        <IconEye class="icon icon1"/>
                                        Ver
                                    </Link>
                                    <Link :href="route('admin.bookings.payments.index', booking.id)"
                                          class="dropdown-item" v-if="booking.access.payments">
                                        <IconCreditCard class="icon icon1"/>
                                        Pagos
                                    </Link>
                                    <button @click="() => confirmCheck(route('admin.bookings.checkin', booking.id), 'Check-in', booking.customer.full_name)"
                                            class="dropdown-item"
                                            v-if="booking.access.checkIn">
                                        <IconDoorEnter class="icon icon1"/>
                                        Check In
                                    </button>
                                    <Link @click="() => confirmCheck(route('admin.bookings.checkout', booking.id), 'Check-out', booking.customer.full_name)"
                                          class="dropdown-item"
                                          v-if="booking.access.checkOut">
                                        <IconDoorExit class="icon icon1"/>
                                        Check Out
                                    </Link>
                                    <button type="button" @click="openCancelModal(booking)"
                                            class="dropdown-item"
                                            v-if="booking.access.cancel">
                                        <IconCalendarX class="icon icon1"/>
                                        Cancelar
                                    </button>
                                </div>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div class="card-footer d-flex align-items-center">
                <select class="form-select w-auto" v-model="limit" @change="syncFilters">
                    <option value="15" selected>15 registros</option>
                    <option value="25">25 registros</option>
                    <option value="50">50 registros</option>
                    <option value="100">100 registros</option>
                </select>
                <Pagination :links="bookings.meta.links"/>
            </div>
        </div>
    </div>
    <Cancel v-if="editingBooking" :booking="editingBooking"/>
</template>

<script setup>
import {provide, ref, toRaw, watch} from "vue";
import {debounce} from "@tabler/core/dist/libs/list.js/src/utils/events.js";
import {router} from "@inertiajs/vue3";
import Pagination from "../../../Shared/Admin/Pagination.vue";
import {IconPlus, IconEye, IconCreditCard, IconDoorEnter, IconDoorExit, IconRestore, IconCalendarX} from '@tabler/icons-vue';
import SortHead from "../../../Components/SortHead.vue";
import {useEnum} from "../../../Composables/useEnum.js";
import Swal from "sweetalert2";
import SelectBox from "../../../Components/SelectBox.vue";
import DatePicker from "../../../Components/DatePicker.vue";
import {money_format} from "../../../Utils/helper.js";
import Cancel from "./Cancel.vue";

const props = defineProps({
    'bookings': Object,
    'smokingPreferences': Array,
    'customers': Object,
    'statuses': Array,
    'filters': Object,
    'sorts': String,
    'limit': Number,
    'access': Object,
    'paymentStatuses': Array,
});

const {
    select: selectStatuses,
    display: displayStatus
} = useEnum(props.statuses)

const {
    select: selectPaymentStatuses,
} = useEnum(props.paymentStatuses)

const editingBooking = ref(null);

provide("closeModal", () => {
    if(editingBooking) editingBooking.value = null
});

const openCancelModal = (booking) => {
    editingBooking.value = booking;
}


const confirmCheck = (url, action, customer) => {
    // Traducción del texto del SweetAlert2
    let translatedAction = action === 'Check-in' ? 'Check-in' : 'Check-out';
    let translatedConfirmationText = action === 'Check-in' ? `Sí, realizar el Check-in` : `Sí, realizar el Check-out`;

    Swal.fire({
        title:  `Confirmar ${translatedAction}`,
        html: `<div style="text-align: left">¿Está seguro de que desea realizar el <b>${translatedAction}</b> de la reserva para el cliente <b>${customer}</b>?</br>
Esta acción es definitiva y será registrada oficialmente en el sistema.</div>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: translatedConfirmationText,
        cancelButtonText: 'Cancelar', // Agregamos la traducción del botón Cancelar
    }).then((result) => {
        if (result.isConfirmed) {
            router.post(url)
        }
    })
}

const filters = ref(props.filters);
const sorts = ref(props.sorts);
const limit = ref(props.limit)

watch(filters, debounce(() => {
    syncFilters()
}, 300), {
    deep: true
})


watch(sorts, () => syncFilters());

const syncFilters = () => {
    router.get(route('admin.bookings.index'), {
        sorts: sorts.value,
        limit: limit.value,
        filters: toRaw(filters.value)
    }, {
        preserveState: true,
        replace: true
    })
}

</script>
