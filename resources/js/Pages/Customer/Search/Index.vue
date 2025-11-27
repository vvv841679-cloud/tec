<template>
    <Head title="Buscar" />
    <Layout>
        <div class="search-page">
            <div class="container py-5">
                <!-- Encabezado de búsqueda -->
                <div class="search-header mb-4">
                    <h1 class="h2 mb-3">Resultados de Búsqueda</h1>
                    <div class="search-input-container">
                        <form @submit.prevent="performSearch" class="d-flex gap-2">
                            <div class="input-group flex-grow-1">
                                <span class="input-group-text bg-white">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input
                                    v-model="searchQuery"
                                    type="text"
                                    class="form-control"
                                    placeholder="Buscar habitaciones, reservas, páginas..."
                                    autofocus
                                />
                            </div>
                            <button type="submit" class="btn btn-primary px-4">
                                Buscar
                            </button>
                        </form>
                    </div>
                    <div v-if="query" class="mt-3">
                        <p class="text-muted mb-0">
                            Mostrando {{ totalResults }} resultados para
                            <strong>"{{ query }}"</strong>
                        </p>
                    </div>

                    <!-- Enlaces rápidos -->
                    <div v-if="quickLinks && quickLinks.length > 0" class="quick-links mt-3">
                        <div class="d-flex gap-2 flex-wrap">
                            <span class="text-muted small me-2">Acceso rápido:</span>
                            <Link
                                v-for="(link, index) in quickLinks"
                                :key="index"
                                :href="link.url"
                                class="btn btn-sm btn-outline-primary d-flex align-items-center gap-1"
                            >
                                <i :class="link.icon"></i>
                                {{ link.label }}
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Sin resultados -->
                <div v-if="query && totalResults === 0" class="text-center py-5">
                    <i class="bi bi-search fs-1 text-muted mb-3 d-block"></i>
                    <h3 class="h4 mb-2">No se encontraron resultados</h3>
                    <p class="text-muted">
                        Intenta con otros términos de búsqueda o verifica la ortografía
                    </p>
                </div>

                <!-- Estado inicial -->
                <div v-else-if="!query" class="text-center py-5">
                    <i class="bi bi-search fs-1 text-muted mb-3 d-block"></i>
                    <h3 class="h4 mb-2">¿Qué estás buscando?</h3>
                    <p class="text-muted">
                        Busca habitaciones, reservas, páginas y más...
                    </p>
                </div>

                <!-- Resultados -->
                <div v-else class="search-results">
                    <!-- Tipos de habitaciones -->
                    <div v-if="results.roomTypes.length > 0" class="result-section mb-5">
                        <h2 class="h4 mb-3 d-flex align-items-center">
                            <i class="bi bi-door-open me-2 text-primary"></i>
                            Tipos de Habitaciones
                            <span class="badge bg-primary ms-2">{{ results.roomTypes.length }}</span>
                        </h2>
                        <div class="row g-3">
                            <div
                                v-for="item in results.roomTypes"
                                :key="item.id"
                                class="col-md-6 col-lg-4"
                            >
                                <Link :href="item.url" class="text-decoration-none">
                                    <div class="card h-100 result-card">
                                        <img
                                            v-if="item.image"
                                            :src="item.image"
                                            class="card-img-top"
                                            :alt="item.title"
                                            style="height: 200px; object-fit: cover;"
                                        />
                                        <div class="card-body">
                                            <h5 class="card-title">{{ item.title }}</h5>
                                            <p class="card-text text-muted small mb-2">
                                                {{ item.subtitle }}
                                            </p>
                                            <p class="card-text" style="font-size: 0.9rem;">
                                                {{ truncate(item.description, 100) }}
                                            </p>
                                        </div>
                                    </div>
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Habitaciones -->
                    <div v-if="results.rooms.length > 0" class="result-section mb-5">
                        <h2 class="h4 mb-3 d-flex align-items-center">
                            <i class="bi bi-key me-2 text-success"></i>
                            Habitaciones
                            <span class="badge bg-success ms-2">{{ results.rooms.length }}</span>
                        </h2>
                        <div class="row g-3">
                            <div
                                v-for="item in results.rooms"
                                :key="item.id"
                                class="col-md-6"
                            >
                                <Link :href="item.url" class="text-decoration-none">
                                    <div class="card result-card">
                                        <div class="card-body d-flex align-items-center">
                                            <div
                                                v-if="item.image"
                                                class="result-image me-3"
                                                :style="{backgroundImage: `url(${item.image})`}"
                                            ></div>
                                            <div class="flex-grow-1">
                                                <h5 class="card-title mb-1">{{ item.title }}</h5>
                                                <p class="card-text text-muted small mb-1">
                                                    {{ item.description }}
                                                </p>
                                                <p class="card-text text-muted small mb-0">
                                                    {{ item.subtitle }}
                                                </p>
                                            </div>
                                            <i class="bi bi-chevron-right text-muted"></i>
                                        </div>
                                    </div>
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Reservas -->
                    <div v-if="results.bookings.length > 0" class="result-section mb-5">
                        <h2 class="h4 mb-3 d-flex align-items-center">
                            <i class="bi bi-calendar-check me-2 text-info"></i>
                            Mis Reservas
                            <span class="badge bg-info ms-2">{{ results.bookings.length }}</span>
                        </h2>
                        <div class="row g-3">
                            <div
                                v-for="item in results.bookings"
                                :key="item.id"
                                class="col-md-6"
                            >
                                <Link :href="item.url" class="text-decoration-none">
                                    <div class="card result-card">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h5 class="card-title mb-0">{{ item.title }}</h5>
                                                <span class="badge bg-info">{{ item.badge }}</span>
                                            </div>
                                            <p class="card-text text-muted small mb-1">
                                                {{ item.description }}
                                            </p>
                                            <p class="card-text text-muted small mb-0">
                                                {{ item.subtitle }}
                                            </p>
                                        </div>
                                    </div>
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Páginas vistas -->
                    <div v-if="results.pageViews.length > 0" class="result-section mb-5">
                        <h2 class="h4 mb-3 d-flex align-items-center">
                            <i class="bi bi-eye me-2 text-secondary"></i>
                            Páginas Populares
                            <span class="badge bg-secondary ms-2">{{ results.pageViews.length }}</span>
                        </h2>
                        <div class="list-group">
                            <a
                                v-for="item in results.pageViews"
                                :key="item.id"
                                :href="item.url"
                                class="list-group-item list-group-item-action"
                            >
                                <div class="d-flex w-100 justify-content-between align-items-center">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">{{ item.title }}</h6>
                                        <p class="mb-0 text-muted small">{{ item.description }}</p>
                                    </div>
                                    <span class="badge bg-secondary rounded-pill">
                                        {{ item.badge }}
                                    </span>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Layout>
</template>

<script setup>
import { ref } from 'vue';
import { router, Head } from '@inertiajs/vue3';
import Layout from '../../../Shared/Customer/Layout.vue';

const props = defineProps({
    query: String,
    results: Object,
    totalResults: Number,
    quickLinks: Array,
});

const searchQuery = ref(props.query || '');

const performSearch = () => {
    if (searchQuery.value.trim()) {
        router.get(route('customer.search.index'), { q: searchQuery.value });
    }
};

const truncate = (text, length) => {
    if (!text) return '';
    if (text.length <= length) return text;
    return text.substring(0, length) + '...';
};
</script>

<style scoped>
.search-page {
    background: #f8f9fa;
    min-height: calc(100vh - 200px);
}

.search-header {
    background: white;
    padding: 2rem;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.input-group-text {
    border-right: none;
}

.input-group .form-control {
    border-left: none;
}

.input-group .form-control:focus {
    box-shadow: none;
    border-color: #ced4da;
}

.result-card {
    transition: all 0.3s ease;
    border: 1px solid #e0e0e0;
    height: 100%;
}

.result-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

.result-image {
    width: 80px;
    height: 80px;
    border-radius: 8px;
    background-size: cover;
    background-position: center;
    flex-shrink: 0;
}

.result-section {
    background: white;
    padding: 1.5rem;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.list-group-item {
    border: none;
    border-bottom: 1px solid #e0e0e0;
    padding: 1rem 0;
}

.list-group-item:last-child {
    border-bottom: none;
}

.list-group-item:hover {
    background-color: #f8f9fa;
}

@media (max-width: 768px) {
    .search-header {
        padding: 1.5rem;
    }

    .result-section {
        padding: 1rem;
    }
}
</style>
