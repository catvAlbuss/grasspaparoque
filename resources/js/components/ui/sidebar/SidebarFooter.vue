<script setup lang="ts">
import type { HTMLAttributes } from "vue"
import { cn } from "@/lib/utils"
import { LogOut } from 'lucide-vue-next'
import { router } from '@inertiajs/vue3'

const props = defineProps<{
  class?: HTMLAttributes["class"]
  expanded?: boolean
}>()

// Función para cerrar sesión
const logout = () => {
  router.post('/logout'); // O usa route('logout') si tienes la ruta nombrada
}
</script>

<template>
  <div
    data-slot="sidebar-footer"
    data-sidebar="footer"
    :class="cn('border-t border-zinc-800', props.class)"
  >
    <!-- Versión expandida -->
    <div v-if="expanded" class="p-4">
      <div class="flex items-center gap-3">
        <!-- Avatar del usuario -->
        <div class="w-10 h-10 rounded-full bg-zinc-800 flex items-center justify-center">
          <span class="text-green-400 font-bold text-sm">G</span>
        </div>
        <div class="flex-1">
          <p class="text-sm font-medium text-white">Gerente</p>
          <p class="text-xs text-zinc-500">admin@grass.com</p>
        </div>
        <!-- Icono de logout con funcionalidad -->
        <button 
          @click="logout"
          class="p-2 hover:bg-zinc-800 rounded-lg transition"
          title="Cerrar sesión"
        >
          <LogOut class="w-4 h-4 text-zinc-400" />
        </button>
      </div>
    </div>

    <!-- Versión colapsada -->
    <div v-else class="p-3">
      <div class="flex flex-col items-center gap-3">
        <div class="w-10 h-10 rounded-full bg-zinc-800 flex items-center justify-center">
          <span class="text-green-400 font-bold text-sm">G</span>
        </div>
        <!-- Icono de logout con funcionalidad -->
        <button 
          @click="logout"
          class="p-2 hover:bg-zinc-800 rounded-lg transition"
          title="Cerrar sesión"
        >
          <LogOut class="w-4 h-4 text-zinc-400" />
        </button>
      </div>
    </div>
    <slot />
  </div>
</template>

