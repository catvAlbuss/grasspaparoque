<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import { BookOpen, Folder, LayoutGrid, ReceiptText, Users2 } from 'lucide-vue-next';
import { computed } from 'vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import box from '@/routes/box';
import eventos from '@/routes/eventos';
import products from '@/routes/products';
import reservations from '@/routes/reservations';
import users from '@/routes/users';
import { type NavItem } from '@/types';
import AppLogo from './AppLogo.vue';

type PageProps = {
    auth?: {
        role_names?: string[];
    };
};

const page = usePage<PageProps>();
const roleNames = computed(() => page.props.auth?.role_names ?? []);
const canManageReservations = computed(() => {
    const allowed = ['root', 'gerente', 'administrador', 'asistente'];
    return roleNames.value.some((role) => allowed.includes(role));
});

const mainNavItems = computed<NavItem[]>(() => {
    const baseItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Usuarios',
        href: users.index.url(),
        icon: Users2,
    },
     
    {
        title: 'reservations',
        href: reservations.index.url(),
        icon: Users2,
    },
    {
        title: 'eventos',
        href: eventos.index.url(),
        icon: LayoutGrid,
    },
    {
        title: 'productos',
        href: products.index.url(),
        icon: LayoutGrid,
    },
    {
        title: 'caja',
        href: box.index.url(),
        icon: LayoutGrid,
    },
    ];


    if (canManageReservations.value) {
        baseItems.push({
            title: 'Comprobaciones',
            href: reservations.index.url(),
            icon: ReceiptText,
        });
    }

    return baseItems;
});

const footerNavItems: NavItem[] = [
    {
        title: 'Github Repo',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>

