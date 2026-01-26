<script setup lang="ts">
import RequestsTable from '@/components/RequestsTable.vue';
import RequestActionModal from '@/components/RequestActionModal.vue';
import PremiumSidebar from '@/components/PremiumSidebar.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { requests as requestsRoute } from '@/routes';

const page = usePage();
import { FileText, Clock, CheckCircle2, XCircle, Plus } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { UserRole } from '@/enums/UserRole';
import { VacationRequestStatus } from '@/enums/VacationRequestStatus';
import { VacationRequestType } from '@/enums/VacationRequestType';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

interface VacationRequest {
    id: string;
    startDate: Date;
    endDate: Date;
    type: VacationRequestType;
    status: VacationRequestStatus;
    reason?: string;
    rejectionReason?: string;
    employeeName?: string;
    department?: string;
}

// Define props received from Inertia controller
interface Props {
    requests: {
        data: VacationRequest[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    counts: {
        all: number;
        pending: number;
        approved: number;
        rejected: number;
    };
    currentStatus: string;
    userRole: UserRole;
}

const props = defineProps<Props>();

// Convert props to refs for reactivity (dates need to be converted)
const requests = computed<VacationRequest[]>(() =>
    props.requests.data.map((req) => ({
        ...req,
        startDate: new Date(req.startDate),
        endDate: new Date(req.endDate),
    })),
);

const userRole = ref<UserRole>(props.userRole as UserRole);

// Modal state
const showActionModal = ref(false);
const modalAction = ref<'approve' | 'reject' | null>(null);
const selectedRequestId = ref<string | null>(null);
const selectedEmployeeName = ref<string>('');

// Active tab is controlled by the currentStatus prop from backend
const activeTab = ref(props.currentStatus);

// Navigate to a different tab
const navigateToTab = (status: string) => {
    const query = status === 'all' ? {} : { status };
    router.get(requestsRoute.url({ query }), {}, {
        preserveState: false,
        preserveScroll: false,
    });
};

const handleApprove = (id: string, employeeName: string) => {
    selectedRequestId.value = id;
    selectedEmployeeName.value = employeeName;
    modalAction.value = 'approve';
    showActionModal.value = true;
};

const handleReject = (id: string, employeeName: string) => {
    selectedRequestId.value = id;
    selectedEmployeeName.value = employeeName;
    modalAction.value = 'reject';
    showActionModal.value = true;
};

const closeModal = () => {
    showActionModal.value = false;
    setTimeout(() => {
        modalAction.value = null;
        selectedRequestId.value = null;
        selectedEmployeeName.value = '';
    }, 300);
};
</script>

<template>
    <Head :title="t('requests.title')" />

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
            <!-- Enhanced Header with Action Button -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-orange-400 to-rose-500 shadow-2xl shadow-orange-500/30">
                        <FileText class="h-8 w-8 text-white" />
                    </div>
                    <div>
                        <h1 class="text-4xl font-bold tracking-tight text-slate-900 dark:text-white">
                            {{ t('requests.title') }}
                        </h1>
                        <p class="mt-1.5 text-sm text-slate-600 dark:text-white/70">
                            {{ userRole === UserRole.EMPLOYEE ? t('requests.subtitle.employee') : t('requests.subtitle.manager') }}
                        </p>
                    </div>
                </div>

                <!-- New Request Button (for employees) -->
                <Button
                    v-if="userRole === UserRole.EMPLOYEE"
                    class="bg-gradient-to-r from-orange-500 to-rose-500 text-white hover:from-orange-600 hover:to-rose-600"
                >
                    <Plus class="mr-2 h-4 w-4" />
                    {{ t('vacation.request.title') }}
                </Button>
            </div>

            <!-- Stats Cards -->
            <div class="grid gap-4 md:grid-cols-4">
                <Card class="border-slate-200 bg-white/80 backdrop-blur-sm hover:bg-white dark:border-white/10 dark:bg-white/5 dark:hover:bg-white/10 transition-all">
                    <CardContent class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-slate-600 dark:text-white/60">{{ t('requests.total') }}</p>
                                <p class="text-3xl font-bold text-slate-900 dark:text-white">{{ counts.all }}</p>
                            </div>
                            <FileText class="h-8 w-8 text-slate-400 dark:text-white/40" />
                        </div>
                    </CardContent>
                </Card>

                <Card class="border-amber-200 bg-white/80 backdrop-blur-sm hover:bg-white dark:border-amber-500/20 dark:bg-white/5 dark:hover:bg-white/10 transition-all">
                    <CardContent class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-amber-600 dark:text-amber-400">{{ t('requests.pending') }}</p>
                                <p class="text-3xl font-bold text-amber-700 dark:text-amber-300">{{ counts.pending }}</p>
                            </div>
                            <Clock class="h-8 w-8 text-amber-400" />
                        </div>
                    </CardContent>
                </Card>

                <Card class="border-emerald-200 bg-white/80 backdrop-blur-sm hover:bg-white dark:border-emerald-500/20 dark:bg-white/5 dark:hover:bg-white/10 transition-all">
                    <CardContent class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-emerald-600 dark:text-emerald-400">{{ t('requests.approved') }}</p>
                                <p class="text-3xl font-bold text-emerald-700 dark:text-emerald-300">{{ counts.approved }}</p>
                            </div>
                            <CheckCircle2 class="h-8 w-8 text-emerald-400" />
                        </div>
                    </CardContent>
                </Card>

                <Card class="border-rose-200 bg-white/80 backdrop-blur-sm hover:bg-white dark:border-rose-500/20 dark:bg-white/5 dark:hover:bg-white/10 transition-all">
                    <CardContent class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-rose-600 dark:text-rose-400">{{ t('requests.rejected') }}</p>
                                <p class="text-3xl font-bold text-rose-700 dark:text-rose-300">{{ counts.rejected }}</p>
                            </div>
                            <XCircle class="h-8 w-8 text-rose-400" />
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Premium Tabs for filtering -->
            <Tabs v-model="activeTab" class="flex flex-1 flex-col space-y-6">
                <!-- Enhanced Tab List -->
                <div class="relative">
                    <!-- Gradient background glow -->
                    <div class="pointer-events-none absolute inset-0 -z-10 rounded-3xl bg-gradient-to-r from-orange-500/20 via-amber-500/20 to-rose-500/20 blur-2xl" />

                    <TabsList class="relative inline-flex gap-2 rounded-2xl border border-white/40 bg-white/80 p-2 shadow-2xl backdrop-blur-2xl dark:border-white/20 dark:bg-slate-900/60">
                        <TabsTrigger
                            value="all"
                            class="group relative overflow-hidden rounded-xl px-6 py-3 font-semibold text-slate-600 transition-all duration-300 hover:text-slate-900 data-[state=active]:text-white dark:text-slate-300 dark:hover:text-white dark:data-[state=active]:text-white"
                            @click="navigateToTab('all')"
                        >
                            <!-- Active gradient background -->
                            <div class="absolute inset-0 bg-gradient-to-r from-slate-600 to-slate-800 opacity-0 transition-opacity duration-300 data-[state=active]:opacity-100 group-data-[state=active]:opacity-100" />

                            <!-- Content -->
                            <span class="relative z-10 flex items-center gap-2">
                                <FileText class="h-4 w-4" />
                                {{ t('requests.all') }}
                                <span class="ml-1 flex h-6 min-w-[24px] items-center justify-center rounded-full bg-slate-200 px-2 text-xs font-bold text-slate-700 transition-all group-data-[state=active]:bg-white/20 group-data-[state=active]:text-white dark:bg-slate-700 dark:text-slate-300 dark:group-data-[state=active]:bg-white/20">
                                    {{ counts.all }}
                                </span>
                            </span>
                        </TabsTrigger>

                        <TabsTrigger
                            value="pending"
                            class="group relative overflow-hidden rounded-xl px-6 py-3 font-semibold text-slate-600 transition-all duration-300 hover:text-amber-700 data-[state=active]:text-white dark:text-slate-300 dark:hover:text-amber-400 dark:data-[state=active]:text-white"
                            @click="navigateToTab('pending')"
                        >
                            <!-- Active gradient background -->
                            <div class="absolute inset-0 bg-gradient-to-r from-amber-500 to-orange-600 opacity-0 shadow-lg transition-all duration-300 group-data-[state=active]:opacity-100 group-data-[state=active]:shadow-amber-500/50" />

                            <!-- Content -->
                            <span class="relative z-10 flex items-center gap-2">
                                <Clock class="h-4 w-4" />
                                {{ t('requests.pending') }}
                                <span
                                    v-if="counts.pending > 0"
                                    class="ml-1 flex h-6 min-w-[24px] animate-pulse items-center justify-center rounded-full bg-amber-100 px-2 text-xs font-bold text-amber-700 transition-all group-data-[state=active]:bg-white/20 group-data-[state=active]:text-white dark:bg-amber-900/50 dark:text-amber-300 dark:group-data-[state=active]:bg-white/20"
                                    style="animation-duration: 2s"
                                >
                                    {{ counts.pending }}
                                </span>
                            </span>
                        </TabsTrigger>

                        <TabsTrigger
                            value="approved"
                            class="group relative overflow-hidden rounded-xl px-6 py-3 font-semibold text-slate-600 transition-all duration-300 hover:text-emerald-700 data-[state=active]:text-white dark:text-slate-300 dark:hover:text-emerald-400 dark:data-[state=active]:text-white"
                            @click="navigateToTab('approved')"
                        >
                            <!-- Active gradient background -->
                            <div class="absolute inset-0 bg-gradient-to-r from-emerald-500 to-teal-600 opacity-0 shadow-lg transition-all duration-300 group-data-[state=active]:opacity-100 group-data-[state=active]:shadow-emerald-500/50" />

                            <!-- Content -->
                            <span class="relative z-10 flex items-center gap-2">
                                <CheckCircle2 class="h-4 w-4" />
                                {{ t('requests.approved') }}
                                <span class="ml-1 flex h-6 min-w-[24px] items-center justify-center rounded-full bg-emerald-100 px-2 text-xs font-bold text-emerald-700 transition-all group-data-[state=active]:bg-white/20 group-data-[state=active]:text-white dark:bg-emerald-900/50 dark:text-emerald-300 dark:group-data-[state=active]:bg-white/20">
                                    {{ counts.approved }}
                                </span>
                            </span>
                        </TabsTrigger>

                        <TabsTrigger
                            value="rejected"
                            class="group relative overflow-hidden rounded-xl px-6 py-3 font-semibold text-slate-600 transition-all duration-300 hover:text-rose-700 data-[state=active]:text-white dark:text-slate-300 dark:hover:text-rose-400 dark:data-[state=active]:text-white"
                            @click="navigateToTab('rejected')"
                        >
                            <!-- Active gradient background -->
                            <div class="absolute inset-0 bg-gradient-to-r from-rose-500 to-red-600 opacity-0 shadow-lg transition-all duration-300 group-data-[state=active]:opacity-100 group-data-[state=active]:shadow-rose-500/50" />

                            <!-- Content -->
                            <span class="relative z-10 flex items-center gap-2">
                                <XCircle class="h-4 w-4" />
                                {{ t('requests.rejected') }}
                                <span class="ml-1 flex h-6 min-w-[24px] items-center justify-center rounded-full bg-rose-100 px-2 text-xs font-bold text-rose-700 transition-all group-data-[state=active]:bg-white/20 group-data-[state=active]:text-white dark:bg-rose-900/50 dark:text-rose-300 dark:group-data-[state=active]:bg-white/20">
                                    {{ counts.rejected }}
                                </span>
                            </span>
                        </TabsTrigger>
                    </TabsList>
                </div>

                <!-- All Tabs Content - Same design for all -->
                <TabsContent
                    value="all"
                    class="relative flex-1 space-y-4 overflow-hidden rounded-3xl border border-white/40 bg-white/70 shadow-2xl backdrop-blur-2xl dark:border-white/20 dark:bg-slate-900/40"
                >
                    <!-- Animated gradient overlay -->
                    <div class="pointer-events-none absolute inset-0 overflow-hidden opacity-30">
                        <div
                            class="absolute -top-1/4 -right-1/4 h-1/2 w-1/2 animate-pulse rounded-full bg-gradient-to-br from-orange-500/20 via-amber-500/20 to-rose-500/20 blur-3xl"
                            style="animation-duration: 8s"
                        />
                    </div>

                    <div class="relative p-8">
                        <RequestsTable
                            :requests="requests"
                            :user-role="userRole"
                            @approve="handleApprove"
                            @reject="handleReject"
                        />
                    </div>

                    <!-- Pagination -->
                    <div
                        v-if="props.requests.last_page > 1"
                        class="relative flex items-center justify-between border-t border-white/20 px-8 py-4"
                    >
                        <p class="text-sm font-medium text-slate-600 dark:text-slate-300">
                            Page {{ props.requests.current_page }} of {{ props.requests.last_page }}
                            <span class="ml-2 text-slate-500 dark:text-slate-400">({{ props.requests.total }} total)</span>
                        </p>
                        <div class="flex gap-2">
                            <Link
                                v-if="props.requests.current_page > 1"
                                :href="requestsRoute.url({ query: currentStatus === 'all' ? { page: props.requests.current_page - 1 } : { status: currentStatus, page: props.requests.current_page - 1 } })"
                                class="rounded-xl bg-gradient-to-r from-slate-600 to-slate-700 px-4 py-2 text-sm font-semibold text-white transition-all hover:from-slate-700 hover:to-slate-800 hover:shadow-lg"
                            >
                                Previous
                            </Link>
                            <Link
                                v-if="props.requests.current_page < props.requests.last_page"
                                :href="requestsRoute.url({ query: currentStatus === 'all' ? { page: props.requests.current_page + 1 } : { status: currentStatus, page: props.requests.current_page + 1 } })"
                                class="rounded-xl bg-gradient-to-r from-orange-500 to-rose-500 px-4 py-2 text-sm font-semibold text-white transition-all hover:from-orange-600 hover:to-rose-600 hover:shadow-lg"
                            >
                                Next
                            </Link>
                        </div>
                    </div>
                </TabsContent>

                <TabsContent
                    value="pending"
                    class="relative flex-1 space-y-4 overflow-hidden rounded-3xl border border-white/40 bg-white/70 shadow-2xl backdrop-blur-2xl dark:border-white/20 dark:bg-slate-900/40"
                >
                    <!-- Animated gradient overlay -->
                    <div class="pointer-events-none absolute inset-0 overflow-hidden opacity-30">
                        <div
                            class="absolute -top-1/4 -right-1/4 h-1/2 w-1/2 animate-pulse rounded-full bg-gradient-to-br from-amber-500/20 via-orange-500/20 to-yellow-500/20 blur-3xl"
                            style="animation-duration: 8s"
                        />
                    </div>

                    <div class="relative p-8">
                        <RequestsTable
                            :requests="requests"
                            :user-role="userRole"
                            @approve="handleApprove"
                            @reject="handleReject"
                        />
                    </div>

                    <!-- Pagination -->
                    <div
                        v-if="props.requests.last_page > 1"
                        class="relative flex items-center justify-between border-t border-white/20 px-8 py-4"
                    >
                        <p class="text-sm font-medium text-slate-600 dark:text-slate-300">
                            Page {{ props.requests.current_page }} of {{ props.requests.last_page }}
                            <span class="ml-2 text-slate-500 dark:text-slate-400">({{ props.requests.total }} total)</span>
                        </p>
                        <div class="flex gap-2">
                            <Link
                                v-if="props.requests.current_page > 1"
                                :href="requestsRoute.url({ query: currentStatus === 'all' ? { page: props.requests.current_page - 1 } : { status: currentStatus, page: props.requests.current_page - 1 } })"
                                class="rounded-xl bg-gradient-to-r from-slate-600 to-slate-700 px-4 py-2 text-sm font-semibold text-white transition-all hover:from-slate-700 hover:to-slate-800 hover:shadow-lg"
                            >
                                Previous
                            </Link>
                            <Link
                                v-if="props.requests.current_page < props.requests.last_page"
                                :href="requestsRoute.url({ query: currentStatus === 'all' ? { page: props.requests.current_page + 1 } : { status: currentStatus, page: props.requests.current_page + 1 } })"
                                class="rounded-xl bg-gradient-to-r from-orange-500 to-rose-500 px-4 py-2 text-sm font-semibold text-white transition-all hover:from-orange-600 hover:to-rose-600 hover:shadow-lg"
                            >
                                Next
                            </Link>
                        </div>
                    </div>
                </TabsContent>

                <TabsContent
                    value="approved"
                    class="relative flex-1 space-y-4 overflow-hidden rounded-3xl border border-white/40 bg-white/70 shadow-2xl backdrop-blur-2xl dark:border-white/20 dark:bg-slate-900/40"
                >
                    <!-- Animated gradient overlay -->
                    <div class="pointer-events-none absolute inset-0 overflow-hidden opacity-30">
                        <div
                            class="absolute -top-1/4 -right-1/4 h-1/2 w-1/2 animate-pulse rounded-full bg-gradient-to-br from-emerald-500/20 via-teal-500/20 to-green-500/20 blur-3xl"
                            style="animation-duration: 8s"
                        />
                    </div>

                    <div class="relative p-8">
                        <RequestsTable
                            :requests="requests"
                            :user-role="userRole"
                            @approve="handleApprove"
                            @reject="handleReject"
                        />
                    </div>

                    <!-- Pagination -->
                    <div
                        v-if="props.requests.last_page > 1"
                        class="relative flex items-center justify-between border-t border-white/20 px-8 py-4"
                    >
                        <p class="text-sm font-medium text-slate-600 dark:text-slate-300">
                            Page {{ props.requests.current_page }} of {{ props.requests.last_page }}
                            <span class="ml-2 text-slate-500 dark:text-slate-400">({{ props.requests.total }} total)</span>
                        </p>
                        <div class="flex gap-2">
                            <Link
                                v-if="props.requests.current_page > 1"
                                :href="requestsRoute.url({ query: currentStatus === 'all' ? { page: props.requests.current_page - 1 } : { status: currentStatus, page: props.requests.current_page - 1 } })"
                                class="rounded-xl bg-gradient-to-r from-slate-600 to-slate-700 px-4 py-2 text-sm font-semibold text-white transition-all hover:from-slate-700 hover:to-slate-800 hover:shadow-lg"
                            >
                                Previous
                            </Link>
                            <Link
                                v-if="props.requests.current_page < props.requests.last_page"
                                :href="requestsRoute.url({ query: currentStatus === 'all' ? { page: props.requests.current_page + 1 } : { status: currentStatus, page: props.requests.current_page + 1 } })"
                                class="rounded-xl bg-gradient-to-r from-orange-500 to-rose-500 px-4 py-2 text-sm font-semibold text-white transition-all hover:from-orange-600 hover:to-rose-600 hover:shadow-lg"
                            >
                                Next
                            </Link>
                        </div>
                    </div>
                </TabsContent>

                <TabsContent
                    value="rejected"
                    class="relative flex-1 space-y-4 overflow-hidden rounded-3xl border border-white/40 bg-white/70 shadow-2xl backdrop-blur-2xl dark:border-white/20 dark:bg-slate-900/40"
                >
                    <!-- Animated gradient overlay -->
                    <div class="pointer-events-none absolute inset-0 overflow-hidden opacity-30">
                        <div
                            class="absolute -top-1/4 -right-1/4 h-1/2 w-1/2 animate-pulse rounded-full bg-gradient-to-br from-rose-500/20 via-red-500/20 to-pink-500/20 blur-3xl"
                            style="animation-duration: 8s"
                        />
                    </div>

                    <div class="relative p-8">
                        <RequestsTable
                            :requests="requests"
                            :user-role="userRole"
                            @approve="handleApprove"
                            @reject="handleReject"
                        />
                    </div>

                    <!-- Pagination -->
                    <div
                        v-if="props.requests.last_page > 1"
                        class="relative flex items-center justify-between border-t border-white/20 px-8 py-4"
                    >
                        <p class="text-sm font-medium text-slate-600 dark:text-slate-300">
                            Page {{ props.requests.current_page }} of {{ props.requests.last_page }}
                            <span class="ml-2 text-slate-500 dark:text-slate-400">({{ props.requests.total }} total)</span>
                        </p>
                        <div class="flex gap-2">
                            <Link
                                v-if="props.requests.current_page > 1"
                                :href="requestsRoute.url({ query: currentStatus === 'all' ? { page: props.requests.current_page - 1 } : { status: currentStatus, page: props.requests.current_page - 1 } })"
                                class="rounded-xl bg-gradient-to-r from-slate-600 to-slate-700 px-4 py-2 text-sm font-semibold text-white transition-all hover:from-slate-700 hover:to-slate-800 hover:shadow-lg"
                            >
                                Previous
                            </Link>
                            <Link
                                v-if="props.requests.current_page < props.requests.last_page"
                                :href="requestsRoute.url({ query: currentStatus === 'all' ? { page: props.requests.current_page + 1 } : { status: currentStatus, page: props.requests.current_page + 1 } })"
                                class="rounded-xl bg-gradient-to-r from-orange-500 to-rose-500 px-4 py-2 text-sm font-semibold text-white transition-all hover:from-orange-600 hover:to-rose-600 hover:shadow-lg"
                            >
                                Next
                            </Link>
                        </div>
                    </div>
                </TabsContent>
            </Tabs>
            </div>
        </div>

        <!-- Action Modal -->
        <RequestActionModal
            :show="showActionModal"
            :action="modalAction"
            :request-id="selectedRequestId"
            :employee-name="selectedEmployeeName"
            @close="closeModal"
        />
    </div>
</template>
