import { ref, watch, onMounted, computed } from 'vue'

export function useThemeSystem() {
    // Estados
    const currentTheme = ref(localStorage.getItem('theme-style') || 'adults')
    const colorMode = ref(localStorage.getItem('color-mode') || 'auto')
    const fontSize = ref(localStorage.getItem('font-size') || 'medium')
    const contrast = ref(localStorage.getItem('contrast') || 'normal')

    // Temas disponibles
    const themes = {
        kids: {
            name: 'Niños',
            colors: {
                primary: '#FF6B9D',
                secondary: '#FFA500',
                accent: '#9B59B6',
                background: '#FFF5F7',
                text: '#2C3E50'
            },
            fontFamily: 'Comic Sans MS, cursive'
        },
        teens: {
            name: 'Jóvenes',
            colors: {
                primary: '#6C63FF',
                secondary: '#00D4FF',
                accent: '#FF3CAC',
                background: '#F8F9FA',
                text: '#212529'
            },
            fontFamily: 'Poppins, sans-serif'
        },
        adults: {
            name: 'Adultos',
            colors: {
                primary: '#206bc4',
                secondary: '#626976',
                accent: '#d63939',
                background: '#FFFFFF',
                text: '#1e293b'
            },
            fontFamily: 'Inter, sans-serif'
        }
    }

    // Tamaños de fuente
    const fontSizes = {
        small: {
            name: 'Pequeño',
            base: '14px',
            scale: 0.875
        },
        medium: {
            name: 'Mediano',
            base: '16px',
            scale: 1
        },
        large: {
            name: 'Grande',
            base: '18px',
            scale: 1.125
        },
        xlarge: {
            name: 'Muy Grande',
            base: '20px',
            scale: 1.25
        }
    }

    // Niveles de contraste
    const contrastLevels = {
        normal: {
            name: 'Normal',
            multiplier: 1
        },
        high: {
            name: 'Alto',
            multiplier: 1.3
        },
        veryHigh: {
            name: 'Muy Alto',
            multiplier: 1.6
        }
    }

    // Detectar si es de día o de noche
    const isDaytime = computed(() => {
        const hour = new Date().getHours()
        return hour >= 6 && hour < 20 // De 6am a 8pm es día
    })

    // Modo de color efectivo (auto se resuelve a light o dark)
    const effectiveColorMode = computed(() => {
        if (colorMode.value === 'auto') {
            return isDaytime.value ? 'light' : 'dark'
        }
        return colorMode.value
    })

    // Aplicar tema completo
    const applyTheme = () => {
        const theme = themes[currentTheme.value]
        const root = document.documentElement

        // Aplicar modo de color (día/noche)
        root.setAttribute('data-bs-theme', effectiveColorMode.value)
        root.setAttribute('data-theme', currentTheme.value)

        // Aplicar colores del tema
        if (effectiveColorMode.value === 'light') {
            root.style.setProperty('--tblr-primary', theme.colors.primary)
            root.style.setProperty('--tblr-secondary', theme.colors.secondary)
            root.style.setProperty('--tblr-accent', theme.colors.accent)
            root.style.setProperty('--theme-bg', theme.colors.background)
            root.style.setProperty('--theme-text', theme.colors.text)
        }

        // Aplicar familia de fuentes
        root.style.setProperty('--theme-font-family', theme.fontFamily)

        // Aplicar tamaño de fuente
        const fontConfig = fontSizes[fontSize.value]
        root.style.setProperty('--base-font-size', fontConfig.base)
        root.style.setProperty('--font-scale', fontConfig.scale)
        root.style.fontSize = fontConfig.base

        // Aplicar contraste
        const contrastConfig = contrastLevels[contrast.value]
        root.style.setProperty('--contrast-multiplier', contrastConfig.multiplier)

        // Aplicar filtro de contraste si es necesario
        if (contrast.value !== 'normal') {
            root.style.filter = `contrast(${contrastConfig.multiplier})`
        } else {
            root.style.filter = 'none'
        }
    }

    // Watchers
    watch([currentTheme, colorMode, fontSize, contrast], () => {
        localStorage.setItem('theme-style', currentTheme.value)
        localStorage.setItem('color-mode', colorMode.value)
        localStorage.setItem('font-size', fontSize.value)
        localStorage.setItem('contrast', contrast.value)
        applyTheme()
    })

    // Actualizar automáticamente cada hora si está en modo auto
    onMounted(() => {
        applyTheme()

        if (colorMode.value === 'auto') {
            const interval = setInterval(() => {
                applyTheme()
            }, 60000) // Cada minuto para detectar cambios de día/noche

            // Limpiar intervalo al desmontar
            return () => clearInterval(interval)
        }
    })

    // Funciones públicas
    const setTheme = (theme) => {
        if (themes[theme]) {
            currentTheme.value = theme
        }
    }

    const setColorMode = (mode) => {
        if (['light', 'dark', 'auto'].includes(mode)) {
            colorMode.value = mode
        }
    }

    const setFontSize = (size) => {
        if (fontSizes[size]) {
            fontSize.value = size
        }
    }

    const setContrast = (level) => {
        if (contrastLevels[level]) {
            contrast.value = level
        }
    }

    const toggleColorMode = () => {
        const modes = ['light', 'dark', 'auto']
        const currentIndex = modes.indexOf(colorMode.value)
        const nextIndex = (currentIndex + 1) % modes.length
        colorMode.value = modes[nextIndex]
    }

    const resetToDefaults = () => {
        currentTheme.value = 'adults'
        colorMode.value = 'auto'
        fontSize.value = 'medium'
        contrast.value = 'normal'
    }

    return {
        // Estados
        currentTheme,
        colorMode,
        fontSize,
        contrast,
        effectiveColorMode,
        isDaytime,

        // Configuraciones
        themes,
        fontSizes,
        contrastLevels,

        // Métodos
        setTheme,
        setColorMode,
        setFontSize,
        setContrast,
        toggleColorMode,
        resetToDefaults,
        applyTheme
    }
}
