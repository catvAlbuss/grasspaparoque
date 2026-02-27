<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import EventosController from '@/actions/App/Http/Controllers/EventosController';
import {
    CalendarDays,
    Plus,
    Pencil,
    Trash2,
    X,
    Tag,
    FileText,
    ToggleLeft,
    Sparkles,
    Hash,
    CheckCircle2,
    XCircle,
    TrendingUp,
    TrendingDown,
    Minus,
    DollarSign,
    Activity,
    BarChart3,
} from 'lucide-vue-next';

type eventos = {
    id: number;
    nombre: string;
    precio: string;
    descripcion: string;
    estado: number;
};

type Props = {
    eventos: eventos[];
};

const props = defineProps<Props>();
const eventos = computed(() => props.eventos);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Eventos', href: './eventos' },
];

const editingId = ref<number | null>(null);

const form = useForm({
    nombre: '',
    precio: '',
    descripcion: '',
    estado: 1,
});

const deleteForm = useForm({});
const deleteError = computed(() => (deleteForm.errors as Record<string, string | undefined>).delete);
const isEditing = computed(() => editingId.value !== null);

// ── Estado inteligente ───────────────────────────────
const getEstadoInfo = (estado: number) => {
    if (estado >= 3) return { label: 'Alto',     icon: TrendingUp,   badgeClass: 'inline-flex items-center gap-1 rounded-full border border-purple-500/25 bg-purple-500/[0.08] px-2.5 py-1 text-[0.72rem] font-semibold text-purple-400 whitespace-nowrap',    dotClass: 'w-2 h-2 rounded-full flex-shrink-0 bg-purple-400  shadow-[0_0_5px_rgba(168,85,247,0.5)]' };
    if (estado === 2) return { label: 'Medio',   icon: Minus,        badgeClass: 'inline-flex items-center gap-1 rounded-full border border-amber-500/25  bg-amber-500/[0.08]  px-2.5 py-1 text-[0.72rem] font-semibold text-amber-400  whitespace-nowrap',    dotClass: 'w-2 h-2 rounded-full flex-shrink-0 bg-amber-400   shadow-[0_0_5px_rgba(245,158,11,0.5)]' };
    if (estado === 1) return { label: 'Activo',  icon: CheckCircle2, badgeClass: 'inline-flex items-center gap-1 rounded-full border border-green-500/20  bg-green-500/10      px-2.5 py-1 text-[0.72rem] font-semibold text-green-500 whitespace-nowrap',    dotClass: 'w-2 h-2 rounded-full flex-shrink-0 bg-green-400   shadow-[0_0_5px_rgba(74,222,128,0.5)]' };
    if (estado === 0) return { label: 'Inactivo',icon: XCircle,      badgeClass: 'inline-flex items-center gap-1 rounded-full border border-zinc-500/20   bg-zinc-500/10       px-2.5 py-1 text-[0.72rem] font-semibold text-zinc-400  whitespace-nowrap',    dotClass: 'w-2 h-2 rounded-full flex-shrink-0 bg-zinc-500' };
    return             { label: 'Bajo',          icon: TrendingDown, badgeClass: 'inline-flex items-center gap-1 rounded-full border border-red-500/25    bg-red-500/[0.08]    px-2.5 py-1 text-[0.72rem] font-semibold text-red-400   whitespace-nowrap',    dotClass: 'w-2 h-2 rounded-full flex-shrink-0 bg-red-400    shadow-[0_0_5px_rgba(239,68,68,0.4)]' };
};

// ── Precio nivel ─────────────────────────────────────
const getPrecioClasses = (precio: string): string => {
    const n = parseFloat(precio.replace(/[^0-9.]/g, ''));
    if (isNaN(n) || n === 0) return 'inline-flex items-center gap-0.5 rounded-md border border-zinc-700   bg-zinc-800/50       px-2 py-0.5 text-[0.78rem] font-bold text-zinc-400';
    if (n <= 50)             return 'inline-flex items-center gap-0.5 rounded-md border border-green-500/20 bg-green-500/[0.09]  px-2 py-0.5 text-[0.78rem] font-bold text-green-500';
    if (n <= 200)            return 'inline-flex items-center gap-0.5 rounded-md border border-amber-500/25 bg-amber-500/[0.08]  px-2 py-0.5 text-[0.78rem] font-bold text-amber-400';
    return                          'inline-flex items-center gap-0.5 rounded-md border border-purple-500/25 bg-purple-500/[0.08] px-2 py-0.5 text-[0.78rem] font-bold text-purple-400';
};

// ── Stats ────────────────────────────────────────────
const totalEventos   = computed(() => eventos.value.length);
const eventosActivos = computed(() => eventos.value.filter(e => e.estado > 0).length);
const precioPromedio = computed(() => {
    if (!eventos.value.length) return '0.00';
    const sum = eventos.value.reduce((a, e) => a + parseFloat(e.precio.replace(/[^0-9.]/g, '') || '0'), 0);
    return (sum / eventos.value.length).toFixed(2);
});

const resetForm = (): void => {
    editingId.value = null;
    form.reset();
    form.clearErrors();
};

const startEdit = (events: eventos): void => {
    editingId.value = events.id;
    form.clearErrors();
    form.nombre      = events.nombre;
    form.precio      = events.precio;
    form.descripcion = events.descripcion;
    form.estado      = events.estado;
};

const submit = (): void => {
    const options = { preserveScroll: true, onSuccess: () => resetForm() };
    if (isEditing.value && editingId.value !== null) {
        form.put(EventosController.update.url(editingId.value), options);
        return;
    }
    form.post(EventosController.store.url(), options);
};

const remove = (events: eventos): void => {
    if (!confirm(`Eliminar evento "${events.nombre}"?`)) return;
    deleteForm.delete(EventosController.destroy.url(events.id), { preserveScroll: true });
};
</script>

<template>
    <Head title="Eventos" />

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

                <!-- Top row: identity + badge -->
                <div class="relative mb-5 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3.5">
                        <div class="flex h-[46px] w-[46px] flex-shrink-0 items-center justify-center rounded-xl border border-green-400/30 bg-green-400/15 text-green-400">
                            <CalendarDays :size="24" />
                        </div>
                        <div>
                            <h1 class="text-[1.35rem] font-bold tracking-tight text-white" style="letter-spacing:-0.03em;">Gestión de Eventos</h1>
                           
                        </div>
                    </div>
                    <div class="flex items-center gap-1.5 whitespace-nowrap rounded-full border border-green-400/30 bg-green-500/15 px-3 py-1.5 text-[0.76rem] font-semibold text-green-400">
                        <Sparkles :size="12" />
                        <span>{{ totalEventos }} evento{{ totalEventos !== 1 ? 's' : '' }}</span>
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
                            <span class="block text-[0.95rem] font-bold leading-none text-white">{{ totalEventos }}</span>
                            <span class="mt-0.5 block text-[0.68rem]" style="color:rgba(134,239,172,0.65);">Total</span>
                        </div>
                    </div>
                    <div class="h-7 w-px flex-shrink-0" style="background:rgba(74,222,128,0.15);" />
                    <!-- Activos -->
                    <div class="flex flex-1 items-center justify-center gap-2">
                        <div class="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-[7px] border border-emerald-500/25 bg-emerald-500/[0.18] text-emerald-400">
                            <CheckCircle2 :size="14" />
                        </div>
                        <div>
                            <span class="block text-[0.95rem] font-bold leading-none text-white">{{ eventosActivos }}</span>
                            <span class="mt-0.5 block text-[0.68rem]" style="color:rgba(134,239,172,0.65);">Activos</span>
                        </div>
                    </div>
                    <div class="h-7 w-px flex-shrink-0" style="background:rgba(74,222,128,0.15);" />
                    <!-- Precio prom -->
                    <div class="flex flex-1 items-center justify-center gap-2">
                        <div class="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-[7px] border border-amber-500/25 bg-amber-500/[0.18] text-amber-400">
                            <DollarSign :size="14" />
                        </div>
                        <div>
                            <span class="block text-[0.95rem] font-bold leading-none text-white">${{ precioPromedio }}</span>
                            <span class="mt-0.5 block text-[0.68rem]" style="color:rgba(134,239,172,0.65);">Precio prom.</span>
                        </div>
                    </div>
                    <div class="h-7 w-px flex-shrink-0" style="background:rgba(74,222,128,0.15);" />
                    <!-- Inactivos -->
                    <div class="flex flex-1 items-center justify-center gap-2">
                        <div class="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-[7px] border border-blue-500/25 bg-blue-500/[0.18] text-blue-400">
                            <BarChart3 :size="14" />
                        </div>
                        <div>
                            <span class="block text-[0.95rem] font-bold leading-none text-white">{{ totalEventos - eventosActivos }}</span>
                            <span class="mt-0.5 block text-[0.68rem]" style="color:rgba(134,239,172,0.65);">Inactivos</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ══ FORM CARD ════════════════════════════════════ -->
            <section class="relative overflow-hidden rounded-[1.1rem] border border-border bg-background transition-shadow duration-300 hover:shadow-[0_4px_24px_rgba(22,163,74,0.07)]">
                <!-- Stripe -->
                <div
                    class="absolute inset-x-0 top-0 h-[2.5px]"
                    :class="isEditing
                        ? 'bg-gradient-to-r from-amber-500 to-orange-400'
                        : 'bg-gradient-to-r from-green-700 via-green-500 to-green-400'"
                />

                <!-- Card header -->
                <div class="flex items-center gap-3.5 border-b border-border px-6 py-4"
                    style="background:rgba(39,39,42,0.30);">
                    <div
                        class="flex h-[38px] w-[38px] flex-shrink-0 items-center justify-center rounded-[9px] border"
                        :class="isEditing
                            ? 'border-amber-500/25 bg-amber-500/[0.08] text-amber-500'
                            : 'border-green-500/20 bg-green-500/10 text-green-500'"
                    >
                        <Pencil v-if="isEditing" :size="16" />
                        <Plus v-else :size="16" />
                    </div>
                    <div>
                        <h2 class="text-[0.95rem] font-bold tracking-tight text-foreground" style="letter-spacing:-0.02em;">
                            {{ isEditing ? 'Editar Evento' : 'Nuevo Evento' }}
                        </h2>
                        <p class="mt-0.5 text-[0.76rem] text-muted-foreground">
                            {{ isEditing ? 'Modifica los datos del evento seleccionado' : 'Completa los campos para registrar un nuevo evento' }}
                        </p>
                    </div>
                </div>

                <!-- Form -->
                <form class="grid grid-cols-1 gap-4 px-6 py-5 md:grid-cols-2" @submit.prevent="submit">

                    <!-- Nombre -->
                    <div class="flex flex-col gap-1.5">
                        <Label class="flex items-center gap-1.5 text-[0.76rem] font-semibold tracking-[0.01em] text-foreground">
                            <CalendarDays :size="12" class="text-green-500" />
                            Nombre del Evento
                        </Label>
                        <Input
                            v-model="form.nombre"
                            type="text"
                            required
                            placeholder="Ej: Festival de Jazz 2025"
                            class="rounded-[9px] border-[1.5px] border-green-500/30 bg-zinc-800/50 text-[0.845rem] placeholder:text-muted-foreground/55 focus-visible:border-green-500 focus-visible:ring-2 focus-visible:ring-green-500/20"
                        />
                        <InputError :message="form.errors.nombre" />
                    </div>

                    <!-- Precio -->
                    <div class="flex flex-col gap-1.5">
                        <Label class="flex items-center gap-1.5 text-[0.76rem] font-semibold tracking-[0.01em] text-foreground">
                            <Tag :size="12" class="text-green-500" />
                            Precio
                        </Label>
                        <div class="relative">
                            <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[0.82rem] font-bold text-muted-foreground">$</span>
                            <Input
                                v-model="form.precio"
                                type="text"
                                required
                                placeholder="0.00"
                                class="rounded-[9px] border-[1.5px] border-green-500/30 bg-zinc-800/50 pl-7 text-[0.845rem] placeholder:text-muted-foreground/55 focus-visible:border-green-500 focus-visible:ring-2 focus-visible:ring-green-500/20"
                            />
                        </div>
                        <InputError :message="form.errors.precio" />
                    </div>

                    <!-- Descripción — full width -->
                    <div class="col-span-full flex flex-col gap-1.5">
                        <Label class="flex items-center gap-1.5 text-[0.76rem] font-semibold tracking-[0.01em] text-foreground">
                            <FileText :size="12" class="text-green-500" />
                            Descripción
                        </Label>
                        <Input
                            v-model="form.descripcion"
                            type="text"
                            placeholder="Describe el evento brevemente..."
                            class="rounded-[9px] border-[1.5px] border-green-500/30 bg-zinc-800/50 text-[0.845rem] placeholder:text-muted-foreground/55 focus-visible:border-green-500 focus-visible:ring-2 focus-visible:ring-green-500/20"
                        />
                        <InputError :message="form.errors.descripcion" />
                    </div>

                    <!-- Estado -->
                    <div class="flex flex-col gap-1.5">
                        <Label class="flex items-center gap-1.5 text-[0.76rem] font-semibold tracking-[0.01em] text-foreground">
                            <ToggleLeft :size="12" class="text-green-500" />
                            Estado
                            <span
                                v-if="isEditing"
                                class="ml-1 rounded-full border border-green-500/20 bg-green-500/10 px-2 py-0.5 text-[0.66rem] font-medium text-green-600"
                            >opcional</span>
                        </Label>
                        <Input
                            v-model="form.estado"
                            type="number"
                            :required="!isEditing"
                            placeholder="0=Inactivo · 1=Activo · 2=Medio · 3+=Alto"
                            class="rounded-[9px] border-[1.5px] border-green-500/30 bg-zinc-800/50 text-[0.845rem] placeholder:text-muted-foreground/55 focus-visible:border-green-500 focus-visible:ring-2 focus-visible:ring-green-500/20"
                        />
                        <p class="mt-0.5 text-[0.68rem] text-muted-foreground/70">0 = Inactivo &nbsp;·&nbsp; 1 = Activo &nbsp;·&nbsp; 2 = Medio &nbsp;·&nbsp; 3+ = Alto</p>
                        <InputError :message="form.errors.estado" />
                    </div>

                    <!-- Botones -->
                    <div class="col-span-full flex items-center gap-2.5 pt-1">
                        <button
                            type="submit"
                            :disabled="form.processing || deleteForm.processing"
                            class="relative inline-flex items-center gap-1.5 overflow-hidden rounded-[9px] bg-green-600 px-5 py-[0.56rem] text-[0.845rem] font-semibold text-white shadow-[0_2px_10px_rgba(22,163,74,0.38)] transition hover:-translate-y-px hover:bg-green-700 hover:shadow-[0_4px_16px_rgba(22,163,74,0.44)] active:translate-y-0 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <span class="pointer-events-none absolute inset-0 bg-gradient-to-br from-white/10 to-transparent" />
                            <Plus v-if="!isEditing" :size="14" />
                            <CheckCircle2 v-else :size="14" />
                            {{ isEditing ? 'Actualizar Evento' : 'Crear Evento' }}
                        </button>
                        <button
                            v-if="isEditing"
                            type="button"
                            :disabled="form.processing || deleteForm.processing"
                            class="inline-flex items-center gap-1.5 rounded-[9px] border-[1.5px] border-border bg-transparent px-4 py-[0.56rem] text-[0.845rem] font-semibold text-muted-foreground transition hover:border-red-500/25 hover:bg-red-500/[0.08] hover:text-red-400 disabled:cursor-not-allowed disabled:opacity-50"
                            @click="resetForm"
                        >
                            <X :size="14" />
                            Cancelar
                        </button>
                    </div>

                </form>
            </section>

            <!-- ══ TABLE CARD ═══════════════════════════════════ -->
            <section class="relative overflow-hidden rounded-[1.1rem] border border-border bg-background transition-shadow duration-300 hover:shadow-[0_4px_24px_rgba(22,163,74,0.07)]">
                <div class="absolute inset-x-0 top-0 h-[2.5px] bg-gradient-to-r from-green-700 via-green-500 to-green-400" />

                <!-- Card header -->
                <div class="flex items-center gap-3.5 border-b border-border px-6 py-4"
                    style="background:rgba(39,39,42,0.30);">
                    <div class="flex h-[38px] w-[38px] flex-shrink-0 items-center justify-center rounded-[9px] border border-blue-500/25 bg-blue-500/[0.08] text-blue-400">
                        <BarChart3 :size="16" />
                    </div>
                    <div>
                        <h2 class="text-[0.95rem] font-bold tracking-tight text-foreground" style="letter-spacing:-0.02em;">Listado de Eventos</h2>
                        <p class="mt-0.5 text-[0.76rem] text-muted-foreground">
                            {{ totalEventos }} evento{{ totalEventos !== 1 ? 's' : '' }} registrado{{ totalEventos !== 1 ? 's' : '' }} · {{ eventosActivos }} activo{{ eventosActivos !== 1 ? 's' : '' }}
                        </p>
                    </div>
                </div>

                <!-- Tabla -->
                <table class="w-full border-separate border-spacing-0 text-[0.83rem]">
                    <thead>
                        <tr style="background:rgba(39,39,42,0.30);">
                            <th class="w-[70px] whitespace-nowrap border-b border-green-500/15 px-4 py-2.5 text-left text-[0.68rem] font-bold uppercase tracking-[0.07em] text-green-500">
                                <Hash :size="11" class="mr-0.5 inline align-middle opacity-80" />ID
                            </th>
                            <th class="whitespace-nowrap border-b border-green-500/15 px-4 py-2.5 text-left text-[0.68rem] font-bold uppercase tracking-[0.07em] text-green-500">
                                <CalendarDays :size="11" class="mr-0.5 inline align-middle opacity-80" />Nombre
                            </th>
                            <th class="whitespace-nowrap border-b border-green-500/15 px-4 py-2.5 text-left text-[0.68rem] font-bold uppercase tracking-[0.07em] text-green-500">
                                <Tag :size="11" class="mr-0.5 inline align-middle opacity-80" />Precio
                            </th>
                            <th class="whitespace-nowrap border-b border-green-500/15 px-4 py-2.5 text-left text-[0.68rem] font-bold uppercase tracking-[0.07em] text-green-500">
                                <FileText :size="11" class="mr-0.5 inline align-middle opacity-80" />Descripción
                            </th>
                            <th class="whitespace-nowrap border-b border-green-500/15 px-4 py-2.5 text-left text-[0.68rem] font-bold uppercase tracking-[0.07em] text-green-500">
                                <Activity :size="11" class="mr-0.5 inline align-middle opacity-80" />Estado
                            </th>
                            <th class="w-[160px] whitespace-nowrap border-b border-green-500/15 px-4 py-2.5 text-right text-[0.68rem] font-bold uppercase tracking-[0.07em] text-green-500">
                                Acciones
                            </th>
                        </tr>
                    </thead>
                    <tbody>

                        <!-- Empty state -->
                        <tr v-if="eventos.length === 0">
                            <td colspan="6" class="px-4 py-12">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <div class="mb-2 flex h-16 w-16 items-center justify-center rounded-2xl border border-green-500/15 bg-green-500/[0.07] text-green-500 opacity-60">
                                        <CalendarDays :size="32" />
                                    </div>
                                    <p class="text-[0.9rem] font-bold text-muted-foreground">Sin eventos registrados</p>
                                    <span class="text-[0.77rem] text-muted-foreground opacity-60">Usa el formulario de arriba para crear tu primer evento</span>
                                </div>
                            </td>
                        </tr>

                        <!-- Filas -->
                        <tr
                            v-for="events in eventos"
                            :key="events.id"
                            class="border-b border-border transition-colors duration-[0.12s] last:border-b-0 hover:bg-zinc-800/30"
                            :class="editingId === events.id
                                ? 'bg-amber-500/[0.05] shadow-[inset_3px_0_0_theme(colors.amber.500)]'
                                : ''"
                        >
                            <!-- ID -->
                            <td class="px-4 py-[0.85rem] align-middle">
                                <span class="inline-flex items-center rounded-md border border-green-500/[0.18] bg-green-500/[0.08] px-2 py-0.5 font-mono text-[0.72rem] font-bold text-green-500">
                                    #{{ events.id }}
                                </span>
                            </td>

                            <!-- Nombre con dot -->
                            <td class="min-w-[140px] px-4 py-[0.85rem] align-middle font-semibold text-foreground">
                                <div class="flex items-center gap-2">
                                    <span :class="getEstadoInfo(events.estado).dotClass" />
                                    <span>{{ events.nombre }}</span>
                                </div>
                            </td>

                            <!-- Precio -->
                            <td class="px-4 py-[0.85rem] align-middle">
                                <span :class="getPrecioClasses(events.precio)">
                                    <DollarSign :size="11" />
                                    {{ events.precio }}
                                </span>
                            </td>

                            <!-- Descripción -->
                            <td class="max-w-[220px] px-4 py-[0.85rem] align-middle">
                                <span v-if="events.descripcion" class="block overflow-hidden text-ellipsis whitespace-nowrap text-[0.8rem] text-muted-foreground">
                                    {{ events.descripcion }}
                                </span>
                                <span v-else class="text-[0.76rem] italic text-zinc-500">Sin descripción</span>
                            </td>

                            <!-- Estado -->
                            <td class="px-4 py-[0.85rem] align-middle">
                                <span :class="getEstadoInfo(events.estado).badgeClass">
                                    <component :is="getEstadoInfo(events.estado).icon" :size="11" />
                                    {{ getEstadoInfo(events.estado).label }}
                                </span>
                            </td>

                           <!-- Acciones - SOLO ICONOS EN FILA con Tailwind -->
<td class="px-4 py-[0.85rem] align-middle text-right">
    <div class="flex items-center justify-end gap-2">
        <button
            class="flex h-8 w-8 items-center justify-center rounded-lg border border-green-500/20 bg-green-500/10 text-green-500 transition-all hover:translate-y-[-2px] hover:border-green-600 hover:bg-green-600 hover:text-white hover:shadow-[0_4px_12px_rgba(34,197,94,0.3)] disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:translate-y-0"
            :disabled="form.processing || deleteForm.processing"
            @click="startEdit(events)"
            :title="'Editar ' + events.nombre"
        >
            <Pencil :size="16" />
        </button>
        <button
            class="flex h-8 w-8 items-center justify-center rounded-lg border border-red-500/20 bg-red-500/10 text-red-400 transition-all hover:translate-y-[-2px] hover:border-red-500 hover:bg-red-500 hover:text-white hover:shadow-[0_4px_12px_rgba(239,68,68,0.3)] disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:translate-y-0"
            :disabled="form.processing || deleteForm.processing"
            @click="remove(events)"
            :title="'Eliminar ' + events.nombre"
        >
            <Trash2 :size="16" />
        </button>
    </div>
</td>
                        </tr>

                    </tbody>
                </table>

                <InputError :message="deleteError" class="px-6 pb-4" />
            </section>

        </div>
    </AppLayout>
</template>


