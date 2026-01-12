<script setup lang="ts">
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Users, Search } from 'lucide-vue-next';
import { Input } from '@/components/ui/input';
import { ref, computed } from 'vue';
import { UserRole } from '@/enums/UserRole';

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

const searchQuery = ref('');

const filteredEmployees = computed(() => {
    if (!searchQuery.value) return props.employees;

    const query = searchQuery.value.toLowerCase();
    return props.employees.filter(emp =>
        emp.name.toLowerCase().includes(query) ||
        emp.email.toLowerCase().includes(query) ||
        emp.role.toLowerCase().includes(query)
    );
});

import { UserRole } from '@/enums/UserRole';

const getRoleBadgeClass = (role: string) => {
    switch (role) {
        case UserRole.ADMIN:
            return 'bg-purple-100 text-purple-800 border-purple-200 dark:bg-purple-950/30 dark:text-purple-300 dark:border-purple-800/30';
        case UserRole.MANAGER:
            return 'bg-blue-100 text-blue-800 border-blue-200 dark:bg-blue-950/30 dark:text-blue-300 dark:border-blue-800/30';
        default:
            return 'bg-slate-100 text-slate-800 border-slate-200 dark:bg-slate-950/30 dark:text-slate-300 dark:border-slate-800/30';
    }
};

const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    }).format(date);
};

const capitalizeFirst = (str: string) => {
    return str.charAt(0).toUpperCase() + str.slice(1);
};
</script>

<template>
    <div class="space-y-6">
        <Card class="border-slate-200 bg-white dark:border-white/10 dark:bg-white/5">
            <CardHeader>
                <div class="flex items-start justify-between">
                    <div>
                        <CardTitle class="flex items-center gap-2 text-slate-900 dark:text-white">
                            <Users class="h-5 w-5 text-purple-500 dark:text-purple-400" />
                            All Employees
                        </CardTitle>
                        <CardDescription class="text-slate-600 dark:text-white/60">
                            {{ employees.length }} total employees in your organization
                        </CardDescription>
                    </div>

                    <!-- Search Input -->
                    <div class="relative w-64">
                        <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400 dark:text-white/40" />
                        <Input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search employees..."
                            class="border-slate-300 bg-white pl-9 focus:border-purple-500 focus:ring-purple-500 dark:border-white/20 dark:bg-white/5"
                        />
                    </div>
                </div>
            </CardHeader>
            <CardContent>
                <!-- Employees Table -->
                <div class="rounded-lg border border-slate-200 dark:border-white/10">
                    <Table>
                        <TableHeader>
                            <TableRow class="border-slate-200 hover:bg-transparent dark:border-white/10">
                                <TableHead class="text-slate-700 dark:text-white/70">Name</TableHead>
                                <TableHead class="text-slate-700 dark:text-white/70">Email</TableHead>
                                <TableHead class="text-slate-700 dark:text-white/70">Role</TableHead>
                                <TableHead class="text-slate-700 dark:text-white/70">Joined</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <!-- Empty State -->
                            <TableRow v-if="filteredEmployees.length === 0" class="hover:bg-transparent">
                                <TableCell colspan="4" class="py-12 text-center">
                                    <div class="flex flex-col items-center gap-2">
                                        <Users class="h-12 w-12 text-slate-400 dark:text-white/40" />
                                        <p class="text-sm font-medium text-slate-600 dark:text-white/60">
                                            {{ searchQuery ? 'No employees found matching your search' : 'No employees yet' }}
                                        </p>
                                    </div>
                                </TableCell>
                            </TableRow>

                            <!-- Employee Rows -->
                            <TableRow
                                v-for="employee in filteredEmployees"
                                :key="employee.id"
                                class="border-slate-200 transition-colors hover:bg-slate-50 dark:border-white/10 dark:hover:bg-white/5"
                            >
                                <TableCell class="font-medium text-slate-900 dark:text-white">
                                    {{ employee.name }}
                                </TableCell>
                                <TableCell class="text-slate-600 dark:text-white/70">
                                    {{ employee.email }}
                                </TableCell>
                                <TableCell>
                                    <Badge variant="outline" :class="getRoleBadgeClass(employee.role)">
                                        {{ capitalizeFirst(employee.role) }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="text-slate-600 dark:text-white/70">
                                    {{ formatDate(employee.created_at) }}
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </CardContent>
        </Card>

        <!-- Stats Cards -->
        <div class="grid gap-4 md:grid-cols-3">
            <Card class="border-slate-200 bg-white dark:border-white/10 dark:bg-white/5">
                <CardContent class="pt-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-600 dark:text-white/60">Total Employees</p>
                            <p class="text-3xl font-bold text-slate-900 dark:text-white">
                                {{ employees.length }}
                            </p>
                        </div>
                        <Users class="h-8 w-8 text-slate-400 dark:text-white/40" />
                    </div>
                </CardContent>
            </Card>

            <Card class="border-slate-200 bg-white dark:border-white/10 dark:bg-white/5">
                <CardContent class="pt-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-600 dark:text-white/60">Admins</p>
                            <p class="text-3xl font-bold text-purple-600 dark:text-purple-400">
                                {{ employees.filter(e => e.role === UserRole.ADMIN).length }}
                            </p>
                        </div>
                        <div class="rounded-lg bg-purple-100 p-2 dark:bg-purple-950/30">
                            <Users class="h-6 w-6 text-purple-600 dark:text-purple-400" />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card class="border-slate-200 bg-white dark:border-white/10 dark:bg-white/5">
                <CardContent class="pt-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-600 dark:text-white/60">Managers</p>
                            <p class="text-3xl font-bold text-blue-600 dark:text-blue-400">
                                {{ employees.filter(e => e.role === UserRole.MANAGER).length }}
                            </p>
                        </div>
                        <div class="rounded-lg bg-blue-100 p-2 dark:bg-blue-950/30">
                            <Users class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
