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
import {
    Users,
    UserPlus,
    Pencil,
    Trash2,
    Search,
    Mail,
    Key,
    User,
    Briefcase,
    Shield,
    Hash,
    X,
    CheckCircle2,
    Sparkles,
} from 'lucide-vue-next';

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
const searchQuery = ref('');

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

const filteredUsers = computed(() => {
    if (!searchQuery.value) return users.value;
    const query = searchQuery.value.toLowerCase();
    return users.value.filter(user =>
        user.name.toLowerCase().includes(query) ||
        user.email.toLowerCase().includes(query) ||
        (user.personal && user.personal.toLowerCase().includes(query)) ||
        (user.role && user.role.toLowerCase().includes(query))
    );
});

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
    if (!confirm(`¿Estás seguro de eliminar al usuario "${user.name}"?`)) return;
    deleteForm.delete(UserController.destroy.url(user.id), { preserveScroll: true });
};

const getRoleClasses = (role: string | null): string => {
    if (!role) return 'bg-zinc-500/10 text-zinc-400 border-zinc-500/20';
    const map: Record<string, string> = {
        admin:      'bg-purple-500/10 text-purple-400 border-purple-500/20',
        supervisor: 'bg-blue-500/10 text-blue-400 border-blue-500/20',
        user:       'bg-green-500/10 text-green-400 border-green-500/20',
    };
    return map[role.toLowerCase()] ?? 'bg-zinc-500/10 text-zinc-400 border-zinc-500/20';
};

const getInitial = (name: string) => name.charAt(0).toUpperCase();
</script>

<template>
    <Head title="Usuarios" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-5 p-5">

            <!-- ── Hero Header ── -->
            <div class="relative overflow-hidden rounded-2xl px-7 py-6"
                style="background: linear-gradient(135deg, #052e16 0%, #14532d 50%, #166534 100%);">
                <!-- Glow radial decorativo -->
                <div class="pointer-events-none absolute inset-0"
                    style="background: radial-gradient(ellipse at 15% 60%, rgba(34,197,94,0.30) 0%, transparent 55%), radial-gradient(ellipse at 85% 20%, rgba(74,222,128,0.18) 0%, transparent 50%);" />

                <div class="relative flex items-center gap-4">
                    <!-- Ícono -->
                    <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-xl border border-green-400/25 bg-green-400/15 text-green-400">
                        <Users :size="26" />
                    </div>
                    <!-- Texto -->
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight text-white">Gestión de Usuarios</h1>
                       
                    </div>
                    <!-- Badge contador -->
                    <div class="ml-auto flex items-center gap-1.5 rounded-full border border-green-400/30 bg-green-500/15 px-3 py-1.5 text-xs font-semibold text-green-400">
                        <Sparkles :size="13" />
                        <span>{{ users.length }} usuario{{ users.length !== 1 ? 's' : '' }}</span>
                    </div>
                </div>
            </div>

            <!-- ── Form Card ── -->
            <section class="relative overflow-hidden rounded-2xl border border-border bg-background transition-shadow duration-300 hover:shadow-lg hover:shadow-green-600/[0.08]">
                <!-- Stripe top — verde normal / ámbar en edición -->
                <div
                    class="absolute inset-x-0 top-0 h-[3px]"
                    :class="isEditing
                        ? 'bg-gradient-to-r from-amber-500 to-orange-500'
                        : 'bg-gradient-to-r from-green-600 via-green-400 to-green-500'"
                />

                <!-- Card header -->
                <div class="flex items-center gap-3.5 px-6 pt-5 pb-3">
                    <div
                        class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-[10px] border"
                        :class="isEditing
                            ? 'border-amber-500/20 bg-amber-500/10 text-amber-500'
                            : 'border-green-500/20 bg-green-500/10 text-green-500'"
                    >
                        <Pencil v-if="isEditing" :size="17" />
                        <UserPlus v-else :size="17" />
                    </div>
                    <div>
                        <h2 class="text-[1rem] font-bold tracking-tight text-foreground">
                            {{ isEditing ? 'Editar Usuario' : 'Nuevo Usuario' }}
                        </h2>
                        <p class="mt-0.5 text-xs text-muted-foreground">
                            {{ isEditing
                                ? 'Modifica los datos del usuario seleccionado'
                                : 'Completa el formulario para registrar un nuevo usuario' }}
                        </p>
                    </div>
                </div>

                <!-- Form -->
                <form class="grid grid-cols-1 gap-4 px-6 pb-6 md:grid-cols-2" @submit.prevent="submit">

                    <!-- Nombre -->
                    <div class="flex flex-col gap-1.5">
                        <Label for="name" class="flex items-center gap-1.5 text-xs font-semibold text-foreground">
                            <User :size="13" class="text-green-500/90" />
                            Nombre completo
                        </Label>
                        <Input id="name" v-model="form.name" type="text" required
                            placeholder="Ej. Juan Pérez"
                            class="rounded-[9px] border-[1.5px] text-[0.855rem] focus-visible:border-green-500 focus-visible:ring-green-500/20" />
                        <InputError :message="form.errors.name" />
                    </div>

                    <!-- Email -->
                    <div class="flex flex-col gap-1.5">
                        <Label for="email" class="flex items-center gap-1.5 text-xs font-semibold text-foreground">
                            <Mail :size="13" class="text-green-500/90" />
                            Correo electrónico
                        </Label>
                        <Input id="email" v-model="form.email" type="email" required
                            placeholder="ejemplo@correo.com"
                            class="rounded-[9px] border-[1.5px] text-[0.855rem] focus-visible:border-green-500 focus-visible:ring-green-500/20" />
                        <InputError :message="form.errors.email" />
                    </div>

                    <!-- Personal -->
                    <div class="flex flex-col gap-1.5">
                        <Label for="personal" class="flex items-center gap-1.5 text-xs font-semibold text-foreground">
                            <Briefcase :size="13" class="text-green-500/90" />
                            Personal
                            <span class="ml-1 rounded-full border border-green-500/20 bg-green-500/10 px-2 py-0.5 text-[0.68rem] font-medium text-green-600">
                                opcional
                            </span>
                        </Label>
                        <Input id="personal" v-model="form.personal" type="text"
                            placeholder="Área o departamento"
                            class="rounded-[9px] border-[1.5px] text-[0.855rem] focus-visible:border-green-500 focus-visible:ring-green-500/20" />
                        <InputError :message="form.errors.personal" />
                    </div>

                    <!-- Rol -->
                    <div class="flex flex-col gap-1.5">
                        <Label for="role" class="flex items-center gap-1.5 text-xs font-semibold text-foreground">
                            <Shield :size="13" class="text-green-500/90" />
                            Rol de usuario
                        </Label>
                        <select id="role" v-model="form.role" required
                            class="h-9 w-full rounded-[9px] border border-input bg-background px-3 text-[0.855rem] text-foreground outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-500/20">
                            <option v-for="role in roles" :key="role" :value="role">{{ role }}</option>
                        </select>
                        <InputError :message="form.errors.role" />
                    </div>

                    <!-- Contraseña -->
                    <div class="flex flex-col gap-1.5">
                        <Label for="password" class="flex items-center gap-1.5 text-xs font-semibold text-foreground">
                            <Key :size="13" class="text-green-500/90" />
                            Contraseña
                            <span v-if="isEditing"
                                class="ml-1 rounded-full border border-green-500/20 bg-green-500/10 px-2 py-0.5 text-[0.68rem] font-medium text-green-600">
                                opcional en edición
                            </span>
                        </Label>
                        <Input id="password" v-model="form.password" type="password"
                            :required="!isEditing" placeholder="••••••••"
                            class="rounded-[9px] border-[1.5px] text-[0.855rem] focus-visible:border-green-500 focus-visible:ring-green-500/20" />
                        <InputError :message="form.errors.password" />
                    </div>

                    <!-- Confirmar Contraseña -->
                    <div class="flex flex-col gap-1.5">
                        <Label for="password_confirmation" class="flex items-center gap-1.5 text-xs font-semibold text-foreground">
                            <Key :size="13" class="text-green-500/90" />
                            Confirmar contraseña
                        </Label>
                        <Input id="password_confirmation" v-model="form.password_confirmation"
                            type="password" :required="!isEditing" placeholder="••••••••"
                            class="rounded-[9px] border-[1.5px] text-[0.855rem] focus-visible:border-green-500 focus-visible:ring-green-500/20" />
                    </div>

                    <!-- Botones -->
                    <div class="col-span-full flex items-center gap-2.5 pt-1">
                        <Button
                            type="submit"
                            :disabled="form.processing || deleteForm.processing"
                            class="inline-flex items-center gap-2 rounded-[9px] bg-green-600 px-5 py-2.5 text-[0.855rem] font-semibold text-white shadow-[0_3px_12px_rgba(22,163,74,0.35)] transition hover:-translate-y-px hover:bg-green-700 hover:shadow-[0_5px_18px_rgba(22,163,74,0.42)] active:translate-y-0 disabled:cursor-not-allowed disabled:opacity-55"
                        >
                            <CheckCircle2 v-if="isEditing" :size="15" />
                            <UserPlus v-else :size="15" />
                            {{ isEditing ? 'Actualizar Usuario' : 'Crear Usuario' }}
                        </Button>
                        <Button
                            v-if="isEditing"
                            type="button"
                            variant="secondary"
                            :disabled="form.processing || deleteForm.processing"
                            class="inline-flex items-center gap-2 rounded-[9px] text-[0.855rem] font-semibold transition hover:border-red-300 hover:bg-red-50 hover:text-red-500"
                            @click="resetForm"
                        >
                            <X :size="15" />
                            Cancelar
                        </Button>
                    </div>

                </form>
            </section>

        
           <!-- ── Table Card ── -->
<section class="relative overflow-hidden rounded-2xl border border-border bg-background transition-shadow duration-300 hover:shadow-lg hover:shadow-green-600/[0.08]">
    <div class="absolute inset-x-0 top-0 h-[3px] bg-gradient-to-r from-green-600 via-green-400 to-green-500" />

    <!-- Card header con buscador -->
    <div class="flex flex-wrap items-center gap-3.5 px-6 pt-5 pb-3">
        <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-[10px] border border-green-500/20 bg-green-500/10 text-green-500">
            <Users :size="17" />
        </div>
        <div>
            <h2 class="text-[1rem] font-bold tracking-tight text-foreground">Listado de Usuarios</h2>
            <p class="mt-0.5 text-xs text-muted-foreground">
                {{ filteredUsers.length }} registro{{ filteredUsers.length !== 1 ? 's' : '' }} encontrado{{ filteredUsers.length !== 1 ? 's' : '' }}
            </p>
        </div>
        <!-- Buscador -->
        <div class="relative ml-auto">
            <Search :size="14" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground" />
            <Input
                v-model="searchQuery"
                type="text"
                placeholder="Buscar usuarios..."
                class="w-52 rounded-[9px] border-[1.5px] pl-8 text-[0.855rem] focus-visible:border-green-500 focus-visible:ring-green-500/20"
            />
        </div>
    </div>

    <!-- Tabla -->
    <div class="px-6 pb-6">
        <table class="w-full border-separate border-spacing-0 text-[0.84rem]">
            <thead>
                <tr class="bg-green-500/[0.05]">
                    <th class="whitespace-nowrap border-b-2 border-green-500/20 px-4 py-2.5 text-left text-[0.7rem] font-bold uppercase tracking-widest text-green-600">
                        <Hash :size="11" class="mr-0.5 inline align-middle opacity-75" />ID
                    </th>
                    <th class="whitespace-nowrap border-b-2 border-green-500/20 px-4 py-2.5 text-left text-[0.7rem] font-bold uppercase tracking-widest text-green-600">
                        <User :size="11" class="mr-0.5 inline align-middle opacity-75" />Usuario
                    </th>
                    <th class="whitespace-nowrap border-b-2 border-green-500/20 px-4 py-2.5 text-left text-[0.7rem] font-bold uppercase tracking-widest text-green-600">
                        <Mail :size="11" class="mr-0.5 inline align-middle opacity-75" />Contacto
                    </th>
                    <th class="whitespace-nowrap border-b-2 border-green-500/20 px-4 py-2.5 text-left text-[0.7rem] font-bold uppercase tracking-widest text-green-600">
                        <Briefcase :size="11" class="mr-0.5 inline align-middle opacity-75" />Personal
                    </th>
                    <th class="whitespace-nowrap border-b-2 border-green-500/20 px-4 py-2.5 text-left text-[0.7rem] font-bold uppercase tracking-widest text-green-600">
                        <Shield :size="11" class="mr-0.5 inline align-middle opacity-75" />Rol
                    </th>
                    <th class="whitespace-nowrap border-b-2 border-green-500/20 px-4 py-2.5 text-left text-[0.7rem] font-bold uppercase tracking-widest text-green-600">
                        Acciones
                    </th>
                </tr>
            </thead>
            <tbody>
                <!-- Empty state -->
                <tr v-if="filteredUsers.length === 0">
                    <td colspan="6" class="border-b-0 px-4 py-10">
                        <div class="flex flex-col items-center justify-center gap-1.5">
                            <Users :size="38" class="mb-1 text-green-400 opacity-45" />
                            <p class="text-[0.92rem] font-semibold text-muted-foreground">No hay usuarios registrados aún</p>
                            <span class="text-xs text-muted-foreground opacity-65">Crea tu primer usuario usando el formulario de arriba</span>
                        </div>
                    </td>
                </tr>

                <!-- Filas -->
                <tr
                    v-for="user in filteredUsers"
                    :key="user.id"
                    class="transition-colors duration-150 hover:bg-green-500/[0.04]"
                    :class="editingId === user.id ? 'bg-amber-500/[0.06]' : ''"
                >
                    <!-- ID -->
                    <td class="border-b border-border px-4 py-3 align-middle">
                        <span class="inline-flex items-center rounded-md border border-green-500/20 bg-green-500/10 px-2 py-0.5 font-mono text-[0.73rem] font-bold text-green-600">
                            #{{ user.id }}
                        </span>
                    </td>

                    <!-- Usuario con avatar - OPCIÓN 4 (difuminado sutil) -->
                    <td class="border-b border-border px-4 py-3 align-middle">
                        <div class="flex items-center gap-2.5">
                            <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-green-400/70 via-emerald-500/90 to-teal-600 text-[0.78rem] font-bold text-white backdrop-blur-sm shadow-xl shadow-green-500/20 ring-1 ring-white/20">
                                {{ getInitial(user.name) }}
                            </div>
                            <span class="font-semibold text-foreground">{{ user.name }}</span>
                        </div>
                    </td>

                    <!-- Email (normal) -->
                    <td class="border-b border-border px-4 py-3 align-middle">
                        <div class="flex items-center gap-1.5">
                            <Mail :size="12" class="flex-shrink-0 text-muted-foreground opacity-60" />
                            <span class="text-[0.84rem] text-foreground">{{ user.email }}</span>
                        </div>
                    </td>

                    <!-- Personal (normal) -->
                    <td class="border-b border-border px-4 py-3 align-middle">
                        <div class="flex items-center gap-1.5">
                            <Briefcase :size="12" class="flex-shrink-0 text-muted-foreground opacity-60" />
                            <span class="text-[0.82rem] text-muted-foreground">{{ user.personal || '—' }}</span>
                        </div>
                    </td>

                    <!-- Rol (con colores) -->
                    <td class="border-b border-border px-4 py-3 align-middle">
                        <span
                            class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-[0.72rem] font-semibold"
                            :class="{
                                'bg-purple-500/10 text-purple-400 border border-purple-500/20': user.role?.toLowerCase().includes('admin') || user.role?.toLowerCase().includes('administrador'),
                                'bg-blue-500/10 text-blue-400 border border-blue-500/20': user.role?.toLowerCase().includes('gerente'),
                                'bg-green-500/10 text-green-400 border border-green-500/20': user.role?.toLowerCase().includes('cliente'),
                                'bg-amber-500/10 text-amber-400 border border-amber-500/20': user.role?.toLowerCase().includes('asistente'),
                                'bg-zinc-500/10 text-zinc-400 border border-zinc-500/20': !user.role
                            }"
                        >
                            <Shield :size="10" />
                            {{ user.role || 'Sin rol' }}
                        </span>
                    </td>

                    <!-- Acciones -->
                    <td class="border-b border-border px-4 py-3 align-middle">
                        <div class="flex items-center gap-1.5">
                            <Button
                                type="button"
                                variant="secondary"
                                size="sm"
                                :disabled="form.processing || deleteForm.processing"
                                class="flex h-8 w-8 items-center justify-center rounded-lg border border-green-500/30 bg-green-500/[0.08] p-0 text-green-600 transition hover:-translate-y-px hover:border-green-600 hover:bg-green-600 hover:text-white hover:shadow-[0_4px_10px_rgba(22,163,74,0.35)] disabled:opacity-45"
                                title="Editar usuario"
                                @click="startEdit(user)"
                            >
                                <Pencil :size="14" />
                            </Button>
                            <Button
                                type="button"
                                variant="destructive"
                                size="sm"
                                :disabled="form.processing || deleteForm.processing"
                                class="flex h-8 w-8 items-center justify-center rounded-lg border border-red-500/25 bg-red-500/[0.07] p-0 text-red-500 transition hover:-translate-y-px hover:border-red-500 hover:bg-red-500 hover:text-white hover:shadow-[0_4px_10px_rgba(239,68,68,0.35)] disabled:opacity-45"
                                title="Eliminar usuario"
                                @click="remove(user)"
                            >
                                <Trash2 :size="14" />
                            </Button>
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

