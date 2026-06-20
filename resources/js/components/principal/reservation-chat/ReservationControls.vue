<script setup lang="ts">
import { computed, ref } from 'vue';
import type { ReservationStep } from './types';

const props = defineProps<{
    step: ReservationStep;
    disabled: boolean;
    typing: boolean;
    minDate: string;
    canUpload: boolean;
}>();
const emit = defineEmits<{
    submit: [value: string];
    date: [value: string];
    duration: [value: number];
    upload: [];
    ask: [value: string];
    reset: [];
}>();

const value = ref('');
const question = ref('');
const asking = ref(false);
const textSteps: ReservationStep[] = [
    'name',
    'lastname',
    'dni',
    'phone',
    'voucher_number',
];
const showTextInput = computed(() => textSteps.includes(props.step));
const placeholders: Partial<Record<ReservationStep, string>> = {
    name: 'Tus nombres',
    lastname: 'Tus apellidos',
    dni: 'DNI de 8 dígitos',
    phone: 'Celular de 9 dígitos',
    voucher_number: 'Número de operación',
};

function submit() {
    const clean = value.value.trim();
    if (!clean) return;
    emit('submit', clean);
    value.value = '';
}

function ask() {
    const clean = question.value.trim();
    if (!clean) return;
    emit('ask', clean);
    question.value = '';
    asking.value = false;
}
</script>

<template>
    <div v-if="step === 'date'" class="space-y-2">
        <label
            for="reservation-date"
            class="block text-xs font-medium text-emerald-100"
            >Selecciona el día de tu reserva</label
        >
        <input
            id="reservation-date"
            type="date"
            :min="minDate"
            :disabled="disabled || typing"
            class="w-full rounded-xl border border-emerald-700 bg-slate-900 px-3 py-2 text-sm text-white [color-scheme:dark]"
            @change="emit('date', ($event.target as HTMLInputElement).value)"
        />
    </div>

    <div v-else-if="step === 'duration'" class="space-y-2">
        <p class="text-xs font-medium text-emerald-100">
            ¿Cuánto tiempo deseas reservar?
        </p>
        <div class="grid grid-cols-4 gap-2">
            <button
                v-for="hours in 8"
                :key="hours"
                type="button"
                :disabled="disabled || typing"
                class="rounded-xl border border-emerald-700 bg-slate-900 py-2 text-xs text-white hover:bg-emerald-900 disabled:opacity-50"
                @click="emit('duration', hours)"
            >
                {{ hours }} h
            </button>
        </div>
    </div>

    <div v-else-if="showTextInput" class="flex gap-2">
        <input
            v-model="value"
            :disabled="disabled || typing"
            type="text"
            autocomplete="off"
            class="min-w-0 flex-1 rounded-xl border border-emerald-800 bg-slate-900 px-3 py-2 text-sm text-white placeholder-emerald-300 outline-none focus:ring-2 focus:ring-emerald-500"
            :placeholder="placeholders[step]"
            @keydown.enter="submit"
        />
        <button
            type="button"
            :disabled="disabled || typing || !value.trim()"
            class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white disabled:opacity-50"
            @click="submit"
        >
            Enviar
        </button>
    </div>

    <button
        v-if="canUpload"
        type="button"
        class="w-full rounded-xl bg-emerald-600 px-3 py-2.5 text-xs font-semibold text-white"
        @click="emit('upload')"
    >
        Subir imagen del comprobante
    </button>

    <div v-if="asking" class="flex gap-2">
        <input
            v-model="question"
            type="text"
            placeholder="Escribe tu pregunta"
            class="min-w-0 flex-1 rounded-xl border border-emerald-800 bg-slate-900 px-3 py-2 text-sm text-white"
            @keydown.enter="ask"
        />
        <button
            type="button"
            class="rounded-xl bg-sky-600 px-3 text-xs font-semibold text-white"
            @click="ask"
        >
            Preguntar
        </button>
    </div>
    <div class="flex justify-between gap-3 text-xs">
        <button
            type="button"
            class="text-sky-200 underline underline-offset-2"
            @click="asking = !asking"
        >
            ¿Necesitas ayuda?
        </button>
        <button
            type="button"
            class="text-emerald-200 underline underline-offset-2"
            @click="emit('reset')"
        >
            Empezar de nuevo
        </button>
    </div>
</template>
