<template>
    <div class="modal modal-blur fade" :id="modalId" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Agregar Servicio Adicional</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <form @submit.prevent="handleSubmit">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label required">Descripción del Servicio</label>
                            <input
                                type="text"
                                class="form-control"
                                v-model="form.description"
                                :class="{'is-invalid': form.errors.description}"
                                placeholder="Ej: Room Service, Spa, Lavandería, etc."
                                required
                            />
                            <div class="invalid-feedback" v-if="form.errors.description">
                                {{ form.errors.description }}
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label required">Monto (Bs.)</label>
                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                class="form-control"
                                v-model="form.amount"
                                :class="{'is-invalid': form.errors.amount}"
                                placeholder="0.00"
                                required
                            />
                            <div class="invalid-feedback" v-if="form.errors.amount">
                                {{ form.errors.amount }}
                            </div>
                        </div>

                        <div class="alert alert-info mb-0">
                            <i class="bi bi-info-circle me-2"></i>
                            Este cargo se agregará al total de la reserva. El cliente deberá pagar la diferencia si aún no ha completado el pago.
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary" :disabled="form.processing">
                            <span v-if="form.processing">
                                <span class="spinner-border spinner-border-sm me-2"></span>
                                Agregando...
                            </span>
                            <span v-else>
                                <i class="bi bi-plus-circle me-2"></i>
                                Agregar Servicio
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
import { ref } from 'vue'

const props = defineProps({
    bookingId: {
        type: Number,
        required: true
    },
    modalId: {
        type: String,
        default: 'add-service-modal'
    }
})

const form = useForm({
    description: '',
    amount: null
})

const handleSubmit = () => {
    form.post(route('admin.bookings.services.store', props.bookingId), {
        preserveScroll: true,
        onSuccess: () => {
            // Cerrar el modal
            const modal = document.getElementById(props.modalId)
            const bsModal = bootstrap.Modal.getInstance(modal)
            if (bsModal) {
                bsModal.hide()
            }

            // Limpiar el formulario
            form.reset()
        }
    })
}
</script>
