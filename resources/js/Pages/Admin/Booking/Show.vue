<template>
    <Head title="Ver reserva"/>
    <div class="row g-2 align-items-center mb-4">
        <div class="col">
            <h2 class="page-title text-capitalize">Reserva  {{ booking.customer.full_name }}</h2>
        </div>
        <div class="col-auto ms-auto d-print-none">
            <div class="btn-list">
                <Link class="btn btn-success" :href="route('admin.bookings.payments.index', booking.id)">
                    <IconCreditCard class="icon"/>
                    Gestionar Pagos
                </Link>
                <Link class="btn btn-1" :href="route('admin.bookings.index')">
                    <IconArrowLeft class="icon"/>
                    Volver
                </Link>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="card">
            <div class="card-header">
                <div class="row g-2 align-items-center w-full my-2">
                    <span class="col m-0">Información Básica</span>
                </div>
            </div>
            <div class="card-body py-5 px-3">
                <div class="datagrid">
                    <div class="datagrid-item">
                        <div class="datagrid-title">Nº de Referencia</div>
                        <div class="datagrid-content">{{ booking.ref_number }}</div>
                    </div>
                    <div class="datagrid-item">
                        <div class="datagrid-title">Nombre Completo</div>
                        <div class="datagrid-content">
                            <Link :href="route('admin.customers.show', booking.customer.id)">
                                {{ booking.customer.full_name }}
                            </Link>
                        </div>
                    </div>
                    <div class="datagrid-item">
                        <div class="datagrid-title">Adultos</div>
                        <div class="datagrid-content">{{ booking.adults }}</div>
                    </div>
                    <div class="datagrid-item">
                        <div class="datagrid-title">Niños</div>
                        <div class="datagrid-content">{{ booking.children ?? '-' }}</div>
                    </div>
                    <div class="datagrid-item">
                        <div class="datagrid-title">Fecha de Entrada (Check In)</div>
                        <div class="datagrid-content">{{ booking.check_in }}</div>
                    </div>
                    <div class="datagrid-item">
                        <div class="datagrid-title">Fecha de Salida (Check Out)</div>
                        <div class="datagrid-content">{{ booking.check_out }}</div>
                    </div>
                    <div class="datagrid-item">
                        <div class="datagrid-title">Régimen de Comida</div>
                        <div class="datagrid-content">{{ booking.mealPlan.name }}</div>
                    </div>
                    <div class="datagrid-item">
                        <div class="datagrid-title">Estado</div>
                        <div class="datagrid-content">
                            <span class="badge" :class="displayStatus(booking.status).bgClass">
                                {{ displayStatus(booking.status).label }}
                            </span>
                        </div>
                    </div>
                    <div class="datagrid-item">
                        <div class="datagrid-title">Estado de Pago</div>
                        <div class="datagrid-content">
                            <Link :href="route('admin.bookings.payments.index', booking.id)">
                             <span class="badge" :class="displayPaymentStatus(booking.payment_status).bgClass">
                                 {{ displayPaymentStatus(booking.payment_status).label }}
                             </span>
                            </Link>
                        </div>
                    </div>
                    <div class="datagrid-item">
                        <div class="datagrid-title">Preferencia de Fumar</div>
                        <div class="datagrid-content">
                            <span class="badge" :class="displaySmoking(booking.smoking_preference).bgClass">
                                {{ displaySmoking(booking.smoking_preference).label }}
                            </span>
                        </div>
                    </div>
                    <div class="datagrid-item">
                        <div class="datagrid-title">Precio Total</div>
                        <div class="datagrid-content">
                            {{ money_format(booking.total_price) }}
                        </div>
                    </div>
                    <div class="datagrid-item">
                        <div class="datagrid-title">Monto Pagado</div>
                        <div class="datagrid-content text-success">
                            <strong>{{ money_format(booking.deposit_amount) }}</strong>
                        </div>
                    </div>
                    <div class="datagrid-item">
                        <div class="datagrid-title">Saldo Pendiente</div>
                        <div class="datagrid-content" :class="remainingAmount > 0 ? 'text-danger' : 'text-success'">
                            <strong>{{ money_format(remainingAmount) }}</strong>
                        </div>
                    </div>
                    <div class="datagrid-item">
                        <div class="datagrid-title">Creado en</div>
                        <div class="datagrid-content">{{ booking.created_at }}</div>
                    </div>
                </div>
                <div class="datagrid mt-4">
                    <div class="datagrid-item">
                        <div class="datagrid-title">Peticiones Especiales</div>
                        <div class="datagrid-content">
                            {{ booking.special_requests ?? '-' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card mt-4">
            <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs nav-fill w-75" data-bs-toggle="tabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a href="#rooms" class="nav-link active" data-bs-toggle="tab" aria-selected="true" role="tab">
                            <IconWindow class="icon me-2    "/>
                            Habitaciones
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a href="#charges" class="nav-link" data-bs-toggle="tab" aria-selected="false" role="tab"
                           tabindex="-1">
                            <IconCreditCard class="icon me-2"/>
                            Cargos
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a href="#statuses" class="nav-link" data-bs-toggle="tab" aria-selected="false" role="tab"
                           tabindex="-1">
                            <IconSquareCheck class="icon me-2"/>
                            Estados
                        </a>
                    </li>
                    <li class="nav-item" role="presentation" v-if="booking.kids.length">
                        <a href="#children" class="nav-link" data-bs-toggle="tab" aria-selected="false" role="tab"
                           tabindex="-1">
                            <IconBabyCarriage class="icon me-2"/>
                            Niños
                        </a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content py-4 px-2">
                    <div id="rooms" class="tab-pane active show" role="tabpanel">
                        <table class="table table-vcenter card-table table-striped">
                            <thead>
                            <tr>
                                <th>Número de Habitación</th>
                                <th>Piso</th>
                                <th>Tipo</th>
                                <th>Preferencia de Fumar</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="room in booking.rooms">
                                <td>{{ room.room_number }}</td>
                                <td>{{ room.floor_number }}</td>
                                <td>
                                    <Link :href="route('admin.roomTypes.edit', room.type.id)">
                                        {{ room.type.name }}
                                    </Link>
                                </td>
                                <td>
                                    <span class="badge" :class="displaySmoking(room.smoking_preference).bgClass">
                                        {{ displaySmoking(room.smoking_preference).label }}
                                    </span>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div id="charges" class="tab-pane" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h3 class="m-0">Cargos</h3>
                            <button
                                v-if="access.addCharge && booking.status === 'check_in'"
                                @click="showAddChargeModal = true"
                                class="btn btn-primary"
                            >
                                <IconPlus class="icon me-1"/>
                                Agregar Servicio
                            </button>
                        </div>
                        <table class="table table-vcenter card-table table-striped">
                            <thead>
                            <tr>
                                <th>Tipo de Cargo</th>
                                <th>Descripción</th>
                                <th>Monto</th>
                                <th>Fecha</th>
                                <th v-if="access.removeCharge">Acciones</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="charge in booking.charges" :key="charge.id">
                                <td>
                                    <span class="badge bg-info">{{ charge.charge_type }}</span>
                                </td>
                                <td>{{ charge.description || '-' }}</td>
                                <td>{{ money_format(charge.amount) }}</td>
                                <td>{{ charge.created_at }}</td>
                                <td v-if="access.removeCharge">
                                    <button
                                        v-if="charge.charge_type === 'service'"
                                        @click="deleteCharge(charge.id)"
                                        class="btn btn-sm btn-danger"
                                        title="Eliminar servicio"
                                    >
                                        <IconTrash class="icon"/>
                                    </button>
                                    <span v-else class="text-muted">-</span>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div id="statuses" class="tab-pane" role="tabpanel">
                        <table class="table table-vcenter card-table table-striped w-75">
                            <thead>
                            <tr>
                                <th>Estado</th>
                                <th>Descripción</th>
                                <th>Creado en</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="status in booking.statuses">
                                <td>
                                    <span class="badge" :class="displayStatus(status.status).bgClass">
                                        {{ displayStatus(status.status).label }}
                                    </span>
                                </td>
                                <td>{{ status.description ?? '-' }}</td>
                                <td>{{ status.created_at }}</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div id="children" class="tab-pane" role="tabpanel" v-if="booking.kids.length">
                        <table class="table table-vcenter card-table table-striped w-50">
                            <thead>
                            <tr>
                                <th>Edad</th>
                            </tr>
                            </thead>
                            <tbody>
                                <tr v-for="kid in booking.kids">
                                    <td>{{ kid.age }} años</td> </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal modal-blur fade" :class="{'show': showAddChargeModal}" :style="{display: showAddChargeModal ? 'block' : 'none'}" tabindex="-1" role="dialog" aria-modal="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Agregar Servicio Adicional</h5>
                    <button type="button" class="btn-close" @click="closeModal" aria-label="Cerrar"></button>
                </div>
                <form @submit.prevent="submitCharge">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label required">Descripción del Servicio</label>
                            <input
                                v-model="chargeForm.description"
                                type="text"
                                class="form-control"
                                :class="{'is-invalid': chargeForm.errors.description}"
                                placeholder="Ej: Room Service - Desayuno"
                                required
                            >
                            <div v-if="chargeForm.errors.description" class="invalid-feedback">
                                {{ chargeForm.errors.description }}
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label required">Monto</label>
                            <input
                                v-model="chargeForm.amount"
                                type="number"
                                step="0.01"
                                min="0.01"
                                class="form-control"
                                :class="{'is-invalid': chargeForm.errors.amount}"
                                placeholder="0.00"
                                required
                            >
                            <div v-if="chargeForm.errors.amount" class="invalid-feedback">
                                {{ chargeForm.errors.amount }}
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="closeModal">Cancelar</button>
                        <button type="submit" class="btn btn-primary" :disabled="chargeForm.processing">
                            <span v-if="chargeForm.processing" class="spinner-border spinner-border-sm me-2"></span>
                            Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div v-if="showAddChargeModal" class="modal-backdrop fade show"></div>
</template>
<script setup>
import {defineProps, ref, computed} from "vue"
import {IconArrowLeft, IconWindow, IconCreditCard, IconSquareCheck, IconBabyCarriage, IconPlus, IconTrash} from "@tabler/icons-vue";
import {useEnum} from "../../../Composables/useEnum.js";
import {Link, useForm, router} from "@inertiajs/vue3";
import {money_format} from "../../../Utils/helper.js";


const props = defineProps({
    booking: Object,
    statuses: Array,
    paymentStatuses: Array,
    smokingPreferences: Array,
    access: Object,
})

const {display: displayStatus} = useEnum(props.statuses)
const {display: displayPaymentStatus} = useEnum(props.paymentStatuses)

const {
    display: displaySmoking
} = useEnum(props.smokingPreferences)

// Calcular el saldo pendiente
const remainingAmount = computed(() => {
    return parseFloat(props.booking.total_price) - parseFloat(props.booking.deposit_amount)
})

// Charge management
const showAddChargeModal = ref(false)
const chargeForm = useForm({
    description: '',
    amount: ''
})

const submitCharge = () => {
    chargeForm.post(route('admin.bookings.charges.store', props.booking.id), {
        preserveScroll: true,
        onSuccess: () => {
            closeModal()
        }
    })
}

const closeModal = () => {
    showAddChargeModal.value = false
    chargeForm.reset()
    chargeForm.clearErrors()
}

const deleteCharge = (chargeId) => {
    if (confirm('¿Está seguro de eliminar este servicio?')) {
        router.delete(route('admin.bookings.charges.destroy', [props.booking.id, chargeId]), {
            preserveScroll: true
        })
    }
}

</script>
