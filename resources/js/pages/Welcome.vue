<script setup lang="ts">
// import { Head, Link } from '@inertiajs/vue3';
// import { dashboard, login, register } from '@/routes';
import Info from '@/components/principal/info.vue';
import Calendar from '@/components/principal/calendar.vue';
import Services1 from '@/components/principal/services-1.vue';
import Carrusel from '@/components/principal/carrusel.vue';
import Form1 from '@/components/principal/form-1.vue';
import Navbar from '@/components/principal/navbar.vue';

// withDefaults(
//     defineProps<{
//         canRegister: boolean;
//     }>(),
//     {
//         canRegister: true,
//     },
// );
</script>

<template>
    
    <Navbar />
    <Info />
    <Calendar />
    <Services1 />
    <Carrusel />
    <Form1 />

    <!-- <Head title="Welcome">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head> -->

    <!-- <div
        class="flex min-h-screen flex-col items-center bg-white-50 p-6 text-[#1b1b18] lg:justify-center lg:p-8 dark:bg-[#0a0a0a]">
        <header class="mb-6 w-full max-w-[335px] text-sm not-has-[nav]:hidden lg:max-w-4xl">
            <nav class="flex items-center justify-end gap-4">

                
            </nav>
        </header>
    </div> -->

</template>
