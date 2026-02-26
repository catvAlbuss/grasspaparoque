<!-- components/ui/sidebar/SidebarMenuItem.vue -->
<script setup lang="ts">
import type { HTMLAttributes } from "vue"
import { cn } from "@/lib/utils"
import { Link } from '@inertiajs/vue3'
import {
  LayoutDashboard,
  Users,
  Calendar,
  Package,
  DollarSign,
} from 'lucide-vue-next'

const props = defineProps<{
  class?: HTMLAttributes["class"]
  href?: string
  active?: boolean
  expanded?: boolean
  icon?: string  // 'dashboard', 'users', 'calendar', 'package', 'dollar'
}>()

const iconMap = {
  dashboard: LayoutDashboard,
  users: Users,
  calendar: Calendar,
  package: Package,
  dollar: DollarSign,
}
</script>

<template>
  <li
    data-slot="sidebar-menu-item"
    data-sidebar="menu-item"
    :class="cn('group/menu-item relative list-none', props.class)"
  >
    <Link
      v-if="href"
      :href="href"
      :class="cn(
        'flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm transition-colors',
        'text-zinc-300 hover:text-white hover:bg-zinc-800',
        active && 'bg-green-500/10 text-green-400 hover:bg-green-500/20',
        !expanded && 'justify-center px-2'
      )"
    >
      <component 
        v-if="icon"
        :is="iconMap[icon as keyof typeof iconMap]" 
        class="h-5 w-5 flex-shrink-0" 
      />
      <span v-if="expanded" class="flex-1">
        <slot />
      </span>
    </Link>
    
    <div
      v-else
      :class="cn(
        'flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm transition-colors',
        'text-zinc-300',
        !expanded && 'justify-center px-2'
      )"
    >
      <component 
        v-if="icon"
        :is="iconMap[icon as keyof typeof iconMap]" 
        class="h-5 w-5 flex-shrink-0" 
      />
      <span v-if="expanded" class="flex-1">
        <slot />
      </span>
    </div>
  </li>
</template>