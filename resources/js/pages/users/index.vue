<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import UserController from '@/actions/App/Http/Controllers/UserController';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { index as usersIndex } from '@/routes/users';
import { type BreadcrumbItem } from '@/types';

type UserRow = {
    id: number;
    name: string;
    email: string;
    personal: string | null;
    role: string | null;
    created_at: string | null;
};

type Props = {
    users: UserRow[];
    roles: string[];
};

const props = defineProps<Props>();
const users = computed(() => props.users);
const roles = computed(() => props.roles);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Usuarios',
        href: usersIndex().url,
    },
];

const editingId = ref<number | null>(null);

const form = useForm({
    name: '',
    email: '',
    personal: '',
    role: props.roles[0] ?? '',
    password: '',
    password_confirmation: '',
});

const deleteForm = useForm({});
const deleteError = computed(() => (deleteForm.errors as Record<string, string | undefined>).delete);

const isEditing = computed(() => editingId.value !== null);

const resetForm = (): void => {
    editingId.value = null;
    form.reset();
    form.clearErrors();
    form.role = props.roles[0] ?? '';
};

const startEdit = (user: UserRow): void => {
    editingId.value = user.id;
    form.clearErrors();
    form.name = user.name;
    form.email = user.email;
    form.personal = user.personal ?? '';
    form.role = user.role ?? props.roles[0] ?? '';
    form.password = '';
    form.password_confirmation = '';
};

const submit = (): void => {
    const options = {
        preserveScroll: true,
        onSuccess: () => resetForm(),
    };

    if (isEditing.value && editingId.value !== null) {
        form.put(UserController.update.url(editingId.value), options);
        return;
    }

    form.post(UserController.store.url(), options);
};

const remove = (user: UserRow): void => {
    if (!confirm(`Eliminar usuario "${user.name}"?`)) {
        return;
    }

    deleteForm.delete(UserController.destroy.url(user.id), {
        preserveScroll: true,
    });
};
</script>

<template>

    <Head title="Usuarios" />

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
                        <Input id="name" v-model="form.name" type="text" required />
                        <InputError :message="form.errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email">Correo</Label>
                        <Input id="email" v-model="form.email" type="email" required />
                        <InputError :message="form.errors.email" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="personal">Personal</Label>
                        <Input id="personal" v-model="form.personal" type="text" />
                        <InputError :message="form.errors.personal" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="role">Rol</Label>
                        <select id="role" v-model="form.role" required
                            class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs transition-colors outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50">
                            <option v-for="role in roles" :key="role" :value="role">
                                {{ role }}
                            </option>
                        </select>
                        <InputError :message="form.errors.role" />
                    </div>

                    <div class="grid gap-2">
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
                    </div>

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
                <h2 class="text-lg font-semibold">Listado de usuarios</h2>

                <div class="mt-4 overflow-x-auto">
                    <table class="w-full min-w-[720px] text-sm">
                        <thead class="border-b text-left">
                            <tr>
                                <th class="px-2 py-2">ID</th>
                                <th class="px-2 py-2">Nombre</th>
                                <th class="px-2 py-2">Correo</th>
                                <th class="px-2 py-2">Personal</th>
                                <th class="px-2 py-2">Rol</th>
                                <th class="px-2 py-2">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
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
                        </tbody>
                    </table>
                </div>
                <InputError :message="deleteError" class="mt-3" />
            </section>
        </div>
    </AppLayout>
</template>
