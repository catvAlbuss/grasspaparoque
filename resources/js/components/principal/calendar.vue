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

const calendarApp = createCalendar({
    selectedDate: Temporal.PlainDate.from('2026-02-07'),
    locale: 'es-ES',
    views: [
        createViewDay(),
        createViewWeek(),
        createViewMonthGrid(),
        createViewMonthAgenda(),
    ],
    events: [
        {
            id: 1,
            title: 'Futbol',
            start: Temporal.ZonedDateTime.from('2026-02-05T08:00:00-05:00[America/Lima]'),
            end: Temporal.ZonedDateTime.from('2026-02-05T09:00:00-05:00[America/Lima]'),
        },
        {
            id: 2,
            title: 'Evento 2',
            start: Temporal.PlainDate.from('2026-02-07'),
            end: Temporal.PlainDate.from('2026-02-08'),
        },
        {
            id: 3,
            title: 'Futbol',
            start: Temporal.ZonedDateTime.from('2026-02-05T00:00:00-05:00[America/Lima]'),
            end: Temporal.ZonedDateTime.from('2026-02-05T01:00:00-05:00[America/Lima]'),
        },
    ],
})


</script>

<template>
    <section id="Calendario" class="mx-auto max-w-6xl px-4 py-6">
        <div class="relative bg-gradient-to-r from-emerald-300 via-emerald-700 to-emerald-300 justify-center rounded-xl shadow-md">

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