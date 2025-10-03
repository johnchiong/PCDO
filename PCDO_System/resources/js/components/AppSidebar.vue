<script setup lang="ts">
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import cooperatives  from '@/routes/cooperatives';
import amortizations from '@/routes/amortizations';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { LayoutGrid, Handshake, HandCoins, Bell, FolderKanban, BookMarked } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';
import programs from '@/routes/programs';
import notification from '@/routes/notifications';
import documentation from '@/routes/documentation';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Cooperative',
        href: cooperatives.index(),
        icon: Handshake,
    },
    {
        title: 'Programs',
        href: programs.index(),
        icon: FolderKanban,
    },
    {
        title: 'Payments',
        href: amortizations.index(),
        icon: HandCoins,
    },
    {
        title: 'Documentation',
        href: documentation.index(),
        icon: BookMarked,
    },
    {
        title: 'Notification',
        href: notification.index(),
        icon: Bell,
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
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
