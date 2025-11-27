<template>
    <div class="page-title light-background">
        <div class="container d-lg-flex justify-content-between align-items-center">
            <h1 class="mb-2 mb-lg-0">Pago Exitoso</h1>
            <nav class="breadcrumbs">
                <ol>
                    <li>
                        <Link :href="route('home')">Inicio</Link>
                    </li>
                    <li class="current">Pago Exitoso</li>
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
                                        class="text-lg-start">{{ roomType.name }}l</h3></Link>
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
                            <h3 class="text-lg-start m-0">Detalles de tu reserva</h3>
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
                                <div class="mb-1">Has seleccionado</div>
                                <div class="bold text-black" style="font-weight: 700">
                                    {{ diffDays(booking.check_out, booking.check_in) }} noches, {{ booking.rooms_count }} habitación para
                                    {{ booking.adults }} adultos{{ booking.children > 0 ? `, ${booking.children} niños` : '' }}
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
                        <div class="form-container">
                            <div class="form-section fs-6">
                                <h4>Detalles de la Reserva</h4>
                                <div class="row mt-4">
                                    <div class="col">
                                        <div class="fs-7 bold">Nº de Referencia</div>
                                        <div class="mt-1 fs-6">{{booking.ref_number}}</div>
                                    </div>
                                    <div class="col">
                                        <div class="fs-7 text-secondary">Nombre del Huésped</div>
                                        <div class="fs-6 mt-1">{{booking.customer.full_name}}</div>
                                    </div>
                                    <div class="col">
                                        <div class="fs-7 text-secondary">Régimen de Comida</div>
                                        <div class="fs-6 mt-1">{{booking.mealPlan.name}}</div>
                                    </div>
                                </div>
                                <div class="row mt-4">
                                    <div class="col">
                                        <div class="fs-7 text-secondary">Estado</div>
                                        <div class="mt-1 fs-6">
                                            <span class="badge" :class="displayStatus(booking.status).bgClass">
                                                {{ displayStatus(booking.status).label }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="fs-7 text-secondary">Estado de Pago</div>
                                        <div class="fs-6 mt-1">
                                            <span class="badge" :class="displayPaymentStatus(booking.payment_status).bgClass">
                                                {{ displayPaymentStatus(booking.payment_status).label }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="fs-7 text-secondary">Monto del Depósito</div>
                                        <div class="mt-1 fs-6">{{ money_format(booking.deposit_amount) }}</div>
                                    </div>
                                </div>
                                <div class="row mt-4">
                                    <div class="col">
                                        <div class="fs-7 text-secondary">Preferencia de Fumar</div>
                                        <div class="fs-6 mt-1">
                                            <span class="badge" :class="displaySmoking(booking.smoking_preference).bgClass">
                                                {{ displaySmoking(booking.smoking_preference).label }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="fs-7 text-secondary">Creado el</div>
                                        <div class="fs-6 mt-1">{{booking.created_at}}</div>
                                    </div>
                                    <div class="col">

                                    </div>
                                </div>
                                <div class="row mt-4">
                                    <div class="col">
                                        <div class="fs-7 text-secondary">Peticiones Especiales</div>
                                        <div class="fs-6 mt-1">{{booking.special_requests ?? '-'}}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-section  mt-5">
                                <h4>Detalles del Pago</h4>
                                <table class="table table-vcenter card-table table-striped mt-4">
                                    <thead>
                                    <tr>
                                        <th>Monto</th>
                                        <th>Tipo</th>
                                        <th>Método</th>
                                        <th>Estado</th>
                                        <th>Pagado el</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr v-for="payment in booking.payments">
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
                                            <span class="badge" :class="displayPayStatus(payment.status).bgClass">
                                                {{ displayPayStatus(payment.status).label }}
                                            </span>
                                        </td>
                                        <td>{{ payment.paid_at }}</td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="form-section  mt-5">
                                <h4>Detalles de las Habitaciones</h4>
                                <table class="table table-vcenter card-table table-striped mt-4">
                                    <thead>
                                    <tr>
                                        <th>Número de Habitación</th>
                                        <th>Piso</th>
                                        <th>Preferencia de Fumar</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr v-for="room in booking.rooms">
                                        <td>{{ room.room_number }}</td>
                                        <td>{{ room.floor_number }}</td>
                                        <td>
                                            <span class="badge" :class="displaySmoking(room.smoking_preference).bgClass">
                                                {{ displaySmoking(room.smoking_preference).label }}
                                            </span>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </section></template>

<script setup>
import {diffDays, money_format} from "../../Utils/helper.js";
import moment from "moment";
import {usePage} from "@inertiajs/vue3";
import {useEnum} from "../../Composables/useEnum.js";


const {props: {auth: {customer}}} = usePage();
const props = defineProps({
    roomType: Object,
    booking: Object,
    charges: Array,
    statuses: Array,
    bookingPayments: Array,
    smokings: Array,
    types: Array,
    methods: Array,
    paymentStatuses: Array,
});

const {display: displayCharge} = useEnum(props.charges)
const {display: displayStatus} = useEnum(props.statuses)
const {display: displayPaymentStatus} = useEnum(props.bookingPayments)
const {display: displaySmoking} = useEnum(props.smokings)
const {display: displayType} = useEnum(props.types)
const {display: displayMethod} = useEnum(props.methods)
const {display: displayPayStatus} = useEnum(props.paymentStatuses)


</script>

<style scoped>
</style>
