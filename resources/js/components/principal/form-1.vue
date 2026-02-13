<script setup lang="ts">
import { ref, computed, reactive } from 'vue';

const formData = reactive({
    nombre: '',
    apellido: '',
    correo: '',
    numero: '',
    tipo: '',
    fecha: '',
    hora: '',
});

// const onSubmit = () => {
//     console.log('Bien hecho chato:', formData.value);
//     // validateEmail(); // Ensure final validation on submit
//     // if (isFormValid.value) {
//     //     console.log('Form submitted successfully:', email.value);
//     // }
// };

const errores = ref({
    nombre: '',
    apellido: '',
    correo: '',
    numero: '',
    tipo: '',
    fecha: '',
    hora: '',
});

const validarFormulario = () => {
    errores.value = {
        nombre: '',
        apellido: '',
        correo: '',
        numero: '',
        tipo: '',
        fecha: '',
        hora: '',
    };

    if (!formData.nombre) errores.value.nombre = 'El nombre es obligatorio';
    if (!formData.apellido) errores.value.apellido = 'El apellido es obligatorio';
    if (!formData.correo) errores.value.correo = 'El correo es obligatorio';
    if (!formData.numero) errores.value.numero = 'El número es obligatorio';
    if (!formData.tipo) errores.value.tipo = 'El tipo de evento es obligatorio';
    if (!formData.fecha) errores.value.fecha = 'La fecha es obligatorio';
    if (!formData.hora) errores.value.hora = 'La hora es obligatorio';

    return Object.keys(errores.value).length === 0;
}

const enviarFormulario = () => {
    if (validarFormulario()) {
        alert('Formulario válido, enviando...');
        console.log(formData);

    } else {
        alert('Por favor, corrija los errores.');
    }
};

// const touched = ref({
//     nombre: false,
//     apellido: false,
//     correo: false,
//     numero: false,
//     tipo: false,
//     fecha: false,
//     hora: false,
// })

// // VALIDAR NOMBRE
// const nombre = ref('');
// const errorsNombre = ref({ nombre: '' });

// const validateNombre = () => {
//     if (!nombre.value) {
//         errorsNombre.value.nombre = 'El nombre es necesario chato';
//     } else if (!/^\S+@\S+\.\S+$/.test(nombre.value)) {
//         errorsNombre.value.nombre = 'Error chatito';
//     } else {
//         errorsNombre.value.nombre = '';
//     }
// };

// // VALIDAR EMAIL
// const email = ref('');
// const errorsEmail = ref({ email: '' });

// const validateEmail = () => {
//     console.log(email.value)
//     if (!email.value) {
//         errorsEmail.value.email = 'Email is required.';
//     } else if (!/^\S+@\S+\.\S+$/.test(email.value)) {
//         errorsEmail.value.email = 'Invalid email format.';
//     } else {
//         errorsEmail.value.email = '';
//     }
// };

// const isFormValid = computed(() => {
//     // Check all fields for errors
//     return !errorsEmail.value.email && email.value;
// });

</script>

<template>
    <section>
        <!-- FORMULARIO -->
        <form @submit.prevent="enviarFormulario">
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
                                v-model="formData.nombre">
                            <span class="text-red-500" v-if="errores.nombre">{{ errores.nombre }}</span>
                            <!-- <span v-if="touched.nombre && errors.nombre" class="text-red-500 text-sm">
                                {{ errors.nombre }}
                            </span> -->
                        </div>
                        <div>
                            <input type="text" placeholder="Apellido" class="px-3 py-3 border rounded-xl w-full"
                                v-model="formData.apellido">
                            <span class="text-red-500" v-if="errores.apellido">{{ errores.apellido }}</span>
                        </div>
                    </div>
                    <!-- CORREO -->
                    <div>
                        <div class="">
                            <input type="email" placeholder="Correo" class="px-3 py-3 border rounded-xl w-full"
                                v-model="formData.correo">
                            <span class="text-red-500" v-if="errores.correo">{{ errores.correo }}</span>
                        </div>
                    </div>
                    <!-- NÚMERO -->
                    <div>
                        <div>
                            <input type="tel" placeholder="Número" pattern="[0-9]{3}[0-9]{3}[0-9]{3}"
                                class="px-3 py-3 border rounded-xl w-full" v-model="formData.numero" maxlength="9">
                            <span class="text-red-500" v-if="errores.numero">{{ errores.numero }}</span>
                        </div>
                    </div>
                </div>
                <!-- COLUMNA 3 -->
                <div class="grid grid-rows-3 gap-5">
                    <!-- TIPO DE RESERVA -->
                    <div>
                        <div>
                            <select placeholder="Tipo de reserva"
                                class="px-3 py-3 border rounded-xl w-full [color-scheme:dark]" v-model="formData.tipo">
                                <option value="" disabled selected>Tipo de reserva</option>
                                <option value="futbol" class="text-black">Fútbol</option>
                                <option value="voley" class="text-black">Vóley</option>
                                <option value="evento" class="text-black">Evento</option>
                            </select>
                            <span class="text-red-500" v-if="errores.tipo">{{ errores.tipo }}</span>
                        </div>
                    </div>
                    <!-- FECHA DE RESERVA -->
                    <div>
                        <div>
                            <input type="date" name="" id="" placeholder="Fecha de reserva"
                                class="px-3 py-3 border rounded-xl w-full [color-scheme:dark]" v-model="formData.fecha">
                            <span class="text-red-500" v-if="errores.fecha">{{ errores.fecha }}</span>
                        </div>
                    </div>
                    <!-- HORA DE RESERVA -->
                    <div>
                        <div>
                            <input type="time" name="" id="" placeholder="Hora de reserva"
                                class="px-3 py-3 border rounded-xl w-full [color-scheme:dark]" v-model="formData.hora">
                            <span class="text-red-500" v-if="errores.hora">{{ errores.hora }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </section>
</template>

<style></style>
