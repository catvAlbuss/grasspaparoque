<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
// import UserController from '@/actions/App/Http/Controllers/UserController';
//import ProductsController from '@/actions/App/Http/Controllers/ProductsController';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import ProductsController from '@/actions/App/Http/Controllers/ProductsController';

type Products = {
    id: number;
    name: string;
    description: string;
    stock: string;
    price_unit: string;
    price_higher: string;
    expiration_date: string;
};

type Props = {
    products: Products[]
};

const props = defineProps<Props>();
const products = computed(() => props.products);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Productos',
        href: './products',
    },
];

const editingId = ref<number | null>(null);

const form = useForm({
    name: '',
    description: '',
    stock:'',
    price_unit: '',
    price_higher:'',
    expiration_date:''
});

const deleteForm = useForm({});
const deleteError = computed(() => (deleteForm.errors as Record<string, string | undefined>).delete);

const isEditing = computed(() => editingId.value !== null);

const resetForm = (): void => {
    editingId.value = null;
    form.reset();
    form.clearErrors();
};

//Funcion para Editar un producto (llena los campos del form)
const startEdit = (products: Products): void => {
    editingId.value = products.id;
    form.clearErrors();
    form.name = products.name;
    form.description = products.description;
    form.stock = products.stock;
    form.price_unit = products.price_unit;
    form.price_higher = products.price_higher;
    form.expiration_date = products.expiration_date;
};

const submit = (): void => {
    const options = {
        preserveScroll: true,
        onSuccess: () => resetForm(),
    };

    if (isEditing.value && editingId.value !== null) {
        //form.put(ProductsController.update.url(editingId.value), options);
        form.put(ProductsController.update.url(editingId.value), options);
        return;
    }

    //manda a la funcion de store en ProductsController
    form.post(ProductsController.store.url(), options);
};

//funcion para remover un producto
const remove = (products: Products): void => {
    if (!confirm(`Eliminar producto "${products.name}"?`)) {
        return;
    }

    deleteForm.delete(ProductsController.destroy.url(products.id), {
        preserveScroll: true,
    });
};
</script>

<template>

    <Head title="Products" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4">
            <section class="rounded-xl border border-sidebar-border/70 bg-background p-4">
                <h1 class="text-xl font-semibold">
                    {{ isEditing ? 'Editar producto' : 'Nuevo producto' }}
                </h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    Gestiona los productos desde esta ventana.
                </p>

                <form class="mt-4 grid gap-4 md:grid-cols-2" @submit.prevent="submit">
                    <div class="grid gap-2">
                        <Label for="name">Nombre</Label>
                        <Input id="name" v-model="form.name" type="text" required />
                        <InputError :message="form.errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email">Descripcion</Label>
                        <Input id="email" v-model="form.description" type="text" required />
                        <InputError :message="form.errors.description" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="email">Stock</Label>
                        <Input id="email" v-model="form.stock" type="text" required />
                        <InputError :message="form.errors.stock" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="personal">Precion unitario</Label>
                        <Input id="personal" v-model="form.price_unit" type="text" />
                        <InputError :message="form.errors.price_unit" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="personal">Precion por Mayor</Label>
                        <Input id="personal" v-model="form.price_higher" type="text" />
                        <InputError :message="form.errors.price_higher" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="role">Fecha Vencimiento</Label>
                        <Input id="personal" v-model="form.expiration_date" type="date" />
                        <InputError :message="form.errors.expiration_date" />
                    </div>

                    <!-- <div class="grid gap-2">
                        <Label for="password">
                            Password
                            <span v-if="isEditing" class="text-xs text-muted-foreground">(opcional en edicion)</span>
                        </Label>
                        <Input id="password" v-model="form.password" type="password" :required="!isEditing" />
                        <InputError :message="form.errors.password" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password_confirmation">Confirmar password</Label>
                        <Input id="password_confirmation" v-model="form.password_confirmation" type="password"
                            :required="!isEditing" />
                    </div> -->

                    <div class="col-span-full flex gap-2">
                        <Button type="submit" :disabled="form.processing || deleteForm.processing">
                            {{ isEditing ? 'Actualizar' : 'Crear' }}
                        </Button>
                        <Button v-if="isEditing" type="button" variant="secondary"
                            :disabled="form.processing || deleteForm.processing" @click="resetForm">
                            Cancelar
                        </Button>
                    </div>
                </form>
            </section>

            <section class="rounded-xl border border-sidebar-border/70 bg-background p-4">
                <h2 class="text-lg font-semibold">Listado de productos</h2>

                <div class="mt-4 overflow-x-auto">
                    <table class="w-full min-w-[720px] text-sm">
                        <thead class="border-b text-left">
                            <tr>
                                <th class="px-2 py-2">ID</th>
                                <th class="px-2 py-2">Nombre</th>
                                <th class="px-2 py-2">Descripcion</th>
                                <th class="px-2 py-2">Stock</th>
                                <th class="px-2 py-2">Precio unitario</th>
                                <th class="px-2 py-2">Precio al mayor</th>
                                <th class="px-2 py-2">Fecha Vencimiento</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="products.length === 0">
                                <td colspan="6" class="px-2 py-4 text-center text-muted-foreground">
                                    No hay productos registrados.
                                </td>
                            </tr>
                            <tr v-for="producto in products" :key="producto.id" class="border-b">
                                <td class="px-2 py-2">{{ producto.id }}</td>
                                <td class="px-2 py-2">{{ producto.name }}</td>
                                <td class="px-2 py-2">{{ producto.description }}</td>
                                <td class="px-2 py-2">{{ producto.stock }}</td>
                                <td class="px-2 py-2">{{ producto.price_unit }}</td>
                                <td class="px-2 py-2">{{ producto.price_higher }}</td>
                                <td class="px-2 py-2">
                                    <div class="flex gap-2">
                                        <Button type="button" variant="secondary" size="sm"
                                            :disabled="form.processing || deleteForm.processing"
                                            @click="startEdit(producto)">
                                            Editar
                                        </Button>
                                        <Button type="button" variant="destructive" size="sm"
                                            :disabled="form.processing || deleteForm.processing" @click="remove(producto)">
                                            Eliminar
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <InputError :message="deleteError" class="mt-3" />
            </section> 
        </div>
    </AppLayout>
</template>
