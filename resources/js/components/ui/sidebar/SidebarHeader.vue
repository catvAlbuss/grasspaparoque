<script setup lang="ts">
import type { HTMLAttributes } from "vue"
import { cn } from "@/lib/utils"
import { ChevronLeft, ChevronRight } from 'lucide-vue-next'

const props = defineProps<{
  class?: HTMLAttributes["class"]
  expanded?: boolean
}>()

const emit = defineEmits<{
  (e: 'toggle'): void
}>()
</script>

<template>
  <div class="flex items-center h-14 px-3 border-b border-zinc-800">
    <div class="flex items-center justify-between w-full">
      <div v-if="expanded" class="flex items-center gap-2">
        <div class="w-7 h-7 rounded-md bg-gradient-to-r from-green-500 to-emerald-600 flex items-center justify-center">
          <span class="text-white font-bold text-xs">GP</span>
        </div>
        <span class="text-sm font-medium text-white">Grass <span class="text-green-400">Paparoque</span></span>
      </div>
      <div v-else class="flex items-center justify-center w-full">
        <div class="w-7 h-7 rounded-md bg-gradient-to-r from-green-500 to-emerald-600 flex items-center justify-center">
          <span class="text-white font-bold text-xs">GP</span>
        </div>
      </div>
      <button class="p-1 hover:bg-zinc-800 rounded" @click="$emit('toggle')">
        <ChevronLeft v-if="expanded" class="w-4 h-4 text-zinc-400" />
        <ChevronRight v-else class="w-4 h-4 text-zinc-400" />
      </button>
    </div>
  </div>
</template>

