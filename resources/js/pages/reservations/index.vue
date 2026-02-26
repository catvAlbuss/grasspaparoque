<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import {
    CalendarDays,
    Clock,
    User,
    Phone,
    FileText,
    CheckCircle2,
    XCircle,
    AlertCircle,
    ClipboardList,
    Hash,
    Sparkles,
    Activity,
    CreditCard,
    CalendarCheck,
    MessageCircle,
    Image,
} from 'lucide-vue-next';

type Reservation = {
    id: number;
    id_evento: number;
    date: string;
    start_time: string;
    end_time: string;
    reservation_status: 'free' | 'busy';
    payment_status: 'pending' | 'approved' | 'rejected';
    payment_proof_name: string | null;
    payment_proof_number: string | null;
    customer_name: string | null;
    customer_lastname: string | null;
    customer_dni: string | null;
    customer_phone: string | null;
    event_name: string | null;
};

type Props = {
    reservations: Reservation[];
};

const props = defineProps<Props>();
const reservations = computed(() => props.reservations);
const reviewForm = useForm({});
const page = usePage();
const approvalError = computed(() => (page.props.errors as Record<string, string | undefined>).approval);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Reservas',  href: '/reservations' },
];

// ── Stats ────────────────────────────────────────────
const totalReservas   = computed(() => reservations.value.length);
const pendientes      = computed(() => reservations.value.filter(r => r.payment_status === 'pending').length);
const aprobadas       = computed(() => reservations.value.filter(r => r.payment_status === 'approved').length);
const rechazadas      = computed(() => reservations.value.filter(r => r.payment_status === 'rejected').length);

// ── Clases de estado de pago ─────────────────────────
const getPaymentClasses = (status: Reservation['payment_status']): string => {
    const map: Record<Reservation['payment_status'], string> = {
        pending:  'inline-flex items-center gap-1 rounded-full border border-amber-500/25  bg-amber-500/[0.08]  px-2.5 py-1 text-[0.72rem] font-semibold text-amber-400  whitespace-nowrap',
        approved: 'inline-flex items-center gap-1 rounded-full border border-green-500/20  bg-green-500/10      px-2.5 py-1 text-[0.72rem] font-semibold text-green-500 whitespace-nowrap',
        rejected: 'inline-flex items-center gap-1 rounded-full border border-red-500/25    bg-red-500/[0.08]    px-2.5 py-1 text-[0.72rem] font-semibold text-red-400   whitespace-nowrap',
    };
    return map[status];
};

const getPaymentIcon = (status: Reservation['payment_status']) => {
    const map = { pending: AlertCircle, approved: CheckCircle2, rejected: XCircle };
    return map[status];
};

const getPaymentLabel = (status: Reservation['payment_status']): string => {
    const map = { pending: 'Pendiente', approved: 'Aprobado', rejected: 'Rechazado' };
    return map[status];
};

// ── Clases de estado de reserva ──────────────────────
const getReservationClasses = (status: Reservation['reservation_status']): string => {
    if (status === 'busy')
        return 'inline-flex items-center gap-1 rounded-full border border-blue-500/25 bg-blue-500/[0.08] px-2.5 py-1 text-[0.72rem] font-semibold text-blue-400 whitespace-nowrap';
    return 'inline-flex items-center gap-1 rounded-full border border-zinc-500/20 bg-zinc-500/10 px-2.5 py-1 text-[0.72rem] font-semibold text-zinc-400 whitespace-nowrap';
};

// ── WhatsApp ─────────────────────────────────────────
function getSwal() {
    return (window as typeof window & {
        Swal?: {
            fire: (options: Record<string, unknown>) => Promise<{ isConfirmed: boolean }>;
        };
    }).Swal;
}

function buildWhatsAppLink(reservation: Reservation) {
    const phone = (reservation.customer_phone ?? '').replace(/\D+/g, '');
    if (!phone) return null;
    const message = [
        'Hola, tu reserva fue confirmada.',
        `Cancha/Servicio: ${reservation.event_name ?? `Evento #${reservation.id_evento}`}`,
        `Fecha: ${reservation.date}`,
        `Horario: ${reservation.start_time} - ${reservation.end_time}`,
        'Gracias por tu preferencia.',
    ].join('\n');
    return `https://wa.me/51${phone}?text=${encodeURIComponent(message)}`;
}

async function approvePayment(reservation: Reservation) {
    const swal = getSwal();
    if (!swal) {
        const allowed = confirm('Aprobar pago y ocupar horario?');
        if (!allowed) return;
        reviewForm.patch(`/reservations/${reservation.id}/approve-payment`, { preserveScroll: true });
        return;
    }
    const result = await swal.fire({
        title: 'Aprobar comprobante',
        text: 'Se confirmara la reserva y se bloqueara el horario en calendario.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Si, aprobar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#059669',
        cancelButtonColor: '#475569',
    });
    if (!result.isConfirmed) return;
    reviewForm.patch(`/reservations/${reservation.id}/approve-payment`, {
        preserveScroll: true,
        onSuccess: async () => {
            const whatsappLink = buildWhatsAppLink(reservation);
            await swal.fire({
                title: 'Reserva confirmada',
                text: whatsappLink
                    ? 'Pago aprobado. Ahora puedes enviar la confirmacion por WhatsApp.'
                    : 'Pago aprobado. Cliente sin numero valido para WhatsApp.',
                icon: 'success',
                confirmButtonColor: '#059669',
                confirmButtonText: whatsappLink ? 'Abrir WhatsApp' : 'Entendido',
                showCancelButton: Boolean(whatsappLink),
                cancelButtonText: 'Cerrar',
            }).then((response) => {
                if (response.isConfirmed && whatsappLink) {
                    window.open(whatsappLink, '_blank', 'noopener,noreferrer');
                }
            });
        },
    });
}

async function rejectPayment(reservation: Reservation) {
    const swal = getSwal();
    if (!swal) {
        const allowed = confirm('Rechazar comprobante?');
        if (!allowed) return;
        reviewForm.patch(`/reservations/${reservation.id}/reject-payment`, { preserveScroll: true });
        return;
    }
    const result = await swal.fire({
        title: 'Rechazar comprobante',
        text: 'La reserva quedara no ocupada y el pago pasara a rechazado.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Si, rechazar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#e11d48',
        cancelButtonColor: '#475569',
    });
    if (!result.isConfirmed) return;
    reviewForm.patch(`/reservations/${reservation.id}/reject-payment`, {
        preserveScroll: true,
        onSuccess: async () => {
            await swal.fire({
                title: 'Comprobante rechazado',
                text: 'Estado actualizado correctamente.',
                icon: 'success',
                confirmButtonColor: '#059669',
            });
        },
    });
}
</script>

<template>
    <Head title="Reservas" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-5 p-5">

            <!-- ══ HERO HEADER ══════════════════════════════════ -->
            <div
                class="relative overflow-hidden rounded-[1.1rem] border border-green-400/[0.12] px-7 py-6"
                style="background: linear-gradient(135deg, #052e16 0%, #14532d 45%, #15803d 100%);"
            >
                <!-- Glow -->
                <div
                    class="pointer-events-none absolute inset-0"
                    style="background: radial-gradient(ellipse at 10% 70%, rgba(34,197,94,0.28) 0%, transparent 55%), radial-gradient(ellipse at 90% 10%, rgba(74,222,128,0.15) 0%, transparent 50%);"
                />

                <!-- Top row -->
                <div class="relative mb-5 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3.5">
                        <div class="flex h-[46px] w-[46px] flex-shrink-0 items-center justify-center rounded-xl border border-green-400/30 bg-green-400/15 text-green-400">
                            <ClipboardList :size="24" />
                        </div>
                        <div>
                            <h1 class="text-[1.35rem] font-bold text-white" style="letter-spacing:-0.03em;">Panel de Reservaciones</h1>
                            <p class="mt-0.5 text-[0.8rem]" style="color:rgba(134,239,172,0.8);">Valida comprobantes pendientes. Solo al aprobar se ocupa la hora en calendario.</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-1.5 whitespace-nowrap rounded-full border border-green-400/30 bg-green-500/15 px-3 py-1.5 text-[0.76rem] font-semibold text-green-400">
                        <Sparkles :size="12" />
                        <span>{{ totalReservas }} reserva{{ totalReservas !== 1 ? 's' : '' }}</span>
                    </div>
                </div>

                <!-- Stats row -->
                <div
                    class="relative flex items-center rounded-[10px] border border-green-400/[0.12] px-4 py-2.5"
                    style="background:rgba(24,24,27,0.50); backdrop-filter:blur(8px);"
                >
                    <!-- Total -->
                    <div class="flex flex-1 items-center justify-center gap-2">
                        <div class="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-[7px] border border-green-500/25 bg-green-500/[0.18] text-green-400">
                            <Activity :size="14" />
                        </div>
                        <div>
                            <span class="block text-[0.95rem] font-bold leading-none text-white">{{ totalReservas }}</span>
                            <span class="mt-0.5 block text-[0.68rem]" style="color:rgba(134,239,172,0.65);">Total</span>
                        </div>
                    </div>
                    <div class="h-7 w-px flex-shrink-0" style="background:rgba(74,222,128,0.15);" />
                    <!-- Pendientes -->
                    <div class="flex flex-1 items-center justify-center gap-2">
                        <div class="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-[7px] border border-amber-500/25 bg-amber-500/[0.18] text-amber-400">
                            <AlertCircle :size="14" />
                        </div>
                        <div>
                            <span class="block text-[0.95rem] font-bold leading-none text-white">{{ pendientes }}</span>
                            <span class="mt-0.5 block text-[0.68rem]" style="color:rgba(134,239,172,0.65);">Pendientes</span>
                        </div>
                    </div>
                    <div class="h-7 w-px flex-shrink-0" style="background:rgba(74,222,128,0.15);" />
                    <!-- Aprobadas -->
                    <div class="flex flex-1 items-center justify-center gap-2">
                        <div class="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-[7px] border border-emerald-500/25 bg-emerald-500/[0.18] text-emerald-400">
                            <CheckCircle2 :size="14" />
                        </div>
                        <div>
                            <span class="block text-[0.95rem] font-bold leading-none text-white">{{ aprobadas }}</span>
                            <span class="mt-0.5 block text-[0.68rem]" style="color:rgba(134,239,172,0.65);">Aprobadas</span>
                        </div>
                    </div>
                    <div class="h-7 w-px flex-shrink-0" style="background:rgba(74,222,128,0.15);" />
                    <!-- Rechazadas -->
                    <div class="flex flex-1 items-center justify-center gap-2">
                        <div class="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-[7px] border border-red-500/25 bg-red-500/[0.18] text-red-400">
                            <XCircle :size="14" />
                        </div>
                        <div>
                            <span class="block text-[0.95rem] font-bold leading-none text-white">{{ rechazadas }}</span>
                            <span class="mt-0.5 block text-[0.68rem]" style="color:rgba(134,239,172,0.65);">Rechazadas</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ══ TABLE CARD ═══════════════════════════════════ -->
            <section class="relative overflow-hidden rounded-[1.1rem] border border-border bg-background transition-shadow duration-300 hover:shadow-[0_4px_24px_rgba(22,163,74,0.07)]">
                <div class="absolute inset-x-0 top-0 h-[2.5px] bg-gradient-to-r from-green-700 via-green-500 to-green-400" />

                <!-- Card header -->
                <div class="flex items-center gap-3.5 border-b border-border px-6 py-4" style="background:rgba(39,39,42,0.30);">
                    <div class="flex h-[38px] w-[38px] flex-shrink-0 items-center justify-center rounded-[9px] border border-blue-500/25 bg-blue-500/[0.08] text-blue-400">
                        <ClipboardList :size="16" />
                    </div>
                    <div>
                        <h2 class="text-[0.95rem] font-bold tracking-tight text-foreground" style="letter-spacing:-0.02em;">Listado de Reservas</h2>
                        <p class="mt-0.5 text-[0.76rem] text-muted-foreground">
                            {{ totalReservas }} reserva{{ totalReservas !== 1 ? 's' : '' }} · {{ pendientes }} pendiente{{ pendientes !== 1 ? 's' : '' }} de revisión
                        </p>
                    </div>
                </div>

                <InputError :message="approvalError" class="px-6 pt-4" />

                <!-- Tabla -->
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[1100px] border-separate border-spacing-0 text-[0.83rem]">
                        <thead>
                            <tr style="background:rgba(39,39,42,0.30);">
                                <th class="whitespace-nowrap border-b border-green-500/15 px-4 py-2.5 text-left text-[0.68rem] font-bold uppercase tracking-[0.07em] text-green-500">
                                    <User :size="11" class="mr-0.5 inline align-middle opacity-80" />Cliente
                                </th>
                                <th class="whitespace-nowrap border-b border-green-500/15 px-4 py-2.5 text-left text-[0.68rem] font-bold uppercase tracking-[0.07em] text-green-500">
                                    <Phone :size="11" class="mr-0.5 inline align-middle opacity-80" />Contacto
                                </th>
                                <th class="whitespace-nowrap border-b border-green-500/15 px-4 py-2.5 text-left text-[0.68rem] font-bold uppercase tracking-[0.07em] text-green-500">
                                    <CalendarDays :size="11" class="mr-0.5 inline align-middle opacity-80" />Evento
                                </th>
                                <th class="whitespace-nowrap border-b border-green-500/15 px-4 py-2.5 text-left text-[0.68rem] font-bold uppercase tracking-[0.07em] text-green-500">
                                    <Clock :size="11" class="mr-0.5 inline align-middle opacity-80" />Fecha / Hora
                                </th>
                                <th class="whitespace-nowrap border-b border-green-500/15 px-4 py-2.5 text-left text-[0.68rem] font-bold uppercase tracking-[0.07em] text-green-500">
                                    <FileText :size="11" class="mr-0.5 inline align-middle opacity-80" />Comprobante
                                </th>
                                <th class="whitespace-nowrap border-b border-green-500/15 px-4 py-2.5 text-left text-[0.68rem] font-bold uppercase tracking-[0.07em] text-green-500">
                                    <CreditCard :size="11" class="mr-0.5 inline align-middle opacity-80" />Pago
                                </th>
                                <th class="whitespace-nowrap border-b border-green-500/15 px-4 py-2.5 text-left text-[0.68rem] font-bold uppercase tracking-[0.07em] text-green-500">
                                    <CalendarCheck :size="11" class="mr-0.5 inline align-middle opacity-80" />Reserva
                                </th>
                                <th class="whitespace-nowrap border-b border-green-500/15 px-4 py-2.5 text-left text-[0.68rem] font-bold uppercase tracking-[0.07em] text-green-500">
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody>

                            <!-- Empty state -->
                            <tr v-if="reservations.length === 0">
                                <td colspan="8" class="px-4 py-12">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <div class="mb-2 flex h-16 w-16 items-center justify-center rounded-2xl border border-green-500/15 bg-green-500/[0.07] text-green-500 opacity-60">
                                            <ClipboardList :size="32" />
                                        </div>
                                        <p class="text-[0.9rem] font-bold text-muted-foreground">No hay reservas registradas</p>
                                        <span class="text-[0.77rem] text-muted-foreground opacity-60">Las reservas aparecerán aquí cuando los clientes realicen solicitudes</span>
                                    </div>
                                </td>
                            </tr>

                            <!-- Filas -->
                            <tr
                                v-for="reservation in reservations"
                                :key="reservation.id"
                                class="border-b border-border align-top transition-colors duration-[0.12s] last:border-b-0 hover:bg-zinc-800/30"
                                :class="reservation.payment_status === 'pending' ? 'bg-amber-500/[0.03]' : ''"
                            >
                                <!-- Cliente -->
                                <td class="px-4 py-3 align-middle">
                                    <div class="flex items-center gap-2.5">
                                        <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full border border-green-500/25 bg-gradient-to-br from-green-500/20 to-green-600/30 text-[0.78rem] font-bold text-green-600">
                                            {{ (reservation.customer_name ?? '?').charAt(0).toUpperCase() }}
                                        </div>
                                        <div>
                                            <p class="font-semibold text-foreground">
                                                {{ reservation.customer_name }} {{ reservation.customer_lastname }}
                                            </p>
                                            <p class="flex items-center gap-1 text-[0.72rem] text-muted-foreground">
                                                <Hash :size="10" class="opacity-60" />
                                                DNI: {{ reservation.customer_dni ?? '—' }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <!-- Contacto -->
                                <td class="px-4 py-3 align-middle">
                                    <div class="flex items-center gap-1.5">
                                        <Phone :size="12" class="flex-shrink-0 text-muted-foreground opacity-60" />
                                        <span class="text-[0.83rem] text-foreground">{{ reservation.customer_phone ?? '—' }}</span>
                                    </div>
                                </td>

                                <!-- Evento -->
                                <td class="px-4 py-3 align-middle">
                                    <span class="inline-flex items-center gap-1 rounded-md border border-green-500/[0.18] bg-green-500/[0.08] px-2 py-0.5 text-[0.78rem] font-semibold text-green-500">
                                        <CalendarDays :size="11" />
                                        {{ reservation.event_name ?? `Evento #${reservation.id_evento}` }}
                                    </span>
                                </td>

                                <!-- Fecha / Hora -->
                                <td class="px-4 py-3 align-middle">
                                    <p class="font-semibold text-foreground">{{ reservation.date }}</p>
                                    <p class="mt-0.5 flex items-center gap-1 text-[0.76rem] text-muted-foreground">
                                        <Clock :size="11" class="opacity-60" />
                                        {{ reservation.start_time }} — {{ reservation.end_time }}
                                    </p>
                                </td>

                                <!-- Comprobante -->
                                <td class="px-4 py-3 align-middle">
                                    <p class="text-[0.83rem] text-foreground">
                                        Nro: <span class="font-semibold">{{ reservation.payment_proof_number ?? '—' }}</span>
                                    </p>
                                    <a
                                        v-if="reservation.payment_proof_name"
                                        :href="`/storage/reservation-proofs/${reservation.payment_proof_name}`"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="mt-0.5 inline-flex items-center gap-1 text-[0.72rem] font-semibold text-blue-400 underline underline-offset-2 hover:text-blue-300"
                                    >
                                        <Image :size="11" />
                                        Ver imagen
                                    </a>
                                    <p v-else class="mt-0.5 text-[0.72rem] italic text-muted-foreground/60">Sin imagen</p>
                                </td>

                                <!-- Estado pago -->
                                <td class="px-4 py-3 align-middle">
                                    <span :class="getPaymentClasses(reservation.payment_status)">
                                        <component :is="getPaymentIcon(reservation.payment_status)" :size="11" />
                                        {{ getPaymentLabel(reservation.payment_status) }}
                                    </span>
                                </td>

                                <!-- Estado reserva -->
                                <td class="px-4 py-3 align-middle">
                                    <span :class="getReservationClasses(reservation.reservation_status)">
                                        <CalendarCheck v-if="reservation.reservation_status === 'busy'" :size="11" />
                                        <CalendarDays v-else :size="11" />
                                        {{ reservation.reservation_status === 'busy' ? 'Ocupada' : 'No ocupada' }}
                                    </span>
                                </td>

                                <!-- Acciones -->
                                <td class="px-4 py-3 align-middle">
                                    <div class="flex flex-col gap-1.5">
                                        <Button
                                            type="button"
                                            size="sm"
                                            :disabled="reviewForm.processing || reservation.payment_status !== 'pending'"
                                            class="inline-flex items-center gap-1.5 rounded-[7px] bg-green-600 px-3 py-1.5 text-[0.75rem] font-semibold text-white shadow-[0_2px_8px_rgba(22,163,74,0.30)] transition hover:-translate-y-px hover:bg-green-700 disabled:cursor-not-allowed disabled:opacity-45 disabled:!transform-none"
                                            @click="approvePayment(reservation)"
                                        >
                                            <CheckCircle2 :size="13" />
                                            Aprobar pago
                                        </Button>
                                        <Button
                                            type="button"
                                            variant="destructive"
                                            size="sm"
                                            :disabled="reviewForm.processing || reservation.payment_status !== 'pending'"
                                            class="inline-flex items-center gap-1.5 rounded-[7px] border border-red-500/25 bg-red-500/[0.08] px-3 py-1.5 text-[0.75rem] font-semibold text-red-400 transition hover:-translate-y-px hover:border-red-500 hover:bg-red-500 hover:text-white hover:shadow-[0_2px_8px_rgba(239,68,68,0.3)] disabled:cursor-not-allowed disabled:opacity-45 disabled:!transform-none"
                                            @click="rejectPayment(reservation)"
                                        >
                                            <XCircle :size="13" />
                                            Rechazar
                                        </Button>
                                    </div>
                                </td>
                            </tr>

                        </tbody>
                    </table>
                </div>
            </section>

        </div>
    </AppLayout>
</template>