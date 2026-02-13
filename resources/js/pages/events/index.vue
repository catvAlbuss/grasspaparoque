<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import EventosController from '@/actions/App/Http/Controllers/EventosController';


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
    {
        title: 'eventos',
        href: './eventos',
    },
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

const resetForm = (): void => {
    editingId.value = null;
    form.reset();
    form.clearErrors();
};

const startEdit = (events: eventos): void => {
    editingId.value = events.id;
    form.clearErrors();
    form.nombre = events.nombre;
    form.precio = events.precio;
    form.descripcion = events.descripcion;
    form.estado = events.estado;
};

const submit = (): void => {
    const options = {
        preserveScroll: true,
        onSuccess: () => resetForm(),
    };

    if (isEditing.value && editingId.value !== null) {
        form.put(EventosController.update.url(editingId.value), options);
        return;
    }

    form.post(EventosController.store.url(), options);
};

const remove = (events: eventos): void => {
    if (!confirm(`Eliminar usuario "${events.nombre}"?`)) {
        return;
    }

    deleteForm.delete(EventosController.destroy.url(events.id), {
        preserveScroll: true,
    });
};
</script>

<template>

    <Head title="eventos" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4">
            <section class="rounded-xl border border-sidebar-border/70 bg-background p-4">
                <h1 class="text-xl font-semibold">
                    {{ isEditing ? 'Editar usuario' : 'Nuevo usuario' }}
                </h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    Gestiona usuarios, rol y personal desde esta misma vista.
                </p>

                <form class="mt-4 grid gap-4 md:grid-cols-2" @submit.prevent="submit">
                    <div class="grid gap-2">
                        <Label for="name">Nombre</Label>
                        <Input id="name" v-model="form.nombre" type="text" required />
                        <InputError :message="form.errors.nombre" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email">Correo</Label>
                        <Input id="email" v-model="form.precio" type="text" required />
                        <InputError :message="form.errors.precio" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="personal">Personal</Label>
                        <Input id="personal" v-model="form.descripcion" type="text" />
                        <InputError :message="form.errors.descripcion" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="estado">
                            Estado
                            <span v-if="isEditing" class="text-xs text-muted-foreground">(opcional en edicion)</span>
                        </Label>
                        <Input id="estado" v-model="form.estado" type="number" :required="!isEditing" />
                        <InputError :message="form.errors.estado" />
                    </div>

                    <!-- <div class="grid gap-2">
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
                <h2 class="text-lg font-semibold">Listado de eventos</h2>

                <div class="mt-4 overflow-x-auto">
                    <table class="w-full min-w-[720px] text-sm">
                        <thead class="border-b text-left">
                            <tr>
                                <th class="px-2 py-2">ID</th>
                                <th class="px-2 py-2">Nombre</th>
                                <th class="px-2 py-2">Precio</th>
                                <th class="px-2 py-2">Descripcion</th>
                                <th class="px-2 py-2">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="eventos.length === 0">
                                <td colspan="6" class="px-2 py-4 text-center text-muted-foreground">
                                    No hay usuarios registrados.
                                </td>
                            </tr>
                            <tr v-for="events in eventos" :key="events.id" class="border-b">
                                <td class="px-2 py-2">{{ events.id }}</td>
                                <td class="px-2 py-2">{{ events.nombre }}</td>
                                <td class="px-2 py-2">{{ events.precio }}</td>
                                <td class="px-2 py-2">{{ events.descripcion ?? '-' }}</td>
                                <td class="px-2 py-2">{{ events.estado ?? '-' }}</td>
                                <td class="px-2 py-2">
                                    <!-- <div class="flex gap-2">
                                        <Button type="button" variant="secondary" size="sm"
                                            :disabled="form.processing || deleteForm.processing"
                                            @click="startEdit(user)">
                                            Editar
                                        </Button>
                                        <Button type="button" variant="destructive" size="sm"
                                            :disabled="form.processing || deleteForm.processing" @click="remove(user)">
                                            Eliminar
                                        </Button>
                                    </div> -->
                                </td>
                            </tr>
                        </tbody>
                        <!-- <tbody>
                            <tr v-if="users.length === 0">
                                <td colspan="6" class="px-2 py-4 text-center text-muted-foreground">
                                    No hay usuarios registrados.
                                </td>
                            </tr>
                            <tr v-for="user in users" :key="user.id" class="border-b">
                                <td class="px-2 py-2">{{ user.id }}</td>
                                <td class="px-2 py-2">{{ user.name }}</td>
                                <td class="px-2 py-2">{{ user.email }}</td>
                                <td class="px-2 py-2">{{ user.personal ?? '-' }}</td>
                                <td class="px-2 py-2">{{ user.role ?? '-' }}</td>
                                <td class="px-2 py-2">
                                    <div class="flex gap-2">
                                        <Button type="button" variant="secondary" size="sm"
                                            :disabled="form.processing || deleteForm.processing"
                                            @click="startEdit(user)">
                                            Editar
                                        </Button>
                                        <Button type="button" variant="destructive" size="sm"
                                            :disabled="form.processing || deleteForm.processing" @click="remove(user)">
                                            Eliminar
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                        </tbody> -->

                    </table>
                </div>
                <InputError :message="deleteError" class="mt-3" />
            </section>

        </div>
    </AppLayout>
</template>
