<template>
    <div class="container">
        <Head title="lista de pagos"/>
        <div class="row g-2 align-items-center mb-4">
            <div class="col">
                <h2 class="page-title">Pagos</h2>
            </div>
        </div>

        <div class="card">
            <div class="card-table">
                <div class="card-header">
                    <div class="row w-full">
                        <div class="col">
                            <h3 class="card-title mb-0">Pagos</h3>
                            <p class="text-secondary m-0">Lista de Pagos.</p>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-selectable card-table table-vcenter text-nowrap datatable">
                        <thead>
                        <tr>
                            <th>
                                Reserva
                            </th>
                            <th>
                                Monto
                            </th>
                            <th>
                                Tipo
                            </th>
                            <th>Método de Pago</th>
                            <th>Estado</th>
                            <th>Fecha de Pago</th>
                            <th>Creado En</th>
                        </tr>
                        </thead>
                        <tbody class="table-tbody">
                        <tr v-for="payment in payments" v-if="payments.length">
                            <td>
                                <Link :href="route('bookings.success', payment.booking.id)">
                                    #{{payment.booking.ref_number}}
                                </Link>
                            </td>
                            <td>{{ money_format(payment.amount) }}</td>
                            <td>
                                <span class="badge" :class="displayType(payment.type).bgClass">
                                    {{ displayType(payment.type).label }}
                                </span>
                            </td>
                            <td>
                                <span class="badge" :class="displayMethod(payment.payment_method).bgClass">
                                {{ displayMethod(payment.payment_method).label }}
                            </span>
                            </td>
                            <td>
                                <span class="badge" :class="displayStatus(payment.status).bgClass">
                                {{ displayStatus(payment.status).label }}
                            </span>
                            </td>
                            <td>{{ payment.paid_at ?? '-' }}</td>
                            <td>{{ payment.created_at }}</td>
                        </tr>
                        <tr v-else>
                            <td colspan="8" class="text-center">No existen registros de pagos.</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import {useEnum} from "../../Composables/useEnum.js";
import {money_format} from "../../Utils/helper.js";

const {types, statuses, methods} = defineProps({
    payments: Array,
    types: Array,
    statuses: Array,
    methods: Array,
    access: Object,
});

const {
    display: displayType
} = useEnum(types)

const {
    display: displayMethod,
} = useEnum(methods)

const {
    display: displayStatus,
} = useEnum(statuses)

</script>
