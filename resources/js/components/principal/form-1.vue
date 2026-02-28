<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useForm } from '@inertiajs/vue3';

// DEFINIR EL TIPO DE EVENTO PARA LOS DATOS QUE SE TRAEN DESDE EL BACKEND
type Evento = {
    id: number;
    nombre: string;
    precio: number;
};

// DEFINIR EL TIPO DE RESERVACIÓN PARA LOS DATOS QUE SE ENVIAN AL BACKEND
type reservationData = {
    name: string;
    lastname: string;
    email: string;
    phone: string;

    id_evento: string | '';
    date: string;
    start_time: string;
    end_time: string;
};

// DEFINIR EL TIPO DE RESERVACIÓN PARA LOS DATOS QUE SE TRAEN DESDE EL BACKEND
type Reservation = {
    id: number;
    id_evento: string;
    nombre: string;
    apellido: string;
    correo: string;
    numero: string;
    tipo: string[];
    fecha: string;
    hora_inicio: string;
    hora_fin: string;
};

// DEFINIR LOS PROPS QUE SE RECIBEN DESDE EL PADRE
type props = {
    reservations: Reservation[];
    eventos: Evento[];
};

const eventsServiceprecie = ref<Evento[]>([]);
const props = defineProps<props>();

// TRAER LOS TIPOS DE RESERVA DESDE EL BACKEND
onMounted(async () => {
    try {
        const response = await fetch('/events/type_events', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
            },
        })

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`)
        }

        const data = await response.json();

        if (Array.isArray(data)) {
            const eventosprecio = data.map((r) => ({
                id: r.id,
                nombre: r.nombre,
                precio: r.precio,
            }))

            eventsServiceprecie.value = eventosprecio;
            console.log('Reservaciones:', eventosprecio)
        } else {
            console.error('La respuesta no es un array:', data)
        }
    } catch (error) {
        console.error('Error al cargar reservas:', error)
    }
})

console.log('Eventos cargados:', eventsServiceprecie)

const reservations = computed(() => props.eventos);

const isEditing = computed(() => !!reservations.value);

// USAR USEFORM PARA MANEJAR LOS DATOS DEL FORMULARIO
const formData = useForm<reservationData>({
    name: '',
    lastname: '',
    email: '',
    phone: '',
    id_evento: '',
    date: '',
    start_time: '',
    end_time: '',
});

const editingId = ref<number | null>(null);

// FUNCION PARA RESETEAR EL FORMULARIO DESPUES DE ENVIARLO O ACTUALIZARLO
const resetForm = (): void => {
    editingId.value = null;
    formData.reset();
    formData.clearErrors();
};

// PARA QUE VALIDE Y SALGA ERRORES EN EL FRONTEND, AUNQUE NO SE ENVÍE EL FORMULARIO
const errores = ref({
    name: '',
    lastname: '',
    email: '',
    phone: '',
    id_evento: '',
    date: '',
    start_time: '',
    end_time: '',
});

// VALIDAR EL FORMULARIO ANTES DE ENVIARLO
const validarFormulario = () => {
    errores.value = {
        name: '',
        lastname: '',
        email: '',
        phone: '',
        id_evento: '',
        date: '',
        start_time: '',
        end_time: '',
    };

    // if (!formData.name) errores.value.name = 'El nombre es obligatorio';
    // if (!formData.lastname) errores.value.lastname = 'El apellido es obligatorio';
    // if (!formData.email) errores.value.email = 'El correo es obligatorio';
    // if (!formData.phone) errores.value.phone = 'El número es obligatorio';
    // if (!formData.id_evento) errores.value.id_evento = 'El tipo de evento es obligatorio';
    // if (!formData.date) errores.value.date = 'La fecha es obligatorio';
    // if (!formData.start_time) errores.value.start_time = 'La hora de inicio es obligatorio';
    // if (!formData.end_time) errores.value.end_time = 'La hora de fin es obligatorio';

    return Object.keys(errores.value).length === 0;
}

// ENVIAR DATOS DEL FORMULARIO AL BACKEND
const submit = (): void => {

    // if (!validarFormulario()) {
    //     alert('Por favor, corrija los errores.');
    //     return;
    // }
    console.log('Datos del formulario:', formData);

    formData.post('/reservations/customers', {
        onSuccess: () => {
            alert('Reservación creada con éxito');
            resetForm();
        },
        onError: () => alert('Error al crear la reservación'),
    });
    
};

</script>


<template>
    <section>
        <!-- FORMULARIO -->
        <form @submit.prevent="submit">
            <!-- MAIN -->
            <div class="grid md:grid-cols-3 gap-5 p-8 mt-2 bg-black text-white md:h-80">
                <!-- COLUMNA 1 -->
                <div class="grid grid-rows-2 gap-2">
                    <!-- TITULO -->
                    <div class="flex justify-center items-center">
                        <h2>¡Haz tu reservación ahora!</h2>
                        <h3>😃👍</h3>
                    </div>
                    <!-- BOTON -->
                    <div class="flex justify-center items-center">
                        <button type="submit"
                            class="border-white border-[2px] px-2 py-2 rounded-xl hover:bg-white hover:text-black hover:scale-110 lg:w-[30%] lg:h-[50%] md:text-lg">
                            Reservar
                        </button>
                    </div>
                </div>
                <!-- COLUMNA 2 -->
                <div class="grid md:grid-rows-3 gap-5">
                    <!-- NOMBRE -->
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div class="">
                            <!-- required="false" -->
                            <input type="text" placeholder="Nombre" class="px-3 py-3 border rounded-xl w-full"
                                v-model="formData.name">
                            <span class="text-red-500" v-if="errores.name">{{ errores.name }}</span>
                            <!-- <span v-if="touched.nombre && errors.nombre" class="text-red-500 text-sm">
                                {{ errors.nombre }}
                            </span> -->
                        </div>
                        <div>
                            <input type="text" placeholder="Apellido" class="px-3 py-3 border rounded-xl w-full"
                                v-model="formData.lastname">
                            <span class="text-red-500" v-if="errores.lastname">{{ errores.lastname }}</span>
                        </div>
                    </div>
                    <!-- CORREO -->
                    <div>
                        <div class="">
                            <input type="email" placeholder="Correo" class="px-3 py-3 border rounded-xl w-full"
                                v-model="formData.email">
                            <span class="text-red-500" v-if="errores.email">{{ errores.email }}</span>
                        </div>
                        <div class="flex-1">
                            <h2 class="text-lg font-bold text-white">Haz tu reservación ahora !</h2>
                        </div>
                        <div class="text-2xl">😃👍</div>
                    </div>
                </div>

                <!-- Contenido -->
                <div class="relative z-10 rounded-b-xl bg-zinc-900/50 px-8 py-6 backdrop-blur-sm">
                    <div class="grid gap-6 xl:grid-cols-2">
                        
                        <!-- Columna izquierda -->
                        <div class="space-y-4">
                            <div>
                                <label class="mb-1.5 block text-xs font-semibold text-zinc-300">Nombre completo</label>
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <div>
                                        <input 
                                            type="text" 
                                            placeholder="Nombre" 
                                            class="w-full rounded-lg border border-zinc-700 bg-zinc-800/50 px-3 py-2.5 text-sm text-white placeholder-zinc-500 transition-all focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none"
                                            v-model="formData.name"
                                        >
                                        <span class="mt-0.5 block text-xs text-red-400" v-if="errores.name">{{ errores.name }}</span>
                                    </div>
                                    <div>
                                        <input 
                                            type="text" 
                                            placeholder="Apellido" 
                                            class="w-full rounded-lg border border-zinc-700 bg-zinc-800/50 px-3 py-2.5 text-sm text-white placeholder-zinc-500 transition-all focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none"
                                            v-model="formData.lastname"
                                        >
                                        <span class="mt-0.5 block text-xs text-red-400" v-if="errores.lastname">{{ errores.lastname }}</span>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="mb-1.5 block text-xs font-semibold text-zinc-300">Correo electrónico</label>
                                <input 
                                    type="email" 
                                    placeholder="ejemplo@correo.com" 
                                    class="w-full rounded-lg border border-zinc-700 bg-zinc-800/50 px-3 py-2.5 text-sm text-white placeholder-zinc-500 transition-all focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none"
                                    v-model="formData.email"
                                >
                                <span class="mt-0.5 block text-xs text-red-400" v-if="errores.email">{{ errores.email }}</span>
                            </div>

                            <div>
                                <label class="mb-1.5 block text-xs font-semibold text-zinc-300">Número de teléfono</label>
                                <input 
                                    type="tel" 
                                    placeholder="999 888 777" 
                                    pattern="[0-9]{3}[0-9]{3}[0-9]{3}"
                                    maxlength="9"
                                    class="w-full rounded-lg border border-zinc-700 bg-zinc-800/50 px-3 py-2.5 text-sm text-white placeholder-zinc-500 transition-all focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none [color-scheme:dark]"
                                    v-model="formData.phone"
                                >
                                <span class="mt-0.5 block text-xs text-red-400" v-if="errores.phone">{{ errores.phone }}</span>
                            </div>
                        </div>

                        <!-- Columna derecha -->
                        <div class="space-y-4">
                            <div>
                                <label class="mb-1.5 block text-xs font-semibold text-zinc-300">Tipo de reserva</label>
                                <select 
                                    id="id_evento"
                                    class="w-full rounded-lg border border-zinc-700 bg-zinc-800/50 px-3 py-2.5 text-sm text-white transition-all focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none [color-scheme:dark] cursor-pointer"
                                    v-model="formData.id_evento"
                                >
                                    <option value="" disabled selected>Selecciona una opción</option>
                                    <option v-for="e in eventsServiceprecie" :key="e.id" :value="e.id" class="bg-zinc-800">
                                        {{ e.nombre }} - S/ {{ e.precio }}
                                    </option>
                                </select>
                                <span class="mt-0.5 block text-xs text-red-400" v-if="errores.id_evento">{{ errores.id_evento }}</span>
                            </div>

                            <div>
                                <label class="mb-1.5 block text-xs font-semibold text-zinc-300">Fecha de reserva</label>
                                <input 
                                    type="date" 
                                    class="w-full rounded-lg border border-zinc-700 bg-zinc-800/50 px-3 py-2.5 text-sm text-white transition-all focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none [color-scheme:dark]"
                                    v-model="formData.date"
                                >
                                <span class="mt-0.5 block text-xs text-red-400" v-if="errores.date">{{ errores.date }}</span>
                            </div>

                            <div class="grid gap-3 sm:grid-cols-2">
                                <div>
                                    <label class="mb-1.5 block text-xs font-semibold text-zinc-300">Hora de inicio</label>
                                    <input 
                                        type="time" 
                                        class="w-full rounded-lg border border-zinc-700 bg-zinc-800/50 px-3 py-2.5 text-sm text-white transition-all focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none [color-scheme:dark]"
                                        v-model="formData.start_time"
                                    >
                                    <span class="mt-0.5 block text-xs text-red-400" v-if="errores.start_time">{{ errores.start_time }}</span>
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-xs font-semibold text-zinc-300">Hora de finalización</label>
                                    <input 
                                        type="time" 
                                        class="w-full rounded-lg border border-zinc-700 bg-zinc-800/50 px-3 py-2.5 text-sm text-white transition-all focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none [color-scheme:dark]"
                                        v-model="formData.end_time"
                                    >
                                    <span class="mt-0.5 block text-xs text-red-400" v-if="errores.end_time">{{ errores.end_time }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Botón Reserva -->
                    <div class="mt-6 flex justify-center">
                        <button 
                            type="submit"
                            class="group relative inline-flex items-center gap-2 rounded-full bg-emerald-500 px-8 py-3 text-sm font-semibold text-zinc-900 transition-all hover:bg-emerald-400 hover:scale-105 hover:shadow-lg hover:shadow-emerald-500/30 focus:outline-none focus:ring-4 focus:ring-emerald-500/30 active:scale-95"
                        >
                            <span class="absolute inset-0 rounded-full bg-white/20 opacity-0 transition-opacity group-hover:opacity-100"></span>
                            <svg class="h-4 w-4 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            {{ isEditing ? 'Actualizar' : 'Confirmar Reserva' }}
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </section>
</template>


