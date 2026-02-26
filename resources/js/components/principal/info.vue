<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue'

const slides = [
    {
        id: 1,
        title: 'Tu Polideportivo y Eventos en un solo lugar',
        description: 'Siente la pasión en cada partido y en cada celebracion.',
        cta: 'Ver servicios',
        href: '#services',
        image: 'https://i.ibb.co/LzwLvYP3/foto1.avif',
    },
    {
        id: 2,
        title: 'Recreacion, Diversion y Eventos sin Complicaciones',
        description: 'Agenda una llamada o déjanos un mensaje para iniciar tu reserva.',
        cta: 'Ir a contacto',
        href: '#contact',
        image: 'https://i.ibb.co/kV3WJ8PG/foto2.jpg',
    },
    {
        id: 3,
        title: 'Nuestros espacios y nuestro Asistente Virtual a tu servicio',
        description: 'Nuestro Asistente Virtual te ayuda a reservar en segundos.',
        cta: 'Reservar ahora',
        href: '#contact',
        image: 'https://i.ibb.co/CpmT1nKg/grass1.jpg',
    },
]

const current = ref(0)
const total = slides.length
let autoplayInterval = null

const activeSlide = computed(() => slides[current.value])

const goNext = () => {
    current.value = (current.value + 1) % total
}

const goPrev = () => {
    current.value = (current.value - 1 + total) % total
}

const goTo = (index) => {
    current.value = index
}

const startAutoplay = () => {
    if (autoplayInterval) return
    autoplayInterval = setInterval(() => {
        current.value = (current.value + 1) % total
    }, 3000)
}

const stopAutoplay = () => {
    if (autoplayInterval) {
        clearInterval(autoplayInterval)
        autoplayInterval = null
    }
}

onMounted(() => {
    startAutoplay()
})

onUnmounted(() => {
    stopAutoplay()
})
</script>

<template>
    <section id="home" class="relative min-h-screen w-full overflow-hidden bg-black">
        
        <!-- Badge de número de slide -->
        <div class="absolute top-6 right-6 z-50 rounded-full bg-black/60 backdrop-blur-md px-4 py-2 text-sm font-bold text-white border border-emerald-500/30">
            {{ current + 1 }} <span class="text-emerald-500">/</span> {{ total }}
        </div>

        <!-- Slide Container -->
        <div 
            class="flex h-full min-h-screen transition-transform duration-700 ease-in-out"
            :style="{ transform: `translateX(-${current * 100}%)` }"
        >
            <!-- Cada Slide -->
            <div 
                v-for="slide in slides" 
                :key="slide.id"
                class="relative w-full flex-shrink-0"
            >
                <div class="absolute inset-0">
                    <img :src="slide.image" :alt="slide.title" class="h-full w-full object-cover" />
                    <div class="absolute inset-0 bg-gradient-to-r from-slate-950/80 via-slate-900/55 to-slate-900/20"></div>
                </div>

                <div class="relative z-10 mx-auto flex min-h-screen max-w-6xl flex-col justify-center px-4 py-16 text-white sm:px-6">
                    <div class="max-w-2xl">
                        <p class="text-sm font-semibold uppercase tracking-[0.3em] text-emerald-300">Grass</p>
                        <h1 class="mt-4 text-4xl font-semibold leading-tight sm:text-5xl">
                            {{ slide.title }}
                        </h1>
                        <p class="mt-4 text-base text-slate-100/90 sm:text-lg">
                            {{ slide.description }}
                        </p>
                        
                        <!-- Botón -->
                        <div class="mt-8 flex flex-wrap items-center gap-3">
                            <a :href="slide.href"
                                class="inline-flex items-center gap-2 rounded-full bg-emerald-500 px-8 py-4 text-base font-bold text-slate-900 transition-all hover:bg-emerald-400 hover:scale-105 focus:outline-none">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                </svg>
                                {{ slide.cta }}
                            </a>
                        </div>
                    </div>

                    <!-- Controles de navegación -->
                    <div v-if="slide.id === activeSlide.id" class="mt-12 flex items-center gap-4">
                        
                        <button type="button"
                            class="group rounded-full border border-white/10 bg-white/5 px-4 py-2 text-xs font-medium uppercase tracking-wide text-white/50 backdrop-blur-sm transition-all hover:border-white/20 hover:bg-white/10 hover:text-white/70 focus:outline-none sm:px-5 sm:py-2.5"
                            @click="goPrev">
                            <span class="flex items-center gap-1.5">
                                <svg class="h-3.5 w-3.5 opacity-50 transition-all group-hover:opacity-80 group-hover:-translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                </svg>
                                Anterior
                            </span>
                        </button>

                        
                        <div class="flex items-center gap-2.5 rounded-full bg-black/40 px-4 py-2.5 backdrop-blur-md border border-emerald-500/20">
                            <button 
                                v-for="(s, index) in slides" 
                                :key="s.id" 
                                type="button"
                                class="h-3 w-10 rounded-full transition-all duration-300"
                                :class="index === current 
                                    ? 'bg-emerald-500 w-14' 
                                    : 'bg-white/30 hover:bg-white/50'" 
                                @click="goTo(index)"
                                :aria-label="`Ir a slide ${index + 1}`">
                            </button>
                        </div>

                        
                        <button type="button"
                            class="group rounded-full border border-white/10 bg-white/5 px-4 py-2 text-xs font-medium uppercase tracking-wide text-white/50 backdrop-blur-sm transition-all hover:border-white/20 hover:bg-white/10 hover:text-white/70 focus:outline-none sm:px-5 sm:py-2.5"
                            @click="goNext">
                            <span class="flex items-center gap-1.5">
                                Siguiente
                                <svg class="h-3.5 w-3.5 opacity-50 transition-all group-hover:opacity-80 group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Barra de progreso -->
        <div class="absolute bottom-0 left-0 z-50 h-1.5 w-full bg-black/30 backdrop-blur-sm">
            <div 
                class="h-full bg-emerald-500"
                :style="{ 
                    width: `${((Date.now() % 3000) / 3000) * 100}%`
                }"
            />
        </div>

        <!-- Indicador "desliza" para móvil -->
        <div class="absolute bottom-24 left-1/2 z-40 -translate-x-1/2 animate-bounce sm:hidden">
            <div class="rounded-full bg-black/40 p-2 backdrop-blur-sm">
                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                </svg>
            </div>
        </div>
    </section>
</template>

