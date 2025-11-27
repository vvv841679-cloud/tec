<template>
    <div class="search-container">
        <form @submit.prevent="performSearch" class="search-form">
            <div class="search-input-wrapper">
                <i class="bi bi-search search-icon"></i>
                <input
                    v-model="searchQuery"
                    type="text"
                    class="search-input"
                    placeholder="Buscar habitaciones, reservas..."
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
                    <i class="bi bi-x-circle"></i>
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
                            <i :class="link.icon"></i>
                        </div>
                        <div class="suggestion-content">
                            <div class="suggestion-label">{{ link.label }}</div>
                        </div>
                        <i class="bi bi-arrow-right-short suggestion-arrow"></i>
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
                            <i :class="getIconClass(suggestion.type)"></i>
                        </div>
                        <div class="suggestion-content">
                            <div class="suggestion-label">{{ suggestion.label }}</div>
                            <div class="suggestion-type">{{ suggestion.type }}</div>
                        </div>
                        <i class="bi bi-arrow-right-short suggestion-arrow"></i>
                    </div>
                </div>
            </div>
        </form>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';

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
            const response = await axios.get(route('customer.search.api'), {
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
        router.get(route('customer.search.index'), { q: searchQuery.value });
    }
};

const goToUrl = (url) => {
    showSuggestions.value = false;
    router.visit(url);
};

const clearSearch = () => {
    searchQuery.value = '';
    suggestions.value = [];
    showSuggestions.value = false;
};

const hideSuggestions = () => {
    setTimeout(() => {
        showSuggestions.value = false;
    }, 200);
};

const getIconClass = (type) => {
    switch (type) {
        case 'Tipo de habitación':
            return 'bi bi-door-open';
        case 'Habitación':
            return 'bi bi-key';
        case 'Reserva':
            return 'bi bi-calendar-check';
        case 'Página':
            return 'bi bi-file-text';
        default:
            return 'bi bi-search';
    }
};
</script>

<style scoped>
.search-container {
    position: relative;
    width: 100%;
    max-width: 400px;
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
    color: #999;
    font-size: 16px;
    pointer-events: none;
}

.search-input {
    width: 100%;
    padding: 8px 40px 8px 38px;
    border: 1px solid rgba(255, 255, 255, 0.3);
    border-radius: 25px;
    background: rgba(255, 255, 255, 0.15);
    color: #fff;
    font-size: 14px;
    transition: all 0.3s ease;
}

.search-input::placeholder {
    color: rgba(255, 255, 255, 0.7);
}

.search-input:focus {
    outline: none;
    background: rgba(255, 255, 255, 0.25);
    border-color: rgba(255, 255, 255, 0.5);
    box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.1);
}

.clear-btn {
    position: absolute;
    right: 12px;
    background: none;
    border: none;
    color: rgba(255, 255, 255, 0.7);
    cursor: pointer;
    font-size: 16px;
    padding: 0;
    display: flex;
    align-items: center;
    transition: color 0.2s ease;
}

.clear-btn:hover {
    color: #fff;
}

.suggestions-dropdown {
    position: absolute;
    top: calc(100% + 8px);
    left: 0;
    right: 0;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
    max-height: 400px;
    overflow-y: auto;
    z-index: 1000;
}

.suggestion-item {
    display: flex;
    align-items: center;
    padding: 12px 16px;
    cursor: pointer;
    transition: background-color 0.2s ease;
    border-bottom: 1px solid #f0f0f0;
}

.suggestion-item:last-child {
    border-bottom: none;
}

.suggestion-item:hover {
    background-color: #f8f9fa;
}

.suggestion-icon {
    flex-shrink: 0;
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f0f0f0;
    border-radius: 8px;
    margin-right: 12px;
    color: #666;
    font-size: 16px;
}

.suggestion-content {
    flex: 1;
    min-width: 0;
}

.suggestion-label {
    font-size: 14px;
    font-weight: 500;
    color: #333;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.suggestion-type {
    font-size: 12px;
    color: #999;
    margin-top: 2px;
}

.suggestion-arrow {
    flex-shrink: 0;
    color: #ccc;
    font-size: 24px;
}

/* Quick Links */
.section-divider {
    padding: 8px 16px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    color: #999;
    background: #f8f9fa;
    border-bottom: 1px solid #e0e0e0;
    letter-spacing: 0.5px;
}

.quick-link-item {
    background: linear-gradient(to right, #f0f8ff, #ffffff);
}

.quick-link-item:hover {
    background: linear-gradient(to right, #e6f3ff, #f8f9fa);
}

.quick-link-icon {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
}

/* Responsive */
@media (max-width: 768px) {
    .search-container {
        max-width: 100%;
    }

    .search-input {
        font-size: 13px;
        padding: 7px 36px 7px 34px;
    }
}
</style>
