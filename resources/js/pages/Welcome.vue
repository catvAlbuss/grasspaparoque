<script setup lang="ts">
// import { Head, Link } from '@inertiajs/vue3';
// import { dashboard, login, register } from '@/routes';
import Info from '@/components/principal/info.vue';
import Calendar from '@/components/principal/calendar.vue';
import Services1 from '@/components/principal/services-1.vue';
import Carrusel from '@/components/principal/carrusel.vue';
import Form1 from '@/components/principal/form-1.vue';
import Navbar from '@/components/principal/navbar.vue';
import reservations from '@/routes/reservations';
import eventos from '@/routes/eventos';
</script>

<template>
    
    <Navbar />
    <Info />
    <Calendar />
    <Services1 />
    <Carrusel />
    <Form1 :reservations="reservations" :eventos="eventos"/>

</template>
