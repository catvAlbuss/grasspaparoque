<script setup lang="ts">
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';

type Evento = {
    id: number;
    nombre: string;
    precio: number;
};

type BusyReservation = {
    id: number;
    id_evento: number;
    date: string;
    start_time: string;
    end_time: string;
};

type Message = {
    role: 'bot' | 'user';
    text: string;
    time: string;
};

type Option = {
    label: string;
    value: string;
};

type Step =
    | 'choose_event'
    | 'date'
    | 'time'
    | 'duration'
    | 'name'
    | 'lastname'
    | 'dni'
    | 'phone'
    | 'voucher_number'
    | 'voucher_file'
    | 'submitting'
    | 'done'
    | 'expired';

const PRICE_PER_HOUR = 50;
const PAYMENT_TIMEOUT_MS = 10 * 60 * 1000;
const HORARIOS = [
    '07:00', '08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00',
    '15:00', '16:00', '17:00', '18:00', '19:00', '20:00', '21:00', '22:00',
];

const whatsappNumber = import.meta.env.VITE_RESERVATION_WHATSAPP || '51999999999';
const yapeNumber = import.meta.env.VITE_YAPE_NUMBER || '999999999';
const yapeName = import.meta.env.VITE_YAPE_NAME || 'Grasspaparoque';
const yapeQrUrl = import.meta.env.VITE_YAPE_QR_URL || '/img/yape-qr.svg';

const isOpen = ref(false);
const initialized = ref(false);
const typing = ref(false);
const disabled = ref(false);
const input = ref('');
const messages = ref<Message[]>([]);
const options = ref<Option[]>([]);
const events = ref<Evento[]>([]);
const busy = ref<BusyReservation[]>([]);
const fileInput = ref<HTMLInputElement | null>(null);
const chatScrollRef = ref<HTMLElement | null>(null);
const chatEndRef = ref<HTMLElement | null>(null);

const paymentDeadlineAt = ref<number | null>(null);
const paymentCountdown = ref('');
const whatsappReceiptLink = ref('');
let paymentTimer: number | null = null;

const step = ref<Step>('choose_event');
const reservation = ref({
    id_evento: null as number | null,
    date: '',
    start_time: '',
    end_time: '',
    hours: 1,
    name: '',
    lastname: '',
    dni: '',
    phone: '',
    payment_proof_number: '',
    payment_proof_file: null as File | null,
});

const totalAmount = computed(() => reservation.value.hours * PRICE_PER_HOUR);
const advanceAmount = computed(() => totalAmount.value / 2);
const showPaymentCard = computed(() => step.value === 'voucher_number' || step.value === 'voucher_file');
const canUploadVoucher = computed(() => step.value === 'voucher_file' && !disabled.value);
const inputPlaceholder = computed(() => {
    if (step.value === 'date') return 'Ejemplo: 25/02/2026';
    if (step.value === 'duration') return 'Cantidad de horas (1 a 8)';
    if (step.value === 'name') return 'Ejemplo: Juan Carlos';
    if (step.value === 'lastname') return 'Ejemplo: Perez Gomez';
    if (step.value === 'dni') return 'Ejemplo: 12345678';
    if (step.value === 'phone') return 'Ejemplo: 987654321';
    if (step.value === 'voucher_number') return 'Nro de operacion (solo numeros)';
    if (step.value === 'voucher_file') return 'Adjunta la imagen con el boton Subir';
    return 'Escribe aqui...';
});

function nowTime() {
    return new Date().toLocaleTimeString('es-PE', { hour: '2-digit', minute: '2-digit' });
}

function addBot(text: string) {
    messages.value.push({ role: 'bot', text, time: nowTime() });
    scrollToBottom(true);
}

function addUser(text: string) {
    messages.value.push({ role: 'user', text, time: nowTime() });
    scrollToBottom(true);
}

function normalizeText(value: string): string {
    return value
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '');
}

function isGeneralEvent(evento: Evento): boolean {
    return normalizeText(evento.nombre).includes('evento');
}

function parseDate(raw: string): string | null {
    const clean = raw.trim().replace(/[.-]/g, '/');

    if (/^\d{4}-\d{2}-\d{2}$/.test(clean)) {
        return clean;
    }

    const parts = clean.split('/');
    if (parts.length !== 3) return null;

    const dd = parts[0].padStart(2, '0');
    const mm = parts[1].padStart(2, '0');
    const yyyy = parts[2];
    const iso = `${yyyy}-${mm}-${dd}`;
    const dt = new Date(`${iso}T00:00:00`);

    if (Number.isNaN(dt.getTime())) return null;
    return iso;
}

function toMinutes(time: string): number {
    const [h, m] = time.split(':').map(Number);
    return h * 60 + m;
}

function fromMinutes(total: number): string {
    const h = Math.floor(total / 60).toString().padStart(2, '0');
    const m = (total % 60).toString().padStart(2, '0');
    return `${h}:${m}`;
}

function hourBlock(start: string, hours: number): string[] {
    const startIndex = HORARIOS.indexOf(start);
    if (startIndex < 0) return [];

    const blocks: string[] = [];
    for (let i = 0; i < hours; i += 1) {
        const slot = HORARIOS[startIndex + i];
        if (!slot) return [];
        blocks.push(slot);
    }
    return blocks;
}

function isBusy(eventId: number, date: string, time: string): boolean {
    return busy.value.some((r) => {
        if (r.id_evento !== eventId || r.date !== date) return false;
        return toMinutes(time) >= toMinutes(r.start_time) && toMinutes(time) < toMinutes(r.end_time);
    });
}

function hasConsecutiveAvailability(eventId: number, date: string, start: string, hours: number): boolean {
    const blocks = hourBlock(start, hours);
    if (blocks.length !== hours) return false;
    return blocks.every((slot) => !isBusy(eventId, date, slot));
}

function availableHours(date: string): Option[] {
    if (!reservation.value.id_evento) return [];

    return HORARIOS
        .filter((h) => !isBusy(reservation.value.id_evento as number, date, h))
        .map((h) => ({ label: h, value: h }));
}

function updatePaymentCountdown() {
    if (!paymentDeadlineAt.value) {
        paymentCountdown.value = '';
        return;
    }

    const remaining = paymentDeadlineAt.value - Date.now();
    if (remaining <= 0) {
        expirePaymentWindow();
        return;
    }

    const totalSeconds = Math.floor(remaining / 1000);
    const mm = Math.floor(totalSeconds / 60).toString().padStart(2, '0');
    const ss = (totalSeconds % 60).toString().padStart(2, '0');
    paymentCountdown.value = `${mm}:${ss}`;
}

function clearPaymentWindow() {
    paymentDeadlineAt.value = null;
    paymentCountdown.value = '';
    if (paymentTimer) {
        window.clearInterval(paymentTimer);
        paymentTimer = null;
    }
}

function startPaymentWindow() {
    clearPaymentWindow();
    paymentDeadlineAt.value = Date.now() + PAYMENT_TIMEOUT_MS;
    updatePaymentCountdown();
    paymentTimer = window.setInterval(updatePaymentCountdown, 1000);
}

function expirePaymentWindow() {
    if (step.value !== 'voucher_number' && step.value !== 'voucher_file') return;

    clearPaymentWindow();
    step.value = 'expired';
    disabled.value = true;
    options.value = [];
    addBot('Se acabo el tiempo de 10 minutos, causa. No se registro la reserva.');
    addBot('Si gustas, reinicia el chat y te ayudo otra vez.');
}

function resetConversation() {
    clearPaymentWindow();
    whatsappReceiptLink.value = '';
    input.value = '';
    options.value = [];
    messages.value = [];
    disabled.value = false;
    reservation.value = {
        id_evento: null,
        date: '',
        start_time: '',
        end_time: '',
        hours: 1,
        name: '',
        lastname: '',
        dni: '',
        phone: '',
        payment_proof_number: '',
        payment_proof_file: null,
    };
}

function scrollToBottom(smooth = false) {
    nextTick(() => {
        if (!chatScrollRef.value) return;

        if (chatEndRef.value) {
            chatEndRef.value.scrollIntoView({
                behavior: smooth ? 'smooth' : 'auto',
                block: 'end',
            });
            return;
        }

        chatScrollRef.value.scrollTop = chatScrollRef.value.scrollHeight;
    });
}

async function botSay(text: string) {
    typing.value = true;
    await new Promise((resolve) => setTimeout(resolve, 300));
    typing.value = false;
    addBot(text);
}

async function loadCatalogData() {
    try {
        const [eventsResponse, busyResponse] = await Promise.all([
            fetch('/events/type_events'),
            fetch('/reservations/busy'),
        ]);
        events.value = await eventsResponse.json();
        busy.value = await busyResponse.json();
    } catch {
        events.value = [];
        busy.value = [];
    }
}

async function refreshBusyReservations() {
    try {
        const response = await fetch('/reservations/busy');
        busy.value = await response.json();
    } catch {
        busy.value = [];
    }
}

async function initChat() {
    resetConversation();
    step.value = 'choose_event';
    await botSay('Hola causa, te ayudo con tu reserva al toque.');
    await botSay('Tenemos futbol, voley y evento general.');

    options.value = events.value.map((event) => ({
        label: `${event.nombre} - S/. ${event.precio}`,
        value: String(event.id),
    }));

    if (options.value.length === 0) {
        options.value = [
            { label: 'Futbol - S/. 50', value: '1' },
            { label: 'Voley - S/. 50', value: '2' },
            { label: 'Evento - Precio variable', value: '3' },
        ];
        await botSay('No pude cargar el catalogo del sistema, usaré opciones de prueba.');
    }

    await botSay('Elige una opcion para empezar.');
}

async function chooseOption(option: Option) {
    if (disabled.value || typing.value) return;

    addUser(option.label);
    options.value = [];

    if (step.value === 'choose_event') {
        const eventId = Number(option.value);
        reservation.value.id_evento = eventId;
        const eventSelected = events.value.find((event) => event.id === eventId);

        if (eventSelected && isGeneralEvent(eventSelected)) {
            await botSay('Evento detectado: precio variable. Seguimos con fecha y hora para separar disponibilidad.');
        }

        step.value = 'date';
        await botSay('Pasame la fecha de reserva (DD/MM/AAAA), bro.');
        return;
    }

    if (step.value === 'time') {
        reservation.value.start_time = option.value;
        step.value = 'duration';
        await botSay('Cuantas horas quieres separar? (1 a 8)');
    }
}

async function handleInputSubmit() {
    const value = input.value.trim();
    if (!value || disabled.value || typing.value) return;

    addUser(value);
    input.value = '';

    if (step.value === 'date') {
        const isoDate = parseDate(value);
        if (!isoDate) {
            await botSay('Fecha invalida. Usa DD/MM/AAAA, porfa.');
            return;
        }

        const today = new Date();
        today.setHours(0, 0, 0, 0);

        const dateObj = new Date(`${isoDate}T00:00:00`);
        if (dateObj < today) {
            await botSay('Esa fecha ya paso. Elige hoy o una fecha futura.');
            return;
        }

        reservation.value.date = isoDate;
        const available = availableHours(isoDate);
        if (available.length === 0) {
            await botSay('Ese dia esta full en ese deporte. Prueba otra fecha.');
            return;
        }

        step.value = 'time';
        options.value = available;
        await botSay('Que hora de inicio prefieres?');
        return;
    }

    if (step.value === 'duration') {
        const n = Number.parseInt(value, 10);
        if (Number.isNaN(n) || n < 1 || n > 8) {
            await botSay('Mandame una cantidad valida entre 1 y 8 horas.');
            return;
        }

        if (!reservation.value.id_evento || !hasConsecutiveAvailability(
            reservation.value.id_evento,
            reservation.value.date,
            reservation.value.start_time,
            n,
        )) {
            await botSay('No hay continuidad en ese horario. Elige otra hora de inicio.');
            step.value = 'time';
            options.value = availableHours(reservation.value.date);
            return;
        }

        reservation.value.hours = n;
        reservation.value.end_time = fromMinutes(toMinutes(reservation.value.start_time) + n * 60);
        step.value = 'name';
        await botSay('Listo. Ahora tus nombres.');
        return;
    }

    if (step.value === 'name') {
        if (value.length < 2) {
            await botSay('Escribe tus nombres completos, porfa.');
            return;
        }
        reservation.value.name = value;
        step.value = 'lastname';
        await botSay('Ahora tus apellidos.');
        return;
    }

    if (step.value === 'lastname') {
        if (value.length < 2) {
            await botSay('Escribe tus apellidos completos, porfa.');
            return;
        }
        reservation.value.lastname = value;
        step.value = 'dni';
        await botSay('Tu DNI de 8 digitos.');
        return;
    }

    if (step.value === 'dni') {
        const dni = value.replace(/\D+/g, '');
        if (!/^\d{8}$/.test(dni)) {
            await botSay('El DNI debe tener 8 digitos.');
            return;
        }
        reservation.value.dni = dni;
        step.value = 'phone';
        await botSay('Tu numero de celular/WhatsApp (9 digitos).');
        return;
    }

    if (step.value === 'phone') {
        const phone = value.replace(/\D+/g, '');
        if (!/^9\d{8}$/.test(phone)) {
            await botSay('Numero invalido. Debe empezar con 9 y tener 9 digitos.');
            return;
        }

        reservation.value.phone = phone;
        step.value = 'voucher_number';
        startPaymentWindow();

        await botSay(`Genial. El total es S/. ${totalAmount.value} y el adelanto (50%) es S/. ${advanceAmount.value}.`);
        await botSay(`Yapea al ${yapeNumber} a nombre de ${yapeName}. Luego me pasas nro de comprobante e imagen.`);
        await botSay('Tienes 10 minutos para enviar el comprobante, causa.');
        return;
    }

    if (step.value === 'voucher_number') {
        if (!paymentDeadlineAt.value || Date.now() > paymentDeadlineAt.value) {
            expirePaymentWindow();
            return;
        }

        if (!/^\d+$/.test(value)) {
            await botSay('El numero de comprobante debe ser numerico.');
            return;
        }

        reservation.value.payment_proof_number = value;
        step.value = 'voucher_file';
        await botSay('Perfecto. Ahora sube la foto/captura del comprobante con el boton "Subir".');
    }
}

async function handleFileChange(event: Event) {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];
    if (!file) return;

    if (!paymentDeadlineAt.value || Date.now() > paymentDeadlineAt.value) {
        expirePaymentWindow();
        target.value = '';
        return;
    }

    reservation.value.payment_proof_file = file;
    addUser(`Archivo cargado: ${file.name}`);
    target.value = '';
    await submitReservation();
}

async function submitReservation() {
    if (step.value !== 'voucher_file') return;
    if (!reservation.value.payment_proof_file || !reservation.value.payment_proof_number) return;

    step.value = 'submitting';
    typing.value = true;

    const form = new FormData();
    form.append('name', reservation.value.name);
    form.append('lastname', reservation.value.lastname);
    form.append('dni', reservation.value.dni);
    form.append('phone', reservation.value.phone);
    form.append('id_evento', String(reservation.value.id_evento));
    form.append('date', reservation.value.date);
    form.append('start_time', reservation.value.start_time);
    form.append('end_time', reservation.value.end_time);
    form.append('payment_proof_number', reservation.value.payment_proof_number);
    form.append('payment_proof_file', reservation.value.payment_proof_file);

    try {
        const response = await fetch('/reservations/customers', {
            method: 'POST',
            body: form,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': (
                    document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement | null
                )?.content || '',
            },
        });

        const payload = await response.json();
        typing.value = false;

        if (!response.ok) {
            await botSay(payload?.message ?? 'No se pudo registrar la reserva.');
            step.value = 'choose_event';
            options.value = events.value.map((event) => ({
                label: `${event.nombre} - S/. ${event.precio}`,
                value: String(event.id),
            }));
            return;
        }

        clearPaymentWindow();
        disabled.value = true;
        step.value = 'done';
        await refreshBusyReservations();
        const whatsappMessage = [
            'Hola, envio comprobante de reserva.',
            `Cliente: ${reservation.value.name} ${reservation.value.lastname}`,
            `DNI: ${reservation.value.dni}`,
            `Celular: ${reservation.value.phone}`,
            `Fecha: ${reservation.value.date}`,
            `Hora: ${reservation.value.start_time} - ${reservation.value.end_time}`,
            `Horas: ${reservation.value.hours}`,
            `Adelanto: S/. ${advanceAmount.value}`,
            `Nro comprobante: ${reservation.value.payment_proof_number}`,
        ].join('\n');
        whatsappReceiptLink.value = `https://wa.me/${whatsappNumber}?text=${encodeURIComponent(whatsappMessage)}`;
        await botSay('Pago recibido en sistema. Tu reserva queda pendiente de validacion administrativa.');
        await botSay(`Comprobante guardado: ${payload?.data?.payment_proof_name ?? 'sin nombre'}.`);
        await botSay('Te enviaremos por WhatsApp la confirmacion de tu comprobante y estado final de reserva.');
    } catch {
        typing.value = false;
        await botSay('Hubo un problema de red al registrar. Intenta nuevamente.');
        step.value = 'voucher_file';
    }
}

onMounted(async () => {
    await loadCatalogData();

    setTimeout(async () => {
        isOpen.value = true;
        if (!initialized.value) {
            initialized.value = true;
            await initChat();
        }
    }, 500);
});

onUnmounted(() => {
    clearPaymentWindow();
});

watch(options, () => {
    scrollToBottom();
}, { deep: true });

watch(typing, () => {
    scrollToBottom();
});
</script>

<template>
    <div class="fixed inset-x-0 bottom-0 z-50 sm:inset-auto sm:bottom-6 sm:right-6">
        <button
            class="mb-2 ml-auto mr-3 h-14 w-14 rounded-full bg-green-600 text-white shadow-xl hover:bg-green-500 sm:mb-3 sm:mr-0"
            @click="isOpen = !isOpen"
        >
            Chat
        </button>

        <div v-if="isOpen" class="flex h-[88dvh] w-full flex-col overflow-hidden rounded-t-2xl border border-emerald-900 bg-slate-950 shadow-2xl sm:h-[38rem] sm:w-[24rem] sm:rounded-2xl">
            <div class="border-b border-emerald-900 bg-gradient-to-r from-emerald-950 via-emerald-900 to-emerald-800 px-4 py-3 text-sm font-semibold text-white">
                Central de Reservas Grasspaparoque
            </div>

            <div ref="chatScrollRef" class="flex-1 space-y-2 overflow-y-auto overscroll-contain bg-slate-900 px-3 py-3 text-sm">
                <div
                    v-for="(msg, idx) in messages"
                    :key="idx"
                    class="flex"
                    :class="msg.role === 'user' ? 'justify-end' : 'justify-start'"
                >
                    <div
                        class="max-w-[85%] rounded-2xl px-3 py-2"
                        :class="msg.role === 'user' ? 'rounded-tr-sm bg-emerald-600 text-white' : 'rounded-tl-sm bg-white text-gray-800'"
                    >
                        <p>{{ msg.text }}</p>
                        <p class="mt-1 text-right text-[11px]" :class="msg.role === 'user' ? 'text-green-100' : 'text-gray-400'">
                            {{ msg.time }}
                        </p>
                    </div>
                </div>

                <div v-if="typing" class="text-xs text-emerald-300">
                    Escribiendo...
                </div>

                <div v-if="options.length > 0 && !typing" class="flex flex-wrap gap-2">
                    <button
                        v-for="opt in options"
                        :key="opt.value"
                        class="rounded-xl border border-emerald-300 bg-white px-3 py-2 text-xs font-medium text-emerald-800"
                        @click="chooseOption(opt)"
                    >
                        {{ opt.label }}
                    </button>
                </div>

                <div v-if="showPaymentCard" class="rounded-2xl border border-emerald-200 bg-gradient-to-br from-white to-emerald-50 p-4 text-xs text-slate-800 shadow-lg">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-emerald-700">Datos de adelanto</p>
                    <div class="mt-2 grid grid-cols-2 gap-x-2 gap-y-1">
                        <p class="text-slate-500">Tarifa por hora</p><p class="text-right font-semibold">S/. 50</p>
                        <p class="text-slate-500">Total</p><p class="text-right font-semibold">S/. {{ totalAmount }}</p>
                        <p class="text-slate-500">Adelanto 50%</p><p class="text-right font-semibold text-emerald-700">S/. {{ advanceAmount }}</p>
                        <p class="text-slate-500">Yape</p><p class="text-right font-semibold">{{ yapeNumber }}</p>
                        <p class="text-slate-500">Titular</p><p class="text-right font-semibold">{{ yapeName }}</p>
                        <p class="text-slate-500">Tiempo limite</p><p class="text-right font-semibold">{{ paymentCountdown }}</p>
                    </div>
                    <img
                        v-if="yapeQrUrl"
                        :src="yapeQrUrl"
                        alt="QR Yape"
                        class="mt-3 h-40 w-full rounded-xl border border-emerald-200 bg-white object-contain p-2"
                    >
                </div>

                <a
                    v-if="whatsappReceiptLink"
                    :href="whatsappReceiptLink"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="block rounded-xl bg-green-500 px-3 py-2 text-center text-xs font-semibold text-white"
                >
                    Enviar por WhatsApp
                </a>

                <div ref="chatEndRef" class="h-1 w-full" />
            </div>

            <div class="border-t border-emerald-900 p-3 pb-[calc(0.75rem+env(safe-area-inset-bottom))]">
                <input
                    ref="fileInput"
                    type="file"
                    accept="image/*"
                    class="hidden"
                    @change="handleFileChange"
                >
                <div class="flex gap-2">
                    <input
                        v-model="input"
                        :disabled="disabled || options.length > 0"
                        type="text"
                        class="flex-1 rounded-xl border border-emerald-800 bg-slate-900 px-3 py-2 text-sm text-white placeholder-emerald-300 outline-none"
                        :placeholder="inputPlaceholder"
                        @keydown.enter="handleInputSubmit"
                    >
                    <button
                        :disabled="disabled || options.length > 0 || !input.trim()"
                        class="rounded-xl bg-emerald-600 px-3 py-2 text-sm font-semibold text-white disabled:opacity-50"
                        @click="handleInputSubmit"
                    >
                        Enviar
                    </button>
                </div>
                <button
                    v-if="canUploadVoucher"
                    class="mt-2 w-full rounded-xl border border-emerald-500 bg-emerald-900/30 px-3 py-2 text-xs font-semibold text-emerald-200"
                    @click="fileInput?.click()"
                >
                    Subir imagen de comprobante
                </button>
                <button
                    class="mt-2 text-xs text-emerald-200 underline underline-offset-2"
                    @click="initChat"
                >
                    Reiniciar chat
                </button>
            </div>
        </div>
    </div>
</template>
