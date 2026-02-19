<script setup>
//   npm install @schedule-x/vue @schedule-x/calendar @schedule-x/theme-default temporal-polyfill
import { ScheduleXCalendar } from '@schedule-x/vue'
import {
    createCalendar,
    createViewDay,
    createViewMonthAgenda,
    createViewMonthGrid,
    createViewWeek,
} from '@schedule-x/calendar'
import '@schedule-x/theme-default/dist/index.css'
import 'temporal-polyfill/global'
import { createEventsServicePlugin } from '@schedule-x/events-service'
import { onMounted } from 'vue'

const eventsServicePlugin = createEventsServicePlugin()


const calendarApp = createCalendar({
    selectedDate: Temporal.PlainDate.from('2026-02-07'),
    locale: 'es-ES',
    views: [
        createViewDay(),
        createViewWeek(),
        createViewMonthGrid(),
        createViewMonthAgenda(),
    ],
    events: [],
},
    [eventsServicePlugin])

// AJUSTAR HORARIO
const timeZone = 'UTC'

const toZoned = (dateStr, timeStr) => {
    const hhmmss = timeStr.length === 5 ? `${timeStr}:00` : timeStr
    return Temporal.ZonedDateTime.from(`${dateStr}T${hhmmss}-00:00[${timeZone}]`)
}

onMounted(async () => {
    try {
        const response = await fetch('/reservations/busy', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
            },
        })

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`)
        }

        const data = await response.json()

        // data.forEach(r => {
        //     console.log(`Reserva ID: ${r.id}, Fecha: ${r.date}, Hora Inicio: ${r.start_time}, Hora Fin: ${r.end_time}`)
        // })

        if (Array.isArray(data)) {
            const eventos = data.map((r) => ({
                id: r.id.toString(),
                title: '🔴 OCUPADO',
                start: toZoned(r.date, r.start_time),
                end: toZoned(r.date, r.end_time),
            }))

            eventsServicePlugin.set(eventos)
            console.log('Reservaciones:', eventos)
        } else {
            console.error('La respuesta no es un array:', data)
        }
    } catch (error) {
        console.error('Error al cargar reservas:', error)
    }
})

</script>

<template>
    <section id="Calendario" class="mx-auto max-w-6xl px-4 py-6">
        <div
            class="relative bg-gradient-to-r from-emerald-300 via-emerald-700 to-emerald-300 justify-center rounded-xl shadow-md">

            <!-- Calendario Title  -->
            <div class="text-center">
                <h3 class="text-white fond-bold text-2xl py-2">Calendario</h3>
            </div>

            <!-- View Calendario -->
            <div class="bg-white rouded-lg p-4">
                <ScheduleXCalendar :calendar-app="calendarApp" />
            </div>
        </div>
    </section>
</template>

<style>
.sx-vue-calendar-wrapper {
    width: auto;
    max-width: 100vw;
    height: 800px;
    max-height: 90vh;
}
</style>