<script setup lang="ts">
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
        <!-- Badge de numero de slide -->
        <div class="absolute right-6 top-20 z-50 rounded-full border border-emerald-500/30 bg-black/60 px-4 py-2 text-sm font-bold text-white backdrop-blur-md md:top-6">
            {{ current + 1 }} <span class="text-emerald-500">/</span> {{ total }}
        </div>

        <!-- Slide Container -->
        <div class="flex h-full min-h-screen transition-transform duration-700 ease-in-out" :style="{ transform: `translateX(-${current * 100}%)` }">
            <div v-for="slide in slides" :key="slide.id" class="relative w-full flex-shrink-0">
                <div class="absolute inset-0">
                    <img :src="slide.image" :alt="slide.title" class="h-full w-full object-cover" />
                    <div class="absolute inset-0 bg-gradient-to-r from-slate-950/80 via-slate-900/55 to-slate-900/20"></div>
                </div>

                <div class="relative z-10 mx-auto flex min-h-screen max-w-6xl flex-col justify-center overflow-hidden px-4 pb-24 pt-28 text-white sm:px-6 sm:pb-20 sm:pt-32">
                    <div class="w-full max-w-2xl">
                        <p class="text-sm font-semibold uppercase tracking-[0.3em] text-emerald-300">Grass</p>
                        <h1 class="mt-4 break-words text-3xl font-semibold leading-tight sm:text-4xl lg:text-5xl">
                            {{ slide.title }}
                        </h1>
                        <p class="mt-4 max-w-prose text-sm text-slate-100/90 sm:text-lg">
                            {{ slide.description }}
                        </p>

                        <div class="mt-8 flex flex-wrap items-center gap-3">
                            <a :href="slide.href" class="inline-flex items-center gap-2 rounded-full bg-emerald-500 px-8 py-4 text-base font-bold text-slate-900 transition-all hover:scale-105 hover:bg-emerald-400 focus:outline-none">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                                {{ slide.cta }}
                            </a>
                        </div>
                    </div>

                    <!-- Controles movil -->
                    <div v-if="slide.id === activeSlide.id" class="mt-10 flex items-center gap-3 md:hidden">
                        <button type="button" class="rounded-full border border-white/20 bg-black/35 p-2 text-white/80 backdrop-blur-sm transition hover:bg-black/50" @click="goPrev" aria-label="Slide anterior">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                        <div class="flex items-center gap-2 rounded-full border border-emerald-500/30 bg-black/40 px-3 py-2 backdrop-blur-md">
                            <button v-for="(s, index) in slides" :key="s.id" type="button" class="h-2.5 rounded-full transition-all duration-300" :class="index === current ? 'w-8 bg-emerald-500' : 'w-2.5 bg-white/35 hover:bg-white/60'" @click="goTo(index)" :aria-label="`Ir a slide ${index + 1}`" />
                        </div>
                        <button type="button" class="rounded-full border border-white/20 bg-black/35 p-2 text-white/80 backdrop-blur-sm transition hover:bg-black/50" @click="goNext" aria-label="Siguiente slide">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar de controles desktop -->
        <aside class="pointer-events-none absolute right-4 top-1/2 z-40 hidden -translate-y-1/2 md:block lg:right-8">
            <div class="pointer-events-auto flex flex-col items-center gap-3 rounded-2xl border border-white/15 bg-black/30 px-3 py-4 backdrop-blur-md">
                <button type="button" class="rounded-full border border-white/20 bg-black/25 p-2 text-white/80 transition hover:bg-black/45" @click="goPrev" aria-label="Slide anterior">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>

                <div class="flex flex-col items-center gap-2">
                    <button v-for="(s, index) in slides" :key="`desk-${s.id}`" type="button" class="w-2.5 rounded-full transition-all duration-300" :class="index === current ? 'h-8 bg-emerald-500' : 'h-2.5 bg-white/35 hover:bg-white/60'" @click="goTo(index)" :aria-label="`Ir a slide ${index + 1}`" />
                </div>

                <button type="button" class="rounded-full border border-white/20 bg-black/25 p-2 text-white/80 transition hover:bg-black/45" @click="goNext" aria-label="Siguiente slide">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>
        </aside>

        <!-- Barra de progreso -->
        <div class="absolute bottom-0 left-0 z-50 h-1.5 w-full bg-black/30 backdrop-blur-sm">
            <div class="h-full bg-emerald-500" :style="{ width: `${((Date.now() % 3000) / 3000) * 100}%` }" />
        </div>

        <!-- Indicador para movil -->
        <div class="absolute bottom-24 left-1/2 z-40 -translate-x-1/2 animate-bounce sm:hidden">
            <div class="rounded-full bg-black/40 p-2 backdrop-blur-sm">
                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                </svg>
            </div>
        </div>
    </section>
</template>
