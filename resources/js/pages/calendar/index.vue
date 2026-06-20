<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import InputError from '@/components/InputError.vue';
import {
    CalendarDays,
    CheckCircle2,
    ChevronLeft,
    ChevronRight,
    Clock3,
    Plus,
    RefreshCw,
    Timer,
    Unlock,
    UserRound,
    X,
} from 'lucide-vue-next';

type Status = 'occupied' | 'pending' | 'free';
type Evento = { id: number; nombre: string; precio: number; tipo_ambiente: 'compartido' | 'propio'; ambiente_grupo: number | null };
type Reservation = {
    id: number;
    id_evento: number;
    date: string;
    start_time: string;
    end_time: string;
    status: Exclude<Status, 'free'>;
    expires_at: string | null;
    customer_name: string;
};

const props = defineProps<{ eventos: Evento[]; reservations: Reservation[] }>();
const today = new Date();
today.setHours(0, 0, 0, 0);
const defaultEvent = props.eventos.find((evento) =>
    evento.nombre
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .includes('futbol'),
);

const viewDate = ref(new Date(today.getFullYear(), today.getMonth(), 1));
const selectedDate = ref(toIso(today));
const selectedEventId = ref<number | null>(defaultEvent?.id ?? props.eventos[0]?.id ?? null);
const bookingOpen = ref(false);
const releaseOpen = ref(false);
const releaseReservation = ref<Reservation | null>(null);
const releaseScope = ref<'hours' | 'day' | 'week'>('hours');
const nowTimestamp = ref(Date.now());
const duration = ref(1);
let countdownTimer: number | null = null;
const hours = Array.from({ length: 16 }, (_, index) => `${String(index + 7).padStart(2, '0')}:00`);

const form = useForm({
    id_evento: selectedEventId.value,
    date: selectedDate.value,
    start_time: '',
    end_time: '',
    name: '',
    lastname: '',
    phone: '',
    email: '',
});

const releaseForm = useForm({
    scope: 'hours' as 'hours' | 'day' | 'week',
    id_evento: selectedEventId.value,
    date: selectedDate.value,
    reservation_id: null as number | null,
});

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Calendario', href: '/calendar' },
];

function toIso(date: Date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

function parseTime(value: string) {
    const [hour, minute] = value.slice(0, 5).split(':').map(Number);
    return hour * 60 + minute;
}

function addHours(value: string, amount: number) {
    return `${String(Number(value.slice(0, 2)) + amount).padStart(2, '0')}:00`;
}

const selectedEvent = computed(() => props.eventos.find((item) => item.id === selectedEventId.value));
const monthLabel = computed(() => viewDate.value.toLocaleDateString('es-PE', { month: 'long', year: 'numeric' }));
const selectedDateLabel = computed(() => new Date(`${selectedDate.value}T12:00:00`).toLocaleDateString('es-PE', {
    weekday: 'long', day: 'numeric', month: 'long',
}));

const siblingEventIds = computed((): number[] => {
    const ev = selectedEvent.value;
    if (!ev || ev.tipo_ambiente !== 'compartido' || !ev.ambiente_grupo) return [];
    return props.eventos
        .filter((e) => e.ambiente_grupo === ev.ambiente_grupo && e.id !== ev.id)
        .map((e) => e.id);
});

const siblingEventNames = computed((): Map<number, string> => {
    const map = new Map<number, string>();
    for (const id of siblingEventIds.value) {
        const ev = props.eventos.find((e) => e.id === id);
        if (ev) map.set(id, ev.nombre);
    }
    return map;
});

function isActive(item: Reservation) {
    return item.status === 'occupied' || !item.expires_at || Date.parse(item.expires_at) > nowTimestamp.value;
}

function pendingCountdown(item?: Reservation) {
    if (!item?.expires_at || item.status !== 'pending') return '';
    const remaining = Math.max(0, Date.parse(item.expires_at) - nowTimestamp.value);
    const totalSeconds = Math.ceil(remaining / 1000);
    const minutes = String(Math.floor(totalSeconds / 60)).padStart(2, '0');
    const seconds = String(totalSeconds % 60).padStart(2, '0');
    return `${minutes}:${seconds}`;
}

function reservationSiblingName(item: Reservation | undefined): string | null {
    if (!item || item.id_evento === selectedEventId.value) return null;
    return siblingEventNames.value.get(item.id_evento) ?? null;
}

const filteredReservations = computed(() => props.reservations.filter((item) =>
    (item.id_evento === selectedEventId.value || siblingEventIds.value.includes(item.id_evento)) && isActive(item),
));
const selectedReservations = computed(() => filteredReservations.value.filter((item) => item.date === selectedDate.value));
const viewedMonthReservations = computed(() => filteredReservations.value.filter((item) => {
    const date = new Date(`${item.date}T12:00:00`);
    return date.getFullYear() === viewDate.value.getFullYear() && date.getMonth() === viewDate.value.getMonth();
}));

const calendarDays = computed(() => {
    const first = new Date(viewDate.value.getFullYear(), viewDate.value.getMonth(), 1);
    const mondayOffset = (first.getDay() + 6) % 7;
    const start = new Date(first);
    start.setDate(first.getDate() - mondayOffset);

    return Array.from({ length: 42 }, (_, index) => {
        const date = new Date(start);
        date.setDate(start.getDate() + index);
        const iso = toIso(date);
        const reservations = filteredReservations.value.filter((item) => item.date === iso);
        return {
            date,
            iso,
            reservations,
            currentMonth: date.getMonth() === viewDate.value.getMonth(),
            past: date < today,
            occupied: reservations.filter((item) => item.status === 'occupied').length,
            pending: reservations.filter((item) => item.status === 'pending').length,
        };
    });
});

const monthStats = computed(() => ({
    occupied: viewedMonthReservations.value.filter((item) => item.status === 'occupied').length,
    pending: viewedMonthReservations.value.filter((item) => item.status === 'pending').length,
    free: calendarDays.value.filter((day) => day.currentMonth && !day.past && day.reservations.length === 0).length,
}));

function reservationAt(hour: string) {
    const start = parseTime(hour);
    const end = start + 60;
    return selectedReservations.value.find((item) =>
        start < parseTime(item.end_time) && end > parseTime(item.start_time),
    );
}

function slotStatus(hour: string): Status {
    return reservationAt(hour)?.status ?? 'free';
}

function canUseDuration(start: string, length: number) {
    const startHour = Number(start.slice(0, 2));
    if (startHour + length > 23) return false;
    return Array.from({ length }, (_, index) => `${String(startHour + index).padStart(2, '0')}:00`)
        .every((hour) => slotStatus(hour) === 'free');
}

const durationOptions = computed(() => {
    if (!form.start_time) return [1];
    return Array.from({ length: 8 }, (_, index) => index + 1).filter((item) => canUseDuration(form.start_time, item));
});

function selectDay(iso: string, past: boolean) {
    if (past) return;
    selectedDate.value = iso;
    form.date = iso;
}

function openBooking(hour: string) {
    if (slotStatus(hour) !== 'free') return;
    duration.value = 1;
    form.clearErrors();
    form.start_time = hour;
    form.end_time = addHours(hour, 1);
    form.date = selectedDate.value;
    form.id_evento = selectedEventId.value;
    bookingOpen.value = true;
}

function openRelease(reservation: Reservation) {
    releaseReservation.value = reservation;
    releaseScope.value = 'hours';
    releaseForm.clearErrors();
    releaseOpen.value = true;
}

function handleSlot(hour: string) {
    const reservation = reservationAt(hour);
    if (!reservation) {
        openBooking(hour);
    } else if (reservation.status === 'occupied') {
        openRelease(reservation);
    }
}

function closeBooking() {
    bookingOpen.value = false;
    form.clearErrors();
}

function changeMonth(offset: number) {
    viewDate.value = new Date(viewDate.value.getFullYear(), viewDate.value.getMonth() + offset, 1);
}

function goToday() {
    viewDate.value = new Date(today.getFullYear(), today.getMonth(), 1);
    selectDay(toIso(today), false);
}

function submit() {
    form.post('/calendar/reservations', {
        preserveScroll: true,
        onSuccess: () => {
            bookingOpen.value = false;
            form.reset('name', 'lastname', 'phone', 'email', 'start_time', 'end_time');
            form.id_evento = selectedEventId.value;
            form.date = selectedDate.value;
        },
    });
}

function submitRelease() {
    releaseForm.scope = releaseScope.value;
    releaseForm.id_evento = releaseScope.value === 'hours' && releaseReservation.value
        ? releaseReservation.value.id_evento
        : selectedEventId.value;
    releaseForm.date = selectedDate.value;
    releaseForm.reservation_id = releaseReservation.value?.id ?? null;
    releaseForm.patch('/calendar/release', {
        preserveScroll: true,
        onSuccess: () => {
            releaseOpen.value = false;
            releaseReservation.value = null;
        },
    });
}

onMounted(() => {
    countdownTimer = window.setInterval(() => {
        nowTimestamp.value = Date.now();
    }, 1000);
});

onUnmounted(() => {
    if (countdownTimer) window.clearInterval(countdownTimer);
});

watch(duration, (value) => {
    if (form.start_time) form.end_time = addHours(form.start_time, value);
});

watch(selectedEventId, (value) => {
    form.id_evento = value;
    bookingOpen.value = false;
    releaseOpen.value = false;
});
</script>

<template>
    <Head title="Calendario de reservas" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-5 p-4 sm:p-6">
            <header class="relative overflow-hidden rounded-3xl border border-emerald-400/20 bg-gradient-to-br from-emerald-950 via-emerald-900 to-teal-800 p-6 text-white shadow-xl shadow-emerald-950/10 sm:p-8">
                <div class="pointer-events-none absolute -right-16 -top-20 h-64 w-64 rounded-full bg-emerald-300/10 blur-3xl" />
                <div class="relative flex flex-col justify-between gap-5 lg:flex-row lg:items-center">
                    <div class="flex items-center gap-4">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl border border-white/15 bg-white/10 backdrop-blur">
                            <CalendarDays class="h-7 w-7 text-emerald-200" />
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-emerald-200/70">Gestión de espacios</p>
                            <h1 class="mt-1 text-2xl font-bold sm:text-3xl">Calendario de disponibilidad</h1>
                            <p class="mt-1 text-sm text-emerald-100/70">Visualiza, confirma y reserva sin cruces de horario.</p>
                            <p v-if="selectedEvent" class="mt-3 inline-flex rounded-full border border-emerald-300/20 bg-emerald-950/35 px-3 py-1.5 text-xs text-emerald-100">
                                Evento seleccionado: <strong class="ml-1 text-white">{{ selectedEvent.nombre }}</strong>
                            </p>
                            <p v-if="selectedEvent?.tipo_ambiente === 'compartido' && siblingEventIds.length > 0" class="mt-2 inline-flex items-center gap-1.5 rounded-full border border-blue-300/20 bg-blue-950/35 px-3 py-1.5 text-xs text-blue-200">
                                <span class="h-1.5 w-1.5 rounded-full bg-blue-400" />
                                Ambiente compartido con:
                                <strong class="text-white">{{ [...siblingEventNames.values()].join(', ') }}</strong>
                                — sus reservas también bloquean este espacio.
                            </p>
                        </div>
                    </div>
                    <div v-if="eventos.length">
                        <label for="calendar-event" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-emerald-200/70">Cambiar evento</label>
                        <select id="calendar-event" v-model="selectedEventId" class="min-w-56 rounded-xl border border-white/20 bg-emerald-950/50 px-4 py-3 text-sm font-semibold text-white outline-none ring-emerald-300 focus:ring-2">
                            <option v-for="evento in eventos" :key="evento.id" :value="evento.id">{{ evento.nombre }}</option>
                        </select>
                    </div>
                </div>
            </header>

            <div v-if="eventos.length === 0" class="rounded-2xl border border-amber-500/30 bg-amber-500/10 p-5 text-amber-400">
                Primero registra una cancha o evento para gestionar su calendario.
            </div>

            <template v-else>
                <section class="grid gap-3 sm:grid-cols-3">
                    <div class="rounded-2xl border border-red-500/20 bg-card p-4 shadow-sm">
                        <div class="flex items-center justify-between"><span class="text-sm font-medium text-muted-foreground">Ocupados</span><span class="h-3 w-3 rounded-full bg-red-500 shadow-[0_0_12px_rgba(239,68,68,.55)]" /></div>
                        <strong class="mt-2 block text-3xl text-red-500">{{ monthStats.occupied }}</strong>
                        <span class="text-xs text-muted-foreground">reservas confirmadas este mes</span>
                    </div>
                    <div class="rounded-2xl border border-amber-500/20 bg-card p-4 shadow-sm">
                        <div class="flex items-center justify-between"><span class="text-sm font-medium text-muted-foreground">Pendientes</span><span class="h-3 w-3 rounded-full bg-amber-400 shadow-[0_0_12px_rgba(251,191,36,.55)]" /></div>
                        <strong class="mt-2 block text-3xl text-amber-500">{{ monthStats.pending }}</strong>
                        <span class="text-xs text-muted-foreground">reservas por validar</span>
                    </div>
                    <div class="rounded-2xl border border-emerald-500/20 bg-card p-4 shadow-sm">
                        <div class="flex items-center justify-between"><span class="text-sm font-medium text-muted-foreground">Libres</span><span class="h-3 w-3 rounded-full bg-emerald-500 shadow-[0_0_12px_rgba(16,185,129,.55)]" /></div>
                        <strong class="mt-2 block text-3xl text-emerald-500">{{ monthStats.free }}</strong>
                        <span class="text-xs text-muted-foreground">días sin reservas desde hoy</span>
                    </div>
                </section>

                <div class="grid gap-5 2xl:grid-cols-[minmax(0,1.65fr)_minmax(320px,.75fr)]">
                    <section class="overflow-hidden rounded-3xl border border-border bg-card shadow-sm">
                        <div class="flex flex-col gap-3 border-b border-border p-4 sm:flex-row sm:items-center sm:justify-between sm:p-5">
                            <div>
                                <p class="text-xs font-medium text-muted-foreground">{{ selectedEvent?.nombre }}</p>
                                <h2 class="text-xl font-bold capitalize">{{ monthLabel }}</h2>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" class="inline-flex items-center gap-2 rounded-xl border border-border px-3 py-2 text-sm font-medium hover:bg-muted" @click="goToday"><RefreshCw class="h-4 w-4" /> Hoy</button>
                                <button type="button" aria-label="Mes anterior" class="rounded-xl border border-border p-2.5 hover:bg-muted" @click="changeMonth(-1)"><ChevronLeft class="h-4 w-4" /></button>
                                <button type="button" aria-label="Mes siguiente" class="rounded-xl border border-border p-2.5 hover:bg-muted" @click="changeMonth(1)"><ChevronRight class="h-4 w-4" /></button>
                            </div>
                        </div>

                        <div class="p-3 sm:p-5">
                            <div class="grid grid-cols-7 text-center text-[11px] font-bold uppercase tracking-wider text-muted-foreground">
                                <span v-for="day in ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom']" :key="day" class="py-3">{{ day }}</span>
                            </div>
                            <div class="grid grid-cols-7 gap-1.5">
                                <button
                                    v-for="day in calendarDays"
                                    :key="day.iso"
                                    type="button"
                                    :disabled="day.past"
                                    class="group relative min-h-20 rounded-xl border p-2 text-left transition sm:min-h-24"
                                    :class="[
                                        day.currentMonth ? 'border-border bg-background' : 'border-transparent bg-muted/20 opacity-35',
                                        day.iso === selectedDate ? 'border-emerald-500 ring-2 ring-emerald-500/25' : '',
                                        day.past ? 'cursor-not-allowed opacity-30' : 'hover:-translate-y-0.5 hover:border-emerald-500/50 hover:shadow-md',
                                    ]"
                                    @click="selectDay(day.iso, day.past)"
                                >
                                    <span class="flex h-7 w-7 items-center justify-center rounded-lg text-sm font-bold" :class="day.iso === selectedDate ? 'bg-emerald-500 text-white' : ''">{{ day.date.getDate() }}</span>
                                    <div class="mt-2 space-y-1">
                                        <span v-if="day.occupied" class="flex items-center gap-1 truncate text-[10px] font-semibold text-red-500"><i class="h-1.5 w-1.5 rounded-full bg-red-500" /> {{ day.occupied }} ocupado</span>
                                        <span v-if="day.pending" class="flex items-center gap-1 truncate text-[10px] font-semibold text-amber-500"><i class="h-1.5 w-1.5 rounded-full bg-amber-400" /> {{ day.pending }} pendiente</span>
                                        <span v-if="!day.past && !day.reservations.length" class="flex items-center gap-1 text-[10px] font-semibold text-emerald-500"><i class="h-1.5 w-1.5 rounded-full bg-emerald-500" /> Libre</span>
                                    </div>
                                </button>
                            </div>
                        </div>
                    </section>

                    <aside class="rounded-3xl border border-border bg-card shadow-sm">
                        <div class="border-b border-border p-5">
                            <p class="text-xs font-medium uppercase tracking-wider text-emerald-500">Agenda diaria</p>
                            <h2 class="mt-1 text-lg font-bold capitalize">{{ selectedDateLabel }}</h2>
                            <p class="text-xs text-muted-foreground">Selecciona una hora libre para reservar.</p>
                        </div>
                        <div class="max-h-[640px] space-y-2 overflow-y-auto p-4">
                            <button
                                v-for="hour in hours"
                                :key="hour"
                                type="button"
                                class="flex w-full items-center gap-3 rounded-xl border p-3 text-left transition"
                                :class="{
                                    'border-red-500/20 bg-red-500/[.07] hover:border-red-500/60': slotStatus(hour) === 'occupied',
                                    'cursor-not-allowed border-amber-500/20 bg-amber-500/[.07]': slotStatus(hour) === 'pending',
                                    'border-emerald-500/20 hover:border-emerald-500 hover:bg-emerald-500/[.07]': slotStatus(hour) === 'free',
                                }"
                                :disabled="slotStatus(hour) === 'pending'"
                                @click="handleSlot(hour)"
                            >
                                <span class="w-12 font-mono text-sm font-bold">{{ hour }}</span>
                                <span class="h-8 w-1 rounded-full" :class="slotStatus(hour) === 'occupied' ? 'bg-red-500' : slotStatus(hour) === 'pending' ? 'bg-amber-400' : 'bg-emerald-500'" />
                                <span class="min-w-0 flex-1">
                                    <strong class="block text-sm" :class="slotStatus(hour) === 'occupied' ? 'text-red-500' : slotStatus(hour) === 'pending' ? 'text-amber-500' : 'text-emerald-500'">
                                        {{ slotStatus(hour) === 'occupied'
                                            ? `Ocupado${reservationSiblingName(reservationAt(hour)) ? ' · ' + reservationSiblingName(reservationAt(hour)) : ''}`
                                            : slotStatus(hour) === 'pending'
                                            ? `Pendiente · ${pendingCountdown(reservationAt(hour))}`
                                            : 'Libre' }}
                                    </strong>
                                    <small class="block truncate text-muted-foreground">{{ reservationAt(hour)?.customer_name || 'Disponible para reservar' }}</small>
                                </span>
                                <Plus v-if="slotStatus(hour) === 'free'" class="h-4 w-4 text-emerald-500" />
                                <Unlock v-if="slotStatus(hour) === 'occupied'" class="h-4 w-4 text-red-400" />
                            </button>
                        </div>
                    </aside>
                </div>
            </template>
        </div>

        <div v-if="bookingOpen" class="fixed inset-0 z-[100] flex items-end justify-center bg-black/60 p-0 backdrop-blur-sm sm:items-center sm:p-5" @click.self="closeBooking">
            <section class="max-h-[92vh] w-full max-w-2xl overflow-y-auto rounded-t-3xl border border-border bg-background shadow-2xl sm:rounded-3xl">
                <header class="sticky top-0 z-10 flex items-center justify-between border-b border-border bg-background/95 p-5 backdrop-blur">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-emerald-500">Nueva reserva</p>
                        <h2 class="text-xl font-bold">Ocupar horario libre</h2>
                    </div>
                    <button type="button" aria-label="Cerrar" class="rounded-xl border border-border p-2 hover:bg-muted" @click="closeBooking"><X class="h-5 w-5" /></button>
                </header>

                <form class="space-y-5 p-5" @submit.prevent="submit">
                    <div class="grid gap-3 rounded-2xl border border-emerald-500/20 bg-emerald-500/[.06] p-4 sm:grid-cols-2 lg:grid-cols-4">
                        <div class="flex items-center gap-2"><CalendarDays class="h-5 w-5 text-emerald-500" /><div><small class="text-muted-foreground">Fecha</small><strong class="block text-sm">{{ selectedDate }}</strong></div></div>
                        <div class="flex items-center gap-2"><Clock3 class="h-5 w-5 text-emerald-500" /><div><small class="text-muted-foreground">Inicio</small><strong class="block text-sm">{{ form.start_time }}</strong></div></div>
                        <div class="flex items-center gap-2">
                            <Timer class="h-5 w-5 flex-shrink-0 text-emerald-500" />
                            <div class="min-w-0 flex-1">
                                <label for="booking-duration" class="mb-1 block text-xs text-muted-foreground">Duración</label>
                                <select
                                    id="booking-duration"
                                    v-model="duration"
                                    class="block w-full rounded-lg border border-border bg-background px-2.5 py-1.5 text-sm font-bold text-foreground outline-none ring-emerald-500/20 focus:border-emerald-500 focus:ring-2"
                                    style="color-scheme: light dark;"
                                >
                                    <option
                                        v-for="item in durationOptions"
                                        :key="item"
                                        :value="item"
                                        class="bg-background text-foreground"
                                    >
                                        {{ item }} hora{{ item > 1 ? 's' : '' }}
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="flex items-center gap-2"><CheckCircle2 class="h-5 w-5 text-emerald-500" /><div><small class="text-muted-foreground">Finaliza</small><strong class="block text-sm">{{ form.end_time }}</strong></div></div>
                    </div>

                    <InputError :message="form.errors.start_time || form.errors.end_time || form.errors.date" />

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div><label class="mb-1.5 block text-sm font-medium">Nombres</label><input v-model="form.name" required autofocus class="w-full rounded-xl border border-border bg-background px-3 py-2.5 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/15" /><InputError :message="form.errors.name" /></div>
                        <div><label class="mb-1.5 block text-sm font-medium">Apellidos</label><input v-model="form.lastname" class="w-full rounded-xl border border-border bg-background px-3 py-2.5 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/15" /><InputError :message="form.errors.lastname" /></div>
                        <div><label class="mb-1.5 block text-sm font-medium">Celular</label><input v-model="form.phone" required inputmode="tel" class="w-full rounded-xl border border-border bg-background px-3 py-2.5 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/15" /><InputError :message="form.errors.phone" /></div>
                        <div><label class="mb-1.5 block text-sm font-medium">Correo <span class="text-muted-foreground">(opcional)</span></label><input v-model="form.email" type="email" class="w-full rounded-xl border border-border bg-background px-3 py-2.5 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/15" /><InputError :message="form.errors.email" /></div>
                    </div>

                    <div class="flex flex-col-reverse gap-2 border-t border-border pt-4 sm:flex-row sm:justify-end">
                        <button type="button" class="rounded-xl border border-border px-5 py-2.5 font-medium hover:bg-muted" @click="closeBooking">Cancelar</button>
                        <button type="submit" :disabled="form.processing" class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-5 py-2.5 font-semibold text-white shadow-lg shadow-emerald-600/20 hover:bg-emerald-700 disabled:opacity-50"><CheckCircle2 class="h-4 w-4" />{{ form.processing ? 'Guardando...' : 'Confirmar reserva' }}</button>
                    </div>
                </form>
            </section>
        </div>

        <div v-if="releaseOpen" class="fixed inset-0 z-[100] flex items-end justify-center bg-black/60 p-0 backdrop-blur-sm sm:items-center sm:p-5" @click.self="releaseOpen = false">
            <section class="w-full max-w-lg rounded-t-3xl border border-border bg-background p-5 shadow-2xl sm:rounded-3xl sm:p-6">
                <header class="mb-5 flex items-start justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="rounded-xl bg-red-500/10 p-3 text-red-500"><Unlock class="h-6 w-6" /></div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-red-500">Evento cancelado</p>
                            <h2 class="text-xl font-bold">Liberar disponibilidad</h2>
                            <p class="text-sm text-muted-foreground">{{ releaseReservation?.start_time }}–{{ releaseReservation?.end_time }} · {{ selectedDate }}</p>
                        </div>
                    </div>
                    <button type="button" aria-label="Cerrar" class="rounded-xl border border-border p-2 hover:bg-muted" @click="releaseOpen = false"><X class="h-5 w-5" /></button>
                </header>

                <div class="space-y-2">
                    <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-border p-3 transition hover:bg-muted/50" :class="releaseScope === 'hours' ? 'border-red-500 bg-red-500/[.05]' : ''">
                        <input v-model="releaseScope" type="radio" value="hours" class="mt-1 accent-red-500" />
                        <span><strong class="block text-sm">Liberar estas horas</strong><small class="text-muted-foreground">Solo la reserva {{ releaseReservation?.start_time }}–{{ releaseReservation?.end_time }}.</small></span>
                    </label>
                    <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-border p-3 transition hover:bg-muted/50" :class="releaseScope === 'day' ? 'border-red-500 bg-red-500/[.05]' : ''">
                        <input v-model="releaseScope" type="radio" value="day" class="mt-1 accent-red-500" />
                        <span><strong class="block text-sm">Liberar todo el día</strong><small class="text-muted-foreground">Cancela todos los espacios ocupados de {{ selectedDate }}.</small></span>
                    </label>
                    <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-border p-3 transition hover:bg-muted/50" :class="releaseScope === 'week' ? 'border-red-500 bg-red-500/[.05]' : ''">
                        <input v-model="releaseScope" type="radio" value="week" class="mt-1 accent-red-500" />
                        <span><strong class="block text-sm">Liberar toda la semana</strong><small class="text-muted-foreground">Cancela los espacios ocupados de lunes a domingo.</small></span>
                    </label>
                </div>

                <InputError class="mt-3" :message="releaseForm.errors.scope || releaseForm.errors.reservation_id" />

                <div class="mt-5 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                    <button type="button" class="rounded-xl border border-border px-5 py-2.5 font-medium hover:bg-muted" @click="releaseOpen = false">Conservar reserva</button>
                    <button type="button" :disabled="releaseForm.processing" class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-600 px-5 py-2.5 font-semibold text-white hover:bg-red-700 disabled:opacity-50" @click="submitRelease"><Unlock class="h-4 w-4" />{{ releaseForm.processing ? 'Liberando...' : 'Confirmar liberación' }}</button>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
