<script></script>

<template>
    <section class="">
        <!-- MAIN -->
        <div class="">
            <div class="h-screen grid grid-cols-1 md:grid-cols-2 my-4 ">

                <!-- FUTBOL -->
                <div class="relative w-full h-full bg-[url(/img/futbol.webp)] bg-cover bg-center overflow-hidden">
                    <!-- FUTBOL BODY -->
                    <div
                        class="absolute md:inset-y-0 md:left-0 flex w-60 flex-col items-center md:items-start justify-center bg-gradient-to-t from-transparent via-[#000000] via-[50%] md:bg-gradient-to-r md:from-[#000000] md:from-90% text-xl p-2 text-white text-left">
                        <h2 class="text-5xl p-2">FÚTBOL</h2>
                        <p class="hidden md:block">Cancha de grass sintético de última generación para fútbol, con
                            estacionamiento y snacks y bebidas para recargar energías. ¡Juega como profesional!</p>
                    </div>
                    <!-- FUTBOL FOOTER -->
                    <div class="absolute inset-x-0 bottom-0 flex flex-col items-center justify-end my-2 mx-2 ">
                        <button
                            class="border border-black py-2 px-2 rounded-xl bg-white text-xl border-[0px] hover:bg-[#210df5] hover:text-white hover:scale-105 transition duration-[1s,3s]">Reservar
                            👇</button>
                    </div>
                </div>

                <!-- VOLEY -->
                <div class="relative w-full h-full bg-[url(/img/voley-1.jpg)] bg-center bg-cover overflow-hidden ">
                    <!-- VOLEY BODY -->
                    <div
                        class="absolute md:inset-y-0 md:right-0 w-60 flex flex-col items-center md:items-end justify-center bg-gradient-to-t from-transparent via-[#000000] via-[50%] md:bg-gradient-to-l md:from-[#000000] md:from-90% p-2 text-xl text-white text-right">
                        <h2 class="text-5xl p-2">VÓLEY</h2>
                        <p class="hidden md:block">Espacio ideal para vóley en grass sintético moderno, con
                            estacionamiento y tienda de snacks y bebidas. ¡Disfruta cada punto!</p>
                    </div>
                    <!-- VOLEY FOOTER -->
                    <div class="absolute inset-x-0 bottom-0 flex flex-col justify-end items-center my-2 mx-2 ">
                        <button
                            class="border border-black py-2 px-2 rounded-xl bg-white text-xl border-[0px] hover:bg-[#210df5] hover:text-white hover:scale-105 transition duration-[1s,3s]">Reservar
                            👇</button>
                    </div>
                </div>

                <!-- EVENTOS -->
                <div
                    class="relative md:col-span-2 w-full h-full bg-[url(/img/eventos.jpg)] bg-center bg-cover overflow-hidden">
                    <!-- EVENTOS BODY -->
                    <div
                        class="absolute md:inset-28 flex flex-col items-center justify-center p-2 text-xl text-white text-center bg-gradient-to-t from-transparent via-[#000000] via-[50%] to-transparent md:bg-gradient-to-r md:from-transparent md:via-[#000000] md:via-[50%] md:to-transparent">
                        <h2 class="text-5xl p-2">EVENTOS</h2>
                        <p class="hidden md:block">Espacio ideal para eventos corporativos y campeonatos, con cancha de
                            grass sintético, estacionamiento y tienda de snacks y bebidas. ¡Organiza tu evento con
                            nosotros!</p>
                    </div>
                    <!-- EVENTOS FOOTER -->
                    <div class="absolute inset-x-0 -bottom-0 flex flex-col items-center justify-end my-2 mx-2 ">
                        <button
                            class="border border-black py-2 px-2 rounded-xl bg-white text-xl border-[0px] hover:bg-[#210df5] hover:text-white hover:scale-105 transition duration-[1s,3s]">Reservar
                            👇</button>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<style></style>