<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

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
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Reservas',
        href: '/reservations',
    },
];

const statusLabel: Record<Reservation['payment_status'], string> = {
    pending: 'Pendiente',
    approved: 'Aprobado',
    rejected: 'Rechazado',
};

const statusClasses: Record<Reservation['payment_status'], string> = {
    pending: 'bg-amber-100 text-amber-900',
    approved: 'bg-emerald-100 text-emerald-900',
    rejected: 'bg-rose-100 text-rose-900',
};

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
        <div class="space-y-6 p-4">
            <section class="rounded-xl border border-sidebar-border/70 bg-background p-4">
                <h1 class="text-xl font-semibold">Panel de Reservaciones</h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    Valida comprobantes pendientes. Solo al aprobar se ocupa la hora en calendario.
                </p>
                <InputError :message="approvalError" class="mt-3" />
            </section>

            <section class="rounded-xl border border-sidebar-border/70 bg-background p-4">
                <div class="mt-2 overflow-x-auto">
                    <table class="w-full min-w-[1100px] text-sm">
                        <thead class="border-b text-left">
                            <tr>
                                <th class="px-2 py-2">Cliente</th>
                                <th class="px-2 py-2">Contacto</th>
                                <th class="px-2 py-2">Evento</th>
                                <th class="px-2 py-2">Fecha/Hora</th>
                                <th class="px-2 py-2">Comprobante</th>
                                <th class="px-2 py-2">Pago</th>
                                <th class="px-2 py-2">Reserva</th>
                                <th class="px-2 py-2">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="reservations.length === 0">
                                <td colspan="8" class="px-2 py-4 text-center text-muted-foreground">
                                    No hay reservas registradas.
                                </td>
                            </tr>
                            <tr v-for="reservation in reservations" :key="reservation.id" class="border-b align-top">
                                <td class="px-2 py-2">
                                    <p class="font-medium">
                                        {{ reservation.customer_name }} {{ reservation.customer_lastname }}
                                    </p>
                                    <p class="text-xs text-muted-foreground">
                                        DNI: {{ reservation.customer_dni ?? '-' }}
                                    </p>
                                </td>
                                <td class="px-2 py-2">
                                    {{ reservation.customer_phone ?? '-' }}
                                </td>
                                <td class="px-2 py-2">
                                    {{ reservation.event_name ?? `Evento #${reservation.id_evento}` }}
                                </td>
                                <td class="px-2 py-2">
                                    <p>{{ reservation.date }}</p>
                                    <p class="text-xs text-muted-foreground">
                                        {{ reservation.start_time }} - {{ reservation.end_time }}
                                    </p>
                                </td>
                                <td class="px-2 py-2">
                                    <p>Nro: {{ reservation.payment_proof_number ?? '-' }}</p>
                                    <a
                                        v-if="reservation.payment_proof_name"
                                        :href="`/storage/reservation-proofs/${reservation.payment_proof_name}`"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="text-xs font-medium text-blue-600 underline"
                                    >
                                        Ver imagen
                                    </a>
                                    <p v-else class="text-xs text-muted-foreground">Sin imagen</p>
                                </td>
                                <td class="px-2 py-2">
                                    <span
                                        class="inline-flex rounded-full px-2 py-1 text-xs font-semibold"
                                        :class="statusClasses[reservation.payment_status]"
                                    >
                                        {{ statusLabel[reservation.payment_status] }}
                                    </span>
                                </td>
                                <td class="px-2 py-2">
                                    {{ reservation.reservation_status === 'busy' ? 'Ocupada' : 'No ocupada' }}
                                </td>
                                <td class="px-2 py-2">
                                    <div class="flex gap-2">
                                        <Button
                                            type="button"
                                            size="sm"
                                            :disabled="reviewForm.processing || reservation.payment_status !== 'pending'"
                                            @click="approvePayment(reservation)"
                                        >
                                            Aprobar pago
                                        </Button>
                                        <Button
                                            type="button"
                                            variant="destructive"
                                            size="sm"
                                            :disabled="reviewForm.processing || reservation.payment_status !== 'pending'"
                                            @click="rejectPayment(reservation)"
                                        >
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
