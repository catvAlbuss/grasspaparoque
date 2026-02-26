<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { 
    Users, Package, ShoppingCart, DollarSign, CalendarDays, 
    TrendingUp, TrendingDown, Activity, ArrowUpRight, ArrowDownRight,
    Package2, CreditCard, Sparkles, BarChart3, AlertTriangle, XCircle
} from 'lucide-vue-next';

type Props = {
    totalUsers?: number;
    activeUsers?: number;
    totalEvents?: number;
    activeEvents?: number;
    totalProducts?: number;
    lowStockProducts?: number;
    totalSales?: number;
    todaySales?: number;
    totalRevenue?: number;
    todayRevenue?: number;
};

const props = withDefaults(defineProps<Props>(), {
    totalUsers: 0,
    activeUsers: 0,
    totalEvents: 0,
    activeEvents: 0,
    totalProducts: 0,
    lowStockProducts: 0,
    totalSales: 0,
    todaySales: 0,
    totalRevenue: 0,
    todayRevenue: 0,
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
];

// Formatear moneda a soles
const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('es-PE', {
        style: 'currency',
        currency: 'PEN',
        minimumFractionDigits: 2
    }).format(value);
};

// Métricas calculadas
const userGrowth = computed(() => {
    const percentage = props.activeUsers > 0 
        ? ((props.activeUsers / props.totalUsers) * 100).toFixed(1)
        : '0';
    return percentage;
});

const eventGrowth = computed(() => {
    const percentage = props.totalEvents > 0 
        ? ((props.activeEvents / props.totalEvents) * 100).toFixed(1)
        : '0';
    return percentage;
});

const stockAlert = computed(() => {
    return props.lowStockProducts > 0 ? `${props.lowStockProducts} productos` : 'OK';
});

const salesGrowth = computed(() => {
    if (props.totalSales === 0) return '0';
    const percentage = ((props.todaySales / props.totalSales) * 100).toFixed(1);
    return percentage;
});
</script>

<template>
    <Head title="Dashboard - Grass Deportivo" />
    
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-5 p-5">

            <!-- HEADER -->
            <div class="relative overflow-hidden rounded-[1.1rem] border border-green-400/[0.12] px-7 py-6"
                style="background: linear-gradient(135deg, #052e16 0%, #14532d 45%, #15803d 100%);">
                <div class="pointer-events-none absolute inset-0"
                    style="background: radial-gradient(ellipse at 10% 70%, rgba(34,197,94,0.28) 0%, transparent 55%), radial-gradient(ellipse at 90% 10%, rgba(74,222,128,0.15) 0%, transparent 50%);" />
                
                <div class="relative flex items-center justify-between">
                    <div class="flex items-center gap-3.5">
                        <div class="flex h-[46px] w-[46px] flex-shrink-0 items-center justify-center rounded-xl border border-green-400/30 bg-green-400/15 text-green-400">
                            <BarChart3 :size="24" />
                        </div>
                        <div>
                            <h1 class="text-[1.35rem] font-bold tracking-tight text-white" style="letter-spacing:-0.03em;">Dashboard</h1>
                            
                        </div>
                    </div>
                    <div class="flex items-center gap-1.5 whitespace-nowrap rounded-full border border-green-400/30 bg-green-500/15 px-3 py-1.5 text-[0.76rem] font-semibold text-green-400">
                        <Sparkles :size="12" />
                        <span>Bienvenido</span>
                    </div>
                </div>
            </div>

            <!-- INDICADORES PRINCIPALES -->
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                
                <!-- Usuarios -->
                <div class="group relative overflow-hidden rounded-[1.1rem] border border-border bg-background transition-all duration-300 hover:shadow-[0_4px_24px_rgba(22,163,74,0.1)] hover:border-green-500/20">
                    <div class="absolute inset-x-0 top-0 h-[2.5px] bg-gradient-to-r from-blue-700 via-blue-500 to-blue-400" />
                    <div class="p-5">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <p class="text-[0.76rem] font-semibold uppercase tracking-[0.05em] text-muted-foreground">Usuarios</p>
                                <div class="mt-2 flex items-baseline gap-2">
                                    <span class="text-[2rem] font-bold leading-none text-white">{{ totalUsers }}</span>
                                    <span class="text-[0.7rem] text-muted-foreground">totales</span>
                                </div>
                                <div class="mt-3 flex items-center gap-1.5">
                                    <div class="flex h-6 w-6 items-center justify-center rounded-[5px] border border-emerald-500/25 bg-emerald-500/[0.18] text-emerald-400">
                                        <Users :size="12" />
                                    </div>
                                    <div>
                                        <span class="text-[0.85rem] font-bold text-white">{{ activeUsers }}</span>
                                        <span class="ml-1 text-[0.7rem] text-muted-foreground">activos ({{ userGrowth }}%)</span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-xl border border-blue-500/25 bg-blue-500/[0.08] text-blue-400 transition-transform group-hover:scale-110">
                                <Users :size="20" />
                            </div>
                        </div>
                    </div>
                    <div class="border-t border-border px-5 py-3" style="background:rgba(39,39,42,0.30);">
                        <a href="/users" class="flex items-center gap-1.5 text-[0.8rem] font-medium text-blue-400 transition hover:text-blue-300">
                            Ver usuarios <ArrowUpRight :size="12" />
                        </a>
                    </div>
                </div>

                <!-- Eventos -->
                <div class="group relative overflow-hidden rounded-[1.1rem] border border-border bg-background transition-all duration-300 hover:shadow-[0_4px_24px_rgba(22,163,74,0.1)] hover:border-green-500/20">
                    <div class="absolute inset-x-0 top-0 h-[2.5px] bg-gradient-to-r from-purple-700 via-purple-500 to-purple-400" />
                    <div class="p-5">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <p class="text-[0.76rem] font-semibold uppercase tracking-[0.05em] text-muted-foreground">Eventos</p>
                                <div class="mt-2 flex items-baseline gap-2">
                                    <span class="text-[2rem] font-bold leading-none text-white">{{ totalEvents }}</span>
                                    <span class="text-[0.7rem] text-muted-foreground">totales</span>
                                </div>
                                <div class="mt-3 flex items-center gap-1.5">
                                    <div class="flex h-6 w-6 items-center justify-center rounded-[5px] border border-emerald-500/25 bg-emerald-500/[0.18] text-emerald-400">
                                        <CalendarDays :size="12" />
                                    </div>
                                    <div>
                                        <span class="text-[0.85rem] font-bold text-white">{{ activeEvents }}</span>
                                        <span class="ml-1 text-[0.7rem] text-muted-foreground">activos ({{ eventGrowth }}%)</span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-xl border border-purple-500/25 bg-purple-500/[0.08] text-purple-400 transition-transform group-hover:scale-110">
                                <CalendarDays :size="20" />
                            </div>
                        </div>
                    </div>
                    <div class="border-t border-border px-5 py-3" style="background:rgba(39,39,42,0.30);">
                        <a href="/eventos" class="flex items-center gap-1.5 text-[0.8rem] font-medium text-purple-400 transition hover:text-purple-300">
                            Ver eventos <ArrowUpRight :size="12" />
                        </a>
                    </div>
                </div>

                <!-- Productos -->
                <div class="group relative overflow-hidden rounded-[1.1rem] border border-border bg-background transition-all duration-300 hover:shadow-[0_4px_24px_rgba(22,163,74,0.1)] hover:border-green-500/20">
                    <div class="absolute inset-x-0 top-0 h-[2.5px] bg-gradient-to-r from-amber-700 via-amber-500 to-amber-400" />
                    <div class="p-5">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <p class="text-[0.76rem] font-semibold uppercase tracking-[0.05em] text-muted-foreground">Productos</p>
                                <div class="mt-2 flex items-baseline gap-2">
                                    <span class="text-[2rem] font-bold leading-none text-white">{{ totalProducts }}</span>
                                    <span class="text-[0.7rem] text-muted-foreground">totales</span>
                                </div>
                                <div class="mt-3 flex items-center gap-1.5">
                                    <div :class="[
                                        'flex h-6 w-6 items-center justify-center rounded-[5px] border',
                                        lowStockProducts > 0 
                                            ? 'border-amber-500/25 bg-amber-500/[0.18] text-amber-400'
                                            : 'border-emerald-500/25 bg-emerald-500/[0.18] text-emerald-400'
                                    ]">
                                        <Package2 :size="12" />
                                    </div>
                                    <div>
                                        <span :class="[
                                            'text-[0.85rem] font-bold',
                                            lowStockProducts > 0 ? 'text-amber-400' : 'text-emerald-400'
                                        ]">{{ stockAlert }}</span>
                                        <span v-if="lowStockProducts > 0" class="ml-1 text-[0.7rem] text-amber-400">stock bajo</span>
                                        <span v-else class="ml-1 text-[0.7rem] text-muted-foreground">inventario OK</span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-xl border border-amber-500/25 bg-amber-500/[0.08] text-amber-400 transition-transform group-hover:scale-110">
                                <Package :size="20" />
                            </div>
                        </div>
                    </div>
                    <div class="border-t border-border px-5 py-3" style="background:rgba(39,39,42,0.30);">
                        <a href="/products" class="flex items-center gap-1.5 text-[0.8rem] font-medium text-amber-400 transition hover:text-amber-300">
                            Ver productos <ArrowUpRight :size="12" />
                        </a>
                    </div>
                </div>

                <!-- Caja / Ventas -->
                <div class="group relative overflow-hidden rounded-[1.1rem] border border-border bg-background transition-all duration-300 hover:shadow-[0_4px_24px_rgba(22,163,74,0.1)] hover:border-green-500/20">
                    <div class="absolute inset-x-0 top-0 h-[2.5px] bg-gradient-to-r from-green-700 via-green-500 to-green-400" />
                    <div class="p-5">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <p class="text-[0.76rem] font-semibold uppercase tracking-[0.05em] text-muted-foreground">Ventas Hoy</p>
                                <div class="mt-2 flex items-baseline gap-2">
                                    <span class="text-[2rem] font-bold leading-none text-green-400">{{ formatCurrency(todayRevenue) }}</span>
                                </div>
                                <div class="mt-3 flex items-center gap-1.5">
                                    <div class="flex h-6 w-6 items-center justify-center rounded-[5px] border border-emerald-500/25 bg-emerald-500/[0.18] text-emerald-400">
                                        <ShoppingCart :size="12" />
                                    </div>
                                    <div>
                                        <span class="text-[0.85rem] font-bold text-white">{{ todaySales }}</span>
                                        <span class="ml-1 text-[0.7rem] text-muted-foreground">ventas ({{ salesGrowth }}% del total)</span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-xl border border-green-500/25 bg-green-500/[0.08] text-green-400 transition-transform group-hover:scale-110">
                                <DollarSign :size="20" />
                            </div>
                        </div>
                    </div>
                    <div class="border-t border-border px-5 py-3" style="background:rgba(39,39,42,0.30);">
                        <a href="/box" class="flex items-center gap-1.5 text-[0.8rem] font-medium text-green-400 transition hover:text-green-300">
                            Ir a caja <ArrowUpRight :size="12" />
                        </a>
                    </div>
                </div>
            </div>

            <!-- RESUMEN -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                
                <!-- Ingresos Totales -->
                <div class="relative overflow-hidden rounded-[1.1rem] border border-border bg-background p-6 transition-shadow duration-300 hover:shadow-[0_4px_24px_rgba(22,163,74,0.07)]">
                    <div class="absolute inset-x-0 top-0 h-[2.5px] bg-gradient-to-r from-green-700 via-green-500 to-green-400" />
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl border border-green-500/25 bg-green-500/[0.18] text-green-400">
                                <DollarSign :size="24" />
                            </div>
                            <div>
                                <p class="text-[0.85rem] font-semibold uppercase tracking-[0.05em] text-muted-foreground">Ingresos Totales</p>
                                <p class="mt-1 text-[2rem] font-bold text-green-400">{{ formatCurrency(totalRevenue) }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-1 rounded-full border border-emerald-500/25 bg-emerald-500/[0.18] px-3 py-1.5 text-[0.76rem] font-semibold text-emerald-400">
                            <TrendingUp :size="12" />
                            <span>+{{ salesGrowth }}%</span>
                        </div>
                    </div>
                </div>

                <!-- Alertas -->
                <div class="relative overflow-hidden rounded-[1.1rem] border border-border bg-background p-6 transition-shadow duration-300 hover:shadow-[0_4px_24px_rgba(22,163,74,0.07)]">
                    <div class="absolute inset-x-0 top-0 h-[2.5px] bg-gradient-to-r from-amber-700 via-amber-500 to-amber-400" />
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl border border-amber-500/25 bg-amber-500/[0.18] text-amber-400">
                                <AlertTriangle :size="24" />
                            </div>
                            <div>
                                <p class="text-[0.85rem] font-semibold uppercase tracking-[0.05em] text-muted-foreground">Alertas de Stock</p>
                                <p class="mt-1 text-[2rem] font-bold" :class="lowStockProducts > 0 ? 'text-amber-400' : 'text-emerald-400'">
                                    {{ lowStockProducts }}
                                </p>
                            </div>
                        </div>
                        <div v-if="lowStockProducts > 0" class="flex items-center gap-1 rounded-full border border-amber-500/25 bg-amber-500/[0.18] px-3 py-1.5 text-[0.76rem] font-semibold text-amber-400">
                            <XCircle :size="12" />
                            <span>Revisar</span>
                        </div>
                        <div v-else class="flex items-center gap-1 rounded-full border border-emerald-500/25 bg-emerald-500/[0.18] px-3 py-1.5 text-[0.76rem] font-semibold text-emerald-400">
                            <Activity :size="12" />
                            <span>OK</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ACCESOS RÁPIDOS -->
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                <a href="/users" class="group flex items-center gap-3 rounded-[10px] border border-border bg-zinc-800/30 p-4 transition-all hover:border-blue-500/30 hover:bg-blue-500/[0.08]">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg border border-blue-500/25 bg-blue-500/[0.18] text-blue-400 transition-transform group-hover:scale-110">
                        <Users :size="18" />
                    </div>
                    <div>
                        <p class="text-[0.85rem] font-semibold text-foreground">Usuarios</p>
                        <p class="text-[0.7rem] text-muted-foreground">Gestionar usuarios</p>
                    </div>
                </a>

                <a href="/eventos" class="group flex items-center gap-3 rounded-[10px] border border-border bg-zinc-800/30 p-4 transition-all hover:border-purple-500/30 hover:bg-purple-500/[0.08]">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg border border-purple-500/25 bg-purple-500/[0.18] text-purple-400 transition-transform group-hover:scale-110">
                        <CalendarDays :size="18" />
                    </div>
                    <div>
                        <p class="text-[0.85rem] font-semibold text-foreground">Eventos</p>
                        <p class="text-[0.7rem] text-muted-foreground">Administrar eventos</p>
                    </div>
                </a>

                <a href="/products" class="group flex items-center gap-3 rounded-[10px] border border-border bg-zinc-800/30 p-4 transition-all hover:border-amber-500/30 hover:bg-amber-500/[0.08]">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg border border-amber-500/25 bg-amber-500/[0.18] text-amber-400 transition-transform group-hover:scale-110">
                        <Package :size="18" />
                    </div>
                    <div>
                        <p class="text-[0.85rem] font-semibold text-foreground">Productos</p>
                        <p class="text-[0.7rem] text-muted-foreground">Control inventario</p>
                    </div>
                </a>

                <a href="/box" class="group flex items-center gap-3 rounded-[10px] border border-border bg-zinc-800/30 p-4 transition-all hover:border-green-500/30 hover:bg-green-500/[0.08]">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg border border-green-500/25 bg-green-500/[0.18] text-green-400 transition-transform group-hover:scale-110">
                        <CreditCard :size="18" />
                    </div>
                    <div>
                        <p class="text-[0.85rem] font-semibold text-foreground">Caja</p>
                        <p class="text-[0.7rem] text-muted-foreground">Registrar ventas</p>
                    </div>
                </a>
            </div>

        </div>
    </AppLayout>
</template>

