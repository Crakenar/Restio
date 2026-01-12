<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Users } from 'lucide-vue-next';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import EmployeeForm from '@/components/EmployeeForm.vue';
import CsvUpload from '@/components/CsvUpload.vue';
import EmployeeList from '@/components/EmployeeList.vue';
import { ref } from 'vue';

interface Employee {
    id: number;
    name: string;
    email: string;
    role: string;
    created_at: string;
}

interface Props {
    employees: Employee[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Employees', href: '/employees' },
];

const activeTab = ref('list');
</script>

<template>
    <Head title="Employees" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <!-- Gradient background - adapts to theme -->
        <div class="absolute inset-0 -z-10 bg-gradient-to-br from-slate-50 via-purple-50 to-indigo-50 dark:from-slate-950 dark:via-purple-950 dark:to-indigo-950" />

        <!-- Animated gradient orbs -->
        <div class="pointer-events-none absolute inset-0 -z-10 overflow-hidden">
            <div
                class="absolute -top-1/2 -right-1/2 h-full w-full animate-pulse rounded-full bg-gradient-to-br from-purple-500/10 via-indigo-500/10 to-blue-500/10 dark:from-purple-500/20 dark:via-indigo-500/20 dark:to-blue-500/20 blur-3xl"
                style="animation-duration: 8s"
            />
            <div
                class="absolute -bottom-1/2 -left-1/2 h-full w-full animate-pulse rounded-full bg-gradient-to-tr from-pink-500/10 via-rose-500/10 to-orange-500/10 dark:from-pink-500/20 dark:via-rose-500/20 dark:to-orange-500/20 blur-3xl"
                style="animation-duration: 10s; animation-delay: 1s"
            />
        </div>

        <!-- Content -->
        <div class="relative flex h-full flex-1 flex-col gap-6 overflow-hidden p-6">
            <!-- Enhanced Header -->
            <div class="flex items-center gap-4">
                <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-purple-500 to-indigo-600 shadow-2xl shadow-purple-500/30">
                    <Users class="h-8 w-8 text-white" />
                </div>
                <div>
                    <h1 class="text-4xl font-bold tracking-tight text-slate-900 dark:text-white">
                        Employees
                    </h1>
                    <p class="mt-1.5 text-sm text-slate-600 dark:text-white/70">
                        Manage your team members and import employees
                    </p>
                </div>
            </div>

            <!-- Tabs for different actions -->
            <Tabs v-model="activeTab" class="flex flex-1 flex-col space-y-4">
                <TabsList class="w-fit border border-slate-200 bg-white/90 shadow-lg backdrop-blur-xl dark:border-white/20 dark:bg-white/10">
                    <TabsTrigger value="list" class="text-slate-600 hover:bg-slate-100 hover:text-slate-900 data-[state=active]:bg-slate-200 data-[state=active]:text-slate-900 dark:text-white/70 dark:hover:bg-white/5 dark:hover:text-white dark:data-[state=active]:bg-white/20 dark:data-[state=active]:text-white">
                        All Employees ({{ employees.length }})
                    </TabsTrigger>
                    <TabsTrigger value="add" class="text-slate-600 hover:bg-slate-100 hover:text-slate-900 data-[state=active]:bg-slate-200 data-[state=active]:text-slate-900 dark:text-white/70 dark:hover:bg-white/5 dark:hover:text-white dark:data-[state=active]:bg-white/20 dark:data-[state=active]:text-white">
                        Add Employee
                    </TabsTrigger>
                    <TabsTrigger value="import" class="text-slate-600 hover:bg-slate-100 hover:text-slate-900 data-[state=active]:bg-slate-200 data-[state=active]:text-slate-900 dark:text-white/70 dark:hover:bg-white/5 dark:hover:text-white dark:data-[state=active]:bg-white/20 dark:data-[state=active]:text-white">
                        Import CSV
                    </TabsTrigger>
                </TabsList>

                <!-- Employee List Tab -->
                <TabsContent
                    value="list"
                    class="flex-1 overflow-auto rounded-3xl border border-slate-200 bg-white/80 p-6 shadow-2xl backdrop-blur-xl dark:border-white/20 dark:bg-white/10"
                >
                    <EmployeeList :employees="employees" />
                </TabsContent>

                <!-- Add Single Employee Tab -->
                <TabsContent
                    value="add"
                    class="flex-1 overflow-auto rounded-3xl border border-slate-200 bg-white/80 p-6 shadow-2xl backdrop-blur-xl dark:border-white/20 dark:bg-white/10"
                >
                    <EmployeeForm @success="activeTab = 'list'" />
                </TabsContent>

                <!-- Import CSV Tab -->
                <TabsContent
                    value="import"
                    class="flex-1 overflow-auto rounded-3xl border border-slate-200 bg-white/80 p-6 shadow-2xl backdrop-blur-xl dark:border-white/20 dark:bg-white/10"
                >
                    <CsvUpload @success="activeTab = 'list'" />
                </TabsContent>
            </Tabs>
        </div>
    </AppLayout>
</template>
