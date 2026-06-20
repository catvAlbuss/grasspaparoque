<script setup lang="ts">
import { CalendarCheck, MessageCircle, X } from 'lucide-vue-next';
import { nextTick, onMounted, ref, watch } from 'vue';
import { useReservationChat } from '@/composables/useReservationChat';
import ChatMessages from './reservation-chat/ChatMessages.vue';
import PaymentCard from './reservation-chat/PaymentCard.vue';
import ReservationControls from './reservation-chat/ReservationControls.vue';

const isOpen = ref(false);
const initialized = ref(false);
const scroll = ref<HTMLElement | null>(null);
const fileInput = ref<HTMLInputElement | null>(null);
const yapeNumber = import.meta.env.VITE_YAPE_NUMBER || '999999999';
const yapeName = import.meta.env.VITE_YAPE_NAME || 'Grasspaparoque';
const yapeQrUrl = import.meta.env.VITE_YAPE_QR_URL || '/img/yape-qr.svg';

const chat = useReservationChat();

async function open() {
    isOpen.value = true;
    if (!initialized.value) {
        initialized.value = true;
        await chat.start();
    }
}

async function restart() {
    initialized.value = true;
    await chat.loadData();
    await chat.start();
}

async function onFile(event: Event) {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0];
    input.value = '';
    if (file) await chat.submitFile(file);
}

watch(
    [chat.messages, chat.options, chat.typing],
    () =>
        nextTick(() => {
            if (scroll.value)
                scroll.value.scrollTop = scroll.value.scrollHeight;
        }),
    { deep: true },
);

onMounted(chat.loadData);
</script>

<template>
    <div class="fixed inset-x-0 bottom-0 z-50 sm:inset-auto sm:right-6 sm:bottom-6">
        <button v-if="!isOpen" type="button" aria-label="Abrir asistente de reservas"
            class="mr-3 mb-3 ml-auto flex items-center gap-2 rounded-full bg-emerald-600 px-5 py-3 font-semibold text-white shadow-xl transition hover:bg-emerald-500 sm:mr-0"
            @click="open">
            <MessageCircle :size="22" /> Reservar
        </button>

        <section v-if="isOpen" aria-label="Asistente de reservas"
            class="flex h-[82dvh] w-full flex-col overflow-hidden rounded-t-2xl border border-emerald-900 bg-slate-950 shadow-2xl sm:mb-14 sm:h-[38rem] sm:w-[25rem] sm:rounded-2xl">
            <header class="bg-gradient-to-r from-emerald-950 to-emerald-800 px-4 py-3 text-white">
                <div class="flex items-center gap-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-full bg-emerald-500/20">
                        <CalendarCheck :size="20" />
                    </span>
                    <div class="min-w-0 flex-1">
                        <h2 class="truncate text-sm font-semibold">
                            Asistente de reservas
                        </h2>
                        <p class="text-[11px] text-emerald-100">
                            Grasspaparoque · Te guiamos paso a paso
                        </p>
                    </div>
                    <button type="button" aria-label="Cerrar asistente" class="rounded-full p-2 hover:bg-white/10"
                        @click="isOpen = false">
                        <X :size="19" />
                    </button>
                </div>
                <div class="mt-3 h-1 overflow-hidden rounded-full bg-emerald-950/50"
                    aria-label="Progreso de la reserva">
                    <div class="h-full rounded-full bg-emerald-300 transition-all duration-500"
                        :style="{ width: `${chat.progress.value}%` }" />
                </div>
            </header>

            <main ref="scroll"
                class="flex-1 space-y-3 overflow-y-auto overscroll-contain bg-slate-900 px-3 py-4 text-sm"
                aria-live="polite">
                <ChatMessages :messages="chat.messages.value" :options="chat.options.value" :typing="chat.typing.value"
                    @select="chat.selectOption" />

                <PaymentCard v-if="chat.showPaymentCard.value" :total="chat.totalAmount.value"
                    :advance="chat.advanceAmount.value" :countdown="chat.paymentCountdown.value"
                    :yape-number="yapeNumber" :yape-name="yapeName" :qr-url="yapeQrUrl" />

                <a v-if="chat.whatsappReceiptLink.value" :href="chat.whatsappReceiptLink.value" target="_blank"
                    rel="noopener noreferrer"
                    class="block rounded-xl bg-green-500 px-3 py-2.5 text-center text-xs font-semibold text-white">Enviar
                    comprobante por WhatsApp</a>
            </main>

            <footer
                class="space-y-2 border-t border-emerald-900 bg-slate-950 p-3 pb-[calc(0.75rem+env(safe-area-inset-bottom))]">
                <input ref="fileInput" type="file" accept="image/jpeg,image/png,image/webp" class="hidden"
                    @change="onFile" />
                <ReservationControls :step="chat.step.value" :disabled="chat.disabled.value" :typing="chat.typing.value"
                    :min-date="chat.minDate.value" :can-upload="chat.canUploadVoucher.value" @submit="chat.submitText"
                    @date="chat.selectDate" @duration="chat.selectDuration" @upload="fileInput?.click()"
                    @ask="chat.askAssistant" @reset="restart" />
            </footer>
        </section>
    </div>
</template>
