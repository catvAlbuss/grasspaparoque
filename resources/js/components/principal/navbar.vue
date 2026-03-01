<script setup lang="ts">
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3';
import { dashboard, login } from '@/routes';

const menuOpen = ref(false)

const links = [
  { label: 'Inicio', to: { path: '/', hash: '#home' } },
  { label: 'Servicios', to: { path: '/', hash: '#services' } },
  { label: 'Contacto', to: { path: '/', hash: '#contact' } },
]
</script>

<template>
  <header class="sticky top-0 z-50 w-full border-b border-zinc-800 bg-zinc-900/95 backdrop-blur-md">
    <nav class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">

      <!-- Logo -->
      <a href="#home" class="group flex items-center gap-2">
        <span class="text-xl font-bold text-white tracking-tight">Grass Papa Roque</span>
      </a>

      <!-- Navegador Desktop -->
      <div class="hidden items-center gap-1 md:flex">
        <RouterLink v-for="link in links" :key="link.label" :to="link.to"
          class="rounded-lg px-4 py-2 text-sm font-medium text-zinc-300 transition-all hover:bg-zinc-800 hover:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/50">
          {{ link.label }}
        </RouterLink>
      </div>

      <!-- Botones de autenticación Desktop -->
      <div class="hidden items-center gap-3 md:flex">
        <Link v-if="$page.props.auth.user" :href="dashboard()"
          class="inline-flex items-center gap-2 rounded-lg bg-emerald-500 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-emerald-500/30 transition-all hover:bg-emerald-600 hover:scale-105 focus:outline-none focus:ring-2 focus:ring-emerald-500/50">
          <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
          </svg>
          Panel de Control
        </Link>

        <template v-else>
          <Link :href="login()"
            class="inline-flex items-center rounded-lg border-2 border-white/20 bg-transparent px-4 py-2 text-sm font-semibold text-white transition-all hover:border-white/40 hover:bg-white/10 hover:scale-105 focus:outline-none focus:ring-2 focus:ring-emerald-500/50">
            Iniciar Session
          </Link>
        </template>
      </div>

      <!-- Boton de menú móvil -->
      <button type="button"
        class="inline-flex items-center justify-center rounded-lg bg-zinc-800 p-2 text-white transition-all hover:bg-zinc-700 md:hidden"
        @click="menuOpen = !menuOpen" :aria-expanded="menuOpen ? 'true' : 'false'" aria-label="Abrir menú">
        <svg v-if="!menuOpen" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
        <svg v-else class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </nav>

    <!-- Mobile Menu -->
    <div class="border-t border-zinc-800 bg-zinc-900 md:hidden" :class="menuOpen ? 'block' : 'hidden'">
      <div class="space-y-2 px-4 py-4 sm:px-6">
        <!-- Links de navegación móvil -->
        <RouterLink v-for="link in links" :key="link.label" :to="link.to"
          class="block rounded-lg px-4 py-3 text-base font-medium text-zinc-300 transition-all hover:bg-zinc-800 hover:text-white"
          @click="menuOpen = false">
          {{ link.label }}
        </RouterLink>

        <!-- Separador -->
        <div class="border-t border-zinc-800 pt-3 mt-3"></div>

        <!-- Botones de autenticación móvil -->
        <div class="space-y-2">
          <Link v-if="$page.props.auth.user" :href="dashboard()"
            class="flex items-center gap-2 rounded-lg bg-emerald-500 px-4 py-3 text-base font-semibold text-white transition-all hover:bg-emerald-600"
            @click="menuOpen = false">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
            </svg>
            Iniciar Session
          </Link>

          <Link v-else :href="login()"
            class="flex items-center rounded-lg border-2 border-white/20 bg-transparent px-4 py-3 text-base font-semibold text-white transition-all hover:bg-white/10"
            @click="menuOpen = false">
            Iniciar Session
          </Link>
        </div>
      </div>
    </div>
  </header>
</template>

<style scoped>
/* Estilos adicionales si son necesarios */
.backdrop-blur-md {
  backdrop-filter: blur(12px);
}
</style>
