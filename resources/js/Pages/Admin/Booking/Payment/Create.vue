<template>
    <Modal title="Registrar Nuevo Pago" formId="createPaymentForm">
        <form @submit.prevent="submitCreate" method="post" id="createPaymentForm" class="gap-inputs">
            <!-- Resumen de pago -->
            <div class="alert alert-info mb-3">
                <div class="d-flex justify-content-between mb-1">
                    <strong>Total de la reserva:</strong>
                    <span>Bs. {{ booking.total_price }}</span>
                </div>
                <div class="d-flex justify-content-between mb-1">
                    <strong>Ya pagado:</strong>
                    <span>Bs. {{ booking.deposit_amount }}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <strong>Saldo pendiente:</strong>
                    <span class="text-danger"><strong>Bs. {{ remainingAmount }}</strong></span>
                </div>
            </div>

            <!-- Botones de porcentaje rápido -->
            <div class="mb-3" v-if="remainingAmount > 0">
                <label class="form-label">Selección rápida:</label>
                <div class="btn-group w-100" role="group">
                    <button type="button" class="btn btn-outline-primary" @click="setPercentage(30)">30%</button>
                    <button type="button" class="btn btn-outline-primary" @click="setPercentage(50)">50%</button>
                    <button type="button" class="btn btn-outline-primary" @click="setPercentage(100)">100%</button>
                    <button type="button" class="btn btn-outline-success" @click="setRemainingAmount">Saldo completo</button>
                </div>
            </div>

            <div class="row">
                <BaseInput
                    label="Monto a pagar (Bs.)"
                    v-model="form.amount"
                    :error="form.errors.amount"
                    placeholder="Ingrese el monto"
                    type="number"
                    step="0.01"
                    required
                />
            </div>
            <div class="row">
                <select-box
                    label="Método de Pago"
                    placeholder="Seleccione el método de pago"
                    :options="selectMethods"
                    v-model="form.payment_method"
                    :error="form.errors.payment_method"
                    required>
                </select-box>
                <small class="text-muted mt-1">
                    💡 Selecciona "Código QR" para generar un QR que el cliente puede escanear
                </small>
            </div>

            <!-- Botón especial para generar QR -->
            <div class="alert alert-success" v-if="form.payment_method === 'QR_CODE'">
                <h5>📱 Generar Código QR</h5>
                <p class="mb-2">Al guardar este pago con método "Código QR", se generará automáticamente un código QR que podrás mostrar al cliente para que escanee y pague.</p>
                <small class="text-muted">El QR aparecerá en la lista de pagos después de guardar.</small>
            </div>
            <div class="row">
                <select-box
                    label="Status"
                    placeholder="Choose Your Payment Status"
                    :options="selectStatuses"
                    v-model="form.status"
                    :error="form.errors.status"
                    required>
                </select-box>
            </div>
            <div class="row">
                <div class="row">
                    <BaseInput
                        label="Reference"
                        v-model="form.reference"
                        :error="form.errors.reference"
                        placeholder="reference"
                        />
                </div>
            </div>
            <div class="row">
                <base-textarea
                    label="Note"
                    placeholder="some information"
                    v-model="form.note"
                    :error="form.errors.note">
                </base-textarea>
            </div>
        </form>
    </Modal>
</template>
<script setup>
import {useForm} from "@inertiajs/vue3";
import {inject, computed} from "vue";
import Modal from "../../../../Components/Modal.vue";
import BaseInput from "../../../../Components/BaseInput.vue";
import SelectBox from "../../../../Components/SelectBox.vue";
import BaseTextarea from "../../../../Components/BaseTextarea.vue";

const {booking_id, booking, defaultStatus} = defineProps({
    'booking_id': Number,
    'booking': Object,
    selectMethods: Object,
    selectStatuses: Object,
    defaultStatus: String,
})

// Calcular el saldo pendiente
const remainingAmount = computed(() => {
    return (parseFloat(booking.total_price) - parseFloat(booking.deposit_amount)).toFixed(2)
})

const form = useForm({
    amount: '',
    payment_method: 'CASH', // Pre-seleccionar CASH por defecto
    status: defaultStatus,
    reference: '',
    note: '',
});

// Función para establecer un porcentaje del total
const setPercentage = (percentage) => {
    const amount = (parseFloat(booking.total_price) * percentage / 100).toFixed(2)
    form.amount = amount
}

// Función para establecer el saldo completo
const setRemainingAmount = () => {
    form.amount = remainingAmount.value
}

const closeModal = inject('closeModal');
const submitCreate = () => {
    form.post(route('admin.bookings.payments.store', booking_id), {
        onSuccess: () => closeModal()
    })
}

</script>
