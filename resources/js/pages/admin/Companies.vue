<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Building2, Search, ArrowLeft } from 'lucide-vue-next';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';

interface Company {
    id: number;
    name: string;
    users_count: number;
    user_limit: number;
    subscription: {
        name: string;
        price: string;
    } | null;
    created_at: string;
    created_at_human: string;
}

interface Props {
    companies: {
        data: Company[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    search?: string;
}

const props = defineProps<Props>();

const searchQuery = ref(props.search || '');

const performSearch = () => {
    router.get(route('admin.companies.index'), {
        search: searchQuery.value,
    }, {
        preserveState: true,
    });
};

const getUsageColor = (company: Company) => {
    const percentage = (company.users_count / company.user_limit) * 100;
    if (percentage >= 100) return 'text-red-400';
    if (percentage >= 80) return 'text-amber-400';
    return 'text-emerald-400';
};
</script>

<template>
    <Head title="Admin - Companies" />

    <div class="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-slate-950">
        <!-- Header -->
        <div class="relative border-b border-white/10 bg-slate-900/50 backdrop-blur-xl">
            <div class="mx-auto max-w-7xl px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <Link :href="route('admin.dashboard')" class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-purple-500 to-pink-600 shadow-lg">
                            <Building2 class="h-6 w-6 text-white" />
                        </Link>
                        <div>
                            <h1 class="text-2xl font-bold text-white">Companies Management</h1>
                            <p class="text-sm text-slate-400">{{ companies.total }} total companies</p>
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
            <!-- Search -->
            <div class="flex gap-4">
                <div class="relative flex-1">
                    <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                    <Input
                        v-model="searchQuery"
                        @keyup.enter="performSearch"
                        type="text"
                        placeholder="Search companies by name..."
                        class="border-slate-700 bg-slate-900/50 pl-10 text-white"
                    />
                </div>
                <Button @click="performSearch" class="bg-purple-600 hover:bg-purple-700">
                    Search
                </Button>
            </div>

            <!-- Companies Table -->
            <div class="rounded-2xl border border-white/10 bg-slate-900/50 backdrop-blur-xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="border-b border-white/10 bg-slate-950/50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-slate-400">Company</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-slate-400">Users</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-slate-400">Subscription</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-slate-400">Created</th>
                                <th class="px-6 py-4 text-right text-xs font-semibold uppercase text-slate-400">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            <tr v-for="company in companies.data" :key="company.id" class="hover:bg-slate-800/50 transition-colors">
                                <td class="px-6 py-4">
                                    <p class="font-medium text-white">{{ company.name }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <div>
                                        <p :class="['font-medium', getUsageColor(company)]">
                                            {{ company.users_count }} / {{ company.user_limit }}
                                        </p>
                                        <p class="text-xs text-slate-500">
                                            {{ Math.round((company.users_count / company.user_limit) * 100) }}% used
                                        </p>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div v-if="company.subscription">
                                        <p class="font-medium text-white">{{ company.subscription.name }}</p>
                                        <p class="text-sm text-slate-400">{{ company.subscription.price }}</p>
                                    </div>
                                    <span v-else class="text-sm text-slate-500">No subscription</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-sm text-slate-400">{{ company.created_at_human }}</span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <Link :href="route('admin.companies.show', company.id)" class="text-sm text-purple-400 hover:text-purple-300">
                                        View Details
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="companies.last_page > 1" class="flex items-center justify-between border-t border-white/10 px-6 py-4">
                    <p class="text-sm text-slate-400">
                        Page {{ companies.current_page }} of {{ companies.last_page }}
                    </p>
                    <div class="flex gap-2">
                        <Link
                            v-if="companies.current_page > 1"
                            :href="route('admin.companies.index', { search: searchQuery, page: companies.current_page - 1 })"
                            class="rounded bg-slate-800 px-3 py-1 text-sm text-slate-300 hover:bg-slate-700"
                        >
                            Previous
                        </Link>
                        <Link
                            v-if="companies.current_page < companies.last_page"
                            :href="route('admin.companies.index', { search: searchQuery, page: companies.current_page + 1 })"
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
