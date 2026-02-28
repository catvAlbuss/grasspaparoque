<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';

// ─── Tipos ────────────────────────────────────────────────────────────────────
type Evento = {
    id: number;
    nombre: string;
    tipo: string;   // 'cancha' | 'evento'
    precio: number;
};

// ─── Estado global ────────────────────────────────────────────────────────────
const eventsServiceprecie = ref<Evento[]>([]);
const step = ref<1 | 2 | 3>(1);
const loading = ref(false);
const successMessage = ref('');
const errorMessage = ref('');
const CONTACT_PHONE = '987 654 321'; // Número de contacto para eventos

// ─── Datos del formulario ─────────────────────────────────────────────────────
const form = ref({
    // Paso 1
    id_evento: '' as string | number,
    date: '',
    start_time: '',
    duration_hours: 1,
    end_time: '',
    // Paso 2
    name: '',
    lastname: '',
    email: '',
    phone: '',
    // Paso 3 – solo para canchas
    payment_proof_number: '',
    payment_proof_file: null as File | null,
});

const errors = ref<Record<string, string>>({});

const toMinutes = (time: string) => {
    const [hh, mm] = time.split(':').map(Number);
    if (Number.isNaN(hh) || Number.isNaN(mm)) return null;
    return hh * 60 + mm;
};

const toTime = (minutes: number) => {
    const safe = Math.max(0, Math.min(minutes, 23 * 60 + 59));
    const hh = String(Math.floor(safe / 60)).padStart(2, '0');
    const mm = String(safe % 60).padStart(2, '0');
    return `${hh}:${mm}`;
};

const updateEndTime = () => {
    if (!form.value.start_time) {
        form.value.end_time = '';
        return;
    }

    const startMinutes = toMinutes(form.value.start_time);
    if (startMinutes === null) return;

    const duration = Math.max(1, Math.min(8, Number(form.value.duration_hours) || 1));
    form.value.duration_hours = duration;
    form.value.end_time = toTime(startMinutes + duration * 60);
};

// ─── Evento seleccionado ──────────────────────────────────────────────────────
const selectedEvento = computed(() =>
    eventsServiceprecie.value.find((e) => e.id === Number(form.value.id_evento)) ?? null,
);

const esCancha = computed(() =>
    selectedEvento.value?.tipo === 'cancha',
);

/** Monto del adelanto: 50% de 50 soles = 25 soles */
const montoAdelanto = computed(() =>
    selectedEvento.value ? Math.ceil((selectedEvento.value.precio * 0.5) / selectedEvento.value.precio * selectedEvento.value.precio * 0.5) : 0,
);

// ─── Carga de eventos ─────────────────────────────────────────────────────────
onMounted(async () => {
    try {
        const response = await fetch('/events/type_events', {
            method: 'GET',
            headers: { 'Content-Type': 'application/json' },
        });

        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);

        const data = await response.json();
        if (Array.isArray(data)) {
            eventsServiceprecie.value = data;
        }
    } catch (error) {
        console.error('Error al cargar reservas:', error);
    }
});

watch(() => form.value.start_time, updateEndTime);
watch(() => form.value.duration_hours, updateEndTime);

// ─── Navegación entre pasos ───────────────────────────────────────────────────
const goToStep2 = () => {
    errors.value = {};
    if (!form.value.id_evento) { errors.value.id_evento = 'Selecciona un tipo de reserva'; return; }
    if (!form.value.date) { errors.value.date = 'La fecha es obligatoria'; return; }
    if (!form.value.start_time) { errors.value.start_time = 'La hora de inicio es obligatoria'; return; }
    if (!form.value.end_time) { errors.value.end_time = 'La hora de fin es obligatoria'; return; }
    step.value = 2;
};

const goToStep3 = () => {
    errors.value = {};
    if (!form.value.name) { errors.value.name = 'El nombre es obligatorio'; return; }
    if (!form.value.phone) { errors.value.phone = 'El teléfono es obligatorio'; return; }
    step.value = 3;
};

const goBack = () => {
    errors.value = {};
    if (step.value === 3) { step.value = 2; return; }
    if (step.value === 2) { step.value = 1; return; }
};

// ─── Selección del comprobante ────────────────────────────────────────────────
const onFileChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        form.value.payment_proof_file = target.files[0];
    }
};

// ─── Envío final ────────────────────────────────────────────────────────────
const submit = async () => {
    errors.value = {};
    errorMessage.value = '';

    // Validar pago solo si es cancha
    if (esCancha.value && !form.value.payment_proof_number && !form.value.payment_proof_file) {
        errors.value.payment = 'Debes ingresar el número de operación o adjuntar el comprobante de pago.';
        return;
    }

    const fd = new FormData();
    fd.append('name', form.value.name);
    fd.append('lastname', form.value.lastname);
    fd.append('email', form.value.email);
    fd.append('phone', form.value.phone);
    fd.append('id_evento', String(form.value.id_evento));
    fd.append('date', form.value.date);
    fd.append('start_time', form.value.start_time);
    fd.append('end_time', form.value.end_time);
    if (form.value.payment_proof_number) fd.append('payment_proof_number', form.value.payment_proof_number);
    if (form.value.payment_proof_file) fd.append('payment_proof_file', form.value.payment_proof_file);

    // CSRF token
    const csrfMeta = document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]');
    const csrfToken = csrfMeta?.content ?? '';

    loading.value = true;
    try {
        const response = await fetch('/reservations/customers', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, Accept: 'application/json' },
            body: fd,
        });

        if (response.status === 422) {
            const data = await response.json();
            if (data.errors) {
                Object.entries(data.errors).forEach(([key, msgs]) => {
                    errors.value[key] = Array.isArray(msgs) ? msgs[0] : (msgs as string);
                });
                step.value = 1; // volver al paso 1 si hay error de horario
            }
            return;
        }

        if (!response.ok) throw new Error(`Error ${response.status}`);

        successMessage.value = '¡Reserva registrada con éxito! Revisaremos tu pago y te confirmaremos pronto.';
        // Resetear formulario
        form.value = {
            id_evento: '', date: '', start_time: '', duration_hours: 1, end_time: '',
            name: '', lastname: '', email: '', phone: '',
            payment_proof_number: '', payment_proof_file: null,
        };
        step.value = 1;

    } catch (err) {
        errorMessage.value = 'Ocurrió un error al enviar tu reserva. Por favor intenta de nuevo.';
        console.error(err);
    } finally {
        loading.value = false;
    }
};
</script>

<template>
    <section class="w-full">

        <!-- Mensaje de éxito -->
        <div v-if="successMessage"
            class="mb-6 rounded-xl border border-emerald-500/40 bg-emerald-950/60 p-5 text-center text-emerald-300 backdrop-blur-sm">
            <svg class="mx-auto mb-2 h-8 w-8 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <p class="text-sm font-medium">{{ successMessage }}</p>
        </div>

        <!-- Indicador de pasos -->
        <div class="mb-6 flex items-center justify-center gap-3">
            <div v-for="n in 3" :key="n" class="flex items-center">
                <div :class="[
                    'flex h-8 w-8 items-center justify-center rounded-full text-xs font-bold transition-all',
                    step > n ? 'bg-emerald-500 text-zinc-900' :
                    step === n ? 'bg-emerald-500 text-zinc-900 ring-4 ring-emerald-500/30' :
                    'bg-zinc-700 text-zinc-400'
                ]">
                    <svg v-if="step > n" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span v-else>{{ n }}</span>
                </div>
                <div v-if="n < 3" :class="['mx-2 h-0.5 w-10 transition-all', step > n ? 'bg-emerald-500' : 'bg-zinc-700']" />
            </div>
        </div>

        <div class="rounded-xl bg-zinc-900/70 px-6 py-6 backdrop-blur-sm border border-zinc-700/50">

            <!-- ══ PASO 1: Tipo de reserva + fecha/hora ══ -->
            <div v-if="step === 1">
                <h3 class="mb-5 text-center text-sm font-semibold uppercase tracking-wider text-zinc-400">
                    Paso 1 — Selecciona tu reserva
                </h3>

                <div class="space-y-4">
                    <!-- Tipo de evento -->
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-zinc-300">Tipo de reserva</label>
                        <select
                            id="id_evento"
                            v-model="form.id_evento"
                            class="w-full rounded-lg border border-zinc-700 bg-zinc-800/50 px-3 py-2.5 text-sm text-white transition-all focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 cursor-pointer [color-scheme:dark]"
                        >
                            <option value="" disabled>Selecciona una opción</option>
                            <option v-for="e in eventsServiceprecie" :key="e.id" :value="e.id" class="bg-zinc-800">
                                {{ e.nombre }}
                                <template v-if="e.tipo === 'cancha'"> — S/ {{ e.precio }}/hora</template>
                            </option>
                        </select>
                        <span v-if="errors.id_evento" class="mt-0.5 block text-xs text-red-400">{{ errors.id_evento }}</span>
                    </div>

                    <!-- Fecha -->
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-zinc-300">Fecha de reserva</label>
                        <input
                            type="date"
                            v-model="form.date"
                            class="w-full rounded-lg border border-zinc-700 bg-zinc-800/50 px-3 py-2.5 text-sm text-white transition-all focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 [color-scheme:dark]"
                        />
                        <span v-if="errors.date" class="mt-0.5 block text-xs text-red-400">{{ errors.date }}</span>
                    </div>

                    <!-- Horario -->
                    <div class="grid gap-3 sm:grid-cols-3">
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold text-zinc-300">Hora de inicio</label>
                            <input
                                type="time"
                                v-model="form.start_time"
                                class="w-full rounded-lg border border-zinc-700 bg-zinc-800/50 px-3 py-2.5 text-sm text-white transition-all focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 [color-scheme:dark]"
                            />
                            <span v-if="errors.start_time" class="mt-0.5 block text-xs text-red-400">{{ errors.start_time }}</span>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold text-zinc-300">Cantidad de horas</label>
                            <select
                                v-model.number="form.duration_hours"
                                class="w-full rounded-lg border border-zinc-700 bg-zinc-800/50 px-3 py-2.5 text-sm text-white transition-all focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 cursor-pointer [color-scheme:dark]"
                            >
                                <option v-for="n in 8" :key="n" :value="n">{{ n }} hora{{ n > 1 ? 's' : '' }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold text-zinc-300">Hora de finalización</label>
                            <input
                                type="time"
                                v-model="form.end_time"
                                readonly
                                class="w-full rounded-lg border border-zinc-700 bg-zinc-800/30 px-3 py-2.5 text-sm text-white transition-all focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 [color-scheme:dark]"
                            />
                            <span v-if="errors.end_time" class="mt-0.5 block text-xs text-red-400">{{ errors.end_time }}</span>
                        </div>
                    </div>

                    <!-- Mostrar precio si es cancha -->
                    <div v-if="esCancha && selectedEvento" class="rounded-lg border border-emerald-500/30 bg-emerald-950/30 p-3">
                        <p class="text-xs text-emerald-400">
                            💡 La hora vale <strong>S/ {{ selectedEvento.precio }}</strong>.
                            Al confirmar pagarás el <strong>50% de adelanto (S/ {{ selectedEvento.precio * 0.5 }})</strong>.
                        </p>
                    </div>

                    <!-- Botón siguiente -->
                    <div class="pt-2">
                        <button
                            type="button"
                            @click="goToStep2"
                            class="w-full rounded-full bg-emerald-500 px-6 py-3 text-sm font-semibold text-zinc-900 transition-all hover:bg-emerald-400 hover:scale-[1.02] active:scale-95"
                        >
                            Continuar →
                        </button>
                    </div>
                </div>
            </div>

            <!-- ══ PASO 2: Datos personales ══ -->
            <div v-if="step === 2">
                <h3 class="mb-5 text-center text-sm font-semibold uppercase tracking-wider text-zinc-400">
                    Paso 2 — Tus datos
                </h3>

                <div class="space-y-4">
                    <!-- Nombre -->
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold text-zinc-300">Nombre</label>
                            <input
                                type="text"
                                placeholder="Nombre"
                                v-model="form.name"
                                class="w-full rounded-lg border border-zinc-700 bg-zinc-800/50 px-3 py-2.5 text-sm text-white placeholder-zinc-500 transition-all focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20"
                            />
                            <span v-if="errors.name" class="mt-0.5 block text-xs text-red-400">{{ errors.name }}</span>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold text-zinc-300">Apellido</label>
                            <input
                                type="text"
                                placeholder="Apellido"
                                v-model="form.lastname"
                                class="w-full rounded-lg border border-zinc-700 bg-zinc-800/50 px-3 py-2.5 text-sm text-white placeholder-zinc-500 transition-all focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20"
                            />
                        </div>
                    </div>

                    <!-- Correo -->
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-zinc-300">Correo electrónico <span class="text-zinc-500">(opcional)</span></label>
                        <input
                            type="email"
                            placeholder="ejemplo@correo.com"
                            v-model="form.email"
                            class="w-full rounded-lg border border-zinc-700 bg-zinc-800/50 px-3 py-2.5 text-sm text-white placeholder-zinc-500 transition-all focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20"
                        />
                        <span v-if="errors.email" class="mt-0.5 block text-xs text-red-400">{{ errors.email }}</span>
                    </div>

                    <!-- Teléfono -->
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-zinc-300">Número de teléfono</label>
                        <input
                            type="tel"
                            placeholder="999 888 777"
                            maxlength="9"
                            v-model="form.phone"
                            class="w-full rounded-lg border border-zinc-700 bg-zinc-800/50 px-3 py-2.5 text-sm text-white placeholder-zinc-500 transition-all focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 [color-scheme:dark]"
                        />
                        <span v-if="errors.phone" class="mt-0.5 block text-xs text-red-400">{{ errors.phone }}</span>
                    </div>

                    <!-- Botones -->
                    <div class="flex gap-3 pt-2">
                        <button
                            type="button"
                            @click="goBack"
                            class="flex-1 rounded-full border border-zinc-600 bg-transparent px-6 py-3 text-sm font-semibold text-zinc-300 transition-all hover:bg-zinc-800 active:scale-95"
                        >
                            ← Atrás
                        </button>
                        <button
                            type="button"
                            @click="goToStep3"
                            class="flex-1 rounded-full bg-emerald-500 px-6 py-3 text-sm font-semibold text-zinc-900 transition-all hover:bg-emerald-400 hover:scale-[1.02] active:scale-95"
                        >
                            Continuar →
                        </button>
                    </div>
                </div>
            </div>

            <!-- ══ PASO 3: Método de pago ══ -->
            <div v-if="step === 3">
                <h3 class="mb-5 text-center text-sm font-semibold uppercase tracking-wider text-zinc-400">
                    Paso 3 — {{ esCancha ? 'Método de pago' : 'Contacto' }}
                </h3>

                <!-- ── Canchas (Fútbol / Vóley) ── -->
                <div v-if="esCancha" class="space-y-4">
                    <!-- Resumen del monto -->
                    <div class="rounded-xl border border-emerald-500/30 bg-emerald-950/40 p-4 text-center">
                        <p class="text-xs text-zinc-400">Monto de adelanto requerido (50%)</p>
                        <p class="mt-1 text-3xl font-bold text-emerald-400">S/ {{ selectedEvento ? (selectedEvento.precio * 0.5) : 0 }}</p>
                        <p class="mt-0.5 text-xs text-zinc-500">del total de S/ {{ selectedEvento?.precio ?? 0 }} por hora</p>
                    </div>

                    <!-- Datos de transferencia -->
                    <div class="rounded-lg border border-zinc-700 bg-zinc-800/40 p-4 text-sm text-zinc-300 space-y-1">
                        <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-zinc-400">Datos de pago (Yape / Transferencia)</p>
                        <div class="flex justify-between">
                            <span class="text-zinc-500">Banco:</span>
                            <span class="font-medium">BCP</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-zinc-500">Titular:</span>
                            <span class="font-medium">PC Grass Sports</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-zinc-500">Número Yape:</span>
                            <span class="font-medium text-emerald-400">{{ CONTACT_PHONE }}</span>
                        </div>
                    </div>

                    <!-- Número de operación -->
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-zinc-300">
                            Número de operación / factura
                        </label>
                        <input
                            type="text"
                            placeholder="Ej: 123456789"
                            v-model="form.payment_proof_number"
                            class="w-full rounded-lg border border-zinc-700 bg-zinc-800/50 px-3 py-2.5 text-sm text-white placeholder-zinc-500 transition-all focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20"
                        />
                        <p class="mt-1 text-xs text-zinc-500">Ingresa el número que aparece en tu comprobante de pago.</p>
                    </div>

                    <!-- Comprobante (imagen) -->
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-zinc-300">
                            Comprobante de pago <span class="text-zinc-500">(opcional)</span>
                        </label>
                        <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-dashed border-zinc-600 bg-zinc-800/30 px-4 py-3 text-sm text-zinc-400 transition-all hover:border-emerald-500/50 hover:bg-zinc-800/50">
                            <svg class="h-5 w-5 flex-shrink-0 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span>{{ form.payment_proof_file ? form.payment_proof_file.name : 'Adjuntar imagen o PDF del pago' }}</span>
                            <input type="file" accept="image/*,.pdf" class="hidden" @change="onFileChange" />
                        </label>
                    </div>

                    <span v-if="errors.payment" class="block text-xs text-red-400">{{ errors.payment }}</span>
                </div>

                <!-- ── Eventos ── -->
                <div v-else class="space-y-4">
                    <div class="rounded-xl border border-yellow-500/30 bg-yellow-950/30 p-5 text-center">
                        <p class="text-sm font-semibold text-yellow-300">¿Tienes un evento especial?</p>
                        <p class="mt-1 text-xs text-zinc-400">Para cotizar eventos, contáctanos directamente y te asesoramos.</p>
                        <a
                            :href="'https://wa.me/51' + CONTACT_PHONE.replace(/\s/g, '')"
                            target="_blank"
                            class="mt-4 inline-flex items-center gap-2 rounded-full bg-green-600 px-5 py-2.5 text-sm font-semibold text-white transition-all hover:bg-green-500"
                        >
                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
                            </svg>
                            Contáctanos: {{ CONTACT_PHONE }}
                        </a>
                        <p class="mt-3 text-xs text-zinc-500">También puedes continuar para apartar la fecha y te contactaremos.</p>
                    </div>
                </div>

                <!-- Error general -->
                <div v-if="errorMessage" class="mt-4 rounded-lg border border-red-500/30 bg-red-950/30 p-3 text-center text-xs text-red-400">
                    {{ errorMessage }}
                </div>

                <!-- Botones -->
                <div class="mt-5 flex gap-3">
                    <button
                        type="button"
                        @click="goBack"
                        class="flex-1 rounded-full border border-zinc-600 bg-transparent px-6 py-3 text-sm font-semibold text-zinc-300 transition-all hover:bg-zinc-800 active:scale-95"
                    >
                        ← Atrás
                    </button>
                    <button
                        type="button"
                        @click="submit"
                        :disabled="loading"
                        class="flex-1 rounded-full bg-emerald-500 px-6 py-3 text-sm font-semibold text-zinc-900 transition-all hover:bg-emerald-400 hover:scale-[1.02] active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span v-if="loading" class="flex items-center justify-center gap-2">
                            <svg class="h-4 w-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Enviando...
                        </span>
                        <span v-else>✓ Confirmar Reserva</span>
                    </button>
                </div>
            </div>

        </div>
    </section>
</template>
