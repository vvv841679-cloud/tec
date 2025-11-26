<template>
    <section id="hotel-hero" class="hotel-hero section hotel-hero-bg">
        <div class="hero-content w-full">
            <div class="container">
                <div class="row justify-content-center text-center">
                    <div class="col-lg-8">
                        <div class="hero-text aos-init aos-animate" data-aos="fade-up" data-aos-delay="100">
                            <h1 class="text-white">Lujo Redefinido</h1>
                            <p class="hero-subtitle text-white">Experimente un confort y una elegancia inigualables en el
                                corazón de la ciudad. Donde cada momento se convierte en un recuerdo preciado.</p>
                            <div class="hero-actions aos-init aos-animate" data-aos="fade-up" data-aos-delay="200">
                                <Link :href="route('roomTypes.index')" class="btn btn-origin p-5 py-3 mx-3" style="border-radius: 50px !important;">Reservar
                                    Su Estancia</Link>
                                <a href="amenities.html" class="btn btn-outline-light p-5 py-3" style="border-radius: 50px">Explorar
                                    Servicios</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row justify-content-center mt-5">
                    <div class="col-lg-10">
                        <div class="booking-card aos-init aos-animate" data-aos="fade-up" data-aos-delay="300">
                            <form class="booking-form php-email-form" @submit.prevent="submitForm" method="get">
                                <div class="row align-items-end g-3">
                                    <div class="col-md-3">
                                        <label for="checkin" class="form-label">Entrada (Check-in)</label>
                                        <input type="date" v-model="form.check_in" class="form-control" id="checkin" :min="currentDate()"
                                            required="">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="checkout" class="form-label">Salida (Check-out)</label>
                                        <input type="date" v-model="form.check_out" class="form-control" id="checkout" :min="addDays(Date.now(), 1)"
                                            required="">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="guests" class="form-label">Huéspedes</label>
                                        <div class="border bg-white rounded px-4 position-relative cursor-pointer" style="padding: 0.75rem;">
                                            <div class="d-flex justify-content-between flex-grow-1" @click="showGuests = !showGuests">
                                                <i class="bi bi-people-fill"
                                                    style="font-size: 1rem;font-weight: 400;line-height: 1.5;color: #6e7174;">
                                                </i>
                                                <div>
                                                    {{form.adults}} adultos
                                                </div>
                                                <div>
                                                    {{form.children}} niños
                                                </div>
                                                <div>
                                                    {{form.rooms}} habitación
                                                </div>
                                                <i class="bi bi-chevron-down mt-1" style="font-size: 13px;font-weight: 700;line-height: 1.2;"></i>
                                            </div>
                                            <div class="border shadow position-absolute w-full bg-white rounded p-4 d-flex flex-column gap-3"
                                                style="left: 0; top: 53px; z-index: 100;" v-if="showGuests">
                                                <div>
                                                    <label for="adults" class="form-label">Adultos</label>
                                                    <input type="number" min="1" v-model="form.adults" class="form-control" id="adults" required="">
                                                </div>
                                                <div>
                                                    <label for="children" class="form-label">Niños</label>
                                                    <input type="number" min="0" v-model="form.children" class="form-control" id="children" required="">
                                                </div>
                                                <div>
                                                    <label for="room" class="form-label">Habitación</label>
                                                    <input type="number" min="1" v-model="form.rooms" class="form-control" id="room" required="">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-origin">
                                            <i class="bi bi-search"></i>
                                            <span class="mx-2">Buscar</span>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="row justify-content-center mt-4">
                    <div class="col-lg-10">
                        <div class="hotel-highlights aos-init aos-animate" data-aos="fade-up" data-aos-delay="400">
                            <div class="row text-center">
                                <div class="col-6 col-md-3">
                                    <div class="highlight-item">
                                        <i class="bi bi-wifi"></i>
                                        <div>WiFi Gratuito</div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="highlight-item">
                                        <i class="bi bi-p-circle"></i>
                                        <div>Estacionamiento Gratuito</div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="highlight-item">
                                        <i class="bi bi-cup-hot"></i>
                                        <div>Servicio de Habitaciones</div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="highlight-item">
                                        <i class="bi bi-water"></i>
                                        <div>Piscina</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </section><section id="about-home" class="about-home section light-background">

        <div class="container" data-aos="fade-up" data-aos-delay="100">

            <div class="row gy-4 align-items-center">
                </div>

        </div>

    </section><section id="rooms-showcase" class="rooms-showcase section">

        <div class="container section-title" data-aos="fade-up">
            <span class="description-title">Habitaciones</span>
            <h2>Habitaciones</h2>
            <p>Necessitatibus eius consequatur ex aliquid fuga eum quidem sint consectetur velit</p>
        </div><div class="container" data-aos="fade-up" data-aos-delay="100">
            <div class="row g-3">
                <a class="col-lg-3 col-sm-6 d-inline-block" :href="route('roomTypes.show', roomType.slug)" v-for="roomType in roomTypes">
                    <div class="minimal-room-card">
                        <div class="room-image">
                            <img :src="roomType.mainImage[0]?.url" alt="Habitación Clásica" class="img-fluid">
                            <div class="hover-overlay">
                                <a href="room-details.html" class="view-room">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </div>
                        </div>
                        <div class="room-summary">
                            <h5>{{ roomType.name}}</h5>
                            <div class="price-tag">{{ money_format(roomType.price) }}<span>/noche</span></div>
                            <div class="basic-amenities">
                                <i class="bi bi-wifi"></i>
                                <i class="bi bi-tv"></i>
                                <i class="bi bi-telephone"></i>
                            </div>
                        </div>
                    </div>
                </a></div>
            <div class="text-center" data-aos="fade-up" data-aos-delay="600">
                <Link :href="route('roomTypes.index')" class="explore-all-link">
                    <span>Explorar Todos los Alojamientos</span>
                    <i class="bi bi-arrow-right"></i>
                </Link>
            </div>

        </div>

    </section><section id="amenities-cards" class="amenities-cards section light-background">

        <div class="container section-title" data-aos="fade-up">
            <span class="description-title">Servicios</span>
            <h2>Servicios</h2>
            <p>Necessitatibus eius consequatur ex aliquid fuga eum quidem sint consectetur velit</p>
        </div><div class="container" data-aos="fade-up" data-aos-delay="100">

            <div class="row g-5">

                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                    <div class="facility-card">
                        <div class="facility-image">
                            <img src="/resources/images/showcase-8.webp" alt="Internet Inalámbrico" class="img-fluid">
                            <div class="facility-overlay">
                                <i class="bi bi-wifi"></i>
                            </div>
                        </div>
                        <div class="facility-info">
                            <h4>Internet de Alta Velocidad</h4>
                            <p>Acceso a internet inalámbrico de cortesía en todas las instalaciones del hotel con
                                seguridad de nivel empresarial y ancho de banda ilimitado para todas sus necesidades de conectividad.</p>
                            <div class="facility-features">
                                <span><i class="bi bi-check-circle"></i> Disponible 24/7</span>
                                <span><i class="bi bi-check-circle"></i> Alta Velocidad</span>
                            </div>
                        </div>
                    </div>
                </div><div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="150">
                    <div class="facility-card">
                        <div class="facility-image">
                            <img src="/resources/images/showcase-8.webp" alt="Piscina" class="img-fluid">
                            <div class="facility-overlay">
                                <i class="bi bi-droplet"></i>
                            </div>
                        </div>
                        <div class="facility-info">
                            <h4>Piscina en la Azotea</h4>
                            <p>Lujosa piscina en la azotea con impresionantes vistas del horizonte de la ciudad. Cuenta con
                                agua climatizada, servicio junto a la piscina y áreas de descanso premium para la máxima relajación.</p>
                            <div class="facility-features">
                                <span><i class="bi bi-check-circle"></i> Piscina Climatizada</span>
                                <span><i class="bi bi-check-circle"></i> Vistas a la Ciudad</span>
                            </div>
                        </div>
                    </div>
                </div><div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="facility-card">
                        <div class="facility-image">
                            <img src="/resources/images/showcase-8.webp" alt="Estacionamiento con Valet" class="img-fluid">
                            <div class="facility-overlay">
                                <i class="bi bi-car-front"></i>
                            </div>
                        </div>
                        <div class="facility-info">
                            <h4>Estacionamiento con Valet</h4>
                            <p>Servicio de estacionamiento con valet premium con una instalación subterránea segura.
                                Los asistentes profesionales se aseguran de que su vehículo esté estacionado de forma segura y disponible bajo petición.</p>
                            <div class="facility-features">
                                <span><i class="bi bi-check-circle"></i> Seguro</span>
                                <span><i class="bi bi-check-circle"></i> Servicio de Valet</span>
                            </div>
                        </div>
                    </div>
                </div><div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="250">
                    <div class="facility-card">
                        <div class="facility-image">
                            <img src="/resources/images/showcase-8.webp" alt="Gimnasio" class="img-fluid">
                            <div class="facility-overlay">
                                <i class="bi bi-heart-pulse"></i>
                            </div>
                        </div>
                        <div class="facility-info">
                            <h4>Moderno Gimnasio</h4>
                            <p>Instalación de fitness de vanguardia con equipos premium, servicios de entrenamiento personal,
                                y programas de bienestar diseñados para mantener su rutina de salud mientras viaja.</p>
                            <div class="facility-features">
                                <span><i class="bi bi-check-circle"></i> Acceso 24/7</span>
                                <span><i class="bi bi-check-circle"></i> Entrenamiento Personal</span>
                            </div>
                        </div>
                    </div>
                </div><div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
                    <div class="facility-card">
                        <div class="facility-image">
                            <img src="/resources/images/showcase-8.webp" alt="Cena de Lujo" class="img-fluid">
                            <div class="facility-overlay">
                                <i class="bi bi-cup-hot"></i>
                            </div>
                        </div>
                        <div class="facility-info">
                            <h4>Restaurante de Autor</h4>
                            <p>Experiencia culinaria galardonada con cocina internacional elaborada por reconocidos
                                chefs. Ambiente elegante con una extensa selección de vinos y un servicio impecable.</p>
                            <div class="facility-features">
                                <span><i class="bi bi-check-circle"></i> Cena de Lujo</span>
                                <span><i class="bi bi-check-circle"></i> Selección de Vinos</span>
                            </div>
                        </div>
                    </div>
                </div><div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="350">
                    <div class="facility-card">
                        <div class="facility-image">
                            <img src="/resources/images/showcase-8.webp" alt="Servicios de Spa" class="img-fluid">
                            <div class="facility-overlay">
                                <i class="bi bi-flower1"></i>
                            </div>
                        </div>
                        <div class="facility-info">
                            <h4>Spa de Lujo</h4>
                            <p>Santuario tranquilo que ofrece tratamientos terapéuticos, masajes rejuvenecedores y experiencias
                                de bienestar holístico. Escape del estrés diario en nuestro sereno entorno.</p>
                            <div class="facility-features">
                                <span><i class="bi bi-check-circle"></i> Servicio Completo</span>
                                <span><i class="bi bi-check-circle"></i> Programas de Bienestar</span>
                            </div>
                        </div>
                    </div>
                </div></div>

        </div>

    </section></template>

<script setup>
import {Swiper, SwiperSlide} from 'swiper/vue';
import 'swiper/css';
import {ref} from "vue";
import {useForm} from "@inertiajs/vue3";
import {addDays, currentDate, money_format} from "../../Utils/helper.js";

defineProps({
    'roomTypes': Array
})

const showGuests = ref(false)

const  form = useForm({
    'check_in': '',
    'check_out': '',
    'adults': 2,
    'children': 0,
    'rooms': 1,
})

const submitForm = () => {
    form.transform(data => ({
        'filters': data
    })).get(route('roomTypes.index'))
}

</script>

<style scoped>
.form-control {
    padding: 0.75rem;
}

.btn-origin {
    padding: 0.75rem
}
/* Añadido z-index para asegurar que el desplegable de huéspedes esté por encima de otros elementos */
.booking-card .position-relative {
    z-index: 100;
}
</style>
