<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { FileText, Download, Trash2, Search, Filter } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

interface Log {
    id?: number;
    event?: string;
    description?: string;
    user_id?: number;
    user_name?: string | null;
    user_email?: string | null;
    company_id?: number;
    company_name?: string | null;
    ip_address?: string;
    user_agent?: string;
    properties?: any;
    created_at?: string;
    created_at_human?: string;
    timestamp?: string;
    level?: string;
    message?: string;
    stack?: string[];
}

interface Props {
    logs: {
        data?: Log[];
        logs?: Log[];
        message?: string;
        current_page?: number;
        last_page?: number;
        per_page?: number;
        total?: number;
    };
    log_type: string;
    search?: string;
}

const props = defineProps<Props>();

const searchQuery = ref(props.search || '');
const selectedType = ref(props.log_type);

const performSearch = () => {
    router.get(route('admin.logs.index'), {
        type: selectedType.value,
        search: searchQuery.value,
    }, {
        preserveState: true,
    });
};

const changeType = (type: string) => {
    selectedType.value = type;
    router.get(route('admin.logs.index'), {
        type: type,
        search: searchQuery.value,
    });
};

const downloadLogs = () => {
    window.location.href = route('admin.logs.download', { type: selectedType.value });
};

const clearLogs = () => {
    if (confirm('Are you sure you want to clear all logs? This action cannot be undone.')) {
        router.post(route('admin.logs.clear', { type: selectedType.value }));
    }
};

const getLevelColor = (level: string) => {
    const levelLower = level?.toLowerCase();
    if (levelLower === 'error' || levelLower === 'critical' || levelLower === 'emergency') {
        return 'text-red-400 bg-red-500/20';
    } else if (levelLower === 'warning' || levelLower === 'alert') {
        return 'text-amber-400 bg-amber-500/20';
    } else {
        return 'text-blue-400 bg-blue-500/20';
    }
};

const displayLogs = computed(() => {
    return props.logs.data || props.logs.logs || [];
});

const isAuditLog = computed(() => selectedType.value === 'audit');
</script>

<template>
    <Head title="Admin Logs" />

    <div class="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-slate-950">
        <!-- Background Pattern -->
        <div class="pointer-events-none fixed inset-0 overflow-hidden opacity-10">
            <div class="absolute -top-1/2 -left-1/2 h-full w-full animate-pulse rounded-full bg-gradient-to-br from-blue-500 via-purple-500 to-pink-500 blur-3xl" style="animation-duration: 15s" />
        </div>

        <!-- Header -->
        <div class="relative border-b border-white/10 bg-slate-900/50 backdrop-blur-xl">
            <div class="mx-auto max-w-7xl px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <Link :href="route('admin.dashboard')" class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-purple-600 shadow-lg shadow-blue-500/50">
                            <FileText class="h-6 w-6 text-white" />
                        </Link>
                        <div>
                            <h1 class="text-2xl font-bold text-white">System Logs</h1>
                            <p class="text-sm text-slate-400">View and manage application logs</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <Link :href="route('admin.dashboard')" class="text-sm text-slate-300 hover:text-white">Dashboard</Link>
                        <Link :href="route('admin.users.index')" class="text-sm text-slate-300 hover:text-white">Users</Link>
                        <Link :href="route('admin.companies.index')" class="text-sm text-slate-300 hover:text-white">Companies</Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="relative mx-auto max-w-7xl space-y-6 px-6 py-8">
            <!-- Controls -->
            <div class="flex flex-wrap items-center gap-4">
                <!-- Type Selector -->
                <div class="flex gap-2 rounded-lg border border-white/10 bg-slate-900/50 p-1">
                    <button
                        @click="changeType('audit')"
                        :class="[
                            'rounded px-4 py-2 text-sm font-medium transition-all',
                            selectedType === 'audit'
                                ? 'bg-blue-500 text-white shadow-lg shadow-blue-500/50'
                                : 'text-slate-400 hover:text-white'
                        ]"
                    >
                        Audit Logs
                    </button>
                    <button
                        @click="changeType('laravel')"
                        :class="[
                            'rounded px-4 py-2 text-sm font-medium transition-all',
                            selectedType === 'laravel'
                                ? 'bg-blue-500 text-white shadow-lg shadow-blue-500/50'
                                : 'text-slate-400 hover:text-white'
                        ]"
                    >
                        Laravel Logs
                    </button>
                    <button
                        @click="changeType('errors')"
                        :class="[
                            'rounded px-4 py-2 text-sm font-medium transition-all',
                            selectedType === 'errors'
                                ? 'bg-red-500 text-white shadow-lg shadow-red-500/50'
                                : 'text-slate-400 hover:text-white'
                        ]"
                    >
                        Errors Only
                    </button>
                </div>

                <!-- Search -->
                <div class="flex flex-1 items-center gap-2">
                    <div class="relative flex-1">
                        <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                        <Input
                            v-model="searchQuery"
                            @keyup.enter="performSearch"
                            type="text"
                            placeholder="Search logs..."
                            class="border-slate-700 bg-slate-900/50 pl-10 text-white placeholder:text-slate-500"
                        />
                    </div>
                    <Button @click="performSearch" variant="outline" class="border-slate-700 text-slate-300 hover:bg-slate-800 hover:text-white">
                        <Filter class="mr-2 h-4 w-4" />
                        Search
                    </Button>
                </div>

                <!-- Actions -->
                <div class="flex gap-2">
                    <Button @click="downloadLogs" variant="outline" size="sm" class="border-slate-700 text-slate-300 hover:bg-slate-800 hover:text-white">
                        <Download class="mr-2 h-4 w-4" />
                        Download
                    </Button>
                    <Button @click="clearLogs" variant="outline" size="sm" class="border-red-700 text-red-400 hover:bg-red-900/50 hover:text-red-300">
                        <Trash2 class="mr-2 h-4 w-4" />
                        Clear Logs
                    </Button>
                </div>
            </div>

            <!-- Logs Display -->
            <div class="rounded-2xl border border-white/10 bg-slate-900/50 backdrop-blur-xl">
                <!-- Audit Logs -->
                <div v-if="isAuditLog && displayLogs.length > 0" class="divide-y divide-white/5">
                    <div v-for="log in displayLogs" :key="log.id" class="p-4 hover:bg-slate-800/50 transition-colors">
                        <div class="flex items-start justify-between">
                            <div class="flex-1 space-y-2">
                                <div class="flex items-center gap-3">
                                    <span class="inline-flex items-center rounded-full bg-blue-500/20 px-3 py-1 text-xs font-semibold text-blue-400">
                                        {{ log.event }}
                                    </span>
                                    <span class="text-sm text-slate-400">{{ log.created_at_human }}</span>
                                </div>
                                <p class="text-sm text-white">{{ log.description }}</p>
                                <div class="flex flex-wrap items-center gap-4 text-xs text-slate-500">
                                    <span v-if="log.user_name">
                                        <strong class="text-slate-400">User:</strong> {{ log.user_name }} ({{ log.user_email }})
                                    </span>
                                    <span v-if="log.company_name">
                                        <strong class="text-slate-400">Company:</strong> {{ log.company_name }}
                                    </span>
                                    <span v-if="log.ip_address">
                                        <strong class="text-slate-400">IP:</strong> {{ log.ip_address }}
                                    </span>
                                </div>
                            </div>
                            <span class="text-xs text-slate-500">{{ log.created_at }}</span>
                        </div>
                    </div>
                </div>

                <!-- Laravel/Error Logs -->
                <div v-else-if="!isAuditLog && displayLogs.length > 0" class="divide-y divide-white/5">
                    <div v-for="(log, index) in displayLogs" :key="index" class="p-4 hover:bg-slate-800/50 transition-colors">
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <span :class="['inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold', getLevelColor(log.level || 'info')]">
                                        {{ log.level?.toUpperCase() }}
                                    </span>
                                    <span class="text-xs text-slate-400">{{ log.timestamp }}</span>
                                </div>
                            </div>
                            <p class="font-mono text-sm text-white">{{ log.message }}</p>
                            <div v-if="log.stack && log.stack.length > 0" class="mt-2 rounded-lg bg-slate-950/50 p-3">
                                <p class="mb-2 text-xs font-semibold text-slate-400">Stack Trace:</p>
                                <pre class="overflow-x-auto text-xs text-slate-500">{{ log.stack.join('\n') }}</pre>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else-if="props.logs.message" class="p-12 text-center">
                    <FileText class="mx-auto h-12 w-12 text-slate-600" />
                    <p class="mt-4 text-sm text-slate-400">{{ props.logs.message }}</p>
                </div>

                <div v-else class="p-12 text-center">
                    <FileText class="mx-auto h-12 w-12 text-slate-600" />
                    <p class="mt-4 text-sm text-slate-400">No logs found</p>
                </div>
            </div>

            <!-- Pagination (for audit logs) -->
            <div v-if="isAuditLog && props.logs.last_page && props.logs.last_page > 1" class="flex items-center justify-between rounded-lg border border-white/10 bg-slate-900/50 px-4 py-3">
                <p class="text-sm text-slate-400">
                    Page {{ props.logs.current_page }} of {{ props.logs.last_page }} ({{ props.logs.total }} total)
                </p>
                <div class="flex gap-2">
                    <Link
                        v-if="props.logs.current_page && props.logs.current_page > 1"
                        :href="route('admin.logs.index', { type: selectedType, search: searchQuery, page: props.logs.current_page - 1 })"
                        class="rounded bg-slate-800 px-3 py-1 text-sm text-slate-300 hover:bg-slate-700"
                    >
                        Previous
                    </Link>
                    <Link
                        v-if="props.logs.current_page && props.logs.current_page < (props.logs.last_page || 1)"
                        :href="route('admin.logs.index', { type: selectedType, search: searchQuery, page: props.logs.current_page + 1 })"
                        class="rounded bg-slate-800 px-3 py-1 text-sm text-slate-300 hover:bg-slate-700"
                    >
                        Next
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
