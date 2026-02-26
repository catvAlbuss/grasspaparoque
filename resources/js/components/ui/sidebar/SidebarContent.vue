<script setup lang="ts">
import type { HTMLAttributes } from "vue"
import { cn } from "@/lib/utils"
import { Link, usePage } from '@inertiajs/vue3'
import {
  LayoutDashboard,
  Users,
  Calendar,
  Package,
  DollarSign,
  Book,
} from 'lucide-vue-next'

const props = defineProps<{
  class?: HTMLAttributes["class"]
  expanded?: boolean
}>()

const page = usePage()
const currentUrl = page.url

const menuItems = [
  { 
    name: 'Panel', 
    icon: LayoutDashboard, 
    path: '/dashboard',
    active: currentUrl === '/dashboard' || currentUrl === '/'
  },
  { 
    name: 'Usuarios', 
    icon: Users, 
    path: '/users',
    active: currentUrl.startsWith('/users')
  },
   { 
    name: 'Reservas', 
    icon: Book, 
    path: '/reservations',
    active: currentUrl.startsWith('/reservations')
  },
  { 
    name: 'Eventos', 
    icon: Calendar, 
    path: '/eventos',
    active: currentUrl.startsWith('/eventos')
  },
  { 
    name: 'Productos', 
    icon: Package, 
    path: '/products',
    active: currentUrl.startsWith('/products')
  },
  { 
    name: 'Caja', 
    icon: DollarSign, 
    path: '/box',
    active: currentUrl.startsWith('/box')
  },
]
</script>

<template>
  <div
    data-slot="sidebar-content"
    data-sidebar="content"
    :class="cn('flex-1 overflow-y-auto py-4 px-2', props.class)"
  >
    <Link
      v-for="item in menuItems"
      :key="item.name"
      :href="item.path"
      class="flex items-center gap-3 px-2 py-2 text-zinc-300 hover:text-white hover:bg-zinc-800 rounded-md transition-colors mb-1 group"
      :class="{ 
        'bg-green-500/10 text-green-400 hover:bg-green-500/15': item.active,
        'justify-center': !expanded
      }"
    >
      <component 
        :is="item.icon" 
        class="w-5 h-5 flex-shrink-0"
        :class="item.active ? 'text-green-400' : 'text-zinc-500 group-hover:text-white'"
      />
      <span v-if="expanded" class="text-sm font-medium">{{ item.name }}</span>
      <span v-else class="sr-only">{{ item.name }}</span>
      
      <!-- Indicador activo (puntito verde) -->
      <span 
        v-if="item.active && expanded" 
        class="w-1.5 h-1.5 rounded-full bg-green-400 ml-auto"
      />
    </Link>
    <slot />
  </div>
</template>

