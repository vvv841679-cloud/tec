<template>
    <Head title="Lista de pagos de la reserva" />
    <div class="row g-2 align-items-center mb-4">
        <div class="col">
            <h2 class="page-title">Lista de pagos de la reserva ({{booking.customer.full_name}})</h2>
        </div>
        <div class="col-auto ms-auto d-print-none">
            <div class="btn-list">
                <button v-if="access.createPayment"
                        class="btn btn-primary btn-5 d-none d-sm-inline-block"
                        @click="openModal = !openModal">
                    <IconPlus class="icon icon-2"/>
                    Nuevo Registro
                </button>
                <Link class="btn btn-1" :href="route('admin.bookings.index')">
                    <IconArrowLeft class="icon"/>
                    Volver
                </Link>
            </div>
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
                        <th class="w-1"></th>
                        <th>
                            Monto
                        </th>
                        <th>
                            Tipo
                        </th>
                        <th>Método de pago</th>
                        <th>Estado</th>
                        <th>QR</th>
                        <th>Fecha de pago</th>
                        <th>Fecha de Creación</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody class="table-tbody">
                        <tr v-for="payment in payments" v-if="payments.length">
                            <td>
                                <input class="form-check-input m-0 align-middle table-selectable-check" type="checkbox"
                                        aria-label="Seleccionar factura" value="true">
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
                            <td>
                                <button
                                    v-if="payment.qr_image_url"
                                    @click="showQR(payment)"
                                    class="btn btn-sm btn-success"
                                    title="Ver código QR">
                                    <i class="bi bi-qr-code"></i> Ver QR
                                </button>
                                <span v-else class="text-muted">-</span>
                            </td>
                            <td>{{ payment.paid_at ?? '-' }}</td>
                            <td>{{ payment.created_at }}</td>
                            <td class="text-end">
                                <div class="dropdown" v-if="Object.values(payment.access).some(per => per)">
                                    <button class="btn dropdown-toggle align-text-top" data-bs-boundary="viewport"
                                            data-bs-toggle="dropdown" aria-expanded="true">
                                        Acciones
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-end" data-popper-placement="bottom-end">
                                        <button class="dropdown-item align-middle"
                                                @click="openEditModal(payment)"
                                                v-if="payment.access.edit">
                                            <IconEdit class="icon icon1"/>
                                            Editar
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr v-else>
                            <td colspan="9" class="text-center">No existen registros de pagos.</td>
                        </tr>
                        <tr class="border-top-wide">
                            <td colspan="4" class="text-center">Precio Total: {{ money_format(booking.total_price) }}</td>
                            <td colspan="4" class="text-center">Monto del Depósito: {{
                                    money_format(booking.deposit_amount)
                                }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <Create v-if="openModal && !editingPayment" :booking_id="booking.id" :booking="booking" v-bind="{selectMethods, selectStatuses, defaultStatus}"/>
    <Update v-if="openModal && editingPayment" :payment="editingPayment" v-bind="{selectMethods, selectStatuses}"/>

    <!-- Modal para mostrar QR -->
    <div class="modal fade" id="qrModal" tabindex="-1" aria-labelledby="qrModalLabel" aria-hidden="true" ref="qrModalRef">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="qrModalLabel">Código QR de Pago</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center" v-if="selectedPayment">
                    <div class="alert alert-info mb-3">
                        <div><strong>Monto:</strong> {{ money_format(selectedPayment.amount) }}</div>
                        <div><strong>Estado:</strong> <span class="badge" :class="displayStatus(selectedPayment.status).bgClass">{{ displayStatus(selectedPayment.status).label }}</span></div>
                        <div v-if="selectedPayment.payment_number"><strong>Nº Pago:</strong> {{ selectedPayment.payment_number }}</div>
                    </div>
                    <div class="qr-container">
                        <img
                            :src="selectedPayment.qr_image_url.startsWith('data:') ? selectedPayment.qr_image_url : `data:image/png;base64,${selectedPayment.qr_image_url}`"
                            alt="Código QR"
                            class="img-fluid"
                            style="max-width: 400px; border: 2px solid #ccc; padding: 20px; border-radius: 8px;">
                    </div>
                    <div class="mt-3">
                        <p class="text-muted mb-0">Muestra este código QR al cliente para que lo escanee con su billetera digital.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import {provide, ref} from "vue";
import Create from "./Create.vue";
import {IconEdit, IconPlus, IconArrowLeft} from '@tabler/icons-vue';
import Update from "./Update.vue";
import {useEnum} from "../../../../Composables/useEnum.js";
import {money_format} from "../../../../Utils/helper.js";


const {types,statuses,methods} = defineProps({
    booking: Object,
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
    select: selectMethods,
    display: displayMethod,
} = useEnum(methods)

const {
    select: selectStatuses,
    default: defaultStatus,
    display: displayStatus,
} = useEnum(statuses)

let editingPayment = ref(null);
let openModal = ref(false);
let selectedPayment = ref(null);
let qrModalRef = ref(null);

provide("closeModal", () => {
    openModal.value = false
    if(editingPayment) editingPayment.value = null
});

const openEditModal = (payment) => {
    editingPayment.value = payment;
    openModal.value = true
}

const showQR = (payment) => {
    selectedPayment.value = payment;
    // Usar Bootstrap 5 Modal API
    const modal = new bootstrap.Modal(document.getElementById('qrModal'));
    modal.show();
}

</script>
