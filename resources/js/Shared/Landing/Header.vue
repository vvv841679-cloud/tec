<template>
    <header id="header" class="header sticky-top">

        <div class="branding d-flex align-items-cente">

            <div class="container position-relative d-flex align-items-center justify-content-between">
                <Link :href="route('home')">
                    <h1 class="sitename d-flex align-items-center gap-1 my-1">
                        <img src="/resources/images/Homa.png" alt="logo"
                            style="width: 40px; height: 40px; filter: brightness(0) invert(1)"/>
                        <span class="px-1"> LA PASCANA</span>
                    </h1>
                </Link>

                <nav id="navmenu" class="navmenu">
                    <ul>
                        <li><Link :href="route('home')" :class="{'active': $page.url === '/'}">Inicio</Link></li>
                        <li><Link :href="route('roomTypes.index')" :class="{'active': $page.url === route('roomTypes.index')}">Habitaciones</Link></li>


                        <li v-if="!customer"><Link :href="route('login')">Iniciar Sesión</Link></li>
                        <li v-if="!customer"><Link :href="route('register')">Registrarse</Link></li>
                        <li class="dropdown" v-if="customer">
                            <a href="#profile">
                                <div class="rounded-5 me-2 mx-1"
                                    style="background: #ffb700; round: 100%; overflow: hidden">
                                    <img width="30" height="30" :src="getMediaUrl(customer.avatar[0], 'thumb')" alt="avatar"/>
                                </div>
                                <span class="me-1">{{ customer.full_name }}</span>
                                <i class="bi bi-chevron-down toggle-dropdown"></i>
                            </a>
                            <ul>
                                <li>
                                    <Link :href="route('customer.dashboard')" class="d-inline-block">
                                        <i class="bi bi-person me-2" style="font-size: 18px"></i>
                                        <span>Mi Cuenta</span>
                                    </Link>
                                </li>
                                <li>
                                    <a href="" @click.prevent="logoutHandle" class="d-inline-block">
                                        <i class="bi bi-box-arrow-left me-2" style="font-size: 18px"></i>
                                        <span>Cerrar Sesión</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                    <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
                </nav>

            </div>

        </div>

    </header>
</template>


<script setup>
import {router, usePage} from "@inertiajs/vue3";
import {getMediaUrl} from "../../Utils/helper.js";

const {props: {auth: {customer}}} = usePage();

const logoutHandle = () => {
    router.delete(route('logout'), {
        onSuccess: () => {
            window.location.reload();
        }
    })
}
</script>
