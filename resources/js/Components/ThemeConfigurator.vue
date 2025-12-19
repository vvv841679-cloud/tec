<template>
    <div class="theme-configurator">
        <!-- Botón flotante para abrir el configurador -->
        <button
            @click="isOpen = !isOpen"
            class="theme-toggle-btn"
            :class="{ 'active': isOpen }"
            title="Configuración de Tema y Accesibilidad"
        >
            <i class="bi bi-palette-fill"></i>
        </button>

        <!-- Panel de configuración -->
        <transition name="slide-fade">
            <div v-if="isOpen" class="theme-panel">
                <div class="theme-panel-header">
                    <h5 class="mb-0">
                        <i class="bi bi-palette me-2"></i>
                        Personalización
                    </h5>
                    <button @click="isOpen = false" class="btn-close" aria-label="Cerrar"></button>
                </div>

                <div class="theme-panel-body">
                    <!-- Selección de Tema -->
                    <div class="config-section">
                        <label class="config-label">
                            <i class="bi bi-brush me-2"></i>
                            Estilo de Tema
                        </label>
                        <div class="theme-options">
                            <button
                                v-for="(theme, key) in themes"
                                :key="key"
                                @click="setTheme(key)"
                                class="theme-option"
                                :class="{ 'active': currentTheme === key }"
                            >
                                <div class="theme-preview" :data-theme="key">
                                    <div class="theme-preview-colors">
                                        <span :style="{ background: theme.colors.primary }"></span>
                                        <span :style="{ background: theme.colors.secondary }"></span>
                                        <span :style="{ background: theme.colors.accent }"></span>
                                    </div>
                                </div>
                                <span class="theme-option-name">{{ theme.name }}</span>
                            </button>
                        </div>
                    </div>

                    <!-- Modo de Color (Día/Noche) -->
                    <div class="config-section">
                        <label class="config-label">
                            <i class="bi bi-moon-stars me-2"></i>
                            Modo de Color
                        </label>
                        <div class="btn-group w-100" role="group">
                            <button
                                @click="setColorMode('light')"
                                class="btn btn-sm"
                                :class="colorMode === 'light' ? 'btn-primary' : 'btn-outline-primary'"
                            >
                                <i class="bi bi-sun-fill me-1"></i>
                                Claro
                            </button>
                            <button
                                @click="setColorMode('dark')"
                                class="btn btn-sm"
                                :class="colorMode === 'dark' ? 'btn-primary' : 'btn-outline-primary'"
                            >
                                <i class="bi bi-moon-fill me-1"></i>
                                Oscuro
                            </button>
                            <button
                                @click="setColorMode('auto')"
                                class="btn btn-sm"
                                :class="colorMode === 'auto' ? 'btn-primary' : 'btn-outline-primary'"
                            >
                                <i class="bi bi-clock-fill me-1"></i>
                                Auto
                            </button>
                        </div>
                        <small class="text-muted" v-if="colorMode === 'auto'">
                            <i class="bi bi-info-circle me-1"></i>
                            Ahora es {{ effectiveColorMode === 'light' ? 'día' : 'noche' }}
                            ({{ isDaytime ? '6am-8pm' : '8pm-6am' }})
                        </small>
                    </div>

                    <!-- Tamaño de Fuente -->
                    <div class="config-section">
                        <label class="config-label">
                            <i class="bi bi-fonts me-2"></i>
                            Tamaño de Fuente
                        </label>
                        <div class="font-size-options">
                            <button
                                v-for="(config, key) in fontSizes"
                                :key="key"
                                @click="setFontSize(key)"
                                class="font-size-option"
                                :class="{ 'active': fontSize === key }"
                                :style="{ fontSize: config.base }"
                            >
                                A
                            </button>
                        </div>
                        <div class="font-size-labels">
                            <small
                                v-for="(config, key) in fontSizes"
                                :key="key"
                                :class="{ 'fw-bold': fontSize === key }"
                            >
                                {{ config.name }}
                            </small>
                        </div>
                    </div>

                    <!-- Contraste -->
                    <div class="config-section">
                        <label class="config-label">
                            <i class="bi bi-circle-half me-2"></i>
                            Nivel de Contraste
                        </label>
                        <select
                            v-model="contrast"
                            @change="setContrast(contrast)"
                            class="form-select form-select-sm"
                        >
                            <option
                                v-for="(config, key) in contrastLevels"
                                :key="key"
                                :value="key"
                            >
                                {{ config.name }}
                            </option>
                        </select>
                    </div>

                    <!-- Botón de Reinicio -->
                    <div class="config-section">
                        <button @click="resetToDefaults" class="btn btn-outline-secondary btn-sm w-100">
                            <i class="bi bi-arrow-clockwise me-2"></i>
                            Restaurar valores por defecto
                        </button>
                    </div>
                </div>
            </div>
        </transition>

        <!-- Overlay -->
        <div
            v-if="isOpen"
            class="theme-overlay"
            @click="isOpen = false"
        ></div>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { useThemeSystem } from '../Composables/useThemeSystem.js'

const {
    currentTheme,
    colorMode,
    fontSize,
    contrast,
    effectiveColorMode,
    isDaytime,
    themes,
    fontSizes,
    contrastLevels,
    setTheme,
    setColorMode,
    setFontSize,
    setContrast,
    resetToDefaults
} = useThemeSystem()

const isOpen = ref(false)
</script>

<style scoped>
.theme-configurator {
    position: fixed;
    z-index: 1050;
}

.theme-toggle-btn {
    position: fixed;
    bottom: 2rem;
    right: 2rem;
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: var(--tblr-primary, #206bc4);
    color: white;
    border: none;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    transition: all 0.3s ease;
    z-index: 1051;
}

.theme-toggle-btn:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
}

.theme-toggle-btn.active {
    background: var(--tblr-secondary, #626976);
}

.theme-panel {
    position: fixed;
    top: 0;
    right: 0;
    width: 360px;
    max-width: 90vw;
    height: 100vh;
    background: var(--tblr-bg-surface, white);
    box-shadow: -4px 0 24px rgba(0, 0, 0, 0.15);
    display: flex;
    flex-direction: column;
    z-index: 1052;
}

.theme-panel-header {
    padding: 1.5rem;
    border-bottom: 1px solid var(--tblr-border-color, #e6e7e9);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.theme-panel-body {
    flex: 1;
    overflow-y: auto;
    padding: 1.5rem;
}

.config-section {
    margin-bottom: 2rem;
}

.config-label {
    display: block;
    font-weight: 600;
    margin-bottom: 0.75rem;
    color: var(--tblr-body-color);
}

/* Opciones de Tema */
.theme-options {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.75rem;
}

.theme-option {
    border: 2px solid var(--tblr-border-color, #e6e7e9);
    border-radius: 8px;
    padding: 0.75rem;
    background: transparent;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
}

.theme-option:hover {
    border-color: var(--tblr-primary, #206bc4);
    transform: translateY(-2px);
}

.theme-option.active {
    border-color: var(--tblr-primary, #206bc4);
    background: var(--tblr-primary-lt, rgba(32, 107, 196, 0.1));
}

.theme-preview {
    width: 100%;
    aspect-ratio: 1;
    border-radius: 4px;
    overflow: hidden;
}

.theme-preview-colors {
    display: flex;
    height: 100%;
}

.theme-preview-colors span {
    flex: 1;
}

.theme-option-name {
    font-size: 0.75rem;
    font-weight: 500;
    text-align: center;
}

/* Opciones de tamaño de fuente */
.font-size-options {
    display: flex;
    justify-content: space-between;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
}

.font-size-option {
    flex: 1;
    aspect-ratio: 1;
    border: 2px solid var(--tblr-border-color, #e6e7e9);
    border-radius: 8px;
    background: transparent;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
}

.font-size-option:hover {
    border-color: var(--tblr-primary, #206bc4);
}

.font-size-option.active {
    border-color: var(--tblr-primary, #206bc4);
    background: var(--tblr-primary-lt, rgba(32, 107, 196, 0.1));
    color: var(--tblr-primary, #206bc4);
}

.font-size-labels {
    display: flex;
    justify-content: space-between;
    font-size: 0.7rem;
    color: var(--tblr-secondary);
}

/* Overlay */
.theme-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1051;
}

/* Animaciones */
.slide-fade-enter-active,
.slide-fade-leave-active {
    transition: all 0.3s ease;
}

.slide-fade-enter-from {
    transform: translateX(100%);
    opacity: 0;
}

.slide-fade-leave-to {
    transform: translateX(100%);
    opacity: 0;
}

/* Responsive */
@media (max-width: 576px) {
    .theme-panel {
        width: 100vw;
    }

    .theme-toggle-btn {
        bottom: 1rem;
        right: 1rem;
        width: 48px;
        height: 48px;
        font-size: 1.25rem;
    }
}
</style>
