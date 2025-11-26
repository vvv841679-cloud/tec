<template>
    <div class="page-title light-background">
        <div class="container d-lg-flex justify-content-between align-items-center">
            <h1 class="mb-2 mb-lg-0">Pago</h1>
            <nav class="breadcrumbs">
                <ol>
                    <li>
                        <Link :href="route('home')">Inicio</Link>
                    </li>
                    <li class="current">Pago</li>
                </ol>
            </nav>
        </div>
    </div><section id="booking" class="booking section">

        <div class="container" data-aos="fade-up" data-aos-delay="100">

            <div class="reservation-wrapper">
                <div class="booking-grid d-flex gap-5">

                    <div class="hotel-showcase col-4" data-aos="fade-left" data-aos-delay="400">
                        <div class="card hotel-highlights p-0">
                            <div class="showcase-image rounded-bottom-0 mb-1">
                                <img :src="roomType.mainImage[0].url" alt="Muestra de lujo del hotel" class="img-fluid">
                            </div>
                            <div class="card-body room-details">
                                <Link :href="route('roomTypes.show', {roomType: roomType.slug})"><h3
                                    class="text-lg-start">{{ roomType.name }}</h3></Link>
                                <div class="room-capacity mb-4">
                                    <div class="capacity-item">
                                        <i class="bi bi-people"></i>
                                        <span>Hasta {{ roomType.max_total_guests }} huéspedes</span>
                                    </div>
                                    <div class="capacity-item">
                                        <i class="bi bi-grid"></i>
                                        <span>{{ roomType.size }} m²</span>
                                    </div>
                                    <div class="capacity-item">
                                        <i class="bi bi-house"></i>
                                        <span v-text="roomType.bedTypes.map(rt => rt.name).join(' + ')"></span>
                                    </div>
                                </div>
                                <div class="room-rating mb-3">
                                    <span class="rating-score">4.8</span>
                                    <div class="stars">
                                        <i class="bi bi-star-fill"></i>
                                        <i class="bi bi-star-fill"></i>
                                        <i class="bi bi-star-fill"></i>
                                        <i class="bi bi-star-fill"></i>
                                        <i class="bi bi-star-fill"></i>
                                    </div>
                                    <span class="reviews-count">(127 reseñas)</span>
                                </div>
                            </div>
                        </div>

                        <div class="hotel-highlights">
                            <h3 class="text-lg-start m-0">Detalles de su reserva</h3>
                            <div class="d-flex gap-4 mt-4 pb-3"
                                 style="border-bottom: 1px solid color-mix(in srgb, var(--default-color), transparent 90%);">
                                <div class="d-flex flex-column gap-2" style="margin-right: 5px">
                                    <span>Entrada (Check-in)</span>
                                    <span class="bold text-black"
                                          style="font-weight: 700">{{
                                            moment(booking.check_in).format('ddd, MMM D, Y')
                                        }}</span>
                                    <span class="text-secondary" style="font-size: 14px">4:00 PM – 11:00 PM</span>
                                </div>
                                <div class="d-flex flex-column gap-2"
                                     style="border-left: 1px solid color-mix(in srgb, var(--default-color), transparent 90%); padding-left: 2rem">
                                    <span>Salida (Check-out)</span>
                                    <span class="bold text-black"
                                          style="font-weight: 700">{{
                                            moment(booking.check_out).format('ddd, MMM D, Y')
                                        }}</span>
                                    <span class="text-secondary" style="font-size: 14px">4:00 PM – 11:00 PM</span>
                                </div>
                            </div>
                            <div class="mt-4">
                                <div class="mb-1">Usted ha seleccionado</div>
                                <div class="bold text-black" style="font-weight: 700">
                                    {{ diffDays(booking.check_out, booking.check_in) }} noche(s), {{ booking.rooms_count }} habitación(es) para
                                    {{ booking.adults }} adulto(s){{ booking.children > 0 ? `, ${booking.children} niño(s)` : '' }}
                                </div>
                            </div>
                        </div>

                        <div class="hotel-highlights">
                            <h3 class="text-lg-start m-0">Resumen de precios</h3>
                            <div class="d-flex flex-column gap-4 mt-4">
                                <div class="d-flex justify-content-between" v-for="charge in booking.charges">
                                    <span>
                                        <i :class="`bi ${displayCharge(charge.charge_type).bgClass} me-2`"></i>
                                        {{ displayCharge(charge.charge_type).label }}
                                    </span>
                                    <span>{{ money_format(charge.amount) }}</span>
                                </div>

                                <div class="d-flex justify-content-between">
                                    <span class="bold text-black" style="font-weight: 700;"><i
                                        class="bi bi-wallet2 me-2"></i>Precio Total</span>
                                    <span class="bold text-black" style="font-weight: 700;">{{
                                            money_format(booking.total_price)
                                        }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="booking-guarantees">
                            <div class="guarantee-item">
                                <i class="bi bi-shield-check"></i>
                                <span>Reserva Segura</span>
                            </div>
                            <div class="guarantee-item">
                                <i class="bi bi-arrow-clockwise"></i>
                                <span>Cancelación Flexible</span>
                            </div>
                            <div class="guarantee-item">
                                <i class="bi bi-telephone"></i>
                                <span>Soporte 24/7</span>
                            </div>
                        </div>
                    </div>

                    <div class="booking-form-section col-8" data-aos="fade-right" data-aos-delay="300">
                        <div class="alert alert-danger" v-if="errorMessage" v-text="errorMessage"></div>
                        <div class="form-container">
                            <form class="reservation-form" method="POST" @submit.prevent="handlePay">
                                <div class="form-section">
                                    <h4>Pagar en línea</h4>
                                    <div class="form-grid">
                                        <div class="form-group full-width">
                                            <label for="primary-guest" class="form-label">Nombre del Titular de la Tarjeta</label>
                                            <input type="text" class="form-control" id="primary-guest"
                                                   :value="customer.full_name"
                                                   name="primary_guest" required="" disabled>
                                        </div>
                                        <div class="form-group full-width">
                                            <label for="card-number" class="form-label">Número de Tarjeta</label>
                                            <div id="card-number" class="form-control"></div>
                                        </div>

                                        <div class="form-group">
                                            <label for="card-expiry" class="form-label">Fecha de Caducidad</label>
                                            <div id="card-expiry" class="form-control"></div>
                                        </div>

                                        <div class="form-group">
                                            <label for="card-cvc" class="form-label">CVC</label>
                                            <div id="card-cvc" class="form-control"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary" :disabled="processing">
                                        <i class="bi bi-wallet2 me-2"></i>
                                        Pagar Reserva
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </section></template>

<script setup>
    import {ref, onMounted} from 'vue'
    import {loadStripe} from '@stripe/stripe-js'
    import {diffDays, money_format} from "../../Utils/helper.js";
    import moment from "moment";
    import {router, usePage} from "@inertiajs/vue3";
    import {useEnum} from "../../Composables/useEnum.js";

    const processing = ref(false)
    const errorMessage = ref(null)

    let stripe = null
    let cardNumber = null

    const {props: {auth: {customer}}} = usePage();
    const {stripeKey, charges, booking} = defineProps({
        stripeKey: String,
        roomType: Object,
        booking: Object,
        charges: Array,
    });

    const {display: displayCharge} = useEnum(charges)

    onMounted(async () => {
        // Inicialización de Stripe Elements
        stripe = await loadStripe(stripeKey)
        const elements = stripe.elements()

        cardNumber = elements.create('cardNumber');
        cardNumber.mount('#card-number');

        const cardExpiry = elements.create('cardExpiry');
        cardExpiry.mount('#card-expiry');

        const cardCvc = elements.create('cardCvc');
        cardCvc.mount('#card-cvc');
    })

    async function handlePay() {
        processing.value = true
        errorMessage.value = null

        try {
            // 1. Obtener el Client Secret del backend
            const {data} = await axios.post(route('bookings.payments.store', booking.id))
            const clientSecret = data.client_secret

            // 2. Confirmar el pago con Stripe
            const result = await stripe.confirmCardPayment(clientSecret, { payment_method: { card: cardNumber } })

            if (result.error) {
                // Mostrar errores de tarjeta al usuario
                errorMessage.value = result.error.message
            } else {
                if (result.paymentIntent && result.paymentIntent.status === 'succeeded') {
                    // 3. Pago exitoso, notificar al backend
                    router.visit(route('payments.confirm'), {
                        method: 'post',
                        data: { payment_intent: result.paymentIntent.id },
                    })
                }
            }
        } catch (e) {
            // Mostrar errores de la API (ej. error de servidor al crear PaymentIntent)
            errorMessage.value = e.response?.data?.message || e.message
        } finally {
            processing.value = false
        }
    }
</script>

<style scoped>
.form-control.StripeElement {
    color: var(--default-color);
    background-color: var(--surface-color);
    border: 1px solid color-mix(in srgb, var(--default-color), transparent 80%);
    border-radius: 4px;
    padding: 1.05rem 1rem;
    font-size: 0.95rem;
    transition: all 0.3s ease;
    width: 100%;
}
</style>
