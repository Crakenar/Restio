<script setup lang="ts">
import HybridDashboard from '@/components/dashboards/HybridDashboard.vue';
import { Head } from '@inertiajs/vue3';
import type { VacationRequest } from '@/types/vacation';

// Sample data for demo
const userName = 'Teo';
const notificationCount = 3;
const totalDaysAllowed = 20;

// Sample vacation requests
const sampleRequests: VacationRequest[] = [
    {
        id: '1',
        employeeName: 'Teo',
        type: 'vacation',
        startDate: new Date(2026, 1, 15), // Feb 15, 2026
        endDate: new Date(2026, 1, 20), // Feb 20, 2026
        status: 'approved',
    },
    {
        id: '2',
        employeeName: 'Teo',
        type: 'sick',
        startDate: new Date(2026, 0, 10), // Jan 10, 2026
        endDate: new Date(2026, 0, 12), // Jan 12, 2026
        status: 'approved',
    },
    {
        id: '3',
        employeeName: 'Teo',
        type: 'wfh',
        startDate: new Date(2026, 2, 5), // Mar 5, 2026
        endDate: new Date(2026, 2, 5), // Mar 5, 2026
        status: 'pending',
    },
    {
        id: '4',
        employeeName: 'Teo',
        type: 'vacation',
        startDate: new Date(2026, 3, 1), // Apr 1, 2026
        endDate: new Date(2026, 3, 7), // Apr 7, 2026
        status: 'pending',
    },
];
</script>

<template>
    <Head title="Dashboard Hybrid - Demo" />

    <HybridDashboard
        :user-name="userName"
        :notification-count="notificationCount"
        :requests="sampleRequests"
        :total-days-allowed="totalDaysAllowed"
    />
</template>
