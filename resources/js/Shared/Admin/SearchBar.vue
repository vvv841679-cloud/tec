<template>
    <div class="search-container">
        <form @submit.prevent="performSearch" class="search-form">
            <div class="search-input-wrapper">
                <IconSearch class="search-icon" :size="18"/>
                <input
                    v-model="searchQuery"
                    type="text"
                    class="search-input"
                    placeholder="Buscar reservas, clientes, habitaciones..."
                    @input="onSearchInput"
                    @focus="showSuggestions = true"
                    @blur="hideSuggestions"
                />
                <button
                    v-if="searchQuery"
                    type="button"
                    class="clear-btn"
                    @click="clearSearch"
                >
                    <IconX :size="16"/>
                </button>
            </div>

            <!-- Sugerencias en tiempo real -->
            <div v-if="showSuggestions && (quickLinks.length > 0 || suggestions.length > 0)" class="suggestions-dropdown">
                <!-- Enlaces rápidos -->
                <div v-if="quickLinks.length > 0" class="quick-links-section">
                    <div class="section-divider">Acceso rápido</div>
                    <div
                        v-for="(link, index) in quickLinks"
                        :key="`quick-${index}`"
                        class="suggestion-item quick-link-item"
                        @mousedown.prevent="goToUrl(link.url)"
                    >
                        <div class="suggestion-icon quick-link-icon">
                            <Component :is="getTablerIcon(link.icon)" :size="18"/>
                        </div>
                        <div class="suggestion-content">
                            <div class="suggestion-label">{{ link.label }}</div>
                        </div>
                        <IconArrowRight :size="18" class="suggestion-arrow"/>
                    </div>
                </div>

                <!-- Resultados de búsqueda -->
                <div v-if="suggestions.length > 0">
                    <div v-if="quickLinks.length > 0" class="section-divider">Resultados</div>
                    <div
                        v-for="suggestion in suggestions"
                        :key="`${suggestion.type}-${suggestion.value}`"
                        class="suggestion-item"
                        @mousedown.prevent="goToUrl(suggestion.url)"
                    >
                        <div class="suggestion-icon">
                            <Component :is="getIconForType(suggestion.type)" :size="18"/>
                        </div>
                        <div class="suggestion-content">
                            <div class="suggestion-label">{{ suggestion.label }}</div>
                            <div class="suggestion-subtitle" v-if="suggestion.subtitle">{{ suggestion.subtitle }}</div>
                            <div class="suggestion-type">{{ suggestion.type }}</div>
                        </div>
                        <IconArrowRight :size="18" class="suggestion-arrow"/>
                    </div>
                </div>

                <!-- Ver todos los resultados -->
                <div v-if="searchQuery.length >= 2" class="view-all-section">
                    <div
                        class="suggestion-item view-all-item"
                        @mousedown.prevent="performSearch"
                    >
                        <div class="suggestion-icon">
                            <IconSearch :size="18"/>
                        </div>
                        <div class="suggestion-content">
                            <div class="suggestion-label">Ver todos los resultados para "{{ searchQuery }}"</div>
                        </div>
                        <IconArrowRight :size="18" class="suggestion-arrow"/>
                    </div>
                </div>
            </div>
        </form>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import {
    IconSearch,
    IconX,
    IconArrowRight,
    IconCalendarCheck,
    IconUsers,
    IconBed,
    IconUserCircle,
    IconBrandMastercard,
    IconBuildingBurjAlArab,
    IconHome,
    IconCalendarWeek,
    IconUser
} from '@tabler/icons-vue';

const searchQuery = ref('');
const suggestions = ref([]);
const quickLinks = ref([]);
const showSuggestions = ref(false);
let searchTimeout = null;

const onSearchInput = () => {
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }

    if (searchQuery.value.length < 2) {
        suggestions.value = [];
        quickLinks.value = [];
        return;
    }

    searchTimeout = setTimeout(async () => {
        try {
            const response = await axios.get(route('admin.search.api'), {
                params: { q: searchQuery.value }
            });
            suggestions.value = response.data.suggestions;
            quickLinks.value = response.data.quickLinks || [];
            showSuggestions.value = true;
        } catch (error) {
            console.error('Error fetching suggestions:', error);
        }
    }, 300);
};

const performSearch = () => {
    if (searchQuery.value.trim()) {
        showSuggestions.value = false;
        router.get(route('admin.search.index'), { q: searchQuery.value });
    }
};

const goToUrl = (url) => {
    showSuggestions.value = false;
    router.visit(url);
};

const clearSearch = () => {
    searchQuery.value = '';
    suggestions.value = [];
    quickLinks.value = [];
    showSuggestions.value = false;
};

const hideSuggestions = () => {
    setTimeout(() => {
        showSuggestions.value = false;
    }, 200);
};

const getIconForType = (type) => {
    switch (type) {
        case 'Reserva':
            return IconCalendarCheck;
        case 'Cliente':
            return IconUsers;
        case 'Habitación':
            return IconBed;
        case 'Usuario':
            return IconUser;
        case 'Pago':
            return IconBrandMastercard;
        default:
            return IconSearch;
    }
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
.search-container {
    position: relative;
    width: 100%;
    max-width: 500px;
}

.search-form {
    position: relative;
}

.search-input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.search-icon {
    position: absolute;
    left: 12px;
    color: #6c757d;
    pointer-events: none;
    z-index: 1;
}

.search-input {
    width: 100%;
    padding: 8px 36px 8px 38px;
    border: 1px solid var(--tblr-border-color);
    border-radius: 4px;
    background: var(--tblr-bg-forms);
    color: var(--tblr-body-color);
    font-size: 14px;
    transition: all 0.2s ease;
}

.search-input::placeholder {
    color: var(--tblr-text-muted);
}

.search-input:focus {
    outline: none;
    border-color: var(--tblr-primary);
    box-shadow: 0 0 0 0.25rem rgba(32, 107, 196, 0.25);
}

.clear-btn {
    position: absolute;
    right: 10px;
    background: none;
    border: none;
    color: var(--tblr-text-muted);
    cursor: pointer;
    padding: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: color 0.2s ease;
    z-index: 1;
}

.clear-btn:hover {
    color: var(--tblr-body-color);
}

.suggestions-dropdown {
    position: absolute;
    top: calc(100% + 4px);
    left: 0;
    right: 0;
    background: var(--tblr-bg-surface);
    border: 1px solid var(--tblr-border-color);
    border-radius: 4px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    max-height: 500px;
    overflow-y: auto;
    z-index: 1000;
}

.suggestion-item {
    display: flex;
    align-items: center;
    padding: 10px 12px;
    cursor: pointer;
    transition: background-color 0.2s ease;
    border-bottom: 1px solid var(--tblr-border-color);
}

.suggestion-item:last-child {
    border-bottom: none;
}

.suggestion-item:hover {
    background-color: var(--tblr-bg-surface-tertiary);
}

.suggestion-icon {
    flex-shrink: 0;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--tblr-bg-surface-tertiary);
    border-radius: 4px;
    margin-right: 10px;
    color: var(--tblr-text-muted);
}

.suggestion-content {
    flex: 1;
    min-width: 0;
}

.suggestion-label {
    font-size: 14px;
    font-weight: 500;
    color: var(--tblr-body-color);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.suggestion-subtitle {
    font-size: 12px;
    color: var(--tblr-text-muted);
    margin-top: 2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.suggestion-type {
    font-size: 11px;
    color: var(--tblr-text-muted);
    margin-top: 2px;
}

.suggestion-arrow {
    flex-shrink: 0;
    color: var(--tblr-text-muted);
}

/* Quick Links */
.section-divider {
    padding: 6px 12px;
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    color: var(--tblr-text-muted);
    background: var(--tblr-bg-surface-tertiary);
    border-bottom: 1px solid var(--tblr-border-color);
    letter-spacing: 0.5px;
}

.quick-link-item {
    background: linear-gradient(to right, rgba(32, 107, 196, 0.05), transparent);
}

.quick-link-item:hover {
    background: linear-gradient(to right, rgba(32, 107, 196, 0.1), var(--tblr-bg-surface-tertiary));
}

.quick-link-icon {
    background: var(--tblr-primary);
    color: #fff;
}

.view-all-section {
    border-top: 1px solid var(--tblr-border-color);
}

.view-all-item {
    background: var(--tblr-bg-surface-secondary);
    font-weight: 500;
}

.view-all-item:hover {
    background: var(--tblr-primary);
}

.view-all-item:hover .suggestion-label,
.view-all-item:hover .suggestion-icon,
.view-all-item:hover .suggestion-arrow {
    color: #fff;
}

/* Responsive */
@media (max-width: 768px) {
    .search-container {
        max-width: 100%;
    }

    .search-input {
        font-size: 13px;
        padding: 7px 32px 7px 34px;
    }

    .suggestion-label {
        font-size: 13px;
    }
}

/* Dark mode adjustments */
@media (prefers-color-scheme: dark) {
    .search-input {
        background: var(--tblr-bg-surface-dark);
    }
}
</style>
