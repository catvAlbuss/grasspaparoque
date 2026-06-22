<script setup lang="ts">
import type { ChatMessage, ChatOption } from './types';

defineProps<{
    messages: ChatMessage[];
    options: ChatOption[];
    typing: boolean;
}>();
const emit = defineEmits<{ select: [option: ChatOption] }>();
</script>

<template>
    <div v-for="(message, index) in messages" :key="`${index}-${message.time}`" class="flex"
        :class="message.role === 'user' ? 'justify-end' : 'justify-start'">
        <div class="max-w-[88%] rounded-2xl px-3 py-2 shadow-sm" :class="message.role === 'user'
                ? 'rounded-tr-sm bg-emerald-600 text-white'
                : 'rounded-tl-sm bg-white text-slate-800'
            ">
            <p class="leading-relaxed whitespace-pre-line">
                {{ message.text }}
            </p>
            <p class="mt-1 text-right text-[10px]" :class="message.role === 'user'
                    ? 'text-emerald-100'
                    : 'text-slate-400'
                ">
                {{ message.time }}
            </p>
        </div>
    </div>

    <div v-if="typing" class="flex items-center gap-1 text-xs text-emerald-300" aria-live="polite">
        <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-emerald-300" />
        Preparando respuesta…
    </div>

    <div v-if="options.length && !typing" class="grid grid-cols-2 gap-2">
        <button v-for="option in options" :key="option.value" type="button"
            class="rounded-xl border border-emerald-300 bg-white px-3 py-2.5 text-xs font-semibold text-emerald-800 transition hover:bg-emerald-50 focus:ring-2 focus:ring-emerald-400 focus:outline-none"
            @click="emit('select', option)">
            {{ option.label }}
        </button>
    </div>
</template>
