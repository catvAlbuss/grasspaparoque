<script setup lang="ts">
// npm install @schedule-x/vue @schedule-x/calendar @schedule-x/theme-default temporal-polyfill
import { ScheduleXCalendar } from '@schedule-x/vue'
import {
    createCalendar,
    createViewDay,
    createViewWeek,
    createViewMonthGrid,
    createViewMonthAgenda,
} from '@schedule-x/calendar'
import '@schedule-x/theme-default/dist/index.css'
import 'temporal-polyfill/global'
import { createEventsServicePlugin } from '@schedule-x/events-service'
import { onMounted } from 'vue'

// CREAR EL PLUGIN PARA MANEJAR LOS EVENTOS
const eventsServicePlugin = createEventsServicePlugin()
// Obtener fecha actual para mostrar el mes por defecto
const today = new Date()
const currentPlainDate = Temporal.PlainDate.from(
    `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`
)

// CREAR LA INSTANCIA DEL CALENDARIO
const calendarApp = createCalendar({
    selectedDate: currentPlainDate,
    defaultView: 'month-grid',
    locale: 'es-ES',
    views: [
        createViewDay(),
        createViewWeek(),
        createViewMonthGrid(),
        createViewMonthAgenda(),
    ],
    events: [],
}, [eventsServicePlugin])

const timeZone = 'UTC'

const toZoned = (dateStr, timeStr) => {
    const hhmmss = timeStr.length === 5 ? `${timeStr}:00` : timeStr
    return Temporal.ZonedDateTime.from(`${dateStr}T${hhmmss}+00:00[${timeZone}]`)
}

onMounted(async () => {
    try {
        const response = await fetch('/reservations/busy', {
            method: 'GET',
            headers: { 'Content-Type': 'application/json' },
        })

        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`)

        const data = await response.json()

        if (Array.isArray(data)) {
            const eventos = data.map((r) => ({
                id: r.id.toString(),
                title: '🔴 OCUPADO',
                start: toZoned(r.date, r.start_time),
                end: toZoned(r.date, r.end_time),
            }))
            eventsServicePlugin.set(eventos)
        }
    } catch (error) {
        console.error('Error al cargar reservas:', error)
    }
})
</script>


<template>
    <section id="Calendario" class="mx-auto w-full px-4 py-6">

        <!-- Contenedor principal -->
        <div class="relative overflow-hidden rounded-2xl border border-emerald-500/20 bg-gradient-to-br from-zinc-900 via-zinc-900 to-emerald-900/20 p-1 shadow-xl shadow-emerald-500/10">

            <!-- Glow efectos -->
            <div class="pointer-events-none absolute -top-20 -right-20 h-64 w-64 rounded-full bg-emerald-500/10 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-20 -left-20 h-64 w-64 rounded-full bg-green-500/10 blur-3xl"></div>

            <!-- Header -->
            <div class="relative z-10 rounded-t-xl bg-zinc-900/80 px-8 py-4 backdrop-blur-sm border-b border-zinc-800">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-500/20 border border-emerald-500/30">
                        <svg class="h-5 w-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-bold text-white">Calendario de Reservas</h3>
                    </div>
                    <div class="hidden sm:flex items-center gap-2 rounded-full border border-emerald-500/30 bg-emerald-500/10 px-3 py-1.5">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                        </span>
                        <span class="text-xs font-medium text-emerald-400">En tiempo real</span>
                    </div>
                </div>
            </div>

            <!-- Contenedor del Calendario -->
            <div class="relative z-10 rounded-b-xl bg-zinc-900/50 px-8 py-6 backdrop-blur-sm">
                <div class="overflow-hidden rounded-lg border border-zinc-800 bg-zinc-900"
                     style="height: 470px;">
                    <ScheduleXCalendar :calendar-app="calendarApp"
                                       class="h-full w-full" />
                </div>
            </div>

            <!-- Estados de disponibilidad -->
            <div class="relative z-10 mt-4 flex flex-wrap items-center justify-center gap-6 px-8 pb-4">
                <div class="flex items-center gap-2">
                    <span class="h-3 w-3 rounded-full bg-red-500"></span>
                    <span class="text-xs text-zinc-400">Ocupado</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="h-3 w-3 rounded-full bg-emerald-500"></span>
                    <span class="text-xs text-zinc-400">Disponible</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="h-3 w-3 rounded-full bg-amber-500"></span>
                    <span class="text-xs text-zinc-400">Pendiente</span>
                </div>
            </div>
        </div>
    </section>
</template>



