<script setup lang="ts">
import { ref } from 'vue'
// Importar iconos de Lucide (los más usados en dashboards profesionales)
import {
  Trophy,      // Para ⚽ (más popular)
  Goal,        // Para fútbol
  Volleyball,  // Para vóley
  PartyPopper, // Para eventos
  Star,
  CalendarCheck,
  Users,
  Medal,
  TrendingUp,
  Clock,
  MapPin,
  Phone
} from 'lucide-vue-next'

const hoveredCard = ref(null)

const services = {
    futbol: {
        title: 'FÚTBOL PRO',
        subtitle: 'CANCHA DE ALTO RENDIMIENTO',
        description: 'Vive la experiencia del fútbol profesional en nuestra cancha de césped sintético de última generación. Iluminación LED, medidas reglamentarias y ambiente exclusivo para que disfrutes cada partido.',
        features: ['Césped FIFA Quality', 'Iluminación LED profesional', 'Vestuarios equipados', 'Snack bar'],
        icon: Goal,
        iconColor: 'text-green-400'
    },
    voley: {
        title: 'VÓLEY PLAY',
        subtitle: 'ARENA SINTÉTICA PREMIUM',
        description: 'El mejor escenario para tus partidos de vóley. Superficie de arena sintética que reduce el impacto y maximiza el rendimiento. Perfecto para torneos y entrenamientos.',
        features: ['Superficie antideslizante', 'Red profesional', 'Zona de descanso', 'Iluminación nocturna'],
        icon: Volleyball,
        iconColor: 'text-green-400'
    },
    eventos: {
        title: 'EVENTOS & TORNEOS',
        subtitle: 'ESPACIO VERSÁTIL',
        description: 'Organiza tu evento soñado en un espacio único. Campeonatos deportivos, eventos corporativos o celebraciones privadas con toda la versatilidad de nuestras instalaciones.',
        features: ['Capacidad para 200+ personas', 'Zona de catering', 'Sonido profesional', 'Estacionamiento privado'],
        icon: PartyPopper,
        iconColor: 'text-green-400'
    }
}
</script>

<template>
    <!-- SECCIÓN NUESTROS ESPACIOS - Fondo oscuro con efecto de desenfoque -->
    <section class="relative py-12 px-4 bg-gradient-to-b from-gray-900 via-gray-800 to-gray-900 overflow-hidden">

        <!-- EFECTOS DE FONDO (VERDES CON DESENFOQUE) -->
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute -top-40 -right-40 w-80 h-80 bg-green-900/20 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-emerald-900/20 rounded-full blur-3xl"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full h-96 bg-gradient-to-r from-transparent via-green-500/5 to-transparent blur-2xl"></div>
        </div>

        <!-- TÍTULO DE SECCIÓN (SIN SUBTÍTULO) -->
        <div class="relative z-10 text-center mb-12">
            <h2 class="text-4xl md:text-5xl font-bold mb-4">
                <span class="bg-clip-text text-transparent bg-gradient-to-r from-green-400 via-emerald-300 to-green-400 drop-shadow-lg">
                    NUESTROS ESPACIOS
                </span>
            </h2>
            <div class="w-24 h-1 bg-gradient-to-r from-green-500 to-emerald-500 mx-auto rounded-full"></div>
        </div>

        <!-- CONTENEDOR PRINCIPAL - Grid collage -->
        <div class="relative z-10 container mx-auto max-w-7xl">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 auto-rows-[400px]">

                <!-- ======================================== -->
                <!-- TARJETA DE FÚTBOL (GRANDE - OCUPA 2 FILAS) -->
                <!-- ======================================== -->
                <div class="relative lg:row-span-2 lg:col-span-1 group overflow-hidden rounded-3xl shadow-2xl shadow-black/50"
                     @mouseenter="hoveredCard = 'futbol'"
                     @mouseleave="hoveredCard = null">

                    <!-- Imagen de fondo -->
                    <div class="absolute inset-0 bg-[url('/img/futbol.webp')] bg-cover bg-center transition-transform duration-1000 group-hover:scale-110"></div>

                    <!-- Overlay gradiente oscuro -->
                    <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/60 to-black/30 group-hover:from-black/80 group-hover:via-black/50 transition-all duration-700"></div>

                    <!-- CONTENIDO DE LA TARJETA -->
                    <div class="absolute inset-0 flex flex-col justify-end p-8 text-white">

                        <!-- Badge "MÁS POPULAR" con icono Lucide -->
                        <div class="absolute top-6 left-6 bg-gradient-to-r from-green-600 to-emerald-800 px-4 py-2 rounded-full text-sm font-semibold shadow-lg transform -rotate-3 hover:rotate-0 transition-all flex items-center gap-2 border border-green-400/30">
                            <Trophy class="w-4 h-4" />
                            MÁS POPULAR
                        </div>

                        <!-- Icono flotante Lucide -->
                        <div class="absolute top-6 right-6 w-12 h-12 bg-black/40 backdrop-blur-md rounded-full flex items-center justify-center border border-green-500/30">
                            <component :is="services.futbol.icon" class="w-6 h-6 text-green-400" stroke-width="1.5" />
                        </div>

                        <!-- TÍTULO Y DESCRIPCIÓN -->
                        <div class="transform transition-transform duration-700 group-hover:-translate-y-4">
                            <h3 class="text-5xl md:text-6xl font-black mb-2 bg-clip-text text-transparent bg-gradient-to-r from-green-300 to-white">
                                {{ services.futbol.title }}
                            </h3>
                            <p class="text-sm uppercase tracking-wider text-green-300 mb-4 font-semibold">
                                {{ services.futbol.subtitle }}
                            </p>

                            <!-- DESCRIPCIÓN QUE APARECE EN HOVER -->
                            <div class="max-w-md opacity-0 group-hover:opacity-100 transition-all duration-700 transform translate-y-4 group-hover:translate-y-0">
                                <p class="text-gray-200 mb-4 text-sm leading-relaxed">
                                    {{ services.futbol.description }}
                                </p>

                                <!-- Características en grid con check Lucide -->
                                <div class="grid grid-cols-2 gap-2 mb-6">
                                    <div v-for="feature in services.futbol.features"
                                         :key="feature"
                                         class="flex items-center gap-2 text-xs bg-black/50 backdrop-blur-sm rounded-lg px-3 py-2 border border-green-500/30">
                                        <Trophy class="w-3 h-3 text-green-400" />
                                        <span class="text-gray-200">{{ feature }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- BOTÓN DE ACCIÓN -->
                        <button class="relative overflow-hidden group/btn mt-4 bg-gradient-to-r from-green-600 to-emerald-700 hover:from-green-700 hover:to-emerald-800 text-white px-8 py-3 rounded-xl font-semibold transition-all transform hover:scale-105 hover:shadow-2xl hover:shadow-green-500/30 w-fit border border-green-400/30 flex items-center gap-2">
                            <span class="relative z-10 flex items-center gap-2">
                                RESERVAR AHORA
                                <CalendarCheck class="w-4 h-4 transition-transform group-hover/btn:translate-x-1" />
                            </span>
                            <div class="absolute inset-0 bg-gradient-to-r from-white/20 to-transparent -translate-x-full group-hover/btn:translate-x-0 transition-transform duration-1000"></div>
                        </button>
                    </div>
                </div>

                <!-- ======================================== -->
                <!-- TARJETA DE VÓLEY (SUPERIOR DERECHA) -->
                <!-- ======================================== -->
                <div class="relative group overflow-hidden rounded-3xl shadow-2xl shadow-black/50 h-[400px]"
                     @mouseenter="hoveredCard = 'voley'"
                     @mouseleave="hoveredCard = null">

                    <div class="absolute inset-0 bg-[url('/img/voley-1.jpg')] bg-center bg-cover transition-transform duration-1000 group-hover:scale-110"></div>
                    <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/60 to-black/30"></div>

                    <div class="absolute inset-0 flex flex-col justify-end p-6">
                        <!-- Icono flotante Lucide -->
                        <div class="absolute top-4 right-4 w-10 h-10 bg-black/40 backdrop-blur-md rounded-full flex items-center justify-center border border-green-500/30">
                            <component :is="services.voley.icon" class="w-5 h-5 text-green-400" stroke-width="1.5" />
                        </div>

                        <h3 class="text-4xl font-black mb-1 text-white">{{ services.voley.title }}</h3>
                        <p class="text-xs uppercase tracking-wider text-green-300 mb-2">{{ services.voley.subtitle }}</p>

                        <div class="max-h-0 group-hover:max-h-40 overflow-hidden transition-all duration-700">
                            <p class="text-gray-200 text-sm mb-3">{{ services.voley.description }}</p>
                            <div class="flex flex-wrap gap-2 mb-4">
                                <span v-for="feature in services.voley.features.slice(0,2)"
                                      :key="feature"
                                      class="text-xs bg-black/50 backdrop-blur-sm px-2 py-1 rounded-full border border-green-500/30 flex items-center gap-1">
                                    <Medal class="w-3 h-3 text-green-400" />
                                    {{ feature }}
                                </span>
                            </div>
                            <button class="text-green-300 text-sm font-semibold flex items-center gap-1 hover:gap-2 transition-all">
                                Reservar <span>→</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- ======================================== -->
                <!-- TARJETA DE EVENTOS (INFERIOR DERECHA) -->
                <!-- ======================================== -->
                <div class="relative group overflow-hidden rounded-3xl shadow-2xl shadow-black/50 h-[400px]"
                     @mouseenter="hoveredCard = 'eventos'"
                     @mouseleave="hoveredCard = null">

                    <div class="absolute inset-0 bg-[url('/img/eventos.jpg')] bg-center bg-cover transition-transform duration-1000 group-hover:scale-110"></div>
                    <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/60 to-black/30"></div>

                    <div class="absolute inset-0 flex flex-col justify-end p-6">
                        <!-- Icono flotante Lucide -->
                        <div class="absolute top-4 right-4 w-10 h-10 bg-black/40 backdrop-blur-md rounded-full flex items-center justify-center border border-green-500/30">
                            <component :is="services.eventos.icon" class="w-5 h-5 text-green-400" stroke-width="1.5" />
                        </div>

                        <h3 class="text-4xl font-black mb-1 text-white">{{ services.eventos.title }}</h3>
                        <p class="text-xs uppercase tracking-wider text-green-300 mb-2">{{ services.eventos.subtitle }}</p>

                        <div class="max-h-0 group-hover:max-h-40 overflow-hidden transition-all duration-700">
                            <p class="text-gray-200 text-sm mb-3">{{ services.eventos.description }}</p>
                            <div class="flex flex-wrap gap-2 mb-4">
                                <span v-for="feature in services.eventos.features.slice(0,2)"
                                      :key="feature"
                                      class="text-xs bg-black/50 backdrop-blur-sm px-2 py-1 rounded-full border border-green-500/30 flex items-center gap-1">
                                    <Users class="w-3 h-3 text-green-400" />
                                    {{ feature }}
                                </span>
                            </div>
                            <button class="text-green-300 text-sm font-semibold flex items-center gap-1 hover:gap-2 transition-all">
                                Cotizar evento <span>→</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ======================================== -->
            <!-- ESTADÍSTICAS RÁPIDAS CON ICONOS LUCIOE -->
            <!-- ======================================== -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-8">
                <!-- Partidos jugados -->
                <div class="bg-black/40 backdrop-blur-sm rounded-2xl p-4 border border-green-500/20 text-center group hover:bg-black/60 transition-all">
                    <Goal class="w-8 h-8 mx-auto mb-2 text-green-400 group-hover:scale-110 transition-transform" stroke-width="1.5" />
                    <div class="text-2xl font-bold text-white">500+</div>
                    <div class="text-xs text-gray-400">Partidos jugados</div>
                </div>

                <!-- Torneos realizados -->
                <div class="bg-black/40 backdrop-blur-sm rounded-2xl p-4 border border-green-500/20 text-center group hover:bg-black/60 transition-all">
                    <Trophy class="w-8 h-8 mx-auto mb-2 text-green-400 group-hover:scale-110 transition-transform" stroke-width="1.5" />
                    <div class="text-2xl font-bold text-white">50+</div>
                    <div class="text-xs text-gray-400">Torneos realizados</div>
                </div>

                <!-- Eventos exitosos -->
                <div class="bg-black/40 backdrop-blur-sm rounded-2xl p-4 border border-green-500/20 text-center group hover:bg-black/60 transition-all">
                    <PartyPopper class="w-8 h-8 mx-auto mb-2 text-green-400 group-hover:scale-110 transition-transform" stroke-width="1.5" />
                    <div class="text-2xl font-bold text-white">100+</div>
                    <div class="text-xs text-gray-400">Eventos exitosos</div>
                </div>

                <!-- Calificación -->
                <div class="bg-black/40 backdrop-blur-sm rounded-2xl p-4 border border-green-500/20 text-center group hover:bg-black/60 transition-all">
                    <Star class="w-8 h-8 mx-auto mb-2 text-green-400 group-hover:scale-110 transition-transform" stroke-width="1.5" />
                    <div class="text-2xl font-bold text-white">4.9</div>
                    <div class="text-xs text-gray-400">Calificación</div>
                </div>
            </div>
        </div>
    </section>
</template>
