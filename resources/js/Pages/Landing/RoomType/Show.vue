<template>
    <div class="page-title light-background">
        <div class="container d-lg-flex justify-content-between align-items-center">
            <h1 class="mb-2 mb-lg-0">Detalles de la Habitación</h1>
            <nav class="breadcrumbs">
                <ol>
                    <li><Link :href="route('home')">Inicio</Link></li>
                    <li><Link :href="route('roomTypes.index')">Habitaciones</Link></li>
                    <li class="current">Detalles de la Habitación</li>
                </ol>
            </nav>
        </div>
    </div><section id="room-details" class="room-details section">

        <div class="container" data-aos="fade-up" data-aos-delay="100">

            <div class="row align-items-center mb-5">
                <div class="col-lg-7" data-aos="fade-right" data-aos-delay="200">
                    <div class="room-header-image">
                        <img :src="roomType.mainImage[0].url" :alt="roomType.name" class="img-fluid rounded">
                        <div class="room-badge">
                            <span class="text-white">{{ roomType.view }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5" data-aos="fade-left" data-aos-delay="300">
                    <div class="room-header-content">
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
                        <h1 class="room-title">{{ roomType.name}}</h1>
                        <p class="room-tagline" v-html="roomType.short_description"></p>
                        <div class="room-capacity mb-4">
                            <div class="capacity-item">
                                <i class="bi bi-people"></i>
                                <span>Hasta {{roomType.max_total_guests}} huéspedes</span>
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
                        <div class="room-price">
                            <span class="price-amount">{{ money_format(roomType.price) }}</span>
                            <span class="price-period">por noche</span>
                        </div>
                        <Link :href="route('bookings.create', {filters, roomType: roomType.slug})" class="btn btn-book-now">Reservar Ahora</Link>
                    </div>
                </div>
            </div>

            <div class="room-gallery mb-5" v-if="roomType.gallery">
                <h3 class="section-subtitle mb-4" >Galería de la Habitación</h3>
                <div class="gallery-carousel swiper init-swiper" data-aos="fade-up" data-aos-delay="200">
                    <swiper
                        :slides-per-view="1"
                        :space-between="20"
                        loop
                        :autoplay="{'delay': 3000 }"
                        centered-slides
                        :speed="600"
                        :breakpoints="{
                        '576': {
                        'slidesPerView': 2,
                        'centeredSlides': false
                        },
                        '768': {
                        'slidesPerView': 3,
                        'centeredSlides': false
                        },
                        '992': {
                        'slidesPerView': 4,
                        'centeredSlides': false
                        },
                        '1200': {
                        'slidesPerView': 4,
                        'centeredSlides': false
                        }
                    }">
                        <swiper-slide class="swiper-slide" v-for="(gallery, index) in roomType.gallery">
                            <div class="gallery-item">
                                <a :href="gallery.url"
                                   class="gallery-overlay glightbox"
                                   :data-gallery="`room-gallery-${roomType.id}`"
                                   :data-glightbox="`title: Imagen ${index + 1}`">
                                    <img :src="getMediaUrl(gallery, 'set')" :alt="roomType.name" class="img-fluid"
                                         loading="lazy">
                                </a>
                            </div>
                        </swiper-slide>
                    </swiper>
                </div>
            </div>

            <div class="row mb-5">
                <div class="col-lg-8" data-aos="fade-up" data-aos-delay="200">
                    <div class="room-description" v-html="roomType.description">
                    </div>
                </div>
                <div class="col-lg-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="highlight-box">
                        <div class="highlight-icon">
                            <i class="bi bi-star"></i>
                        </div>
                        <h4>Experiencia Premium</h4>
                        <p>"La suite más hermosa en la que nos hemos alojado. La vista al mar es absolutamente impresionante y la atención al detalle es notable."</p>
                        <div class="quote-author">
                            <span>- Sarah M., Huésped Verificada</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="room-amenities mb-5" data-aos="fade-up" data-aos-delay="200">
                <h3 class="section-subtitle mb-4">Servicios de la Habitación</h3>
                <div class="grid gap-5" style="--bs-columns: 3;">
                    <div class="g-col-3 w-full" v-for="facility in roomType.facilities">
                        <i class="bi bi-check2 text-success me-1" style="font-size: 18px" />
                        <span>{{ facility.name }}</span>
                    </div>
                </div>
            </div>

            <div class="room-tabs mb-5" data-aos="fade-up" data-aos-delay="200">
                <ul class="nav nav-tabs" id="room-detailsRoomTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="room-details-policies-tab" data-bs-toggle="tab" data-bs-target="#room-details-policies" type="button" role="tab">Políticas</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="room-details-location-tab" data-bs-toggle="tab" data-bs-target="#room-details-location" type="button" role="tab">Ubicación</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="room-details-services-tab" data-bs-toggle="tab" data-bs-target="#room-details-services" type="button" role="tab">Servicios</button>
                    </li>
                </ul>
                <div class="tab-content" id="room-detailsRoomTabsContent">
                    <div class="tab-pane fade show active" id="room-details-policies" role="tabpanel">
                        <div class="tab-content-wrapper">
                            <div class="row">
                                <div class="col-md-4">
                                    <h6>Entrada / Salida</h6>
                                    <p>Entrada (Check-in): 3:00 PM<br>Salida (Check-out): 11:00 AM</p>
                                </div>
                                <div class="col-md-4">
                                    <h6>Cancelación</h6>
                                    <p>Cancelación gratuita hasta 24 horas antes de la llegada</p>
                                </div>
                                <div class="col-md-4">
                                    <h6>Mascotas</h6>
                                    <p>Se admiten mascotas con cargo adicional</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="room-details-location" role="tabpanel">
                        <div class="tab-content-wrapper">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Atracciones Cercanas</h6>
                                    <ul>
                                        <li>Acceso a la playa - 2 minutos a pie</li>
                                        <li>Distrito Marina - 0.5 millas</li>
                                        <li>Centro Histórico - 1.2 millas</li>
                                        <li>Centro Comercial - 0.8 millas</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h6>Transporte</h6>
                                    <ul>
                                        <li>Servicio de traslado al aeropuerto disponible</li>
                                        <li>Estacionamiento con valet - $25/noche</li>
                                        <li>Transporte público cercano</li>
                                        <li>Escritorio de alquiler de coches en el vestíbulo</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="room-details-services" role="tabpanel">
                        <div class="tab-content-wrapper">
                            <div class="row">
                                <div class="col-md-4">
                                    <h6>Conserjería</h6>
                                    <p>Servicio de conserjería 24/7 para reservas y recomendaciones</p>
                                </div>
                                <div class="col-md-4">
                                    <h6>Servicio de Habitaciones</h6>
                                    <p>Disponible todos los días de 6:00 AM a 11:00 PM</p>
                                </div>
                                <div class="col-md-4">
                                    <h6>Servicio de Limpieza</h6>
                                    <p>Limpieza diaria y servicio de cobertura nocturno</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="booking-cta" data-aos="fade-up" data-aos-delay="200">
                <div class="booking-card">
                    <div class="row align-items-center">
                        <div class="col-lg-8">
                            <h4>¿Listo para reservar su estancia?</h4>
                            <p>Experimente el lujo y la comodidad en nuestra Suite Deluxe con Vista al Mar. Reserve ahora y cree recuerdos inolvidables.</p>
                        </div>
                        <div class="col-lg-4 text-center text-lg-end">
                            <div class="price-display">
                                <span class="price">{{ money_format(roomType.price) }}</span>
                                <span class="period">por noche</span>
                            </div>
                            <Link :href="route('bookings.create', {filters, roomType: roomType.slug})" class="btn btn-primary btn-lg">Consultar Disponibilidad</Link>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </section></template>

<script setup>
import {Swiper, SwiperSlide} from "swiper/vue";
import {getMediaUrl, money_format} from "../../../Utils/helper.js";
import {onMounted, onUpdated} from "vue";
import GLightbox from "glightbox";
import 'glightbox/dist/css/glightbox.min.css'


defineProps({
    roomType: Object,
    filters: Object,
})
let lightbox = null;
onMounted(() => {
    // initialize glightbox once
    lightbox = GLightbox({
        selector: '.glightbox',
        touchNavigation: true,
        loop: true,
        autoplayVideos: false,
    })
})

// if swiper re-renders (e.g. reactive update)
onUpdated(() => {
    lightbox.reload()
})
</script>
