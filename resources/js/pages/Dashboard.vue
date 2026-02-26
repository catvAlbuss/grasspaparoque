<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { cn } from '@/lib/utils'; // Ajusta la ruta según donde tengas tu archivo
import { 
  Users, 
  Calendar, 
  Package, 
  DollarSign, 
  AlertCircle,
  TrendingUp,
  TrendingDown,
  ArrowRight
} from 'lucide-vue-next';

// Tus props o datos
const breadcrumbs = [ /* ... */ ];

const stats = [
  { label: 'Usuarios', value: '128', change: '+4 este mes', trend: 'up' },
  { label: 'Eventos', value: '34', change: '3 próximos', trend: 'up' },
  { label: 'Productos', value: '216', change: '12 bajo stock', trend: 'down' },
  { label: 'Caja', value: 'S/ 8,450', change: '+S/1,200 hoy', trend: 'up' },
];

const eventosPróximos = [
  { nombre: 'Cumpleaños Martínez', personas: 80, fecha: '22 de febrero', estado: 'Confirmado' },
  // ... más eventos
];

const productosBajoStock = [
  { nombre: 'Producto 1', stock: 5, max: 50, unidad: 'uds' },
  // ... más productos
];

const movimientosCaja = [
  { concepto: 'Venta entrada evento', tipo: 'ingreso', monto: '+S/ 500', hora: '10:30' },
  { concepto: 'Compra suministros', tipo: 'egreso', monto: '-S/ 200', hora: '11:45' },
  // ... más movimientos
];

const estadoStyle = {
  Confirmado: {
    badge: 'bg-green-400/10 text-green-400 border border-green-400/20',
    dot: 'bg-green-400'
  },
  Pendiente: {
    badge: 'bg-yellow-400/10 text-yellow-400 border border-yellow-400/20',
    dot: 'bg-yellow-400'
  }
};
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <div :class="cn('space-y-4 text-gray-100', 'px-2 sm:px-3')">
      
      <!-- ===== TARJETAS DE ESTADÍSTICAS ===== -->
      <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-2">
        <div
          v-for="s in stats"
          :key="s.label"
          :class="cn(
            'bg-zinc-900 border border-zinc-800 rounded-xl p-3',
            'hover:bg-zinc-800/80 transition-all duration-200',
            'hover:border-zinc-700'
          )"
        >
          <div class="flex items-center justify-between mb-3">
            <div :class="cn('w-7 h-7 rounded-lg', 'bg-zinc-800/50', 'flex items-center justify-center')">
              <component 
                :is="s.label === 'Usuarios' ? Users : 
                      s.label === 'Eventos' ? Calendar : 
                      s.label === 'Productos' ? Package : 
                      DollarSign" 
                class="w-4 h-4 text-green-400"
                :stroke-width="1.5"
              />
            </div>
            <span
              :class="cn(
                'text-xs font-semibold flex items-center gap-0.5',
                s.trend === 'up' ? 'text-green-400' : 'text-red-400'
              )"
            >
              <component :is="s.trend === 'up' ? TrendingUp : TrendingDown" class="w-3 h-3" />
              {{ s.change }}
            </span>
          </div>
          <p class="text-2xl font-bold text-white mb-0">{{ s.value }}</p>
          <p class="text-xs text-zinc-500 uppercase tracking-widest">{{ s.label }}</p>
        </div>
      </div>

      <!-- ===== SECCIÓN PRINCIPAL: EVENTOS + STOCK ===== -->
      <div class="grid grid-cols-1 xl:grid-cols-3 gap-2">
        
        <!-- PRÓXIMOS EVENTOS -->
        <div class="xl:col-span-2 bg-zinc-900 border border-zinc-800 rounded-xl p-4">
          <div class="flex items-center justify-between mb-3">
            <h2 class="text-xs font-semibold text-white flex items-center gap-1.5">
              <Calendar class="w-4 h-4 text-green-400" /> Próximos Eventos
            </h2>
            <button class="text-xs text-green-400 hover:text-green-300 transition-colors flex items-center gap-1">
              Ver todos <ArrowRight class="w-3 h-3" />
            </button>
          </div>

          <!-- Cabecera -->
          <div class="grid grid-cols-4 text-2xs text-zinc-500 uppercase tracking-widest border-b border-zinc-800 pb-2">
            <span class="col-span-2">Evento</span>
            <span class="text-center">Fecha</span>
            <span class="text-right">Estado</span>
          </div>

          <!-- Filas -->
          <div class="divide-y divide-zinc-800">
            <div
              v-for="ev in eventosPróximos"
              :key="ev.nombre"
              class="grid grid-cols-4 items-center py-2 hover:bg-zinc-800/50 transition-colors rounded-lg"
            >
              <div class="col-span-2">
                <p class="text-sm font-medium text-zinc-100">{{ ev.nombre }}</p>
                <p class="text-2xs text-zinc-500">{{ ev.personas }} personas</p>
              </div>
              <p class="text-center text-2xs text-zinc-400 font-mono">{{ ev.fecha }}</p>
              <div class="flex justify-end">
                <span
                  class="inline-flex items-center gap-1 text-2xs font-medium px-2 py-0.5 rounded-full"
                  :class="estadoStyle[ev.estado]?.badge"
                >
                  <span class="w-1 h-1 rounded-full" :class="estadoStyle[ev.estado]?.dot" />
                  {{ ev.estado }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- STOCK CRÍTICO -->
        <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-4">
          <div class="flex items-center justify-between mb-3">
            <h2 class="text-xs font-semibold text-white flex items-center gap-1.5">
              <AlertCircle class="w-4 h-4 text-red-400" /> Stock Crítico
            </h2>
            <span class="text-2xs text-red-400 font-semibold bg-red-400/10 px-2 py-0.5 rounded-full">
              {{ productosBajoStock.length }} alertas
            </span>
          </div>

          <div class="space-y-3">
            <div v-for="p in productosBajoStock" :key="p.nombre">
              <div class="flex justify-between items-center mb-1">
                <p class="text-xs font-medium text-zinc-200">{{ p.nombre }}</p>
                <p class="text-2xs text-red-400 font-semibold">{{ p.stock }} {{ p.unidad }}</p>
              </div>
              <div class="w-full bg-zinc-800 rounded-full h-1.5">
                <div
                  class="h-1.5 rounded-full bg-red-500 transition-all"
                  :style="{ width: `${Math.round((p.stock / p.max) * 100)}%` }"
                />
              </div>
              <p class="text-2xs text-zinc-600 mt-0.5">
                {{ Math.round((p.stock / p.max) * 100) }}% del mínimo
              </p>
            </div>
          </div>

          <button class="mt-3 w-full py-2 text-2xs text-zinc-400 border border-zinc-700 rounded-lg hover:bg-zinc-800 hover:text-white transition-colors">
            Ver inventario completo
          </button>
        </div>
      </div>

      <!-- ===== MOVIMIENTOS DE CAJA ===== -->
      <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-4">
        <div class="flex items-center justify-between mb-3">
          <h2 class="text-xs font-semibold text-white flex items-center gap-1.5">
            <DollarSign class="w-4 h-4 text-green-400" /> Movimientos de Caja — Hoy
          </h2>
          <button class="text-xs text-green-400 hover:text-green-300 transition-colors flex items-center gap-1">
            Ver caja <ArrowRight class="w-3 h-3" />
          </button>
        </div>

        <!-- Cabecera -->
        <div class="grid grid-cols-3 text-2xs text-zinc-500 uppercase tracking-widest border-b border-zinc-800 pb-2">
          <span class="col-span-2">Concepto</span>
          <span class="text-right">Monto</span>
        </div>

        <!-- Filas -->
        <div class="divide-y divide-zinc-800">
          <div
            v-for="(m, i) in movimientosCaja"
            :key="i"
            class="grid grid-cols-3 items-center py-2 hover:bg-zinc-800/50 transition-colors rounded-lg"
          >
            <div class="col-span-2 flex items-center gap-2">
              <span
                class="w-6 h-6 rounded-md flex items-center justify-center text-xs font-bold flex-shrink-0"
                :class="m.tipo === 'ingreso' ? 'bg-green-400/10 text-green-400' : 'bg-red-400/10 text-red-400'"
              >
                {{ m.tipo === 'ingreso' ? '+' : '−' }}
              </span>
              <div>
                <p class="text-xs text-zinc-200">{{ m.concepto }}</p>
                <p class="text-2xs text-zinc-600 font-mono">{{ m.hora }}</p>
              </div>
            </div>
            <p
              class="text-right font-mono font-semibold text-xs"
              :class="m.tipo === 'ingreso' ? 'text-green-400' : 'text-red-400'"
            >
              {{ m.monto }}
            </p>
          </div>
        </div>

        <!-- Balance -->
        <div class="mt-2 pt-2 border-t border-zinc-800 flex items-center justify-between">
          <p class="text-2xs text-zinc-500 uppercase tracking-widest font-medium">Balance del día</p>
          <p class="text-base font-bold text-green-400 font-mono">+S/ 3,900</p>
        </div>
      </div>

      <!-- ===== ACCESOS RÁPIDOS ===== -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
        <Link 
          href="/users" 
          :class="cn(
            'bg-zinc-900 border border-zinc-800 rounded-xl p-3',
            'text-center hover:bg-zinc-800 transition-all duration-200',
            'hover:border-zinc-700 group'
          )"
        >
          <Users class="w-6 h-6 mx-auto mb-1 text-green-400 group-hover:scale-110 transition-transform" />
          <span class="text-xs font-medium text-gray-300">Usuarios</span>
        </Link>
        
        <Link 
          href="/events" 
          :class="cn(
            'bg-zinc-900 border border-zinc-800 rounded-xl p-3',
            'text-center hover:bg-zinc-800 transition-all duration-200',
            'hover:border-zinc-700 group'
          )"
        >
          <Calendar class="w-6 h-6 mx-auto mb-1 text-green-400 group-hover:scale-110 transition-transform" />
          <span class="text-xs font-medium text-gray-300">Eventos</span>
        </Link>
        
        <Link 
          href="/products" 
          :class="cn(
            'bg-zinc-900 border border-zinc-800 rounded-xl p-3',
            'text-center hover:bg-zinc-800 transition-all duration-200',
            'hover:border-zinc-700 group'
          )"
        >
          <Package class="w-6 h-6 mx-auto mb-1 text-green-400 group-hover:scale-110 transition-transform" />
          <span class="text-xs font-medium text-gray-300">Productos</span>
        </Link>
        
        <Link 
          href="/box" 
          :class="cn(
            'bg-zinc-900 border border-zinc-800 rounded-xl p-3',
            'text-center hover:bg-zinc-800 transition-all duration-200',
            'hover:border-zinc-700 group'
          )"
        >
          <DollarSign class="w-6 h-6 mx-auto mb-1 text-green-400 group-hover:scale-110 transition-transform" />
          <span class="text-xs font-medium text-gray-300">Caja</span>
        </Link>
      </div>

    </div>
  </AppLayout>
</template>

<style scoped>
/* Utilidad para texto más pequeño */
.text-2xs {
  font-size: 0.625rem; /* 10px */
  line-height: 0.875rem; /* 14px */
}
</style>