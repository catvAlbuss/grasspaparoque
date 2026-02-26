<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import ProductsController from '@/actions/App/Http/Controllers/ProductsController';
import { Package, AlertTriangle, XCircle, DollarSign, Plus, Pencil, Trash2, X, Tag, FileText, Hash, CalendarDays, CheckCircle2, Sparkles, Activity } from 'lucide-vue-next';

type Products = {
    id: number;
    name: string;
    description: string;
    stock: number;
    price_unit: number;
    price_higher: number;
    expiration_date: string;
};

type Props = {
    products: Products[]
};

const props = defineProps<Props>();
const products = computed(() => props.products);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Productos', href: './products' },
];

const editingId = ref<number | null>(null);

const form = useForm({
    name: '',
    description: '',
    stock:1,
    price_unit: 1,
    price_higher:1,
    expiration_date:''
});

const deleteForm = useForm({});
const deleteError = computed(() => (deleteForm.errors as Record<string, string | undefined>).delete);
const isEditing = computed(() => editingId.value !== null);

// ── Badge de stock (clases completas ) ─────────────────────────────
function getStockBadge(stock: number) {
    const num = parseInt(stock.toString());
    if (num === 0) return {
        label: 'Agotado', icon: XCircle,
        badgeClass: 'inline-flex items-center gap-1 rounded-full border border-red-500/25 bg-red-500/[0.08] px-2.5 py-1 text-[0.72rem] font-semibold text-red-400 whitespace-nowrap',
        dotClass: 'w-2 h-2 rounded-full flex-shrink-0 bg-red-400 shadow-[0_0_5px_rgba(239,68,68,0.4)]'
    };
    if (num < 10) return {
        label: 'Bajo', icon: AlertTriangle,
        badgeClass: 'inline-flex items-center gap-1 rounded-full border border-amber-500/25 bg-amber-500/[0.08] px-2.5 py-1 text-[0.72rem] font-semibold text-amber-400 whitespace-nowrap',
        dotClass: 'w-2 h-2 rounded-full flex-shrink-0 bg-amber-400 shadow-[0_0_5px_rgba(245,158,11,0.5)]'
    };
    return {
        label: `${num}`, icon: CheckCircle2,
        badgeClass: 'inline-flex items-center gap-1 rounded-full border border-green-500/20 bg-green-500/10 px-2.5 py-1 text-[0.72rem] font-semibold text-green-500 whitespace-nowrap',
        dotClass: 'w-2 h-2 rounded-full flex-shrink-0 bg-green-400 shadow-[0_0_5px_rgba(74,222,128,0.5)]'
    };
}

// ── Nivel de precio (clases completas Tailwind) ─────────────────────────────
const getPrecioClasses = (price: string): string => {
    const n = parseFloat(price) || 0;
    if (n === 0) return 'inline-flex items-center gap-0.5 rounded-md border border-zinc-700 bg-zinc-800/50 px-2 py-0.5 text-[0.78rem] font-bold text-zinc-400';
    if (n <= 50) return 'inline-flex items-center gap-0.5 rounded-md border border-green-500/20 bg-green-500/[0.09] px-2 py-0.5 text-[0.78rem] font-bold text-green-500';
    if (n <= 200) return 'inline-flex items-center gap-0.5 rounded-md border border-amber-500/25 bg-amber-500/[0.08] px-2 py-0.5 text-[0.78rem] font-bold text-amber-400';
    return 'inline-flex items-center gap-0.5 rounded-md border border-purple-500/25 bg-purple-500/[0.08] px-2 py-0.5 text-[0.78rem] font-bold text-purple-400';
};

// ── Stats ────────────────────────────────────────────
const totalProducts = computed(() => products.value.length);
const lowStockProducts = computed(() => products.value.filter(p => p.stock < 10 && p.stock > 0).length);
const outOfStockProducts = computed(() => products.value.filter(p => p.stock === 0).length);
const totalInventoryValue = computed(() => 
    products.value.reduce((sum, p) => {
        const stock = p.stock || 0;
        const price = p.price_unit || 0;
        return sum + (stock * price);
    }, 0)
);

// Formatear moneda a Soles Peruanos (PEN)
const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('es-PE', {
        style: 'currency', currency: 'PEN', minimumFractionDigits: 2, currencyDisplay: 'symbol'
    }).format(value);
};

const resetForm = (): void => {
    editingId.value = null;
    form.reset();
    form.clearErrors();
};

const startEdit = (product: Products): void => {
    editingId.value = product.id;
    form.clearErrors();
    form.name = product.name;
    form.description = product.description;
    form.stock = product.stock;
    form.price_unit = product.price_unit;
    form.price_higher = product.price_higher;
    form.expiration_date = product.expiration_date;
};

const submit = (): void => {
    const options = { preserveScroll: true, onSuccess: () => resetForm() };
    if (isEditing.value && editingId.value !== null) {
        form.put(ProductsController.update.url(editingId.value), options);
        return;
    }
    form.post(ProductsController.store.url(), options);
};

const remove = (product: Products): void => {
    if (!confirm(`Eliminar producto "${product.name}"?`)) return;
    deleteForm.delete(ProductsController.destroy.url(product.id), { preserveScroll: true });
};
</script>

<template>
    <Head title="Productos - Grass Deportivo" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-5 p-5">

            <!-- ══ HERO HEADER ══════════════════════════════════ -->
            <div class="relative overflow-hidden rounded-[1.1rem] border border-green-400/[0.12] px-7 py-6"
                style="background: linear-gradient(135deg, #052e16 0%, #14532d 45%, #15803d 100%);">
                <div class="pointer-events-none absolute inset-0"
                    style="background: radial-gradient(ellipse at 10% 70%, rgba(34,197,94,0.28) 0%, transparent 55%), radial-gradient(ellipse at 90% 10%, rgba(74,222,128,0.15) 0%, transparent 50%);" />
                
                <div class="relative mb-5 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3.5">
                        <div class="flex h-[46px] w-[46px] flex-shrink-0 items-center justify-center rounded-xl border border-green-400/30 bg-green-400/15 text-green-400">
                            <Package :size="24" />
                        </div>
                        <div>
                            <h1 class="text-[1.35rem] font-bold tracking-tight text-white" style="letter-spacing:-0.03em;">Gestión de Productos</h1>
                        </div>
                    </div>
                    <div class="flex items-center gap-1.5 whitespace-nowrap rounded-full border border-green-400/30 bg-green-500/15 px-3 py-1.5 text-[0.76rem] font-semibold text-green-400">
                        <Sparkles :size="12" />
                        <span>{{ totalProducts }} producto{{ totalProducts !== 1 ? 's' : '' }}</span>
                    </div>
                </div>

                <!-- Stats row -->
                <div class="relative flex items-center rounded-[10px] border border-green-400/[0.12] px-4 py-2.5"
                    style="background:rgba(24,24,27,0.50); backdrop-filter:blur(8px);">
                    <div class="flex flex-1 items-center justify-center gap-2">
                        <div class="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-[7px] border border-green-500/25 bg-green-500/[0.18] text-green-400">
                            <Package :size="14" />
                        </div>
                        <div>
                            <span class="block text-[0.95rem] font-bold leading-none text-white">{{ totalProducts }}</span>
                            <span class="mt-0.5 block text-[0.68rem]" style="color:rgba(134,239,172,0.65);">Total</span>
                        </div>
                    </div>
                    <div class="h-7 w-px flex-shrink-0" style="background:rgba(74,222,128,0.15);" />
                    <div class="flex flex-1 items-center justify-center gap-2">
                        <div class="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-[7px] border border-amber-500/25 bg-amber-500/[0.18] text-amber-400">
                            <AlertTriangle :size="14" />
                        </div>
                        <div>
                            <span class="block text-[0.95rem] font-bold leading-none text-white">{{ lowStockProducts }}</span>
                            <span class="mt-0.5 block text-[0.68rem]" style="color:rgba(134,239,172,0.65);">Stock Bajo</span>
                        </div>
                    </div>
                    <div class="h-7 w-px flex-shrink-0" style="background:rgba(74,222,128,0.15);" />
                    <div class="flex flex-1 items-center justify-center gap-2">
                        <div class="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-[7px] border border-red-500/25 bg-red-500/[0.18] text-red-400">
                            <XCircle :size="14" />
                        </div>
                        <div>
                            <span class="block text-[0.95rem] font-bold leading-none text-white">{{ outOfStockProducts }}</span>
                            <span class="mt-0.5 block text-[0.68rem]" style="color:rgba(134,239,172,0.65);">Sin Stock</span>
                        </div>
                    </div>
                    <div class="h-7 w-px flex-shrink-0" style="background:rgba(74,222,128,0.15);" />
                    <div class="flex flex-1 items-center justify-center gap-2">
                        <div class="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-[7px] border border-emerald-500/25 bg-emerald-500/[0.18] text-emerald-400">
                            <DollarSign :size="14" />
                        </div>
                        <div>
                            <span class="block text-[0.95rem] font-bold leading-none text-white">{{ formatCurrency(totalInventoryValue) }}</span>
                            <span class="mt-0.5 block text-[0.68rem]" style="color:rgba(134,239,172,0.65);">Valor Inv.</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ══ FORM CARD ════════════════════════════════════ -->
            <section class="relative overflow-hidden rounded-[1.1rem] border border-border bg-background transition-shadow duration-300 hover:shadow-[0_4px_24px_rgba(22,163,74,0.07)]">
                <div class="absolute inset-x-0 top-0 h-[2.5px]"
                    :class="isEditing ? 'bg-gradient-to-r from-amber-500 to-orange-400' : 'bg-gradient-to-r from-green-700 via-green-500 to-green-400'" />
                
                <div class="flex items-center gap-3.5 border-b border-border px-6 py-4" style="background:rgba(39,39,42,0.30);">
                    <div class="flex h-[38px] w-[38px] flex-shrink-0 items-center justify-center rounded-[9px] border"
                        :class="isEditing ? 'border-amber-500/25 bg-amber-500/[0.08] text-amber-500' : 'border-green-500/20 bg-green-500/10 text-green-500'">
                        <Pencil v-if="isEditing" :size="16" />
                        <Plus v-else :size="16" />
                    </div>
                    <div>
                        <h2 class="text-[0.95rem] font-bold tracking-tight text-foreground" style="letter-spacing:-0.02em;">
                            {{ isEditing ? 'Editar Producto' : 'Nuevo Producto' }}
                        </h2>
                        <p class="mt-0.5 text-[0.76rem] text-muted-foreground">
                            {{ isEditing ? 'Modifica los datos del producto seleccionado' : 'Completa los campos para registrar un nuevo producto' }}
                        </p>
                    </div>
                </div>

                <form class="grid grid-cols-1 gap-4 px-6 py-5 md:grid-cols-2" @submit.prevent="submit">
                    <!-- Nombre -->
                    <div class="flex flex-col gap-1.5">
                        <Label class="flex items-center gap-1.5 text-[0.76rem] font-semibold tracking-[0.01em] text-foreground">
                            <Package :size="12" class="text-green-500" />
                            Nombre del Producto
                        </Label>
                        <Input v-model="form.name" type="text" required placeholder="Ej: Balón de fútbol profesional"
                            class="rounded-[9px] border-[1.5px] border-green-500/30 bg-zinc-800/50 text-[0.845rem] placeholder:text-muted-foreground/55 focus-visible:border-green-500 focus-visible:ring-2 focus-visible:ring-green-500/20" />
                        <InputError :message="form.errors.name" />
                    </div>

                    <!-- Precio Unitario -->
                    <div class="flex flex-col gap-1.5">
                        <Label class="flex items-center gap-1.5 text-[0.76rem] font-semibold tracking-[0.01em] text-foreground">
                            <Tag :size="12" class="text-green-500" />
                            Precio Unitario
                        </Label>
                        <div class="relative">
                            <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[0.82rem] font-bold text-muted-foreground">S/</span>
                            <Input v-model="form.price_unit" type="number" step="0.01" min="0" required placeholder="0.00"
                                class="rounded-[9px] border-[1.5px] border-green-500/30 bg-zinc-800/50 pl-7 text-[0.845rem] placeholder:text-muted-foreground/55 focus-visible:border-green-500 focus-visible:ring-2 focus-visible:ring-green-500/20" />
                        </div>
                        <InputError :message="form.errors.price_unit" />
                    </div>

                    <!-- Descripción (full width) -->
                    <div class="col-span-full flex flex-col gap-1.5">
                        <Label class="flex items-center gap-1.5 text-[0.76rem] font-semibold tracking-[0.01em] text-foreground">
                            <FileText :size="12" class="text-green-500" />
                            Descripción
                        </Label>
                        <Input v-model="form.description" type="text" placeholder="Describe el producto brevemente..."
                            class="rounded-[9px] border-[1.5px] border-green-500/30 bg-zinc-800/50 text-[0.845rem] placeholder:text-muted-foreground/55 focus-visible:border-green-500 focus-visible:ring-2 focus-visible:ring-green-500/20" />
                        <InputError :message="form.errors.description" />
                    </div>

                    <!-- Stock -->
                    <div class="flex flex-col gap-1.5">
                        <Label class="flex items-center gap-1.5 text-[0.76rem] font-semibold tracking-[0.01em] text-foreground">
                            <CalendarDays :size="12" class="text-green-500" />
                            Stock
                        </Label>
                        <Input v-model="form.stock" type="number" min="0" required placeholder="0"
                            class="rounded-[9px] border-[1.5px] border-green-500/30 bg-zinc-800/50 text-[0.845rem] placeholder:text-muted-foreground/55 focus-visible:border-green-500 focus-visible:ring-2 focus-visible:ring-green-500/20" />
                        <p class="mt-0.5 text-[0.68rem] text-muted-foreground/70">Cantidad disponible en inventario</p>
                        <InputError :message="form.errors.stock" />
                    </div>

                    <!-- Precio al Mayor -->
                    <div class="flex flex-col gap-1.5">
                        <Label class="flex items-center gap-1.5 text-[0.76rem] font-semibold tracking-[0.01em] text-foreground">
                            <Tag :size="12" class="text-green-500" />
                            Precio al Mayor
                        </Label>
                        <div class="relative">
                            <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[0.82rem] font-bold text-muted-foreground">S/</span>
                            <Input v-model="form.price_higher" type="number" step="0.01" min="0" placeholder="0.00"
                                class="rounded-[9px] border-[1.5px] border-green-500/30 bg-zinc-800/50 pl-7 text-[0.845rem] placeholder:text-muted-foreground/55 focus-visible:border-green-500 focus-visible:ring-2 focus-visible:ring-green-500/20" />
                        </div>
                        <InputError :message="form.errors.price_higher" />
                    </div>

                    <!-- Fecha Vencimiento -->
                    <div class="flex flex-col gap-1.5">
                        <Label class="flex items-center gap-1.5 text-[0.76rem] font-semibold tracking-[0.01em] text-foreground">
                            <CalendarDays :size="12" class="text-green-500" />
                            Fecha Vencimiento
                        </Label>
                        <Input v-model="form.expiration_date" type="date"
                            class="rounded-[9px] border-[1.5px] border-green-500/30 bg-zinc-800/50 text-[0.845rem] placeholder:text-muted-foreground/55 focus-visible:border-green-500 focus-visible:ring-2 focus-visible:ring-green-500/20" />
                        <InputError :message="form.errors.expiration_date" />
                    </div>

                    <!-- Botones -->
                    <div class="col-span-full flex items-center gap-2.5 pt-1">
                        <button type="submit" :disabled="form.processing || deleteForm.processing"
                            class="relative inline-flex items-center gap-1.5 overflow-hidden rounded-[9px] bg-green-600 px-5 py-[0.56rem] text-[0.845rem] font-semibold text-white shadow-[0_2px_10px_rgba(22,163,74,0.38)] transition hover:-translate-y-px hover:bg-green-700 hover:shadow-[0_4px_16px_rgba(22,163,74,0.44)] active:translate-y-0 disabled:cursor-not-allowed disabled:opacity-50">
                            <span class="pointer-events-none absolute inset-0 bg-gradient-to-br from-white/10 to-transparent" />
                            <Plus v-if="!isEditing" :size="14" />
                            <CheckCircle2 v-else :size="14" />
                            {{ isEditing ? 'Actualizar Producto' : 'Crear Producto' }}
                        </button>
                        <button v-if="isEditing" type="button" :disabled="form.processing || deleteForm.processing" @click="resetForm"
                            class="inline-flex items-center gap-1.5 rounded-[9px] border-[1.5px] border-border bg-transparent px-4 py-[0.56rem] text-[0.845rem] font-semibold text-muted-foreground transition hover:border-red-500/25 hover:bg-red-500/[0.08] hover:text-red-400 disabled:cursor-not-allowed disabled:opacity-50">
                            <X :size="14" />
                            Cancelar
                        </button>
                    </div>
                </form>
            </section>

            <!-- ══ TABLE CARD ═══════════════════════════════════ -->
            <section class="relative overflow-hidden rounded-[1.1rem] border border-border bg-background transition-shadow duration-300 hover:shadow-[0_4px_24px_rgba(22,163,74,0.07)]">
                <div class="absolute inset-x-0 top-0 h-[2.5px] bg-gradient-to-r from-green-700 via-green-500 to-green-400" />
                
                <div class="flex items-center gap-3.5 border-b border-border px-6 py-4" style="background:rgba(39,39,42,0.30);">
                    <div class="flex h-[38px] w-[38px] flex-shrink-0 items-center justify-center rounded-[9px] border border-blue-500/25 bg-blue-500/[0.08] text-blue-400">
                        <Package :size="16" />
                    </div>
                    <div>
                        <h2 class="text-[0.95rem] font-bold tracking-tight text-foreground" style="letter-spacing:-0.02em;">Listado de Productos</h2>
                        <p class="mt-0.5 text-[0.76rem] text-muted-foreground">
                            {{ totalProducts }} producto{{ totalProducts !== 1 ? 's' : '' }} registrado{{ totalProducts !== 1 ? 's' : '' }} · {{ lowStockProducts }} con stock bajo
                        </p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full border-separate border-spacing-0 text-[0.83rem]">
                        <thead>
                            <tr style="background:rgba(39,39,42,0.30);">
                                <th class="w-[70px] whitespace-nowrap border-b border-green-500/15 px-4 py-2.5 text-left text-[0.68rem] font-bold uppercase tracking-[0.07em] text-green-500">
                                    <Hash :size="11" class="mr-0.5 inline align-middle opacity-80" />ID
                                </th>
                                <th class="whitespace-nowrap border-b border-green-500/15 px-4 py-2.5 text-left text-[0.68rem] font-bold uppercase tracking-[0.07em] text-green-500">
                                    <Package :size="11" class="mr-0.5 inline align-middle opacity-80" />Nombre
                                </th>
                                <th class="whitespace-nowrap border-b border-green-500/15 px-4 py-2.5 text-left text-[0.68rem] font-bold uppercase tracking-[0.07em] text-green-500">
                                    <FileText :size="11" class="mr-0.5 inline align-middle opacity-80" />Descripción
                                </th>
                                <th class="whitespace-nowrap border-b border-green-500/15 px-4 py-2.5 text-left text-[0.68rem] font-bold uppercase tracking-[0.07em] text-green-500">
                                    <CalendarDays :size="11" class="mr-0.5 inline align-middle opacity-80" />Stock
                                </th>
                                <th class="whitespace-nowrap border-b border-green-500/15 px-4 py-2.5 text-left text-[0.68rem] font-bold uppercase tracking-[0.07em] text-green-500">
                                    <Tag :size="11" class="mr-0.5 inline align-middle opacity-80" />P. Unitario
                                </th>
                                <th class="whitespace-nowrap border-b border-green-500/15 px-4 py-2.5 text-left text-[0.68rem] font-bold uppercase tracking-[0.07em] text-green-500">
                                    <Tag :size="11" class="mr-0.5 inline align-middle opacity-80" />P. Mayor
                                </th>
                                <th class="whitespace-nowrap border-b border-green-500/15 px-4 py-2.5 text-left text-[0.68rem] font-bold uppercase tracking-[0.07em] text-green-500">
                                    <CalendarDays :size="11" class="mr-0.5 inline align-middle opacity-80" />Vencimiento
                                </th>
                                <th class="w-[160px] whitespace-nowrap border-b border-green-500/15 px-4 py-2.5 text-right text-[0.68rem] font-bold uppercase tracking-[0.07em] text-green-500">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Empty state -->
                            <tr v-if="products.length === 0">
                                <td colspan="8" class="px-4 py-12">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <div class="mb-2 flex h-16 w-16 items-center justify-center rounded-2xl border border-green-500/15 bg-green-500/[0.07] text-green-500 opacity-60">
                                            <Package :size="32" />
                                        </div>
                                        <p class="text-[0.9rem] font-bold text-muted-foreground">Sin productos registrados</p>
                                        <span class="text-[0.77rem] text-muted-foreground opacity-60">Usa el formulario de arriba para crear tu primer producto</span>
                                    </div>
                                </td>
                            </tr>

                            <!-- Filas -->
                            <tr v-for="producto in products" :key="producto.id"
                                class="border-b border-border transition-colors duration-[0.12s] last:border-b-0 hover:bg-zinc-800/30"
                                :class="editingId === producto.id ? 'bg-amber-500/[0.05] shadow-[inset_3px_0_0_theme(colors.amber.500)]' : ''">
                                
                                <!-- ID -->
                                <td class="px-4 py-[0.85rem] align-middle">
                                    <span class="inline-flex items-center rounded-md border border-green-500/[0.18] bg-green-500/[0.08] px-2 py-0.5 font-mono text-[0.72rem] font-bold text-green-500">#{{ producto.id }}</span>
                                </td>

                                <!-- Nombre con dot -->
                                <td class="min-w-[140px] px-4 py-[0.85rem] align-middle font-semibold text-foreground">
                                    <div class="flex items-center gap-2">
                                        <span :class="getStockBadge(producto.stock).dotClass" />
                                        <span>{{ producto.name }}</span>
                                    </div>
                                </td>

                                <!-- Descripción -->
                                <td class="max-w-[220px] px-4 py-[0.85rem] align-middle">
                                    <span v-if="producto.description" class="block overflow-hidden text-ellipsis whitespace-nowrap text-[0.8rem] text-muted-foreground">{{ producto.description }}</span>
                                    <span v-else class="text-[0.76rem] italic text-zinc-500">Sin descripción</span>
                                </td>

                                <!-- Stock badge -->
                                <td class="px-4 py-[0.85rem] align-middle">
                                    <span :class="getStockBadge(producto.stock).badgeClass">
                                        <component :is="getStockBadge(producto.stock).icon" :size="11" />
                                        {{ getStockBadge(producto.stock).label }}
                                    </span>
                                </td>

                                <!-- Precio Unitario -->
                                <td class="px-4 py-[0.85rem] align-middle">
                                    <span :class="getPrecioClasses(producto.price_unit)">
                                        <DollarSign :size="11" />
                                        {{ formatCurrency(parseFloat(producto.price_unit) || 0) }}
                                    </span>
                                </td>

                                <!-- Precio al Mayor -->
                                <td class="px-4 py-[0.85rem] align-middle">
                                    <span :class="getPrecioClasses(producto.price_higher)">
                                        <DollarSign :size="11" />
                                        {{ formatCurrency(parseFloat(producto.price_higher) || 0) }}
                                    </span>
                                </td>

                                <!-- Vencimiento -->
                                <td class="px-4 py-[0.85rem] align-middle">
                                    <span v-if="producto.expiration_date" class="text-[0.8rem] text-muted-foreground">{{ producto.expiration_date }}</span>
                                    <span v-else class="text-[0.76rem] italic text-zinc-500">—</span>
                                </td>

                                <!-- Acciones (solo iconos) -->
                                <td class="px-4 py-[0.85rem] align-middle text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button class="flex h-8 w-8 items-center justify-center rounded-lg border border-green-500/20 bg-green-500/10 text-green-500 transition-all hover:translate-y-[-2px] hover:border-green-600 hover:bg-green-600 hover:text-white hover:shadow-[0_4px_12px_rgba(34,197,94,0.3)] disabled:cursor-not-allowed disabled:opacity-40"
                                            :disabled="form.processing || deleteForm.processing" @click="startEdit(producto)" :title="'Editar ' + producto.name">
                                            <Pencil :size="16" />
                                        </button>
                                        <button class="flex h-8 w-8 items-center justify-center rounded-lg border border-red-500/20 bg-red-500/10 text-red-400 transition-all hover:translate-y-[-2px] hover:border-red-500 hover:bg-red-500 hover:text-white hover:shadow-[0_4px_12px_rgba(239,68,68,0.3)] disabled:cursor-not-allowed disabled:opacity-40"
                                            :disabled="form.processing || deleteForm.processing" @click="remove(producto)" :title="'Eliminar ' + producto.name">
                                            <Trash2 :size="16" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <InputError :message="deleteError" class="px-6 pb-4" />
            </section>

        </div>
    </AppLayout>
</template>

