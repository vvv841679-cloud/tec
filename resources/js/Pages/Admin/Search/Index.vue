<template>
    <Layout>
        <Head title="Buscar" />
        <div class="page-header d-print-none">
            <div class="container-xl">
                <div class="row g-2 align-items-center">
                    <div class="col">
                        <h2 class="page-title">Resultados de Búsqueda</h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-body">
            <div class="container-xl">
                <!-- Barra de búsqueda -->
                <div class="card mb-3">
                    <div class="card-body">
                        <form @submit.prevent="performSearch" class="d-flex gap-2">
                            <div class="input-group flex-grow-1">
                                <span class="input-group-text">
                                    <IconSearch :size="18"/>
                                </span>
                                <input
                                    v-model="searchQuery"
                                    type="text"
                                    class="form-control"
                                    placeholder="Buscar reservas, clientes, habitaciones, usuarios, pagos..."
                                    autofocus
                                />
                            </div>
                            <button type="submit" class="btn btn-primary px-4">
                                Buscar
                            </button>
                        </form>
                        <div v-if="query" class="mt-3">
                            <p class="text-muted mb-0">
                                Mostrando {{ totalResults }} resultados para
                                <strong>"{{ query }}"</strong>
                            </p>
                        </div>

                        <!-- Enlaces rápidos -->
                        <div v-if="quickLinks && quickLinks.length > 0" class="quick-links mt-3">
                            <div class="d-flex gap-2 flex-wrap align-items-center">
                                <span class="text-muted small me-2">Acceso rápido:</span>
                                <Link
                                    v-for="(link, index) in quickLinks"
                                    :key="index"
                                    :href="link.url"
                                    class="btn btn-sm btn-outline-primary d-flex align-items-center gap-1"
                                >
                                    <Component :is="getTablerIcon(link.icon)" :size="16"/>
                                    {{ link.label }}
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sin resultados -->
                <div v-if="query && totalResults === 0" class="empty">
                    <div class="empty-icon">
                        <IconSearch :size="48"/>
                    </div>
                    <p class="empty-title">No se encontraron resultados</p>
                    <p class="empty-subtitle text-muted">
                        Intenta con otros términos de búsqueda o verifica la ortografía
                    </p>
                </div>

                <!-- Estado inicial -->
                <div v-else-if="!query" class="empty">
                    <div class="empty-icon">
                        <IconSearch :size="48"/>
                    </div>
                    <p class="empty-title">¿Qué estás buscando?</p>
                    <p class="empty-subtitle text-muted">
                        Busca reservas, clientes, habitaciones, usuarios, pagos y más...
                    </p>
                </div>

                <!-- Resultados -->
                <div v-else class="search-results">
                    <!-- Reservas -->
                    <div v-if="results.bookings.length > 0" class="mb-4">
                        <h3 class="mb-3 d-flex align-items-center">
                            <IconCalendarCheck :size="24" class="me-2 text-primary"/>
                            Reservas
                            <span class="badge bg-primary ms-2">{{ results.bookings.length }}</span>
                        </h3>
                        <div class="row row-cards">
                            <div
                                v-for="item in results.bookings"
                                :key="item.id"
                                class="col-md-6 col-lg-4"
                            >
                                <Link :href="item.url" class="text-decoration-none">
                                    <div class="card card-sm card-link">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div class="me-3">
                                                    <div class="avatar bg-primary-lt">
                                                        <IconCalendarCheck :size="24"/>
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <div class="fw-bold">{{ item.title }}</div>
                                                    <div class="text-muted small">{{ item.description }}</div>
                                                    <div class="text-muted small">{{ item.subtitle }}</div>
                                                </div>
                                                <span class="badge" :class="getBadgeClass(item.badge)">
                                                    {{ item.badge }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Clientes -->
                    <div v-if="results.customers.length > 0" class="mb-4">
                        <h3 class="mb-3 d-flex align-items-center">
                            <IconUsers :size="24" class="me-2 text-info"/>
                            Clientes
                            <span class="badge bg-info ms-2">{{ results.customers.length }}</span>
                        </h3>
                        <div class="row row-cards">
                            <div
                                v-for="item in results.customers"
                                :key="item.id"
                                class="col-md-6 col-lg-4"
                            >
                                <Link :href="item.url" class="text-decoration-none">
                                    <div class="card card-sm card-link">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div class="me-3">
                                                    <div class="avatar bg-info-lt">
                                                        <IconUsers :size="24"/>
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <div class="fw-bold">{{ item.title }}</div>
                                                    <div class="text-muted small">{{ item.description }}</div>
                                                    <div class="text-muted small">{{ item.subtitle }}</div>
                                                </div>
                                                <span class="badge" :class="item.badge === 'Verificado' ? 'bg-success' : 'bg-warning'">
                                                    {{ item.badge }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Habitaciones -->
                    <div v-if="results.rooms.length > 0" class="mb-4">
                        <h3 class="mb-3 d-flex align-items-center">
                            <IconBed :size="24" class="me-2 text-success"/>
                            Habitaciones
                            <span class="badge bg-success ms-2">{{ results.rooms.length }}</span>
                        </h3>
                        <div class="row row-cards">
                            <div
                                v-for="item in results.rooms"
                                :key="item.id"
                                class="col-md-6 col-lg-4"
                            >
                                <Link :href="item.url" class="text-decoration-none">
                                    <div class="card card-sm card-link">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div class="me-3">
                                                    <div class="avatar bg-success-lt">
                                                        <IconBed :size="24"/>
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <div class="fw-bold">{{ item.title }}</div>
                                                    <div class="text-muted small">{{ item.description }}</div>
                                                    <div class="text-muted small">{{ item.subtitle }}</div>
                                                </div>
                                                <span class="badge" :class="getRoomBadgeClass(item.badge)">
                                                    {{ item.badge }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Usuarios -->
                    <div v-if="results.users.length > 0" class="mb-4">
                        <h3 class="mb-3 d-flex align-items-center">
                            <IconUser :size="24" class="me-2 text-purple"/>
                            Usuarios
                            <span class="badge bg-purple ms-2">{{ results.users.length }}</span>
                        </h3>
                        <div class="row row-cards">
                            <div
                                v-for="item in results.users"
                                :key="item.id"
                                class="col-md-6 col-lg-4"
                            >
                                <Link :href="item.url" class="text-decoration-none">
                                    <div class="card card-sm card-link">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div class="me-3">
                                                    <div class="avatar bg-purple-lt">
                                                        <IconUser :size="24"/>
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <div class="fw-bold">{{ item.title }}</div>
                                                    <div class="text-muted small">{{ item.description }}</div>
                                                    <div class="text-muted small">{{ item.subtitle }}</div>
                                                </div>
                                                <span class="badge bg-purple">
                                                    {{ item.badge }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Pagos -->
                    <div v-if="results.payments.length > 0" class="mb-4">
                        <h3 class="mb-3 d-flex align-items-center">
                            <IconBrandMastercard :size="24" class="me-2 text-warning"/>
                            Pagos
                            <span class="badge bg-warning ms-2">{{ results.payments.length }}</span>
                        </h3>
                        <div class="row row-cards">
                            <div
                                v-for="item in results.payments"
                                :key="item.id"
                                class="col-md-6"
                            >
                                <Link :href="item.url" class="text-decoration-none">
                                    <div class="card card-sm card-link">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div class="me-3">
                                                    <div class="avatar bg-warning-lt">
                                                        <IconBrandMastercard :size="24"/>
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <div class="fw-bold">{{ item.title }}</div>
                                                    <div class="text-muted small">{{ item.description }}</div>
                                                    <div class="text-muted small">{{ item.subtitle }}</div>
                                                </div>
                                                <span class="badge" :class="getPaymentBadgeClass(item.badge)">
                                                    {{ item.badge }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Tipos de habitaciones -->
                    <div v-if="results.roomTypes.length > 0" class="mb-4">
                        <h3 class="mb-3 d-flex align-items-center">
                            <IconBuildingBurjAlArab :size="24" class="me-2 text-teal"/>
                            Tipos de Habitaciones
                            <span class="badge bg-teal ms-2">{{ results.roomTypes.length }}</span>
                        </h3>
                        <div class="row row-cards">
                            <div
                                v-for="item in results.roomTypes"
                                :key="item.id"
                                class="col-md-6 col-lg-4"
                            >
                                <Link :href="item.url" class="text-decoration-none">
                                    <div class="card card-link">
                                        <img
                                            v-if="item.image"
                                            :src="item.image"
                                            class="card-img-top"
                                            :alt="item.title"
                                            style="height: 160px; object-fit: cover;"
                                        />
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h4 class="card-title mb-0">{{ item.title }}</h4>
                                                <span class="badge" :class="item.badge === 'Activo' ? 'bg-success' : 'bg-secondary'">
                                                    {{ item.badge }}
                                                </span>
                                            </div>
                                            <p class="text-muted small mb-2">{{ item.subtitle }}</p>
                                            <p class="card-text" style="font-size: 0.875rem;">
                                                {{ truncate(item.description, 100) }}
                                            </p>
                                        </div>
                                    </div>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Layout>
</template>

<script setup>
import { ref } from 'vue';
import { router, Head, Link } from '@inertiajs/vue3';
import Layout from '../../../Shared/Admin/Layout.vue';
import {
    IconSearch,
    IconCalendarCheck,
    IconUsers,
    IconBed,
    IconUser,
    IconBrandMastercard,
    IconBuildingBurjAlArab,
    IconHome,
    IconCalendarWeek,
    IconUserCircle
} from '@tabler/icons-vue';

const props = defineProps({
    query: String,
    results: Object,
    totalResults: Number,
    quickLinks: Array,
});

const searchQuery = ref(props.query || '');

const performSearch = () => {
    if (searchQuery.value.trim()) {
        router.get(route('admin.search.index'), { q: searchQuery.value });
    }
};

const truncate = (text, length) => {
    if (!text) return '';
    if (text.length <= length) return text;
    return text.substring(0, length) + '...';
};

const getBadgeClass = (status) => {
    const statusMap = {
        'pending': 'bg-warning',
        'confirmed': 'bg-info',
        'checked_in': 'bg-primary',
        'checked_out': 'bg-success',
        'cancelled': 'bg-danger',
    };
    return statusMap[status] || 'bg-secondary';
};

const getRoomBadgeClass = (status) => {
    const statusMap = {
        'available': 'bg-success',
        'occupied': 'bg-danger',
        'maintenance': 'bg-warning',
        'reserved': 'bg-info',
    };
    return statusMap[status] || 'bg-secondary';
};

const getPaymentBadgeClass = (status) => {
    const statusMap = {
        'pending': 'bg-warning',
        'completed': 'bg-success',
        'failed': 'bg-danger',
        'refunded': 'bg-info',
    };
    return statusMap[status] || 'bg-secondary';
};

const getTablerIcon = (iconName) => {
    const iconMap = {
        'IconCalendarCheck': IconCalendarCheck,
        'IconUsers': IconUsers,
        'IconBed': IconBed,
        'IconUserCircle': IconUserCircle,
        'IconBrandMastercard': IconBrandMastercard,
        'IconBuildingBurjAlArab': IconBuildingBurjAlArab,
        'IconHome': IconHome,
        'IconCalendarWeek': IconCalendarWeek,
        'IconUser': IconUser,
    };
    return iconMap[iconName] || IconSearch;
};
</script>

<style scoped>
.card-link {
    transition: all 0.2s ease;
    cursor: pointer;
}

.card-link:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    transform: translateY(-2px);
}

.empty {
    text-align: center;
    padding: 3rem 1rem;
}

.empty-icon {
    margin-bottom: 1rem;
    color: var(--tblr-text-muted);
}

.empty-title {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.empty-subtitle {
    font-size: 0.875rem;
}

.avatar {
    width: 2.5rem;
    height: 2.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>
