<template>
    <footer id="footer" class="footer position-relative dark-background">

        <div class="footer-top">
            <div class="container">
                <div class="row gy-4">
                    <div class="col-lg-4 col-md-6 footer-about">
                        <a href="index.html" class="logo d-flex align-items-center">
                            <span class="sitename">Hotel  LA PASCANA</span>
                        </a>
                        <div class="footer-contact pt-3">

                        </div>
                    </div>

                    <div class="col-lg-2 col-md-3 footer-links">
                        <h4>Enlaces Útiles</h4>
                        <ul>
                            <li><a href="#">Inicio</a></li>
                            <li><a href="#">Acerca de</a></li>
                            <li><a href="#">Servicios</a></li>
                            <li><a href="#">Términos de servicio</a></li>
                            <li><a href="#">Política de privacidad</a></li>
                        </ul>
                    </div>

                    <div class="col-lg-2 col-md-3 footer-links">
                        <h4>Nuestros Servicios</h4>
                        <ul>
                            <li><a href="#">Alojamiento</a></li>
                            <li><a href="#">Restaurante</a></li>
                            <li><a href="#">Spa y Bienestar</a></li>
                            <li><a href="#">Eventos</a></li>
                            <li><a href="#">Tours</a></li>
                        </ul>
                    </div>

                    <div class="col-lg-2 col-md-3 footer-links">
                        <h4>Instalaciones</h4>
                        <ul>
                            <li><a href="#">Piscina</a></li>
                            <li><a href="#">Gimnasio</a></li>
                            <li><a href="#">WiFi Gratuito</a></li>
                            <li><a href="#">Estacionamiento</a></li>
                            <li><a href="#">Sala de Conferencias</a></li>
                        </ul>
                    </div>

                    <div class="col-lg-2 col-md-3 footer-links">
                        <h4>Contáctanos</h4>
                        <ul>
                            <li><a href="#">Reservaciones</a></li>
                            <li><a href="#">Atención al cliente</a></li>
                            <li><a href="#">Ubicación</a></li>
                            <li><a href="#">FAQ</a></li>
                            <li><a href="#">Comentarios</a></li>
                        </ul>
                    </div>

                </div>
            </div>
        </div>

        <div class="copyright text-center">
            <div class="container d-flex flex-column flex-lg-row justify-content-center justify-content-lg-between align-items-center">

                <div class="d-flex flex-column align-items-center align-items-lg-start">
                    <div class="mb-2">
                        <span class="text-white">
                            <i class="bi bi-eye"></i> Visitas a esta página: <strong>{{ $page.props.pageViews }}</strong>
                        </span>
                    </div>
                    <div class="credits">
                        <!-- All the links in the footer should remain intact. -->
                        <!-- You can delete the links only if you purchased the pro version. -->
                        <!-- Licensing information: https://bootstrapmade.com/license/ -->
                        <!-- Purchase the pro version with working PHP/AJAX contact form: https://bootstrapmade.com/herobiz-bootstrap-business-template/ -->
                        Designed by <a class="text-white" href="https://bootstrapmade.com/">BootstrapMade</a>
                        <span class="mx-2 text-white-50">|</span>
                        <Link :href="route('admin.loginForm')" class="text-white-50" style="font-size: 0.85rem; text-decoration: none;">
                            <i class="bi bi-shield-lock me-1"></i>Panel de Administración
                        </Link>
                    </div>
                </div>

                <div class="social-links order-first order-lg-last mb-3 mb-lg-0">
                    <a href=""><i class="bi bi-twitter-x"></i></a>
                    <a href=""><i class="bi bi-facebook"></i></a>
                    <a href=""><i class="bi bi-instagram"></i></a>
                    <a href=""><i class="bi bi-linkedin"></i></a>
                </div>

            </div>
        </div>

    </footer>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
</script>
