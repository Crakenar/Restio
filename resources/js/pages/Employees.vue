<script setup lang="ts">
import PremiumSidebar from '@/components/PremiumSidebar.vue';
import { Head, usePage } from '@inertiajs/vue3';
const page = usePage();
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

const activeTab = ref('list');
</script>

<template>
    <Head title="Employees" />

    <div class="flex min-h-screen bg-gradient-to-br from-slate-50 via-orange-50 to-rose-50 dark:from-slate-950 dark:via-orange-950 dark:to-rose-950">
        <!-- Sidebar -->
        <PremiumSidebar :notifications="$page.props.notifications || []" />

        <!-- Main content area -->
        <div class="ml-72 flex-1 p-4 transition-all duration-500 sm:p-6 lg:p-8">
            <!-- Animated gradient orbs -->
            <div class="pointer-events-none fixed inset-0 overflow-hidden">
                <div
                    class="absolute -top-1/2 -right-1/2 h-full w-full animate-pulse rounded-full bg-gradient-to-br from-orange-500/10 via-amber-500/10 to-yellow-500/10 dark:from-orange-500/20 dark:via-amber-500/20 dark:to-yellow-500/20 blur-3xl"
                    style="animation-duration: 8s"
                />
                <div
                    class="absolute -bottom-1/2 -left-1/2 h-full w-full animate-pulse rounded-full bg-gradient-to-tr from-rose-500/10 via-pink-500/10 to-red-500/10 dark:from-rose-500/20 dark:via-pink-500/20 dark:to-red-500/20 blur-3xl"
                    style="animation-duration: 10s; animation-delay: 1s"
                />
            </div>

            <!-- Content -->
            <div class="relative mx-auto max-w-7xl space-y-6">
            <!-- Enhanced Header -->
            <div class="flex items-center gap-4">
                <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-orange-400 to-rose-500 shadow-2xl shadow-orange-500/30">
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
            <Tabs v-model="activeTab" class="flex flex-1 flex-col space-y-6">
                <!-- Enhanced Tab List -->
                <div class="relative">
                    <!-- Gradient background glow -->
                    <div class="pointer-events-none absolute inset-0 -z-10 rounded-3xl bg-gradient-to-r from-orange-500/20 via-amber-500/20 to-rose-500/20 blur-2xl" />

                    <TabsList class="relative inline-flex gap-2 rounded-2xl border border-white/40 bg-white/80 p-2 shadow-2xl backdrop-blur-2xl dark:border-white/20 dark:bg-slate-900/60">
                        <TabsTrigger
                            value="list"
                            class="group relative overflow-hidden rounded-xl px-6 py-3 font-semibold text-slate-600 transition-all duration-300 hover:text-slate-900 data-[state=active]:text-white dark:text-slate-300 dark:hover:text-white dark:data-[state=active]:text-white"
                        >
                            <!-- Active gradient background -->
                            <div class="absolute inset-0 bg-gradient-to-r from-orange-500 to-rose-500 opacity-0 shadow-lg transition-all duration-300 group-data-[state=active]:opacity-100 group-data-[state=active]:shadow-orange-500/50" />

                            <!-- Content -->
                            <span class="relative z-10 flex items-center gap-2">
                                <Users class="h-4 w-4" />
                                All Employees
                                <span class="ml-1 flex h-6 min-w-[24px] items-center justify-center rounded-full bg-slate-200 px-2 text-xs font-bold text-slate-700 transition-all group-data-[state=active]:bg-white/20 group-data-[state=active]:text-white dark:bg-slate-700 dark:text-slate-300 dark:group-data-[state=active]:bg-white/20">
                                    {{ employees.length }}
                                </span>
                            </span>
                        </TabsTrigger>

                        <TabsTrigger
                            value="add"
                            class="group relative overflow-hidden rounded-xl px-6 py-3 font-semibold text-slate-600 transition-all duration-300 hover:text-orange-700 data-[state=active]:text-white dark:text-slate-300 dark:hover:text-orange-400 dark:data-[state=active]:text-white"
                        >
                            <!-- Active gradient background -->
                            <div class="absolute inset-0 bg-gradient-to-r from-amber-500 to-orange-600 opacity-0 shadow-lg transition-all duration-300 group-data-[state=active]:opacity-100 group-data-[state=active]:shadow-amber-500/50" />

                            <!-- Content -->
                            <span class="relative z-10">Add Employee</span>
                        </TabsTrigger>

                        <TabsTrigger
                            value="import"
                            class="group relative overflow-hidden rounded-xl px-6 py-3 font-semibold text-slate-600 transition-all duration-300 hover:text-rose-700 data-[state=active]:text-white dark:text-slate-300 dark:hover:text-rose-400 dark:data-[state=active]:text-white"
                        >
                            <!-- Active gradient background -->
                            <div class="absolute inset-0 bg-gradient-to-r from-rose-500 to-pink-600 opacity-0 shadow-lg transition-all duration-300 group-data-[state=active]:opacity-100 group-data-[state=active]:shadow-rose-500/50" />

                            <!-- Content -->
                            <span class="relative z-10">Import CSV</span>
                        </TabsTrigger>
                    </TabsList>
                </div>

                <!-- Employee List Tab -->
                <TabsContent
                    value="list"
                    class="relative flex-1 overflow-hidden rounded-3xl border border-white/40 bg-white/70 shadow-2xl backdrop-blur-2xl dark:border-white/20 dark:bg-slate-900/40"
                >
                    <!-- Animated gradient overlay -->
                    <div class="pointer-events-none absolute inset-0 overflow-hidden opacity-30">
                        <div
                            class="absolute -top-1/4 -right-1/4 h-1/2 w-1/2 animate-pulse rounded-full bg-gradient-to-br from-orange-500/20 via-amber-500/20 to-rose-500/20 blur-3xl"
                            style="animation-duration: 8s"
                        />
                    </div>

                    <div class="relative p-8">
                        <EmployeeList :employees="employees" />
                    </div>
                </TabsContent>

                <!-- Add Single Employee Tab -->
                <TabsContent
                    value="add"
                    class="relative flex-1 overflow-hidden rounded-3xl border border-white/40 bg-white/70 shadow-2xl backdrop-blur-2xl dark:border-white/20 dark:bg-slate-900/40"
                >
                    <!-- Animated gradient overlay -->
                    <div class="pointer-events-none absolute inset-0 overflow-hidden opacity-30">
                        <div
                            class="absolute -top-1/4 -right-1/4 h-1/2 w-1/2 animate-pulse rounded-full bg-gradient-to-br from-amber-500/20 via-orange-500/20 to-yellow-500/20 blur-3xl"
                            style="animation-duration: 8s"
                        />
                    </div>

                    <div class="relative p-8">
                        <EmployeeForm @success="activeTab = 'list'" />
                    </div>
                </TabsContent>

                <!-- Import CSV Tab -->
                <TabsContent
                    value="import"
                    class="relative flex-1 overflow-hidden rounded-3xl border border-white/40 bg-white/70 shadow-2xl backdrop-blur-2xl dark:border-white/20 dark:bg-slate-900/40"
                >
                    <!-- Animated gradient overlay -->
                    <div class="pointer-events-none absolute inset-0 overflow-hidden opacity-30">
                        <div
                            class="absolute -top-1/4 -right-1/4 h-1/2 w-1/2 animate-pulse rounded-full bg-gradient-to-br from-rose-500/20 via-pink-500/20 to-red-500/20 blur-3xl"
                            style="animation-duration: 8s"
                        />
                    </div>

                    <div class="relative p-8">
                        <CsvUpload @success="activeTab = 'list'" />
                    </div>
                </TabsContent>
            </Tabs>
            </div>
        </div>
    </div>
</template>
