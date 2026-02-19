<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import ReservationController from '@/actions/App/Http/Controllers/ReservationController';

type Evento = {
    id: number;
    nombre: string;
    precio: number;
};

type reservations = {
    id: number;
    id_evento: string;
    id_user: string;
    id_pay: string;
    id_customer: string;
    start_time: string;
    end_time: string;
    date: string;
    reservation_status: string;
};

type Props = {
    reservations: reservations[];
    eventos: Evento[];
};

const props = defineProps<Props>();
const reservations = computed(() => props.reservations);
const eventos = computed(() => props.eventos);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'inicio',
        href: './dashboard',
    },
];

const editingId = ref<number | null>(null);

const form = useForm({
    id_evento: '',
    id_user: '',
    id_pay: '',
    id_customer: '',
    start_time: '',
    end_time: '',
    date: '',
    reservation_status: '',
});

const deleteForm = useForm({});
const deleteError = computed(() => (deleteForm.errors as Record<string, string | undefined>).delete);

const isEditing = computed(() => editingId.value !== null);

const resetForm = (): void => {
    editingId.value = null;
    form.reset();
    form.clearErrors();
};

const startEdit = (reservations: reservations): void => {
    editingId.value = reservations.id;
    form.clearErrors();
    form.id_evento = reservations.id_evento;
    form.id_user = reservations.id_user;
    form.id_pay = reservations.id_pay;
    form.id_customer = reservations.id_customer;
    form.start_time = reservations.start_time;
    form.end_time = reservations.end_time;
    form.date = reservations.date;
    form.reservation_status = reservations.reservation_status;
};

const submit = (): void => {
    const options = {
        preserveScroll: true,
        onSuccess: () => resetForm(),
    };
    console.log(editingId.value);
    if (isEditing.value && editingId.value !== null) {
        form.put(ReservationController.update.url(editingId.value), options);
        return;
    }

    // form.post(ReservationController.store.url(), options);
};

const remove = (reservations: reservations): void => {
    if (!confirm(`Eliminar reserva "${reservations.id}"?`)) {
        return;
    }

    deleteForm.delete(ReservationController.destroy.url(reservations.id), {
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
                    <!-- ID_EVENTO -->
                    <div class="grid gap-2">
                        <Label for="id_evento">ID Evento</Label>
                        <Input id="id_evento" v-model="form.id_evento" type="text" required />
                        <InputError :message="form.errors.id_evento" />
                    </div>
                    <!-- ID_USUARIO -->
                    <div class="grid gap-2">
                        <Label for="id_user">ID Usuario</Label>
                        <Input id="id_user" v-model="form.id_user" type="text" required />
                        <InputError :message="form.errors.id_user" />
                    </div>
                    <!-- ID_PAGO -->
                    <div class="grid gap-2">
                        <Label for="id_pay">ID Pago</Label>
                        <Input id="id_pay" v-model="form.id_pay" type="text" />
                        <InputError :message="form.errors.id_pay" />
                    </div>
                    <!-- ID_CLIENTE -->
                    <div class="grid gap-2">
                        <Label for="id_customer">ID Cliente</Label>
                        <Input id="id_customer" v-model="form.id_customer" type="text" />
                        <InputError :message="form.errors.id_customer" />
                    </div>
                    <!-- HORA INICIO -->
                    <div class="grid gap-2">
                        <Label for="start_time">Hora Inicio</Label>
                        <Input id="start_time" v-model="form.start_time" type="text" />
                        <InputError :message="form.errors.start_time" />
                    </div>
                    <!-- HORA FINAL -->
                    <div class="grid gap-2">
                        <Label for="end_time">Hora Final</Label>
                        <Input id="end_time" v-model="form.end_time" type="text" />
                        <InputError :message="form.errors.end_time" />
                    </div>
                    <!-- FECHA -->
                    <div class="grid gap-2">
                        <Label for="date">Fecha</Label>
                        <Input id="date" v-model="form.date" type="text" />
                        <InputError :message="form.errors.date" />
                    </div>
                    <!-- ESTADO DE RESERVA -->
                    <div class="grid gap-2">
                        <Label for="reservation_status">Estado de Reserva</Label>
                        <select name="reservation_status" id="reservation_status" v-model="form.reservation_status"
                            class="text-xs text-muted-foreground">
                            <option value="free" class="text-black">Libre</option>
                            <option value="busy" class="text-black">Ocupado</option>
                        </select>
                        <InputError :message="form.errors.reservation_status" />
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
                <h2 class="text-lg font-semibold">Listado de reservas</h2>

                <div class="mt-4 overflow-x-auto">
                    <table class="w-full min-w-[720px] text-sm">
                        <thead class="border-b text-left">
                            <tr>
                                <th class="px-2 py-2">ID Evento</th>
                                <th class="px-2 py-2">ID Usuario</th>
                                <th class="px-2 py-2">ID Pago</th>
                                <th class="px-2 py-2">ID Cliente</th>
                                <th class="px-2 py-2">Hora Inicio</th>
                                <th class="px-2 py-2">Hora Final</th>
                                <th class="px-2 py-2">Fecha</th>
                                <th class="px-2 py-2">Estado de Reserva</th>
                                <th class="px-2 py-2">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="reservations.length === 0">
                                <td colspan="6" class="px-2 py-4 text-center text-muted-foreground">
                                    No hay reservas registradas.
                                </td>
                            </tr>
                            <tr v-for="reservation in reservations" :key="reservation.id" class="border-b">
                                <td class="px-2 py-2">{{ reservation.id_evento }}</td>
                                <td class="px-2 py-2">{{ reservation.id_user }}</td>
                                <td class="px-2 py-2">{{ reservation.id_pay }}</td>
                                <td class="px-2 py-2">{{ reservation.id_customer }}</td>
                                <td class="px-2 py-2">{{ reservation.start_time }}</td>
                                <td class="px-2 py-2">{{ reservation.end_time }}</td>
                                <td class="px-2 py-2">{{ reservation.date }}</td>
                                <td class="px-2 py-2">{{ reservation.reservation_status === 'free' ? 'Libre' : 'Ocupado'
                                    }}</td>
                                <td class="px-2 py-2">
                                    <div class="flex gap-2">
                                        <Button type="button" variant="secondary" size="sm"
                                            :disabled="form.processing || deleteForm.processing"
                                            @click="startEdit(reservation)">
                                            Editar
                                        </Button>
                                        <Button type="button" variant="destructive" size="sm"
                                            :disabled="form.processing || deleteForm.processing"
                                            @click="remove(reservation)">
                                            Eliminar
                                        </Button>
                                        <!-- BOTON CAMBIAR ESTADO [LIBRE,OCUPADO] -->
                                        <!-- <button @click="toggleStatus(reservation)"
                                            :class="reservation.reservation_status === 'free' ? 'btn-success' : 'btn-danger'"
                                            class="btn">
                                            {{ reservation.reservation_status === 'free' ? 'Libre' : 'Ocupado' }}
                                        </button> -->
                                    </div>
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
