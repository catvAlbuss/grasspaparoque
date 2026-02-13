<script setup>
import { computed, ref } from 'vue'

const slides = [
    {
        id: 1,
        title: 'Servicios que impulsan tu crecimiento',
        description: 'Estrategia, desarrollo y automatización en un solo equipo.',
        cta: 'Ver servicios',
        href: '#services',
        image:
            'https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?q=80&w=1600&auto=format&fit=crop',
    },
    {
        id: 2,
        title: 'Conversemos sobre tu proyecto',
        description: 'Agenda una llamada o déjanos un mensaje para iniciar.',
        cta: 'Ir a contacto',
        href: '#contact',
        image:
            'https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?q=80&w=1600&auto=format&fit=crop',
    },
    {
        id: 3,
        title: 'Soporte que responde a tiempo',
        description: 'Nuestro bot puede ayudarte a reservar en segundos.',
        cta: 'Reservar ahora',
        href: '#contact',
        image:
            'https://images.unsplash.com/photo-1521737604893-d14cc237f11d?q=80&w=1600&auto=format&fit=crop',
    },
]

const current = ref(0)
const total = slides.length

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
</script>

<template>
    <section id="home" class="relative min-h-screen w-full overflow-hidden">
        <div class="absolute inset-0">
            <img :src="activeSlide.image" :alt="activeSlide.title" class="h-full w-full object-cover" />
            <div class="absolute inset-0 bg-gradient-to-r from-slate-950/80 via-slate-900/55 to-slate-900/20"></div>
        </div>

        <div
            class="relative z-10 mx-auto flex min-h-screen max-w-6xl flex-col justify-center px-4 py-16 text-white sm:px-6">
            <div class="max-w-2xl">
                <p class="text-sm font-semibold uppercase tracking-[0.3em] text-emerald-300">Grasst</p>
                <h1 class="mt-4 text-4xl font-semibold leading-tight sm:text-5xl">
                    {{ activeSlide.title }}
                </h1>
                <p class="mt-4 text-base text-slate-100/90 sm:text-lg">
                    {{ activeSlide.description }}
                </p>
                <div class="mt-8 flex flex-wrap items-center gap-3">
                    <a :href="activeSlide.href"
                        class="rounded-full bg-emerald-400 px-6 py-3 text-sm font-semibold text-slate-900 transition hover:bg-emerald-300">
                        {{ activeSlide.cta }}
                    </a>
                    <button type="button"
                        class="rounded-full border border-white/30 px-5 py-3 text-sm font-semibold text-white/90 transition hover:bg-white/10"
                        @click="goNext">
                        Siguiente
                    </button>
                </div>
            </div>

            <div class="mt-10 flex items-center gap-3">
                <button type="button"
                    class="rounded-full border border-white/20 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white/80 transition hover:bg-white/10"
                    @click="goPrev">
                    Anterior
                </button>
                <div class="flex items-center gap-2">
                    <button v-for="(slide, index) in slides" :key="slide.id" type="button"
                        class="h-2 w-8 rounded-full transition"
                        :class="index === current ? 'bg-emerald-300' : 'bg-white/40'" @click="goTo(index)"
                        :aria-label="`Ir a slide ${index + 1}`"></button>
                </div>
                <button type="button"
                    class="rounded-full border border-white/20 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white/80 transition hover:bg-white/10"
                    @click="goNext">
                    Siguiente
                </button>
            </div>
        </div>
    </section>
</template>