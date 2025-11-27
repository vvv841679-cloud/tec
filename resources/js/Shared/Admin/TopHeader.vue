<template>
    <header class="navbar navbar-expand-md d-print-none">
        <div class="container-xl">
            <!-- BEGIN NAVBAR TOGGLER -->
            <button
                class="navbar-toggler"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#navbar-menu"
                aria-controls="navbar-menu"
                aria-expanded="false"
                aria-label="Toggle navigation"
            >
                <span class="navbar-toggler-icon"></span>
            </button>
            <!-- END NAVBAR TOGGLER -->
            <!-- BEGIN NAVBAR LOGO -->
            <div class="navbar-brand navbar-brand-autodark d-none-navbar-horizontal pe-0 pe-md-3">
                <Link :href="route('admin.dashboard')">
                    <img width="38" src="/resources/images/Homa.png" alt="logo"/> <span
                    class="bold h4">Hotel  LA PASCANA</span>
                </Link>
            </div>
            <!-- BEGIN SEARCH BAR -->
            <div class="d-none d-md-flex flex-fill justify-content-center px-3">
                <SearchBar />
            </div>
            <!-- END SEARCH BAR -->
            <Settings />
        </div>
    </header>
</template>

<script setup>

import Settings from "./Settings.vue";
import SearchBar from "./SearchBar.vue";
</script>
