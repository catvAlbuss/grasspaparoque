<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import BoxController from '@/actions/App/Http/Controllers/BoxController';
import {
    ShoppingCart, Package, AlertTriangle, XCircle, DollarSign, Plus, X,
    Tag, Hash, CalendarDays, CheckCircle2, Receipt, CreditCard, Smartphone,
    Search, Filter, Activity, Minus, TrendingUp, TrendingDown
} from 'lucide-vue-next';

type Products = {
    id: number;
    name: string;
    description: string;
    stock: string;
    price_unit: number;
    price_higher: string;
    expiration_date: string;
};

type SaleItem = {
    id: number,
    name: string,
    quantity: number,
    price_unit: number,
    total: number,
    method: string,
    status: string,
    max_stock: number
};

type Sale = {
    id: number;
    sale_number: string;
    created_at: string;
    total: number;
    subtotal: number;
    igv: number;
    payment_method: string;
    state: string;
    items: Array<{
        name: string;
        quantity: number;
        price_unit: number;
        total: number;
    }>;
};

type Props = {
    products: Products[];
    sales?: Sale[];
};

const props = defineProps<Props>();
const products = computed(() => props.products);
const sales = computed(() => props.sales || []);

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Caja', href: './caja' }];

// ============================
// FUNCIONES DE VENTA
// ============================
const SaleItems = ref<SaleItem[]>([]);
const searchQuery = ref<string>('');
const paymentMethod = ref<string>('cash');
const state = ref<string>('paid');

const addToSale = (product: Products) => {
    const stockDisponible = parseInt(product.stock);
    const existItem = SaleItems.value.find(item => item.id == product.id);
    if (existItem) {
        const newQuanti = existItem.quantity + 1;
        if (newQuanti > stockDisponible) {
            alert(`Stock insuficiente para ${product.name}. Disponible: ${stockDisponible}`);
            return;
        }
        existItem.quantity = newQuanti;
        existItem.total = existItem.price_unit * existItem.quantity;
    } else {
        SaleItems.value.push({
            id: product.id, name: product.name, quantity: 1,
            price_unit: product.price_unit, total: product.price_unit,
            method: paymentMethod.value, status: state.value, max_stock: stockDisponible
        });
    }
};

const updateQuantity = (index: number, newQuantity: number | string) => {
    let quantity = typeof newQuantity === 'string' ? parseInt(newQuantity) : newQuantity;
    if (isNaN(quantity) || quantity < 1) quantity = 1;
    
    const item = SaleItems.value[index];
    // Validar que no exceda el stock máximo
    if (quantity > item.max_stock) {
        alert(`Stock insuficiente para ${item.name}. Disponible: ${item.max_stock}`);
        quantity = item.max_stock;
    }
    
    item.quantity = quantity;
    item.total = item.price_unit * quantity;
};

const removeItemSale = (index: number) => SaleItems.value.splice(index, 1);

// ============================
// PAGINACIÓN
// ============================
const currentPage = ref(1);
const itemsPage = 8;
const paginatedProducts = computed(() => {
    const filtered = products.value.filter(p => 
        p.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        p.description.toLowerCase().includes(searchQuery.value.toLowerCase()));
    return filtered.slice((currentPage.value - 1) * itemsPage, currentPage.value * itemsPage);
});
const totalPages = computed(() => {
    const filtered = products.value.filter(p => 
        p.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        p.description.toLowerCase().includes(searchQuery.value.toLowerCase()));
    return Math.ceil(filtered.length / itemsPage);
});
const changePage = (page: number) => {
    if (page >= 1 && page <= totalPages.value) currentPage.value = page;
};

// ============================
// TOTALES Y MONEDA
// ============================
const subTotal = computed(() => SaleItems.value.reduce((s, i) => s + i.total, 0));
const igv = computed(() => Number(subTotal.value) * 0.18);
const total = computed(() => Number(subTotal.value) + igv.value);

const formatCurrency = (value: number) => new Intl.NumberFormat('es-PE', {
    style: 'currency', currency: 'PEN', minimumFractionDigits: 2
}).format(value);

const processSale = async () => {
    if (SaleItems.value.length === 0) { 
        alert('Selecciona al menos un producto'); 
        return; 
    }
    if (!confirm('¿Confirmar la venta?')) return;
    
    router.post('/box', {
        items: SaleItems.value.map(item => ({
            id: item.id,
            name: item.name,
            quantity: item.quantity,
            price_unit: item.price_unit,
            total: item.total
        })),
        total: total.value,
        state: state.value,
        payment_method: paymentMethod.value,
    }, {
        onSuccess: () => {
            // Limpiar carrito después de venta exitosa
            SaleItems.value = [];
        }
    });
};

// ============================
// INDICADORES
// ============================
const totalProducts = computed(() => products.value.length);
const lowStockCount = computed(() => products.value.filter(p => parseInt(p.stock) < 10 && parseInt(p.stock) > 0).length);
const outOfStockCount = computed(() => products.value.filter(p => parseInt(p.stock) === 0).length);

// ============================
// FILTROS HISTORIAL
// ============================
const historySearch = ref('');
const historyPaymentFilter = ref('all');
const historyStateFilter = ref('all');
const filteredSales = computed(() => sales.value.filter(sale => {
const matchSearch = historySearch.value === '' || 
sale.sale_number.toLowerCase().includes(historySearch.value.toLowerCase()) ||
sale.items.some(i => i.name.toLowerCase().includes(historySearch.value.toLowerCase()));
const matchPayment = historyPaymentFilter.value === 'all' || sale.payment_method === historyPaymentFilter.value;
const matchState = historyStateFilter.value === 'all' || sale.state === historyStateFilter.value;
return matchSearch && matchPayment && matchState;
}));
const historyPage = ref(1);
const historyPerPage = 10;
const paginatedSales = computed(() => filteredSales.value.slice((historyPage.value - 1) * historyPerPage, historyPage.value * historyPerPage));
const historyTotalPages = computed(() => Math.ceil(filteredSales.value.length / historyPerPage));

const formatDate = (dateString: string) => new Date(dateString).toLocaleString('es-PE', {
    year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit'
});

const clearHistoryFilters = () => {
    historySearch.value = '';
    historyPaymentFilter.value = 'all';
    historyStateFilter.value = 'all';
    historyPage.value = 1;
};

// ── Badges de stock (clases completas Tailwind) ─────────────────────────────
const getStockBadge = (stock: string) => {
    const num = parseInt(stock);
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
        label: num.toString(), icon: CheckCircle2,
        badgeClass: 'inline-flex items-center gap-1 rounded-full border border-green-500/20 bg-green-500/10 px-2.5 py-1 text-[0.72rem] font-semibold text-green-500 whitespace-nowrap',
        dotClass: 'w-2 h-2 rounded-full flex-shrink-0 bg-green-400 shadow-[0_0_5px_rgba(74,222,128,0.5)]'
    };
};

// ── Niveles de precio ───────────────────────────────────────────────────────
const getPrecioClasses = (price: number): string => {
    if (price === 0) return 'inline-flex items-center gap-0.5 rounded-md border border-zinc-700 bg-zinc-800/50 px-2 py-0.5 text-[0.78rem] font-bold text-zinc-400';
    if (price <= 50) return 'inline-flex items-center gap-0.5 rounded-md border border-green-500/20 bg-green-500/[0.09] px-2 py-0.5 text-[0.78rem] font-bold text-green-500';
    if (price <= 200) return 'inline-flex items-center gap-0.5 rounded-md border border-amber-500/25 bg-amber-500/[0.08] px-2 py-0.5 text-[0.78rem] font-bold text-amber-400';
    return 'inline-flex items-center gap-0.5 rounded-md border border-purple-500/25 bg-purple-500/[0.08] px-2 py-0.5 text-[0.78rem] font-bold text-purple-400';
};

// ── Método de pago badge ────────────────────────────────────────────────────
const getPaymentBadge = (method: string) => {
    const icons: Record<string, any> = { cash: DollarSign, card: CreditCard, transfer: Smartphone };
    const labels: Record<string, string> = { cash: 'Efectivo', card: 'Tarjeta', transfer: 'Yape/Plin' };
    const classes: Record<string, string> = {
        cash: 'inline-flex items-center gap-1 rounded-full border border-green-500/20 bg-green-500/10 px-2.5 py-1 text-[0.72rem] font-semibold text-green-500 whitespace-nowrap',
        card: 'inline-flex items-center gap-1 rounded-full border border-blue-500/25 bg-blue-500/[0.08] px-2.5 py-1 text-[0.72rem] font-semibold text-blue-400 whitespace-nowrap',
        transfer: 'inline-flex items-center gap-1 rounded-full border border-purple-500/25 bg-purple-500/[0.08] px-2.5 py-1 text-[0.72rem] font-semibold text-purple-400 whitespace-nowrap'
    };
    return { icon: icons[method] || DollarSign, label: labels[method] || method, class: classes[method] || classes.cash };
};

// ── Estado de venta badge ───────────────────────────────────────────────────
const getStateBadge = (s: string) => s === 'paid'
    ? { label: 'Pagado', class: 'inline-flex items-center gap-1 rounded-full border border-green-500/20 bg-green-500/10 px-2.5 py-1 text-[0.72rem] font-semibold text-green-500 whitespace-nowrap' }
    : { label: 'Pendiente', class: 'inline-flex items-center gap-1 rounded-full border border-amber-500/25 bg-amber-500/[0.08] px-2.5 py-1 text-[0.72rem] font-semibold text-amber-400 whitespace-nowrap' };
</script>

<template>
    <Head title="Caja - Grass Deportivo" />
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
                            <ShoppingCart :size="24" />
                        </div>
                        <div>
                            <h1 class="text-[1.35rem] font-bold tracking-tight text-white" style="letter-spacing:-0.03em;">Sistema de Caja</h1>
                        </div>
                    </div>
                    <div class="flex items-center gap-1.5 whitespace-nowrap rounded-full border border-green-400/30 bg-green-500/15 px-3 py-1.5 text-[0.76rem] font-semibold text-green-400">
                        <Receipt :size="12" />
                        <span>{{ formatCurrency(total) }}</span>
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
                            <span class="mt-0.5 block text-[0.68rem]" style="color:rgba(134,239,172,0.65);">Productos</span>
                        </div>
                    </div>
                    <div class="h-7 w-px flex-shrink-0" style="background:rgba(74,222,128,0.15);" />
                    <div class="flex flex-1 items-center justify-center gap-2">
                        <div class="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-[7px] border border-emerald-500/25 bg-emerald-500/[0.18] text-emerald-400">
                            <DollarSign :size="14" />
                        </div>
                        <div>
                            <span class="block text-[0.95rem] font-bold leading-none text-white">{{ formatCurrency(subTotal) }}</span>
                            <span class="mt-0.5 block text-[0.68rem]" style="color:rgba(134,239,172,0.65);">Subtotal</span>
                        </div>
                    </div>
                    <div class="h-7 w-px flex-shrink-0" style="background:rgba(74,222,128,0.15);" />
                    <div class="flex flex-1 items-center justify-center gap-2">
                        <div class="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-[7px] border border-amber-500/25 bg-amber-500/[0.18] text-amber-400">
                            <AlertTriangle :size="14" />
                        </div>
                        <div>
                            <span class="block text-[0.95rem] font-bold leading-none text-white">{{ lowStockCount }}</span>
                            <span class="mt-0.5 block text-[0.68rem]" style="color:rgba(134,239,172,0.65);">Stock Bajo</span>
                        </div>
                    </div>
                    <div class="h-7 w-px flex-shrink-0" style="background:rgba(74,222,128,0.15);" />
                    <div class="flex flex-1 items-center justify-center gap-2">
                        <div class="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-[7px] border border-blue-500/25 bg-blue-500/[0.18] text-blue-400">
                            <ShoppingCart :size="14" />
                        </div>
                        <div>
                            <span class="block text-[0.95rem] font-bold leading-none text-white">{{ SaleItems.length }}</span>
                            <span class="mt-0.5 block text-[0.68rem]" style="color:rgba(134,239,172,0.65);">En Carrito</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Layout: Productos + Venta -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                
                <!-- ══ PANEL IZQUIERDO: PRODUCTOS ═══════════════ -->
                <section class="relative overflow-hidden rounded-[1.1rem] border border-border bg-background transition-shadow duration-300 hover:shadow-[0_4px_24px_rgba(22,163,74,0.07)]">
                    <div class="absolute inset-x-0 top-0 h-[2.5px] bg-gradient-to-r from-green-700 via-green-500 to-green-400" />
                    
                    <div class="flex items-center gap-3.5 border-b border-border px-6 py-4" style="background:rgba(39,39,42,0.30);">
                        <div class="flex h-[38px] w-[38px] flex-shrink-0 items-center justify-center rounded-[9px] border border-blue-500/25 bg-blue-500/[0.08] text-blue-400">
                            <Package :size="16" />
                        </div>
                        <div>
                            <h2 class="text-[0.95rem] font-bold tracking-tight text-foreground" style="letter-spacing:-0.02em;">Productos</h2>
                            <p class="mt-0.5 text-[0.76rem] text-muted-foreground">{{ paginatedProducts.length }} de {{ totalProducts }} productos  |  {{ lowStockCount }} con stock bajo</p>
                        </div>
                    </div>

                    <!-- Buscador -->
                    <div class="px-6 py-4">
                        <div class="relative">
                            <Search :size="14" class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground" />
                            <Input v-model="searchQuery" type="text" placeholder="Buscar producto por nombre o descripción..."
                                class="rounded-[9px] border-[1.5px] border-green-500/30 bg-zinc-800/50 pl-9 text-[0.845rem] placeholder:text-muted-foreground/55 focus-visible:border-green-500 focus-visible:ring-2 focus-visible:ring-green-500/20" />
                        </div>
                    </div>

                    <!-- Tabla -->
                    <div class="overflow-x-auto">
                        <table class="w-full border-separate border-spacing-0 text-[0.83rem]">
                            <thead>
                                <tr style="background:rgba(39,39,42,0.30);">
                                    <th class="w-[70px] whitespace-nowrap border-b border-green-500/15 px-4 py-2.5 text-left text-[0.68rem] font-bold uppercase tracking-[0.07em] text-green-500">
                                        <Hash :size="11" class="mr-0.5 inline align-middle opacity-80" />ID
                                    </th>
                                    <th class="whitespace-nowrap border-b border-green-500/15 px-4 py-2.5 text-left text-[0.68rem] font-bold uppercase tracking-[0.07em] text-green-500">
                                        <Package :size="11" class="mr-0.5 inline align-middle opacity-80" />Producto
                                    </th>
                                    <th class="whitespace-nowrap border-b border-green-500/15 px-4 py-2.5 text-left text-[0.68rem] font-bold uppercase tracking-[0.07em] text-green-500">
                                        <CalendarDays :size="11" class="mr-0.5 inline align-middle opacity-80" />Stock
                                    </th>
                                    <th class="whitespace-nowrap border-b border-green-500/15 px-4 py-2.5 text-left text-[0.68rem] font-bold uppercase tracking-[0.07em] text-green-500">
                                        <Tag :size="11" class="mr-0.5 inline align-middle opacity-80" />Precio
                                    </th>
                                    <th class="w-[120px] whitespace-nowrap border-b border-green-500/15 px-4 py-2.5 text-right text-[0.68rem] font-bold uppercase tracking-[0.07em] text-green-500">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="paginatedProducts.length === 0">
                                    <td colspan="5" class="px-4 py-12">
                                        <div class="flex flex-col items-center justify-center gap-2">
                                            <div class="mb-2 flex h-16 w-16 items-center justify-center rounded-2xl border border-green-500/15 bg-green-500/[0.07] text-green-500 opacity-60">
                                                <Package :size="32" />
                                            </div>
                                            <p class="text-[0.9rem] font-bold text-muted-foreground">{{ searchQuery ? 'No se encontraron resultados' : 'No hay productos registrados' }}</p>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-for="p in paginatedProducts" :key="p.id"
                                    class="border-b border-border transition-colors duration-[0.12s] last:border-b-0 hover:bg-zinc-800/30"
                                    :class="{ 'opacity-50': parseInt(p.stock) === 0 }">
                                    <td class="px-4 py-[0.85rem] align-middle">
                                        <span class="inline-flex items-center rounded-md border border-green-500/[0.18] bg-green-500/[0.08] px-2 py-0.5 font-mono text-[0.72rem] font-bold text-green-500">#{{ p.id }}</span>
                                    </td>
                                    <td class="min-w-[140px] px-4 py-[0.85rem] align-middle font-semibold text-foreground">
                                        <div class="flex items-center gap-2">
                                            <span :class="getStockBadge(p.stock).dotClass" />
                                            <div>
                                                <span class="block">{{ p.name }}</span>
                                                <span v-if="p.description" class="block text-[0.72rem] text-muted-foreground">{{ p.description }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-[0.85rem] align-middle">
                                        <span :class="getStockBadge(p.stock).badgeClass">
                                            <component :is="getStockBadge(p.stock).icon" :size="11" />
                                            {{ getStockBadge(p.stock).label }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-[0.85rem] align-middle">
                                        <span :class="getPrecioClasses(p.price_unit)">
                                            <DollarSign :size="11" />
                                            {{ formatCurrency(p.price_unit) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-[0.85rem] align-middle text-right">
                                        <button class="flex h-8 w-8 items-center justify-center rounded-lg border border-green-500/20 bg-green-500/10 text-green-500 transition-all 
                                        hover:translate-y-[-2px] hover:border-green-600 hover:bg-green-600 hover:text-white hover:shadow-[0_4px_12px_rgba(34,197,94,0.3)] disabled:cursor-not-allowed disabled:opacity-40"
                                            :disabled="parseInt(p.stock) === 0" @click="addToSale(p)" :title="parseInt(p.stock) === 0 ? 'Agotado' : 'Agregar'">
                                            <Plus :size="16" />
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <div v-if="totalPages > 1" class="flex items-center justify-between border-t border-border px-6 py-3" style="background:rgba(39,39,42,0.30);">
                        <span class="text-[0.8rem] text-muted-foreground">Página {{ currentPage }} de {{ totalPages }}</span>
                        <div class="flex gap-2">
                            <button @click="changePage(currentPage - 1)" :disabled="currentPage === 1"
                                class="inline-flex items-center rounded-[7px] border border-border bg-zinc-800/50 px-3 py-1.5 text-[0.8rem] font-medium text-foreground transition hover:border-green-500/30 disabled:cursor-not-allowed disabled:opacity-40">Anterior</button>
                            <button @click="changePage(currentPage + 1)" :disabled="currentPage === totalPages"
                                class="inline-flex items-center rounded-[7px] border border-border bg-zinc-800/50 px-3 py-1.5 text-[0.8rem] font-medium text-foreground transition hover:border-green-500/30 disabled:cursor-not-allowed disabled:opacity-40">Siguiente</button>
                        </div>
                    </div>
                </section>

                <!-- ══ PANEL DERECHO: VENTA ACTUAL ═════════════ -->
                <section class="relative overflow-hidden rounded-[1.1rem] border border-border bg-background transition-shadow duration-300 hover:shadow-[0_4px_24px_rgba(22,163,74,0.07)]">
                    <div class="absolute inset-x-0 top-0 h-[2.5px] bg-gradient-to-r from-green-700 via-green-500 to-green-400" />
                    
                    <div class="flex items-center gap-3.5 border-b border-border px-6 py-4" style="background:rgba(39,39,42,0.30);">
                        <div class="flex h-[38px] w-[38px] flex-shrink-0 items-center justify-center rounded-[9px] border border-green-500/20 bg-green-500/10 text-green-500">
                            <ShoppingCart :size="16" />
                        </div>
                        <div>
                            <h2 class="text-[0.95rem] font-bold tracking-tight text-foreground" style="letter-spacing:-0.02em;">Boleta de Venta</h2>
                            <p class="mt-0.5 text-[0.76rem] text-muted-foreground">{{ SaleItems.length }} item{{ SaleItems.length !== 1 ? 's' : '' }} en la venta actual</p>
                        </div>
                    </div>

                    <!-- Lista de items -->
                    <div class="max-h-[320px] overflow-y-auto px-6 py-4 space-y-2">
                        <div v-if="SaleItems.length === 0" class="flex flex-col items-center justify-center gap-2 py-8">
                            <div class="flex h-16 w-16 items-center justify-center rounded-2xl border border-green-500/15 bg-green-500/[0.07] text-green-500 opacity-60">
                                <ShoppingCart :size="32" />
                            </div>
                            <p class="text-[0.9rem] font-bold text-muted-foreground">Carrito vacío</p>
                            <span class="text-[0.77rem] text-muted-foreground opacity-60">Selecciona productos para comenzar</span>
                        </div>

                        <div v-for="(item, idx) in SaleItems" :key="idx"
                            class="flex items-center justify-between rounded-[10px] border border-border bg-zinc-800/50 px-4 py-3 transition hover:border-green-500/30">
                            <div class="min-w-0">
                                <span class="block font-semibold text-foreground">{{ item.name }}</span>
                                <span class="block text-[0.75rem] text-muted-foreground">{{ formatCurrency(item.price_unit) }} c/u</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <!-- Controles de cantidad -->
                                <div class="flex items-center gap-1.5">
                                    <button type="button" @click="updateQuantity(idx, item.quantity - 1)"
                                        class="flex h-7 w-7 items-center justify-center rounded-[6px] bg-zinc-700 text-white transition hover:bg-green-600">
                                        <span class="text-lg leading-none">−</span>
                                    </button>
                                    <input type="number" :value="item.quantity" @input="(e) => updateQuantity(idx, (e.target as HTMLInputElement).value)"
                                        class="w-11 h-7 rounded-[6px] border border-zinc-600 bg-zinc-700 px-1 text-center text-[0.9rem] font-semibold text-white outline-none focus:border-green-500 focus:ring-1 focus:ring-green-500/30 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none" />
                                    <button type="button" @click="updateQuantity(idx, item.quantity + 1)"
                                        :disabled="item.quantity >= item.max_stock"
                                        class="flex h-7 w-7 items-center justify-center rounded-[6px] bg-zinc-700 text-white transition hover:bg-green-600 disabled:cursor-not-allowed disabled:opacity-40">
                                        <span class="text-lg leading-none">+</span>
                                    </button>
                                </div>
                                <span class="w-16 text-right font-bold text-green-400">{{ formatCurrency(item.total) }}</span>
                                <button type="button" @click="removeItemSale(idx)"
                                    class="flex h-7 w-7 items-center justify-center rounded-[6px] border border-red-500/20 bg-red-500/10 text-red-400 transition hover:border-red-500 hover:bg-red-500 hover:text-white">
                                    <X :size="14" />
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Footer: Pago + Totales -->
                    <div class="border-t border-border px-6 py-4" style="background:rgba(39,39,42,0.30);">
                        <!-- Métodos de pago -->
                        <div class="mb-4 flex gap-2">
                            <button type="button" @click="paymentMethod = 'cash'"
                                :class="['flex-1 inline-flex items-center justify-center gap-1.5 rounded-[8px] px-3 py-2 text-[0.8rem] font-semibold transition', paymentMethod === 'cash' ? 'border border-green-600 bg-green-600 text-white' : 'border border-border bg-zinc-800/50 text-muted-foreground hover:border-green-500/30']">
                                <DollarSign :size="14" /> Efectivo
                            </button>
                            <button type="button" @click="paymentMethod = 'card'"
                                :class="['flex-1 inline-flex items-center justify-center gap-1.5 rounded-[8px] px-3 py-2 text-[0.8rem] font-semibold transition', paymentMethod === 'card' ? 'border border-green-600 bg-green-600 text-white' : 'border border-border bg-zinc-800/50 text-muted-foreground hover:border-green-500/30']">
                                <CreditCard :size="14" /> Tarjeta
                            </button>
                            <button type="button" @click="paymentMethod = 'transfer'"
                                :class="['flex-1 inline-flex items-center justify-center gap-1.5 rounded-[8px] px-3 py-2 text-[0.8rem] font-semibold transition', paymentMethod === 'transfer' ? 'border border-green-600 bg-green-600 text-white' : 'border border-border bg-zinc-800/50 text-muted-foreground hover:border-green-500/30']">
                                <Smartphone :size="14" /> Yape
                            </button>
                        </div>

                        <!-- Totales -->
                        <div class="mb-4 space-y-2 text-[0.9rem]">
                            <div class="flex justify-between text-muted-foreground"><span>Subtotal</span><span>{{ formatCurrency(subTotal) }}</span></div>
                            <div class="flex justify-between text-muted-foreground"><span>IGV (18%)</span><span>{{ formatCurrency(igv) }}</span></div>
                            <div class="flex justify-between border-t border-border pt-2 text-[1.1rem] font-bold">
                                <span class="text-foreground">TOTAL</span>
                                <span class="text-green-400">{{ formatCurrency(total) }}</span>
                            </div>
                        </div>

                        <!-- Botón procesar -->
                        <button type="button" @click="processSale" :disabled="SaleItems.length === 0"
                            class="relative w-full overflow-hidden rounded-[10px] bg-green-600 py-4 text-[1rem] font-semibold text-white shadow-[0_2px_10px_rgba(22,163,74,0.38)] transition hover:-translate-y-0.5 hover:bg-green-700 hover:shadow-[0_4px_16px_rgba(22,163,74,0.44)] active:translate-y-0 disabled:cursor-not-allowed disabled:opacity-50">
                            <span class="pointer-events-none absolute inset-0 bg-gradient-to-br from-white/10 to-transparent" />
                            <span class="relative flex items-center justify-center gap-2">
                                <Receipt :size="18" />
                                Registrar Venta: {{ formatCurrency(total) }}
                            </span>
                        </button>
                    </div>
                </section>
            </div>

            <!-- ══ HISTORIAL DE VENTAS (DEBAJO) ═══════════════ -->
            <section class="relative overflow-hidden rounded-[1.1rem] border border-border bg-background transition-shadow duration-300 hover:shadow-[0_4px_24px_rgba(22,163,74,0.07)]">
                <div class="absolute inset-x-0 top-0 h-[2.5px] bg-gradient-to-r from-green-700 via-green-500 to-green-400" />
                
                <div class="flex items-center gap-3.5 border-b border-border px-6 py-4" style="background:rgba(39,39,42,0.30);">
                    <div class="flex h-[38px] w-[38px] flex-shrink-0 items-center justify-center rounded-[9px] border border-blue-500/25 bg-blue-500/[0.08] text-blue-400">
                        <Receipt :size="16" />
                    </div>
                    <div>
                        <h2 class="text-[0.95rem] font-bold tracking-tight text-foreground" style="letter-spacing:-0.02em;">Historial de Ventas</h2>
                        <p class="mt-0.5 text-[0.76rem] text-muted-foreground">{{ filteredSales.length }} de {{ sales.length }} ventas registradas</p>
                    </div>
                </div>

                <!-- Stats rápidos -->
                <div class="grid grid-cols-3 gap-4 px-6 py-4">
                    <div class="flex items-center gap-3 rounded-[8px] border border-border bg-zinc-800/30 px-4 py-3">
                        <span class="flex h-8 w-8 items-center justify-center rounded-[7px] border border-green-500/25 bg-green-500/[0.18] text-green-400">
                            <Activity :size="14" />
                        </span>
                        <div>
                            <span class="block text-[1rem] font-bold text-white">{{ sales.length }}</span>
                            <span class="block text-[0.7rem] text-muted-foreground">Total Ventas</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 rounded-[8px] border border-border bg-zinc-800/30 px-4 py-3">
                        <span class="flex h-8 w-8 items-center justify-center rounded-[7px] border border-emerald-500/25 bg-emerald-500/[0.18] text-emerald-400">
                            <DollarSign :size="14" />
                        </span>
                        <div>
                            <span class="block text-[1rem] font-bold text-green-400">{{ formatCurrency(sales.reduce((s,v) => s + v.total, 0)) }}</span>
                            <span class="block text-[0.7rem] text-muted-foreground">Ingresos Totales</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 rounded-[8px] border border-border bg-zinc-800/30 px-4 py-3">
                        <span class="flex h-8 w-8 items-center justify-center rounded-[7px] border border-blue-500/25 bg-blue-500/[0.18] text-blue-400">
                            <CalendarDays :size="14" />
                        </span>
                        <div>
                            <span class="block text-[1rem] font-bold text-white">{{ sales.filter(s => s.created_at.startsWith(new Date().toISOString().split('T')[0])).length }}</span>
                            <span class="block text-[0.7rem] text-muted-foreground">Ventas Hoy</span>
                        </div>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="grid grid-cols-1 gap-4 border-b border-border px-6 py-4 md:grid-cols-4" style="background:rgba(39,39,42,0.30);">
                    <div class="md:col-span-2">
                        <Label class="mb-1.5 block text-[0.76rem] font-semibold text-foreground">Buscar</Label>
                        <div class="relative">
                            <Search :size="14" class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground" />
                            <Input v-model="historySearch" placeholder="N° venta o producto..."
                                class="rounded-[9px] border-[1.5px] border-green-500/30 bg-zinc-800/50 pl-9 text-[0.845rem] placeholder:text-muted-foreground/55 focus-visible:border-green-500 focus-visible:ring-2 focus-visible:ring-green-500/20" />
                        </div>
                    </div>
                    <div>
                        <Label class="mb-1.5 block text-[0.76rem] font-semibold text-foreground">Método</Label>
                        <select v-model="historyPaymentFilter"
                            class="w-full rounded-[9px] border-[1.5px] border-border bg-zinc-800/50 px-3 py-2 text-[0.845rem] text-foreground focus-visible:border-green-500 focus-visible:ring-2 focus-visible:ring-green-500/20">
                            <option value="all">Todos</option>
                            <option value="cash">Efectivo</option>
                            <option value="card">Tarjeta</option>
                            <option value="transfer">Yape/Plin</option>
                        </select>
                    </div>
                    <div>
                        <Label class="mb-1.5 block text-[0.76rem] font-semibold text-foreground">Estado</Label>
                        <select v-model="historyStateFilter"
                            class="w-full rounded-[9px] border-[1.5px] border-border bg-zinc-800/50 px-3 py-2 text-[0.845rem] text-foreground focus-visible:border-green-500 focus-visible:ring-2 focus-visible:ring-green-500/20">
                            <option value="all">Todos</option>
                            <option value="paid">Pagado</option>
                            <option value="pending">Pendiente</option>
                        </select>
                    </div>
                </div>
                <div class="flex justify-end px-6 py-3">
                    <button @click="clearHistoryFilters"
                        class="inline-flex items-center gap-1.5 rounded-[7px] border border-border bg-zinc-800/50 px-3 py-1.5 text-[0.8rem] font-medium text-muted-foreground transition hover:border-red-500/25 hover:bg-red-500/[0.08] hover:text-red-400">
                        <Filter :size="14" /> Limpiar
                    </button>
                </div>

                <!-- Tabla de ventas -->
                <div class="overflow-x-auto">
                    <table class="w-full border-separate border-spacing-0 text-[0.83rem]">
                        <thead>
                            <tr style="background:rgba(39,39,42,0.30);">
                                <th class="w-[90px] whitespace-nowrap border-b border-green-500/15 px-4 py-2.5 text-left text-[0.68rem] font-bold uppercase tracking-[0.07em] text-green-500">
                                    <Hash :size="11" class="mr-0.5 inline align-middle opacity-80" />N° Venta
                                </th>
                                <th class="whitespace-nowrap border-b border-green-500/15 px-4 py-2.5 text-left text-[0.68rem] font-bold uppercase tracking-[0.07em] text-green-500">
                                    <CalendarDays :size="11" class="mr-0.5 inline align-middle opacity-80" />Fecha
                                </th>
                                <th class="whitespace-nowrap border-b border-green-500/15 px-4 py-2.5 text-left text-[0.68rem] font-bold uppercase tracking-[0.07em] text-green-500">
                                    <Package :size="11" class="mr-0.5 inline align-middle opacity-80" />Items
                                </th>
                                <th class="whitespace-nowrap border-b border-green-500/15 px-4 py-2.5 text-left text-[0.68rem] font-bold uppercase tracking-[0.07em] text-green-500">
                                    <CreditCard :size="11" class="mr-0.5 inline align-middle opacity-80" />Método
                                </th>
                                <th class="whitespace-nowrap border-b border-green-500/15 px-4 py-2.5 text-left text-[0.68rem] font-bold uppercase tracking-[0.07em] text-green-500">
                                    <Activity :size="11" class="mr-0.5 inline align-middle opacity-80" />Estado
                                </th>
                                <th class="whitespace-nowrap border-b border-green-500/15 px-4 py-2.5 text-right text-[0.68rem] font-bold uppercase tracking-[0.07em] text-green-500">
                                    <DollarSign :size="11" class="mr-0.5 inline align-middle opacity-80" />Total
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="sale in paginatedSales" :key="sale.id" class="border-b border-border transition-colors duration-[0.12s] last:border-b-0 hover:bg-zinc-800/30">
                                <td class="px-4 py-[0.85rem] align-middle">
                                    <span class="inline-flex items-center rounded-md border border-green-500/[0.18] bg-green-500/[0.08] px-2 py-0.5 font-mono text-[0.72rem] font-bold text-green-500">#{{ sale.sale_number }}</span>
                                </td>
                                <td class="px-4 py-[0.85rem] align-middle text-[0.8rem] text-muted-foreground whitespace-nowrap">{{ formatDate(sale.created_at) }}</td>
                                <td class="px-4 py-[0.85rem] align-middle">
                                    <span class="block font-semibold text-foreground">{{ sale.items.length }} prod.</span>
                                    <span v-if="sale.items[0]" class="block text-[0.72rem] text-muted-foreground max-w-[120px] truncate">{{ sale.items[0].name }}{{ sale.items.length > 1 ? ' +...' : '' }}</span>
                                </td>
                                <td class="px-4 py-[0.85rem] align-middle">
                                    <span :class="getPaymentBadge(sale.payment_method).class">
                                        <component :is="getPaymentBadge(sale.payment_method).icon" :size="11" />
                                        {{ getPaymentBadge(sale.payment_method).label }}
                                    </span>
                                </td>
                                <td class="px-4 py-[0.85rem] align-middle">
                                    <span :class="getStateBadge(sale.state).class">{{ getStateBadge(sale.state).label }}</span>
                                </td>
                                <td class="px-4 py-[0.85rem] align-middle text-right font-bold text-green-400">{{ formatCurrency(sale.total) }}</td>
                            </tr>
                            <tr v-if="paginatedSales.length === 0">
                                <td colspan="6" class="px-4 py-12">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <div class="mb-2 flex h-16 w-16 items-center justify-center rounded-2xl border border-green-500/15 bg-green-500/[0.07] text-green-500 opacity-60">
                                            <Receipt :size="32" />
                                        </div>
                                        <p class="text-[0.9rem] font-bold text-muted-foreground">{{ historySearch || historyPaymentFilter !== 'all' || historyStateFilter !== 'all' ? 'No se encontraron ventas' : 'No hay ventas registradas' }}</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div v-if="historyTotalPages > 1" class="flex items-center justify-between border-t border-border px-6 py-3" style="background:rgba(39,39,42,0.30);">
                    <span class="text-[0.8rem] text-muted-foreground">Página {{ historyPage }} de {{ historyTotalPages }}</span>
                    <div class="flex gap-2">
                        <button @click="historyPage--" :disabled="historyPage === 1"
                            class="inline-flex items-center rounded-[7px] border border-border bg-zinc-800/50 px-3 py-1.5 text-[0.8rem] font-medium text-foreground transition hover:border-green-500/30 disabled:cursor-not-allowed disabled:opacity-40">Anterior</button>
                        <button @click="historyPage++" :disabled="historyPage === historyTotalPages"
                            class="inline-flex items-center rounded-[7px] border border-border bg-zinc-800/50 px-3 py-1.5 text-[0.8rem] font-medium text-foreground transition hover:border-green-500/30 disabled:cursor-not-allowed disabled:opacity-40">Siguiente</button>
                    </div>
                </div>
            </section>

        </div>
    </AppLayout>
</template>