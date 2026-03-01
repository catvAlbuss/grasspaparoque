<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'

const images = [
    {
        id: '1',
        title: 'PAPAROQUE PRO',
        subtitle: 'Rendimiento Elite',
        description: 'Césped sintético de última generación diseñado para competiciones profesionales. Máxima durabilidad y respuesta.',
        src: '/img/cesped-premium.png'
    },
    {
        id: '2',
        title: 'RESISTENCIA ',
        subtitle: 'Tecnología Avanzada',
        description: 'Tratamiento especial anti-UV que mantiene el color y la textura intactos ante la exposición solar intensa.',
        src: '/img/areas-recreativas.png'
    },
    {
        id: '3',
        title: 'DRENAJE PERFECTO',
        subtitle: 'Juego Sin Pausas',
        description: 'Sistema de drenaje de alta capacidad que elimina el agua rápidamente, permitiendo jugar incluso después de lluvia.',
        src: '/img/canchas_deportivas.png'
    },
    {
        id: '4',
        title: 'CUMPLEAÑOS Y EVENTOS',
        subtitle: 'Celebraciones',
        description: 'Celebra tus eventos especiales en un ambiente único al aire libre.',
        src: '/img/eventos.png'
    }
]

const rotation = ref(0)
const totalImages = images.length
const isTransitioning = ref(false)

// Calcular ángulos para cada imagen
const getImageStyle = (index) => {
    const angle = (index * (360 / totalImages) + rotation.value) % 360
    const radian = (angle * Math.PI) / 180

    // Radio del círculo
    const radius = 500

    // Calcular posición en 3D
    const z = Math.cos(radian) * radius

    // Determinar posición
    const isFront = Math.abs(angle) < 45 || Math.abs(angle - 360) < 45
    const isBack = Math.abs(angle - 180) < 45

    // Escala y opacidad
    let scale = 1
    let opacity = 1
    let brightness = 1
    let zIndex = 50

    if (isFront) {
        scale = 1.2
        brightness = 1.1
        opacity = 1
        zIndex = 100
    } else if (isBack) {
        scale = 0.7
        opacity = 0.4
        brightness = 0.5
        zIndex = 10
    } else {
        scale = 0.9
        opacity = 0.8
        brightness = 0.8
        zIndex = 50
    }

    return {
        transform: `translateX(-50%) translateY(-50%) translateZ(${z}px) rotateY(${angle}deg) scale(${scale})`,
        opacity,
        filter: `brightness(${brightness})`,
        zIndex,
        left: '50%',
        top: '50%',
        position: 'absolute'
    }
}

const nextImage = () => {
    if (isTransitioning.value) return
    isTransitioning.value = true
    rotation.value = (rotation.value + 90) % 360
    setTimeout(() => isTransitioning.value = false, 800)
}

const prevImage = () => {
    if (isTransitioning.value) return
    isTransitioning.value = true
    rotation.value = (rotation.value - 90 + 360) % 360
    setTimeout(() => isTransitioning.value = false, 800)
}

// Autoplay
const isAutoPlaying = ref(true)
const autoPlayInterval = ref(null)

const startAutoPlay = () => {
    stopAutoPlay()
    autoPlayInterval.value = setInterval(() => {
        if (!isTransitioning.value) nextImage()
    }, 4000)
}

const stopAutoPlay = () => {
    if (autoPlayInterval.value) {
        clearInterval(autoPlayInterval.value)
        autoPlayInterval.value = null
    }
}

const toggleAutoPlay = () => {
    isAutoPlaying.value = !isAutoPlaying.value
    if (isAutoPlaying.value) startAutoPlay()
    else stopAutoPlay()
}

onMounted(() => {
    if (isAutoPlaying.value) startAutoPlay()
})

onUnmounted(() => {
    stopAutoPlay()
})
</script>

<template>
    <section class="w-full bg-gradient-to-b from-gray-900 via-gray-800 to-gray-900 overflow-hidden" style="min-height: 100vh;">
        <!-- Efectos de fondo -->
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute -top-40 -right-40 w-80 h-80 bg-green-900/20 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-emerald-900/20 rounded-full blur-3xl"></div>
        </div>

        <!-- Título -->
        <div class="relative pt-8 text-center z-10">
            <h2 class="text-4xl md:text-5xl font-bold">
                <span class="bg-clip-text text-transparent bg-gradient-to-r from-green-400 via-emerald-300 to-green-400">
                     Galería GrassPaparoque
                </span>
            </h2>
            <div class="w-24 h-0.5 bg-gradient-to-r from-green-500 to-emerald-500 mx-auto rounded-full my-4"></div>
        </div>

        <!-- Contenedor principal -->
        <div class="relative flex items-center justify-center px-4" style="height: 550px; perspective: 1200px; margin-top: 20px;">
            <!-- Carrusel 3D -->
            <div class="relative" style="transform-style: preserve-3d; width: 100%; height: 100%;">
                <div v-for="(image, index) in images"
                     :key="image.id"
                     class="absolute transition-all duration-700 cursor-pointer"
                     :style="getImageStyle(index)">

                    <!-- Tarjeta de imagen -->
                    <div class="relative group">
                        <div class="relative overflow-hidden rounded-xl shadow-xl shadow-black/70"
                             :class="{ 'shadow-lg shadow-green-500/20': index === 0 }">

                            <img :src="image.src"
                                 :alt="image.title"
                                 class="w-[500px] h-[280px] object-cover transition-transform duration-700 group-hover:scale-110 brightness-100 group-hover:brightness-110">

                            <!-- Overlay -->
                            <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/40 to-transparent"></div>

                            <!-- Número -->
                            <div class="absolute top-4 left-4">
                                <span class="text-5xl font-black text-green-500/30 group-hover:text-green-500/50 transition-all">
                                    {{ image.id }}
                                </span>
                            </div>

                            <!-- Info hover -->
                            <div class="absolute bottom-0 left-0 right-0 p-4 transform translate-y-full group-hover:translate-y-0 transition-transform duration-500">
                                <div class="bg-black/80 backdrop-blur-xl p-3 rounded-lg border border-green-500/30">
                                    <span class="text-xs uppercase tracking-wider text-green-400">{{ image.subtitle }}</span>
                                    <h3 class="text-base font-bold text-green-300">{{ image.title }}</h3>
                                    <p class="text-xs text-gray-300 mt-1 line-clamp-2">{{ image.description }}</p>
                                </div>
                            </div>

                            <!-- Indicador -->
                            <div v-if="index === 0"
                                 class="absolute top-4 right-4 w-3 h-3 bg-green-400 rounded-full animate-pulse shadow-md shadow-green-500/40">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Navegación -->
            <button @click="prevImage"
                    class="absolute left-4 top-1/2 -translate-y-1/2 z-[200] w-14 h-14 bg-black/50 backdrop-blur-md rounded-full flex items-center justify-center text-green-400 hover:bg-black/70
                    transition-all border border-green-500/30 hover:scale-110">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </button>

            <button @click="nextImage"
                    class="absolute right-4 top-1/2 -translate-y-1/2 z-[200] w-14 h-14 bg-black/50 backdrop-blur-md rounded-full flex items-center justify-center text-green-400 hover:bg-black/70
                    transition-all border border-green-500/30 hover:scale-110">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </button>
        </div>

        <!-- Miniaturas (actualizadas para 4 imágenes) -->
        <div class="relative pb-2 flex justify-center gap-3 z-10">
            <div v-for="(image, index) in images"
                 :key="image.id"
                 @click="rotation = (index * 90) % 360"
                 class="relative group/thumb cursor-pointer transition-all duration-300"
                 :class="index * 90 === rotation % 360 ? 'scale-110 z-10' : 'opacity-50 hover:opacity-100'">

                <div class="w-14 h-10 rounded-md overflow-hidden border-2"
                     :class="index * 90 === rotation % 360 ? 'border-green-500 shadow-md shadow-green-500/30' : 'border-transparent'">
                    <img :src="image.src"
                         :alt="image.title"
                         class="w-full h-full object-cover brightness-75">
                </div>

                <!-- Tooltip -->
                <div class="absolute -top-8 left-1/2 -translate-x-1/2 bg-black/80 text-green-400 text-xs px-2 py-1 rounded opacity-0 group-hover/thumb:opacity-100 transition-all whitespace-nowrap">
                    {{ image.title }}
                </div>
            </div>
        </div>

        <!-- Autoplay -->
        <div class="absolute bottom-4 right-4 z-50">
            <button @click="toggleAutoPlay"
                    class="w-10 h-10 bg-black/50 backdrop-blur-md rounded-full flex items-center justify-center text-green-400 hover:bg-black/70 transition-all border border-green-500/30">
                <svg v-if="isAutoPlaying" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </button>
        </div>
    </section>
</template>

<style scoped>
.transition-all {
    transition-property: all;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

.animate-pulse {
    animation: pulse 2s ease-in-out infinite;
}

[style*="transform-style: preserve-3d"] {
    transform-style: preserve-3d;
}

.backdrop-blur-md {
    backdrop-filter: blur(12px);
}

@media (max-width: 768px) {
    .w-\[500px\] {
        width: 300px;
    }
    .h-\[280px\] {
        height: 170px;
    }
}
</style>
