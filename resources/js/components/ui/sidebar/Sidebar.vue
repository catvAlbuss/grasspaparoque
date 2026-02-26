<!-- resources/js/components/ui/sidebar/Sidebar.vue -->
<script setup lang="ts">
import type { SidebarProps } from "."
import { cn } from "@/lib/utils"
import { Sheet, SheetContent } from '@/components/ui/sheet'
import SheetDescription from '@/components/ui/sheet/SheetDescription.vue'
import SheetHeader from '@/components/ui/sheet/SheetHeader.vue'
import SheetTitle from '@/components/ui/sheet/SheetTitle.vue'
import { SIDEBAR_WIDTH_MOBILE, useSidebar } from "./utils"
import SidebarHeader from './SidebarHeader.vue'
import SidebarContent from './SidebarContent.vue'
import SidebarFooter from './SidebarFooter.vue'

defineOptions({
  inheritAttrs: false,
})

const props = withDefaults(defineProps<SidebarProps>(), {
  side: "left",
  variant: "sidebar",
  collapsible: "offcanvas",
})

const { isMobile, state, openMobile, setOpenMobile } = useSidebar()
</script>

<template>
  <!-- Versión móvil -->
  <Sheet v-if="isMobile" :open="openMobile" v-bind="$attrs" @update:open="setOpenMobile">
    <SheetContent
      data-sidebar="sidebar"
      data-slot="sidebar"
      data-mobile="true"
      :side="side"
      class="bg-zinc-900 border-zinc-800 w-[280px] p-0 [&>button]:hidden"
    >
      <SheetHeader class="sr-only">
        <SheetTitle>Sidebar</SheetTitle>
        <SheetDescription>Displays the mobile sidebar.</SheetDescription>
      </SheetHeader>
      
      <div class="flex h-full w-full flex-col">
        <SidebarHeader :expanded="true" />
        <SidebarContent :expanded="true" />
        <SidebarFooter :expanded="true" />
      </div>
    </SheetContent>
  </Sheet>

  <!-- Versión escritorio -->
  <div
    v-else
    class="group peer hidden md:block"
    data-slot="sidebar"
    :data-state="state"
    :data-collapsible="state === 'collapsed' ? collapsible : ''"
    :data-variant="variant"
    :data-side="side"
  >
    <!-- Espaciador -->
    <div
      :class="cn(
        'relative bg-transparent transition-[width] duration-200 ease-linear',
        state === 'expanded' ? 'w-52' : 'w-20'
      )"
    />

    <!-- Sidebar principal -->
    <div
      :class="cn(
        'fixed inset-y-0 left-0 z-10 hidden h-svh transition-[left,right,width] duration-200 ease-linear md:flex bg-zinc-900 border-r border-zinc-800',
        state === 'expanded' ? 'w-52' : 'w-20',
        props.class,
      )"
      v-bind="$attrs"
    >
      <div class="flex h-full w-full flex-col">
        <SidebarHeader :expanded="state === 'expanded'" @toggle="setOpenMobile" />
        <SidebarContent :expanded="state === 'expanded'" />
        <SidebarFooter :expanded="state === 'expanded'" />
      </div>
    </div>
  </div>
</template>