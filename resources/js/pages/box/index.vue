<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import BoxController from '@/actions/App/Http/Controllers/BoxController';

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

type Props = {
    products: Products[]
};

const props = defineProps<Props>();
const products = computed(() => props.products);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Caja',
        href: './caja',
    },
];

const SaleItems = ref<SaleItem[]>([]); //Array reactivo que almacena los productos agregados

const searchQuery = ref<string>('');
const paymentMethod = ref<string>('cash');
const state = ref<string>('paid');

//Agrega a las ventas los productos seleccionados
const addToSale = (product: Products) => {
    const stockDisponible = parseInt(product.stock);

    const existItem = SaleItems.value.find(item => item.id == product.id);

    //si existe el item suma la cantidad
    if (existItem) {
        const newQuanti = existItem.quantity + 1;

        //valida si la nueva cantidad no es mayor al stockDisponible
        if (newQuanti > stockDisponible) {
            alert(`Stock insuficiente para ${product.name}. 
                   Disponible: ${stockDisponible} | Solicitado: ${newQuanti}`);
            return;
        }
        existItem.quantity = newQuanti;
        existItem.total = existItem.price_unit * existItem.quantity;
    } else {
        const newItem: SaleItem = {
            id: product.id,
            name: product.name,
            quantity: 1,
            price_unit: product.price_unit,
            total: product.price_unit * 1,
            method: paymentMethod.value,
            status: state.value,
            max_stock: stockDisponible
        };
        SaleItems.value.push(newItem);
        // console.log('Producto agregado:', newItem);
    }

};

//Actualiza la cantidad de los productos agregados a la ventana de venta
const updateQuantity = (index: number, newQuantity: number | string) => {
    let quantity = typeof newQuantity === 'string' ? parseInt(newQuantity) : newQuantity; //convierte a numero para asegurar la validez

    // Si no es un número válido, establecer a 1
    if (isNaN(quantity) || quantity < 1) {
        quantity = 1;
    }

    const item = SaleItems.value[index];
    const product = products.value.find(p => p.id === item.id);

    // if (item && quantity > parseInt(product.stock)) {
    //     alert(`Stock insuficiente. Solo hay ${product.stock} unidades disponibles.`);
    //     return;
    // }
}

//Elimina un producto de la ventana de ventas
const removeItemSale = (index: number) => {
    SaleItems.value.splice(index, 1);
}

//estado
const currentPage = ref(1);
const itemsPage = 8;

//Productos de la pagina 
const paginatedProducts = computed(() => {
    const start = (currentPage.value - 1) * itemsPage;
    const end = start + itemsPage;
    return products.value.slice(start, end);
});

//total de paginas
const totalPages = computed(() => {
    return Math.ceil(products.value.length / itemsPage);
});

//funcion para camibar de pagina
const changePage = (page: number) => {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
    }
};

//

const subTotal = computed(() => {
    return SaleItems.value.reduce((suma, item) => suma + item.total, 0);
});

const iva = computed(() => {
    return (Number(subTotal.value) * 0.18).toFixed(2);
});

const total = computed(() => {
    return Number(subTotal.value) + Number(iva.value);
});

const procesSale = async () => {
    //condicionamos si hay algo en las ventas
    if (SaleItems.value.length === 0) {
        alert('Selecciona un producto');
        return;
    }

    if (!confirm('¿Confirmar la venta?')) {
        return;
    }

    //prepara los datos
    const data = {
        items: SaleItems.value.map(item => ({
            id: item.id,
            name: item.name,
            quantity: item.quantity,
            status: item.status,
            method: item.method,
            total: item.total
        })),
        total: total.value,
        state: state.value,
        payment: paymentMethod.value,
    };

    console.log(data);
    const box = JSON.stringify(data);
    console.log(box);
    //Enviar con Inertia

    router.post('/box', data);
    // await(BoxController.store, box);
}
</script>

<template>

    <Head title="Caja Registradora" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4 mb-5">
                <div class="w-2/3">
                    <h1 class="text-2xl font-bold text-white mb-4">Sistema de Caja</h1>
                </div>
                <div class="rounded-lg shadow p-4 w-1/3">
                    <div class="flex items-center justify-between">
                        <div class="flex gap-3">
                            <p class="text-sm text-gray-500">Total productos: </p>
                            <p class="text-sm text-white text-center"> {{ products.length }}</p>
                        </div>
                        <i class="fas fa-boxes text-3xl text-green-500"></i>
                    </div>
                </div>
            </div>


            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <!-- Panel izquierdo - Lista de Productos -->
                <div class=" bg-tranparent border rounded-lg shadow-lg p-4">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-lg font-semibold text-white">Lista de Productos</h2>
                        <div class="relative">
                            <!-- buscador -->
                            <Input v-model="searchQuery" type="text" placeholder="Buscar producto..."
                                class="pl-8 pr-4 py-2" />
                            <i class="fas fa-search absolute left-2 top-3 text-gray-400"></i>
                        </div>
                    </div>
                    <section class="rounded-xl border border-sidebar-border/70 bg-background p-4">
                        <div class="mt-4 overflow-x-auto">
                            <table class="w-full min-w-auto text-sm">
                                <thead class="border-b text-left">
                                    <tr>
                                        <th class="px-2 py-2">ID</th>
                                        <th class="px-2 py-2">Nombre</th>
                                        <th class="px-2 py-2">Descripcion</th>
                                        <th class="px-2 py-2">Stock</th>
                                        <th class="px-2 py-2">Precio unitario</th>
                                        <th class="px-2 py-2"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="products.length === 0">
                                        <td colspan="6" class="px-2 py-4 text-center text-muted-foreground">
                                            No hay productos registrados.
                                        </td>
                                    </tr>
                                    <tr v-for="producto in paginatedProducts" :key="producto.id" class="border-b">
                                        <td class="px-2 py-2">{{ producto.id }}</td>
                                        <td class="px-2 py-2">{{ producto.name }}</td>
                                        <td class="px-2 py-2">{{ producto.description }}</td>
                                        <td class="px-2 py-2">{{ producto.stock }}</td>
                                        <td class="px-2 py-2">{{ producto.price_unit }}</td>
                                        <td class="px-4 py-3 text-sm">
                                            <Button type="button" variant="secondary" @click.stop="addToSale(producto)">
                                                Agregar
                                            </Button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Start el apartado de paginacion -->
                        <div class="flex items-center justify-between mt-4">
                            <div class="text-sm text-gray-500">
                                Mostrando {{ ((currentPage - 1) * itemsPage) + 1 }}
                                - {{ Math.min(currentPage * itemsPage, products.length) }}
                                de {{ products.length }} productos
                            </div>

                            <div class="flex space-x-2">
                                <!-- Botón Anterior -->
                                <button @click="changePage(currentPage - 1)" :disabled="currentPage === 1"
                                    class="px-3 py-1 rounded border border-gray-300 text-sm disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50">
                                    Anterior
                                </button>

                                <!-- Números de página -->
                                <button v-for="page in totalPages" :key="page" @click="changePage(page)"
                                    class="px-3 py-1 rounded border text-sm" :class="[
                                        currentPage === page
                                            ? 'bg-blue-600 text-white border-blue-600'
                                            : 'border-gray-300 hover:bg-gray-50'
                                    ]">
                                    {{ page }}
                                </button>

                                <!-- Botón Siguiente -->
                                <button @click="changePage(currentPage + 1)" :disabled="currentPage === totalPages"
                                    class="px-3 py-1 rounded border border-gray-300 text-sm disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50">
                                    Siguiente
                                </button>
                            </div>
                        </div>
                    </section>
                </div>

                <!-- Panel derecho - Venta actual -->
                <div class="border rounded-lg shadow-lg p-4 flex flex-col h-[600px]">
                    <h2 class="text-lg font-semibold text-white mb-4">Venta Actual</h2>

                    <!-- Productos agregados -->
                    <div class="flex-1 overflow-y-auto mb-4">

                        <table class="w-full min-w-auto text-sm">
                            <thead class="border-b text-left">
                                <tr>
                                    <th class="px-2 py-2">Producto</th>
                                    <th class="px-2 py-2">Cantidad</th>
                                    <th class="px-2 py-2">P. unitario</th>
                                    <th class="px-2 py-2">Total</th>
                                    <th class="px-2 py-2"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(item, index) in SaleItems" :key="index" class="border-b">
                                    <td class="px-2 py-3">
                                        <p class="font-medium">{{ item.name }}</p>
                                    </td>
                                    <td class="px-2 py-2">
                                        <!-- Input de cantidad -->
                                        <Input type="number" :value="item.quantity"
                                            @input="updateQuantity(index, parseInt($event.target.value) || 0)"
                                            class="w-16 h-8 text-center" placeholder='1' />
                                    </td>

                                    <td class="px-2 py-2">S/{{ item.price_unit }}</td>
                                    <td class="px-2 py-2">S/{{ item.total }}</td>
                                    <td class="px-4 py-3 text-sm">
                                        <Button type="button" variant="destructive" @click.stop="removeItemSale(index)">
                                            Eliminar
                                        </Button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                    </div>
                    <!--Apartado de la venta de producto -->
                    <div class="border-t pt-4">
                        <h3 class="font-medium text-gray-700 mb-2"></h3>
                        <div class="space-y-3">
                            <div class="flex space-x-2">
                                <Button @click="procesSale" type="submit">Generar Pago
                                    <i class="fas fa-plus"></i>
                                </Button>
                                <div class="flex gap-3 border-t pt-4 space-y-2 text-white">
                                    <div class="flex justify-between">
                                        sub total: {{ subTotal }}
                                    </div>
                                    <div class="flex justify-between">
                                        IGV: {{ iva }}
                                    </div>
                                    <div class="flex justify-between">
                                        TOTAL: {{ total.toFixed(2) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>