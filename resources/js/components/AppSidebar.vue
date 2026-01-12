<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
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
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import {
    BookOpen,
    Calendar,
    FileText,
    Folder,
    LayoutGrid,
    Palmtree,
    Users,
} from 'lucide-vue-next';
import { UserRole } from '@/enums/UserRole';

// Get user role from auth (adjust based on your actual auth structure)
const page = usePage();
const userRole = computed(() => {
    const user = page.props.auth?.user as { role?: string } | undefined;
    return user?.role as UserRole | undefined;
});

// Main navigation items
const mainNavItems = computed<NavItem[]>(() => {
    const items: NavItem[] = [
        {
            title: 'Dashboard',
            href: '/dashboard',
            icon: LayoutGrid,
        },
        {
            title: 'Requests',
            href: '/requests',
            icon: FileText,
        },
    ];

    // Only show Team for managers and admins
    if (userRole.value === UserRole.MANAGER || userRole.value === UserRole.ADMIN) {
        items.push({
            title: 'Team',
            href: '/teams',
            icon: Users,
        });
    }

    // Only show Employees for admins
    if (userRole.value === UserRole.ADMIN) {
        items.push({
            title: 'Employees',
            href: '/employees',
            icon: Users,
        });
    }

    return items;
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
                        <Link href="/dashboard">
                            <div
                                class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-600"
                            >
                                <Palmtree class="h-5 w-5 text-white" />
                            </div>
                            <div class="ml-1 grid flex-1 text-left text-sm">
                                <span class="font-semibold">Vacationly</span>
                                <span class="text-xs text-muted-foreground">
                                    Manage your time off
                                </span>
                            </div>
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
</template>
