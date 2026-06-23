import { computed, onUnmounted, ref } from 'vue';
import type {
    BusyReservation,
    ChatMessage,
    ChatOption,
    Evento,
    ReservationDraft,
    ReservationStep,
} from '@/components/principal/reservation-chat/types';

const HOURS = Array.from(
    { length: 16 },
    (_, index) => `${String(index + 7).padStart(2, '0')}:00`,
);
const PAYMENT_TIMEOUT_MS = 10 * 60 * 1000;

const emptyDraft = (): ReservationDraft => ({
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
});

function timeNow() {
    return new Date().toLocaleTimeString('es-PE', {
        hour: '2-digit',
        minute: '2-digit',
    });
}

function minutes(time: string) {
    const [hour, minute] = time.split(':').map(Number);
    return hour * 60 + minute;
}

function timeFromMinutes(value: number) {
    return `${String(Math.floor(value / 60)).padStart(2, '0')}:${String(value % 60).padStart(2, '0')}`;
}

export function useReservationChat() {
    const step = ref<ReservationStep>('choose_event');
    const draft = ref<ReservationDraft>(emptyDraft());
    const events = ref<Evento[]>([]);
    const busy = ref<BusyReservation[]>([]);
    const messages = ref<ChatMessage[]>([]);
    const options = ref<ChatOption[]>([]);
    const typing = ref(false);
    const disabled = ref(false);
    const catalogError = ref(false);
    const paymentDeadline = ref<number | null>(null);
    const paymentCountdown = ref('');
    const whatsappReceiptLink = ref('');
    let paymentTimer: number | null = null;

    const selectedEvent = computed(() =>
        events.value.find((event) => event.id === draft.value.id_evento),
    );
    const totalAmount = computed(
        () => Number(selectedEvent.value?.precio ?? 50) * draft.value.hours,
    );
    const advanceAmount = computed(() => totalAmount.value / 2);
    const minDate = computed(() => {
        const now = new Date();
        const local = new Date(
            now.getTime() - now.getTimezoneOffset() * 60_000,
        );
        return local.toISOString().slice(0, 10);
    });
    const showPaymentCard = computed(() =>
        ['voucher_number', 'voucher_file'].includes(step.value),
    );
    const canUploadVoucher = computed(
        () => step.value === 'voucher_file' && !disabled.value,
    );
    const progress = computed(() => {
        const order: ReservationStep[] = [
            'choose_event',
            'date',
            'time',
            'duration',
            'name',
            'lastname',
            'dni',
            'phone',
            'voucher_number',
            'voucher_file',
        ];
        const index = order.indexOf(step.value);
        return step.value === 'done'
            ? 100
            : Math.max(8, Math.round(((index + 1) / order.length) * 100));
    });

    function addMessage(role: 'bot' | 'user', text: string) {
        messages.value.push({ role, text, time: timeNow() });
    }

    async function say(text: string) {
        typing.value = true;
        await new Promise((resolve) => window.setTimeout(resolve, 180));
        typing.value = false;
        addMessage('bot', text);
    }

    function eventGroupIds(eventId: number) {
        const event = events.value.find((item) => item.id === eventId);
        if (
            !event ||
            event.tipo_ambiente !== 'compartido' ||
            !event.ambiente_grupo
        )
            return [eventId];
        return events.value
            .filter((item) => item.ambiente_grupo === event.ambiente_grupo)
            .map((item) => item.id);
    }

    function isBusy(eventId: number, date: string, time: string) {
        const ids = eventGroupIds(eventId);
        return busy.value.some(
            (reservation) =>
                ids.includes(reservation.id_evento) &&
                reservation.date === date &&
                minutes(time) >= minutes(reservation.start_time) &&
                minutes(time) < minutes(reservation.end_time),
        );
    }

    function availableTimes(date: string) {
        if (!draft.value.id_evento) return [];
        return HOURS.filter(
            (time) => !isBusy(draft.value.id_evento as number, date, time),
        ).map((time) => ({ label: time, value: time }));
    }

    function hasAvailability(hours: number) {
        if (!draft.value.id_evento) return false;
        const start = HOURS.indexOf(draft.value.start_time);
        const slots = HOURS.slice(start, start + hours);
        return (
            start >= 0 &&
            slots.length === hours &&
            slots.every(
                (time) =>
                    !isBusy(
                        draft.value.id_evento as number,
                        draft.value.date,
                        time,
                    ),
            )
        );
    }

    async function loadData() {
        catalogError.value = false;
        try {
            const [eventResponse, busyResponse] = await Promise.all([
                fetch('/events/type_events'),
                fetch('/reservations/busy'),
            ]);
            if (!eventResponse.ok || !busyResponse.ok)
                throw new Error('Catalog request failed');
            events.value = await eventResponse.json();
            busy.value = await busyResponse.json();
        } catch {
            catalogError.value = true;
            events.value = [];
            busy.value = [];
        }
    }

    async function refreshBusy() {
        const response = await fetch('/reservations/busy');
        if (response.ok) busy.value = await response.json();
    }

    function clearPaymentTimer() {
        paymentDeadline.value = null;
        paymentCountdown.value = '';
        if (paymentTimer !== null) window.clearInterval(paymentTimer);
        paymentTimer = null;
    }

    function updateCountdown() {
        if (!paymentDeadline.value) return;
        const remaining = paymentDeadline.value - Date.now();
        if (remaining <= 0) {
            clearPaymentTimer();
            step.value = 'expired';
            disabled.value = true;
            options.value = [];
            addMessage(
                'bot',
                'El tiempo para enviar el comprobante terminó. No se registró la reserva. Puedes empezar de nuevo cuando desees.',
            );
            return;
        }
        const seconds = Math.floor(remaining / 1000);
        paymentCountdown.value = `${String(Math.floor(seconds / 60)).padStart(2, '0')}:${String(seconds % 60).padStart(2, '0')}`;
    }

    function startPaymentTimer() {
        clearPaymentTimer();
        paymentDeadline.value = Date.now() + PAYMENT_TIMEOUT_MS;
        updateCountdown();
        paymentTimer = window.setInterval(updateCountdown, 1000);
    }

    async function start() {
        clearPaymentTimer();
        draft.value = emptyDraft();
        messages.value = [];
        options.value = [];
        disabled.value = false;
        whatsappReceiptLink.value = '';
        step.value = 'choose_event';
        if (!events.value.length && !catalogError.value) await loadData();
        await say(
            '¡Hola! Soy tu asistente de reservas. Te acompañaré paso a paso.',
        );
        if (catalogError.value || !events.value.length) {
            disabled.value = true;
            await say(
                'No pude cargar los tipos de reserva en este momento. Por seguridad no mostraré datos de prueba. Intenta nuevamente en unos minutos.',
            );
            return;
        }
        options.value = events.value.map((event) => ({
            label: `${event.nombre} · S/ ${Number(event.precio).toFixed(2)}`,
            value: String(event.id),
        }));
        await say('Primero, elige qué deseas reservar.');
    }

    async function selectOption(option: ChatOption) {
        if (disabled.value || typing.value) return;
        addMessage('user', option.label);
        options.value = [];
        if (step.value === 'choose_event') {
            draft.value.id_evento = Number(option.value);
            step.value = 'date';
            await say(
                'Muy bien. Selecciona una fecha disponible en el calendario.',
            );
        } else if (step.value === 'time') {
            draft.value.start_time = option.value;
            step.value = 'duration';
            await say('¿Cuántas horas necesitas?');
        }
    }

    async function selectDate(date: string) {
        if (!date || date < minDate.value) {
            await say('Selecciona hoy o una fecha futura.');
            return;
        }
        addMessage(
            'user',
            new Date(`${date}T12:00:00`).toLocaleDateString('es-PE', {
                dateStyle: 'long',
            }),
        );
        draft.value.date = date;
        options.value = availableTimes(date);
        if (!options.value.length) {
            await say(
                'Ese día no tiene horarios disponibles. Elige otra fecha, por favor.',
            );
            return;
        }
        step.value = 'time';
        await say('Estos son los horarios de inicio disponibles:');
    }

    async function selectDuration(hours: number) {
        addMessage('user', `${hours} ${hours === 1 ? 'hora' : 'horas'}`);
        if (!hasAvailability(hours)) {
            step.value = 'time';
            options.value = availableTimes(draft.value.date);
            await say(
                'Ese bloque no está disponible de forma continua. Elige otra hora de inicio.',
            );
            return;
        }
        draft.value.hours = hours;
        draft.value.end_time = timeFromMinutes(
            minutes(draft.value.start_time) + hours * 60,
        );
        step.value = 'name';
        await say(
            `Perfecto: ${draft.value.start_time} a ${draft.value.end_time}. Ahora escribe tus nombres.`,
        );
    }

    async function submitText(value: string) {
        if (disabled.value || typing.value) return;
        addMessage('user', value);
        if (step.value === 'name' || step.value === 'lastname') {
            if (value.trim().length < 2) {
                await say(
                    'Necesito al menos dos caracteres. Inténtalo nuevamente.',
                );
                return;
            }
            if (step.value === 'name') {
                draft.value.name = value;
                step.value = 'lastname';
                await say('Gracias. Ahora escribe tus apellidos.');
            } else {
                draft.value.lastname = value;
                step.value = 'dni';
                await say('Escribe tu DNI de 8 dígitos.');
            }
            return;
        }
        if (step.value === 'dni') {
            const digits = value.replace(/\D/g, '');
            if (!/^\d{8}$/.test(digits)) {
                await say('El DNI debe contener exactamente 8 dígitos.');
                return;
            }
            draft.value.dni = digits;
            step.value = 'phone';
            await say('Ahora escribe tu celular de 9 dígitos.');
            return;
        }
        if (step.value === 'phone') {
            const digits = value.replace(/\D/g, '');
            if (!/^9\d{8}$/.test(digits)) {
                await say('El celular debe empezar con 9 y tener 9 dígitos.');
                return;
            }
            draft.value.phone = digits;
            step.value = 'voucher_number';
            startPaymentTimer();
            await say(
                `Tu reserva suma S/ ${totalAmount.value.toFixed(2)}. El adelanto es S/ ${advanceAmount.value.toFixed(2)}. Realiza el pago y escribe el número de operación.`,
            );
            return;
        }
        if (step.value === 'voucher_number') {
            if (!/^\d+$/.test(value)) {
                await say('El número de operación debe contener solo dígitos.');
                return;
            }
            draft.value.payment_proof_number = value;
            step.value = 'voucher_file';
            await say('Por último, sube una foto clara del comprobante.');
        }
    }

    async function askAssistant(question: string) {
        addMessage('user', question);
        typing.value = true;
        try {
            const response = await fetch('/reservation-assistant', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN':
                        (
                            document.querySelector(
                                'meta[name="csrf-token"]',
                            ) as HTMLMetaElement | null
                        )?.content ?? '',
                },
                body: JSON.stringify({
                    question,
                    step: step.value,
                    reservation: {
                        event: selectedEvent.value?.nombre,
                        date: draft.value.date,
                        start_time: draft.value.start_time,
                        hours: draft.value.hours,
                    },
                }),
            });
            const payload = await response.json();
            addMessage(
                'bot',
                response.ok
                    ? payload.message
                    : 'Puedo ayudarte con el flujo guiado. Selecciona la opción indicada o comunícate con nosotros si necesitas atención especial.',
            );
        } catch {
            addMessage(
                'bot',
                'La ayuda inteligente no está disponible ahora, pero puedes continuar con el flujo guiado.',
            );
        } finally {
            typing.value = false;
        }
    }

    async function submitFile(file: File) {
        if (
            step.value !== 'voucher_file' ||
            !paymentDeadline.value ||
            Date.now() >= paymentDeadline.value
        )
            return;
        if (!file.type.startsWith('image/') || file.size > 5 * 1024 * 1024) {
            await say('Usa una imagen JPG, PNG o WEBP de hasta 5 MB.');
            return;
        }
        draft.value.payment_proof_file = file;
        addMessage('user', `Comprobante: ${file.name}`);
        await save();
    }

    async function save() {
        if (!draft.value.payment_proof_file) return;
        step.value = 'submitting';
        typing.value = true;
        const form = new FormData();
        const fields: (keyof ReservationDraft)[] = [
            'name',
            'lastname',
            'dni',
            'phone',
            'id_evento',
            'date',
            'start_time',
            'end_time',
            'payment_proof_number',
        ];
        fields.forEach((field) =>
            form.append(field, String(draft.value[field] ?? '')),
        );
        form.append('payment_proof_file', draft.value.payment_proof_file);
        try {
            const response = await fetch('/reservations/customers', {
                method: 'POST',
                body: form,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN':
                        (
                            document.querySelector(
                                'meta[name="csrf-token"]',
                            ) as HTMLMetaElement | null
                        )?.content ?? '',
                },
            });
            const payload = await response.json();
            typing.value = false;
            if (!response.ok) {
                step.value = 'voucher_file';
                await say(
                    payload?.message ??
                        'No se pudo registrar la reserva. Revisa los datos e intenta nuevamente.',
                );
                return;
            }
            clearPaymentTimer();
            disabled.value = true;
            step.value = 'done';
            await refreshBusy();
            const text = `Hola, envío el comprobante de la reserva de ${draft.value.name} ${draft.value.lastname}, para el ${draft.value.date} de ${draft.value.start_time} a ${draft.value.end_time}. Operación: ${draft.value.payment_proof_number}.`;
            const number =
                import.meta.env.VITE_RESERVATION_WHATSAPP || '51999999999';
            whatsappReceiptLink.value = `https://wa.me/${number}?text=${encodeURIComponent(text)}`;
            await say(
                `¡Listo, ${draft.value.name}! Guardamos tu solicitud. Queda pendiente de validación administrativa y te confirmaremos por WhatsApp.`,
            );
        } catch {
            typing.value = false;
            step.value = 'voucher_file';
            await say(
                'Hubo un problema de red. Tu información sigue aquí; intenta subir el comprobante nuevamente.',
            );
        }
    }

    onUnmounted(clearPaymentTimer);
    return {
        step,
        messages,
        options,
        typing,
        disabled,
        draft,
        totalAmount,
        advanceAmount,
        minDate,
        progress,
        showPaymentCard,
        canUploadVoucher,
        paymentCountdown,
        whatsappReceiptLink,
        loadData,
        start,
        selectOption,
        selectDate,
        selectDuration,
        submitText,
        submitFile,
        askAssistant,
    };
}
