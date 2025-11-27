<template>
    <header>
        <title>Pagar con QR - {{ booking.id }}</title>
    </header>

    <div class="container-xl my-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Pagar con Código QR</h3>
                    </div>
                    <div class="card-body">
                        <!-- Resumen de la reserva -->
                        <div class="mb-4">
                            <h4>Resumen de tu reserva</h4>
                            <table class="table">
                                <tbody>
                                    <tr>
                                        <td><strong>Reserva #:</strong></td>
                                        <td>{{ booking.id }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Habitación:</strong></td>
                                        <td>{{ roomType.name }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Check-in:</strong></td>
                                        <td>{{ booking.check_in }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Check-out:</strong></td>
                                        <td>{{ booking.check_out }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Total a pagar:</strong></td>
                                        <td class="h3 text-primary">Bs. {{ booking.total_price }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Selector de porcentaje a pagar -->
                        <div v-if="!qrGenerated" class="mb-4">
                            <h5 class="mb-3">Selecciona el monto a pagar</h5>
                            <div class="row g-3">
                                <div class="col-md-4" v-for="option in paymentOptions" :key="option.value">
                                    <div
                                        class="card cursor-pointer payment-option"
                                        :class="{ 'border-primary': selectedPercentage === option.value }"
                                        @click="selectPercentage(option.value)">
                                        <div class="card-body text-center">
                                            <h4 class="mb-1">{{ option.label }}</h4>
                                            <p class="text-muted mb-0">Bs. {{ calculateAmount(option.value) }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Porcentaje personalizado -->
                            <div class="mt-3">
                                <label class="form-label">O ingresa un porcentaje personalizado:</label>
                                <div class="input-group">
                                    <input
                                        type="number"
                                        v-model.number="customPercentage"
                                        @input="selectPercentage(customPercentage)"
                                        class="form-control"
                                        placeholder="Ej: 40"
                                        min="1"
                                        max="100">
                                    <span class="input-group-text">%</span>
                                </div>
                                <small class="text-muted">
                                    Monto calculado: <strong>Bs. {{ calculatedAmount }}</strong>
                                </small>
                            </div>
                        </div>

                        <!-- Botón para generar QR -->
                        <div v-if="!qrGenerated" class="text-center">
                            <button
                                @click="generateQR"
                                class="btn btn-primary btn-lg"
                                :disabled="loading || !selectedPercentage">
                                <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                                {{ loading ? 'Generando...' : `Generar QR para Bs. ${calculatedAmount}` }}
                            </button>
                            <p class="text-muted mt-2 mb-0" v-if="selectedPercentage && selectedPercentage < 100">
                                <small>Pagarás el {{ selectedPercentage }}% del total (Bs. {{ calculatedAmount }})</small>
                            </p>
                        </div>

                        <!-- QR Code generado -->
                        <div v-if="qrGenerated" class="text-center">
                            <!-- Alerta de modo prueba -->
                            <div class="alert alert-warning" v-if="isTest">
                                <h5>🧪 MODO PRUEBA</h5>
                                <div class="mb-2">
                                    <p class="mb-1"><strong>Monto real calculado ({{ paymentPercentage }}%):</strong> Bs. {{ realAmount }}</p>
                                    <p class="mb-1"><strong>Monto de prueba en QR:</strong> Bs. {{ testAmount }}</p>
                                </div>
                                <small class="text-muted">
                                    💡 El QR genera un monto de prueba para evitar cobros altos durante desarrollo.
                                    En producción, se cobrará el monto real.
                                </small>
                            </div>

                            <!-- Info pago parcial -->
                            <div class="alert alert-info" v-if="isPartialPayment">
                                <h5>💰 Pago Parcial - {{ paymentPercentage }}%</h5>
                                <p class="mb-0">Pagarás <strong>Bs. {{ realAmount }}</strong> de Bs. {{ booking.total_price }}</p>
                            </div>

                            <div class="alert alert-success" v-else>
                                <h5>✅ Pago Completo - 100%</h5>
                                <p class="mb-0">Pagarás el total de <strong>Bs. {{ realAmount }}</strong></p>
                            </div>

                            <div class="alert alert-info">
                                <h4>Escanea el código QR para pagar</h4>
                                <p>Usa tu aplicación bancaria para escanear el código QR y completar el pago.</p>
                            </div>

                            <div class="qr-container my-4">
                                <img
                                    :src="qrImageUrl.startsWith('data:') ? qrImageUrl : `data:image/png;base64,${qrImageUrl}`"
                                    alt="Código QR de Pago"
                                    class="img-fluid"
                                    style="max-width: 400px; border: 2px solid #ccc; padding: 20px; border-radius: 8px;">
                            </div>

                            <div class="mb-3">
                                <p class="text-muted">Número de pago: <strong>{{ paymentNumber }}</strong></p>
                                <div v-if="isTest">
                                    <p class="text-muted mb-1">Monto en QR (prueba): <strong class="text-warning">Bs. {{ testAmount }}</strong></p>
                                    <p class="text-muted mb-1">Monto real ({{ paymentPercentage }}%): <strong class="text-primary">Bs. {{ realAmount }}</strong></p>
                                </div>
                                <div v-else>
                                    <p class="text-muted">Monto a pagar: <strong>Bs. {{ realAmount }}</strong></p>
                                </div>
                            </div>

                            <!-- Verificar estado del pago -->
                            <button
                                @click="checkPaymentStatus"
                                class="btn btn-success"
                                :disabled="checking">
                                <span v-if="checking" class="spinner-border spinner-border-sm me-2"></span>
                                {{ checking ? 'Verificando...' : 'Verificar Pago' }}
                            </button>

                            <div class="mt-3">
                                <small class="text-muted">
                                    El pago se verificará automáticamente.
                                    Si ya pagaste, espera unos momentos o haz clic en "Verificar Pago".
                                </small>
                            </div>
                        </div>

                        <!-- Errores -->
                        <div v-if="error" class="alert alert-danger mt-3">
                            {{ error }}
                        </div>
                    </div>

                    <div class="card-footer">
                        <Link :href="route('home')" class="btn btn-link">
                            Volver al inicio
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onUnmounted } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
    booking: Object,
    roomType: Object,
});

const loading = ref(false);
const checking = ref(false);
const qrGenerated = ref(false);
const qrImageUrl = ref('');
const paymentNumber = ref('');
const transactionId = ref('');
const testAmount = ref(0);
const realAmount = ref(0);
const isTest = ref(false);
const error = ref('');
const isPartialPayment = ref(false);
const paymentPercentage = ref(100);

// Pago parcial por porcentaje
const selectedPercentage = ref(100); // Por defecto 100%
const customPercentage = ref(null);
const calculatedAmount = ref(props.booking.total_price);

const paymentOptions = [
    { value: 30, label: '30%' },
    { value: 50, label: '50%' },
    { value: 100, label: '100%' },
];

const calculateAmount = (percentage) => {
    return (props.booking.total_price * percentage / 100).toFixed(2);
};

const selectPercentage = (percentage) => {
    if (percentage >= 1 && percentage <= 100) {
        selectedPercentage.value = percentage;
        calculatedAmount.value = calculateAmount(percentage);
    }
};

const generateQR = async () => {
    loading.value = true;
    error.value = '';

    console.log('🚀 Iniciando generación de QR para booking:', props.booking.id);
    console.log('💰 Porcentaje seleccionado:', selectedPercentage.value, '%');
    console.log('💵 Monto calculado:', calculatedAmount.value);

    try {
        const response = await axios.post(
            route('bookings.qr.generate', { booking: props.booking.id }),
            {
                percentage: selectedPercentage.value,
                amount: calculatedAmount.value
            }
        );

        console.log('✅ Respuesta del servidor:', response.data);

        if (response.data.success) {
            qrGenerated.value = true;
            qrImageUrl.value = response.data.qr_image;
            paymentNumber.value = response.data.payment_number;
            transactionId.value = response.data.transaction_id;
            testAmount.value = response.data.amount; // Monto de prueba en QR
            realAmount.value = response.data.real_amount; // Monto real calculado
            isPartialPayment.value = response.data.is_partial || false;
            paymentPercentage.value = response.data.percentage || 100;
            isTest.value = response.data.is_test || false;

            console.log('✅ QR generado exitosamente:', {
                qrImage: qrImageUrl.value ? 'Presente' : 'Ausente',
                paymentNumber: paymentNumber.value,
                transactionId: transactionId.value,
                testAmount: testAmount.value,
                realAmount: realAmount.value,
                percentage: paymentPercentage.value,
                isPartial: isPartialPayment.value,
                isTest: isTest.value
            });

            // Iniciar polling para verificar el pago automáticamente
            startPaymentPolling();
        } else {
            console.error('❌ Respuesta sin éxito:', response.data);
            error.value = 'La respuesta del servidor no indica éxito';
        }
    } catch (err) {
        console.error('❌ Error completo:', err);
        console.error('❌ Respuesta del error:', err.response);
        console.error('❌ Datos del error:', err.response?.data);
        console.error('❌ Status del error:', err.response?.status);

        error.value = err.response?.data?.error || err.response?.data?.message || 'Error al generar el código QR';

        // Mostrar el error completo en consola para debugging
        if (err.response?.data) {
            console.error('📋 Detalles completos del error:', JSON.stringify(err.response.data, null, 2));
        }
    } finally {
        loading.value = false;
        console.log('🏁 Generación de QR finalizada');
    }
};

const checkPaymentStatus = async () => {
    checking.value = true;
    error.value = '';

    try {
        const response = await axios.get(
            route('bookings.qr.status', { booking: props.booking.id })
        );

        if (response.data.payment_status === 'paid') {
            // Redirigir a la página de éxito
            router.visit(route('bookings.success', { booking: props.booking.id }));
        }
    } catch (err) {
        error.value = err.response?.data?.error || 'Error al verificar el estado del pago';
        console.error('Error checking payment status:', err);
    } finally {
        checking.value = false;
    }
};

// Polling automático cada 5 segundos
let pollingInterval = null;

const startPaymentPolling = () => {
    pollingInterval = setInterval(async () => {
        try {
            const response = await axios.get(
                route('bookings.qr.status', { booking: props.booking.id })
            );

            if (response.data.payment_status === 'paid') {
                clearInterval(pollingInterval);
                router.visit(route('bookings.success', { booking: props.booking.id }));
            }
        } catch (err) {
            console.error('Polling error:', err);
        }
    }, 5000); // Cada 5 segundos
};

// Limpiar el intervalo cuando se destruye el componente
onUnmounted(() => {
    if (pollingInterval) {
        clearInterval(pollingInterval);
    }
});
</script>

<style scoped>
.qr-container {
    display: flex;
    justify-content: center;
    align-items: center;
}

.payment-option {
    transition: all 0.3s ease;
    cursor: pointer;
}

.payment-option:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.payment-option.border-primary {
    border-width: 2px;
    box-shadow: 0 0 0 3px rgba(32, 107, 196, 0.2);
}

.cursor-pointer {
    cursor: pointer;
}
</style>
