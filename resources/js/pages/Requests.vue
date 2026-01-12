<script setup lang="ts">
import RequestsTable from '@/components/RequestsTable.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { FileText, Clock, CheckCircle2, XCircle, Plus } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { UserRole } from '@/enums/UserRole';
import { VacationRequestStatus } from '@/enums/VacationRequestStatus';
import { VacationRequestType } from '@/enums/VacationRequestType';

interface VacationRequest {
    id: string;
    startDate: Date;
    endDate: Date;
    type: VacationRequestType;
    status: VacationRequestStatus;
    reason?: string;
    employeeName?: string;
    department?: string;
}

// Define props received from Inertia controller
interface Props {
    requests: VacationRequest[];
    userRole: UserRole;
}

const props = defineProps<Props>();

// Convert props to refs for reactivity (dates need to be converted)
const requests = ref<VacationRequest[]>(
    props.requests.map((req) => ({
        ...req,
        startDate: new Date(req.startDate),
        endDate: new Date(req.endDate),
    })),
);

const userRole = ref<UserRole>(props.userRole as UserRole);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Requests', href: '/requests' },
];

const activeTab = ref('all');

// Filter requests based on role
const visibleRequests = computed(() => {
    return userRole.value === UserRole.EMPLOYEE
        ? requests.value.filter((r) => r.employeeName === 'John Doe')
        : requests.value;
});

// Filter by status
const pendingRequests = computed(() =>
    visibleRequests.value.filter((r) => r.status === VacationRequestStatus.PENDING)
);

const approvedRequests = computed(() =>
    visibleRequests.value.filter((r) => r.status === VacationRequestStatus.APPROVED)
);

const rejectedRequests = computed(() =>
    visibleRequests.value.filter((r) => r.status === VacationRequestStatus.REJECTED)
);

// Get filtered requests based on active tab
const filteredRequests = computed(() => {
    switch (activeTab.value) {
        case 'pending':
            return pendingRequests.value;
        case 'approved':
            return approvedRequests.value;
        case 'rejected':
            return rejectedRequests.value;
        default:
            return visibleRequests.value;
    }
});

const handleApprove = (id: string) => {
    requests.value = requests.value.map((req) =>
        req.id === id ? { ...req, status: VacationRequestStatus.APPROVED } : req,
    );
};

const handleReject = (id: string) => {
    requests.value = requests.value.map((req) =>
        req.id === id ? { ...req, status: VacationRequestStatus.REJECTED } : req,
    );
};
</script>

<template>
    <Head title="Requests" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <!-- Gradient background - adapts to theme -->
        <div class="absolute inset-0 -z-10 bg-gradient-to-br from-slate-50 via-orange-50 to-rose-50 dark:from-slate-950 dark:via-orange-950 dark:to-rose-950" />

        <!-- Animated gradient orbs -->
        <div class="pointer-events-none absolute inset-0 -z-10 overflow-hidden">
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
        <div class="relative flex h-full flex-1 flex-col gap-6 overflow-hidden p-6">
            <!-- Enhanced Header with Action Button -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-orange-400 to-rose-500 shadow-2xl shadow-orange-500/30">
                        <FileText class="h-8 w-8 text-white" />
                    </div>
                    <div>
                        <h1 class="text-4xl font-bold tracking-tight text-slate-900 dark:text-white">
                            Time Off Requests
                        </h1>
                        <p class="mt-1.5 text-sm text-slate-600 dark:text-white/70">
                            {{ userRole === UserRole.EMPLOYEE ? 'View and manage your time off requests' : 'Review and approve team requests' }}
                        </p>
                    </div>
                </div>

                <!-- New Request Button (for employees) -->
                <Button
                    v-if="userRole === UserRole.EMPLOYEE"
                    class="bg-gradient-to-r from-orange-500 to-rose-500 text-white hover:from-orange-600 hover:to-rose-600"
                >
                    <Plus class="mr-2 h-4 w-4" />
                    New Request
                </Button>
            </div>

            <!-- Stats Cards -->
            <div class="grid gap-4 md:grid-cols-4">
                <Card class="border-slate-200 bg-white/80 backdrop-blur-sm hover:bg-white dark:border-white/10 dark:bg-white/5 dark:hover:bg-white/10 transition-all">
                    <CardContent class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-slate-600 dark:text-white/60">Total</p>
                                <p class="text-3xl font-bold text-slate-900 dark:text-white">{{ visibleRequests.length }}</p>
                            </div>
                            <FileText class="h-8 w-8 text-slate-400 dark:text-white/40" />
                        </div>
                    </CardContent>
                </Card>

                <Card class="border-amber-200 bg-white/80 backdrop-blur-sm hover:bg-white dark:border-amber-500/20 dark:bg-white/5 dark:hover:bg-white/10 transition-all">
                    <CardContent class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-amber-600 dark:text-amber-400">Pending</p>
                                <p class="text-3xl font-bold text-amber-700 dark:text-amber-300">{{ pendingRequests.length }}</p>
                            </div>
                            <Clock class="h-8 w-8 text-amber-400" />
                        </div>
                    </CardContent>
                </Card>

                <Card class="border-emerald-200 bg-white/80 backdrop-blur-sm hover:bg-white dark:border-emerald-500/20 dark:bg-white/5 dark:hover:bg-white/10 transition-all">
                    <CardContent class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-emerald-600 dark:text-emerald-400">Approved</p>
                                <p class="text-3xl font-bold text-emerald-700 dark:text-emerald-300">{{ approvedRequests.length }}</p>
                            </div>
                            <CheckCircle2 class="h-8 w-8 text-emerald-400" />
                        </div>
                    </CardContent>
                </Card>

                <Card class="border-rose-200 bg-white/80 backdrop-blur-sm hover:bg-white dark:border-rose-500/20 dark:bg-white/5 dark:hover:bg-white/10 transition-all">
                    <CardContent class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-rose-600 dark:text-rose-400">Rejected</p>
                                <p class="text-3xl font-bold text-rose-700 dark:text-rose-300">{{ rejectedRequests.length }}</p>
                            </div>
                            <XCircle class="h-8 w-8 text-rose-400" />
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Tabs for filtering -->
            <Tabs v-model="activeTab" class="flex flex-1 flex-col space-y-4">
                <TabsList class="w-fit border border-slate-200 bg-white/90 shadow-lg backdrop-blur-xl dark:border-white/20 dark:bg-white/10">
                    <TabsTrigger value="all" class="text-slate-600 hover:bg-slate-100 hover:text-slate-900 data-[state=active]:bg-slate-200 data-[state=active]:text-slate-900 dark:text-white/70 dark:hover:bg-white/5 dark:hover:text-white dark:data-[state=active]:bg-white/20 dark:data-[state=active]:text-white">
                        All ({{ visibleRequests.length }})
                    </TabsTrigger>
                    <TabsTrigger value="pending" class="text-slate-600 hover:bg-slate-100 hover:text-slate-900 data-[state=active]:bg-slate-200 data-[state=active]:text-slate-900 dark:text-white/70 dark:hover:bg-white/5 dark:hover:text-white dark:data-[state=active]:bg-white/20 dark:data-[state=active]:text-white">
                        <Clock class="mr-1.5 h-3.5 w-3.5" />
                        Pending
                        <Badge v-if="pendingRequests.length > 0" class="ml-1.5 bg-amber-500 text-white border-0">
                            {{ pendingRequests.length }}
                        </Badge>
                    </TabsTrigger>
                    <TabsTrigger value="approved" class="text-slate-600 hover:bg-slate-100 hover:text-slate-900 data-[state=active]:bg-slate-200 data-[state=active]:text-slate-900 dark:text-white/70 dark:hover:bg-white/5 dark:hover:text-white dark:data-[state=active]:bg-white/20 dark:data-[state=active]:text-white">
                        <CheckCircle2 class="mr-1.5 h-3.5 w-3.5" />
                        Approved ({{ approvedRequests.length }})
                    </TabsTrigger>
                    <TabsTrigger value="rejected" class="text-slate-600 hover:bg-slate-100 hover:text-slate-900 data-[state=active]:bg-slate-200 data-[state=active]:text-slate-900 dark:text-white/70 dark:hover:bg-white/5 dark:hover:text-white dark:data-[state=active]:bg-white/20 dark:data-[state=active]:text-white">
                        <XCircle class="mr-1.5 h-3.5 w-3.5" />
                        Rejected ({{ rejectedRequests.length }})
                    </TabsTrigger>
                </TabsList>

                <!-- All Tabs Content -->
                <TabsContent
                    value="all"
                    class="flex-1 overflow-auto rounded-3xl border border-slate-200 bg-white/80 p-6 shadow-2xl backdrop-blur-xl dark:border-white/20 dark:bg-white/10"
                >
                    <RequestsTable
                        :requests="filteredRequests"
                        :user-role="userRole"
                        @approve="handleApprove"
                        @reject="handleReject"
                    />
                </TabsContent>

                <TabsContent
                    value="pending"
                    class="flex-1 overflow-auto rounded-3xl border border-slate-200 bg-white/80 p-6 shadow-2xl backdrop-blur-xl dark:border-white/20 dark:bg-white/10"
                >
                    <RequestsTable
                        :requests="filteredRequests"
                        :user-role="userRole"
                        @approve="handleApprove"
                        @reject="handleReject"
                    />
                </TabsContent>

                <TabsContent
                    value="approved"
                    class="flex-1 overflow-auto rounded-3xl border border-slate-200 bg-white/80 p-6 shadow-2xl backdrop-blur-xl dark:border-white/20 dark:bg-white/10"
                >
                    <RequestsTable
                        :requests="filteredRequests"
                        :user-role="userRole"
                        @approve="handleApprove"
                        @reject="handleReject"
                    />
                </TabsContent>

                <TabsContent
                    value="rejected"
                    class="flex-1 overflow-auto rounded-3xl border border-slate-200 bg-white/80 p-6 shadow-2xl backdrop-blur-xl dark:border-white/20 dark:bg-white/10"
                >
                    <RequestsTable
                        :requests="filteredRequests"
                        :user-role="userRole"
                        @approve="handleApprove"
                        @reject="handleReject"
                    />
                </TabsContent>
            </Tabs>
        </div>
    </AppLayout>
</template>
