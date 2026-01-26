<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Users as UsersIcon, Search, ArrowLeft } from 'lucide-vue-next';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';

interface User {
    id: number;
    name: string;
    email: string;
    role: string;
    company_id: number;
    company_name: string | null;
    email_verified_at: string | null;
    created_at: string;
    created_at_human: string;
}

interface Props {
    users: {
        data: User[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    search?: string;
    role?: string;
}

const props = defineProps<Props>();

const searchQuery = ref(props.search || '');
const selectedRole = ref(props.role || '');

const performSearch = () => {
    router.get(route('admin.users.index'), {
        search: searchQuery.value,
        role: selectedRole.value,
    }, {
        preserveState: true,
    });
};

const getRoleBadgeColor = (role: string) => {
    switch (role) {
        case 'owner':
            return 'bg-purple-500/20 text-purple-400';
        case 'admin':
            return 'bg-blue-500/20 text-blue-400';
        case 'manager':
            return 'bg-emerald-500/20 text-emerald-400';
        default:
            return 'bg-slate-500/20 text-slate-400';
    }
};
</script>

<template>
    <Head title="Admin - Users" />

    <div class="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-slate-950">
        <!-- Header -->
        <div class="relative border-b border-white/10 bg-slate-900/50 backdrop-blur-xl">
            <div class="mx-auto max-w-7xl px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <Link :href="route('admin.dashboard')" class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-purple-600 shadow-lg">
                            <UsersIcon class="h-6 w-6 text-white" />
                        </Link>
                        <div>
                            <h1 class="text-2xl font-bold text-white">Users Management</h1>
                            <p class="text-sm text-slate-400">{{ users.total }} total users</p>
                        </div>
                    </div>

                    <Link :href="route('admin.dashboard')" class="text-slate-300 hover:text-white">
                        <ArrowLeft class="h-5 w-5" />
                    </Link>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="relative mx-auto max-w-7xl space-y-6 px-6 py-8">
            <!-- Search & Filters -->
            <div class="flex gap-4">
                <div class="relative flex-1">
                    <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                    <Input
                        v-model="searchQuery"
                        @keyup.enter="performSearch"
                        type="text"
                        placeholder="Search users by name, email, or company..."
                        class="border-slate-700 bg-slate-900/50 pl-10 text-white"
                    />
                </div>
                <select
                    v-model="selectedRole"
                    @change="performSearch"
                    class="rounded-md border border-slate-700 bg-slate-900/50 px-4 text-sm text-white"
                >
                    <option value="">All Roles</option>
                    <option value="owner">Owner</option>
                    <option value="admin">Admin</option>
                    <option value="manager">Manager</option>
                    <option value="employee">Employee</option>
                </select>
                <Button @click="performSearch" class="bg-blue-600 hover:bg-blue-700">
                    Search
                </Button>
            </div>

            <!-- Users Table -->
            <div class="rounded-2xl border border-white/10 bg-slate-900/50 backdrop-blur-xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="border-b border-white/10 bg-slate-950/50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-slate-400">User</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-slate-400">Role</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-slate-400">Company</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-slate-400">Verified</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-slate-400">Joined</th>
                                <th class="px-6 py-4 text-right text-xs font-semibold uppercase text-slate-400">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            <tr v-for="user in users.data" :key="user.id" class="hover:bg-slate-800/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div>
                                        <p class="font-medium text-white">{{ user.name }}</p>
                                        <p class="text-sm text-slate-400">{{ user.email }}</p>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span :class="['inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold capitalize', getRoleBadgeColor(user.role)]">
                                        {{ user.role }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-sm text-slate-300">{{ user.company_name || 'N/A' }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span v-if="user.email_verified_at" class="text-sm text-emerald-400">✓ Verified</span>
                                    <span v-else class="text-sm text-amber-400">Pending</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-sm text-slate-400">{{ user.created_at_human }}</span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <Link :href="route('admin.users.show', user.id)" class="text-sm text-blue-400 hover:text-blue-300">
                                        View Details
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="users.last_page > 1" class="flex items-center justify-between border-t border-white/10 px-6 py-4">
                    <p class="text-sm text-slate-400">
                        Page {{ users.current_page }} of {{ users.last_page }}
                    </p>
                    <div class="flex gap-2">
                        <Link
                            v-if="users.current_page > 1"
                            :href="route('admin.users.index', { search: searchQuery, role: selectedRole, page: users.current_page - 1 })"
                            class="rounded bg-slate-800 px-3 py-1 text-sm text-slate-300 hover:bg-slate-700"
                        >
                            Previous
                        </Link>
                        <Link
                            v-if="users.current_page < users.last_page"
                            :href="route('admin.users.index', { search: searchQuery, role: selectedRole, page: users.current_page + 1 })"
                            class="rounded bg-slate-800 px-3 py-1 text-sm text-slate-300 hover:bg-slate-700"
                        >
                            Next
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
