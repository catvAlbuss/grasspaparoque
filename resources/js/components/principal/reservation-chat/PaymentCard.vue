<script setup lang="ts">
defineProps<{
    total: number;
    advance: number;
    countdown: string;
    yapeNumber: string;
    yapeName: string;
    qrUrl: string;
}>();
</script>

<template>
    <section
        class="rounded-2xl border border-emerald-200 bg-gradient-to-br from-white to-emerald-50 p-4 text-xs text-slate-800 shadow-lg">
        <p class="font-semibold tracking-wide text-emerald-700 uppercase">
            Datos del adelanto
        </p>
        <dl class="mt-2 grid grid-cols-2 gap-y-1.5">
            <dt class="text-slate-500">Total</dt>
            <dd class="text-right font-semibold">S/ {{ total.toFixed(2) }}</dd>
            <dt class="text-slate-500">Adelanto 50%</dt>
            <dd class="text-right font-semibold text-emerald-700">
                S/ {{ advance.toFixed(2) }}
            </dd>
            <dt class="text-slate-500">Yape</dt>
            <dd class="text-right font-semibold">{{ yapeNumber }}</dd>
            <dt class="text-slate-500">Titular</dt>
            <dd class="text-right font-semibold">{{ yapeName }}</dd>
            <dt class="text-slate-500">Tiempo restante</dt>
            <dd class="text-right font-semibold">{{ countdown }}</dd>
        </dl>
        <img v-if="qrUrl" :src="qrUrl" alt="Código QR de Yape"
            class="mt-3 h-36 w-full rounded-xl border border-emerald-200 bg-white object-contain p-2" />
    </section>
</template>
