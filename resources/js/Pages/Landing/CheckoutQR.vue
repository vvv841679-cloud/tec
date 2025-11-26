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

                        <!-- Botón para generar QR -->
                        <div v-if="!qrGenerated" class="text-center">
                            <button
                                @click="generateQR"
                                class="btn btn-primary btn-lg"
                                :disabled="loading">
                                <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                                {{ loading ? 'Generando...' : 'Generar Código QR' }}
                            </button>
                        </div>

                        <!-- QR Code generado -->
                        <div v-if="qrGenerated" class="text-center">
                            <div class="alert alert-warning" v-if="isTest">
                                <h5>⚠️ MODO DEMOSTRACIÓN</h5>
                                <p class="mb-1"><strong>Monto de prueba:</strong> Bs. {{ testAmount }}</p>
                                <p class="mb-0"><small>Monto real de la reserva: Bs. {{ booking.total_price }}</small></p>
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
                                <p class="text-muted">Monto a pagar: <strong>Bs. {{ testAmount }}</strong></p>
                                <p class="text-muted" v-if="isTest">
                                    <small class="text-warning">⚠️ Este es un monto de demostración</small>
                                </p>
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
const isTest = ref(false);
const error = ref('');

const generateQR = async () => {
    loading.value = true;
    error.value = '';

    console.log('🚀 Iniciando generación de QR para booking:', props.booking.id);

    try {
        const response = await axios.post(
            route('bookings.qr.generate', { booking: props.booking.id })
        );

        console.log('✅ Respuesta del servidor:', response.data);

        if (response.data.success) {
            qrGenerated.value = true;
            qrImageUrl.value = response.data.qr_image;
            paymentNumber.value = response.data.payment_number;
            transactionId.value = response.data.transaction_id;
            testAmount.value = response.data.amount;
            isTest.value = response.data.is_test || false;

            console.log('✅ QR generado exitosamente:', {
                qrImage: qrImageUrl.value ? 'Presente' : 'Ausente',
                paymentNumber: paymentNumber.value,
                transactionId: transactionId.value,
                testAmount: testAmount.value,
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
</style>
