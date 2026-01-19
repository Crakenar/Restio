<script setup lang="ts">
import PremiumSidebar from '@/components/PremiumSidebar.vue';
import { Head, useForm, router, usePage } from '@inertiajs/vue3';
import { useToast } from '@/composables/useToast';

const page = usePage();
const toast = useToast();
import { Users, Plus, Edit2, Trash2, UserPlus, UserMinus } from 'lucide-vue-next';
import { ref, computed } from 'vue';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';

interface User {
    id: number;
    name: string;
    email: string;
}

interface Team {
    id: number;
    name: string;
    users_count: number;
    users: User[];
    created_at: string;
}

interface Props {
    teams: Team[];
    unassignedUsers: User[];
    userRole: string;
}

const props = defineProps<Props>();

const isCreateDialogOpen = ref(false);
const isEditDialogOpen = ref(false);
const isAssignDialogOpen = ref(false);
const selectedTeam = ref<Team | null>(null);
const selectedUserIds = ref<number[]>([]);

// Form for creating team
const createForm = useForm({
    name: '',
});

// Form for editing team
const editForm = useForm({
    name: '',
});

const handleCreateTeam = () => {
    createForm.post('/team-management', {
        onSuccess: () => {
            createForm.reset();
            isCreateDialogOpen.value = false;
            toast.success('Team created successfully!');
        },
        onError: () => {
            toast.error('Failed to create team. Please check your inputs.');
        },
    });
};

const handleEditTeam = () => {
    if (!selectedTeam.value) return;

    editForm.patch(`/team-management/${selectedTeam.value.id}`, {
        onSuccess: () => {
            editForm.reset();
            isEditDialogOpen.value = false;
            selectedTeam.value = null;
            toast.success('Team updated successfully!');
        },
        onError: () => {
            toast.error('Failed to update team. Please try again.');
        },
    });
};

const handleDeleteTeam = (teamId: number) => {
    if (confirm('Are you sure you want to delete this team? Users will be unassigned.')) {
        router.delete(`/team-management/${teamId}`, {
            onSuccess: () => {
                toast.success('Team deleted successfully!');
            },
            onError: () => {
                toast.error('Failed to delete team. Please try again.');
            },
        });
    }
};

const openEditDialog = (team: Team) => {
    selectedTeam.value = team;
    editForm.name = team.name;
    isEditDialogOpen.value = true;
};

const openAssignDialog = (team: Team) => {
    selectedTeam.value = team;
    selectedUserIds.value = [];
    isAssignDialogOpen.value = true;
};

const handleAssignUsers = () => {
    if (!selectedTeam.value || selectedUserIds.value.length === 0) return;

    const userCount = selectedUserIds.value.length;

    router.post(
        `/team-management/${selectedTeam.value.id}/assign-users`,
        {
            user_ids: selectedUserIds.value,
        },
        {
            onSuccess: () => {
                selectedUserIds.value = [];
                isAssignDialogOpen.value = false;
                selectedTeam.value = null;
                toast.success(`Successfully assigned ${userCount} user(s) to team!`);
            },
            onError: () => {
                toast.error('Failed to assign users to team. Please try again.');
            },
        },
    );
};

const handleRemoveUser = (teamId: number, userId: number) => {
    if (confirm('Are you sure you want to remove this user from the team?')) {
        router.delete(`/team-management/${teamId}/users/${userId}`, {
            onSuccess: () => {
                toast.success('User removed from team successfully!');
            },
            onError: () => {
                toast.error('Failed to remove user from team. Please try again.');
            },
        });
    }
};

const toggleUserSelection = (userId: number) => {
    const index = selectedUserIds.value.indexOf(userId);
    if (index > -1) {
        selectedUserIds.value.splice(index, 1);
    } else {
        selectedUserIds.value.push(userId);
    }
};
</script>

<template>
    <Head title="Team Management" />

    <div
        class="flex min-h-screen bg-gradient-to-br from-slate-50 via-orange-50 to-rose-50 dark:from-slate-950 dark:via-orange-950 dark:to-rose-950"
    >
        <!-- Sidebar -->
        <PremiumSidebar :notifications="$page.props.notifications || []" />

        <!-- Main content area -->
        <div class="ml-72 flex-1 p-4 transition-all duration-500 sm:p-6 lg:p-8">
            <!-- Animated gradient orbs -->
            <div class="pointer-events-none fixed inset-0 overflow-hidden">
                <div
                    class="absolute -top-1/2 -right-1/2 h-full w-full animate-pulse rounded-full bg-gradient-to-br from-orange-500/10 via-amber-500/10 to-yellow-500/10 blur-3xl dark:from-orange-500/20 dark:via-amber-500/20 dark:to-yellow-500/20"
                    style="animation-duration: 8s"
                />
                <div
                    class="absolute -bottom-1/2 -left-1/2 h-full w-full animate-pulse rounded-full bg-gradient-to-tr from-rose-500/10 via-pink-500/10 to-red-500/10 blur-3xl dark:from-rose-500/20 dark:via-pink-500/20 dark:to-red-500/20"
                    style="animation-duration: 10s; animation-delay: 1s"
                />
            </div>

            <!-- Content -->
            <div class="relative mx-auto max-w-7xl space-y-6">
                <!-- Enhanced Header -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div
                            class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-orange-400 to-rose-500 shadow-2xl shadow-orange-500/30"
                        >
                            <Users class="h-8 w-8 text-white" />
                        </div>
                        <div>
                            <h1
                                class="text-4xl font-bold tracking-tight text-slate-900 dark:text-white"
                            >
                                Team Management
                            </h1>
                            <p class="mt-1.5 text-sm text-slate-600 dark:text-white/70">
                                Create and manage teams for your organization
                            </p>
                        </div>
                    </div>

                    <!-- Create Team Button -->
                    <Dialog v-model:open="isCreateDialogOpen">
                        <DialogTrigger as-child>
                            <Button
                                class="bg-gradient-to-r from-orange-500 to-rose-500 text-white hover:from-orange-600 hover:to-rose-600"
                            >
                                <Plus class="mr-2 h-4 w-4" />
                                Create Team
                            </Button>
                        </DialogTrigger>
                        <DialogContent>
                            <DialogHeader>
                                <DialogTitle>Create New Team</DialogTitle>
                            </DialogHeader>
                            <form @submit.prevent="handleCreateTeam" class="space-y-4">
                                <div>
                                    <Label for="team-name">Team Name</Label>
                                    <Input
                                        id="team-name"
                                        v-model="createForm.name"
                                        placeholder="Enter team name"
                                        required
                                    />
                                </div>
                                <div class="flex justify-end gap-2">
                                    <Button
                                        type="button"
                                        variant="outline"
                                        @click="isCreateDialogOpen = false"
                                    >
                                        Cancel
                                    </Button>
                                    <Button
                                        type="submit"
                                        :disabled="createForm.processing"
                                        class="bg-gradient-to-r from-orange-500 to-rose-500 text-white hover:from-orange-600 hover:to-rose-600"
                                    >
                                        Create Team
                                    </Button>
                                </div>
                            </form>
                        </DialogContent>
                    </Dialog>
                </div>

                <!-- Teams Grid -->
                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <Card
                        v-for="team in teams"
                        :key="team.id"
                        class="group relative overflow-hidden border border-white/60 bg-white/70 backdrop-blur-xl transition-all duration-500 hover:border-white/80 hover:bg-white/80 hover:shadow-2xl dark:border-white/10 dark:bg-slate-900/70 dark:hover:border-white/20 dark:hover:bg-slate-900/80"
                    >
                        <CardContent class="p-6">
                            <!-- Team Header -->
                            <div class="mb-4 flex items-start justify-between">
                                <div class="flex-1">
                                    <h3
                                        class="text-xl font-bold text-slate-900 dark:text-white"
                                    >
                                        {{ team.name }}
                                    </h3>
                                    <p class="text-sm text-slate-600 dark:text-slate-400">
                                        {{ team.users_count }}
                                        {{ team.users_count === 1 ? 'member' : 'members' }}
                                    </p>
                                </div>
                                <div class="flex gap-2">
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        @click="openEditDialog(team)"
                                        class="h-8 w-8"
                                    >
                                        <Edit2 class="h-4 w-4" />
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        @click="handleDeleteTeam(team.id)"
                                        class="h-8 w-8 text-red-600 hover:bg-red-100 dark:text-red-400 dark:hover:bg-red-900/20"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </Button>
                                </div>
                            </div>

                            <!-- Team Members -->
                            <div class="mb-4 space-y-2">
                                <div
                                    v-for="user in team.users.slice(0, 3)"
                                    :key="user.id"
                                    class="flex items-center justify-between rounded-lg bg-slate-100 p-2 dark:bg-slate-800"
                                >
                                    <div class="flex-1">
                                        <p
                                            class="text-sm font-medium text-slate-900 dark:text-white"
                                        >
                                            {{ user.name }}
                                        </p>
                                        <p class="text-xs text-slate-600 dark:text-slate-400">
                                            {{ user.email }}
                                        </p>
                                    </div>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        @click="handleRemoveUser(team.id, user.id)"
                                        class="h-6 w-6"
                                    >
                                        <UserMinus class="h-3 w-3" />
                                    </Button>
                                </div>
                                <p
                                    v-if="team.users.length > 3"
                                    class="text-xs text-slate-600 dark:text-slate-400"
                                >
                                    +{{ team.users.length - 3 }} more members
                                </p>
                            </div>

                            <!-- Assign Users Button -->
                            <Button
                                variant="outline"
                                class="w-full"
                                @click="openAssignDialog(team)"
                            >
                                <UserPlus class="mr-2 h-4 w-4" />
                                Assign Users
                            </Button>
                        </CardContent>
                    </Card>

                    <!-- Empty State -->
                    <Card
                        v-if="teams.length === 0"
                        class="col-span-full border border-white/60 bg-white/70 backdrop-blur-xl dark:border-white/10 dark:bg-slate-900/70"
                    >
                        <CardContent class="flex flex-col items-center justify-center p-12">
                            <Users class="mb-4 h-16 w-16 text-slate-400 dark:text-slate-600" />
                            <h3 class="mb-2 text-xl font-semibold text-slate-900 dark:text-white">
                                No teams yet
                            </h3>
                            <p class="text-sm text-slate-600 dark:text-slate-400">
                                Create your first team to get started
                            </p>
                        </CardContent>
                    </Card>
                </div>

                <!-- Unassigned Users -->
                <Card
                    v-if="unassignedUsers.length > 0"
                    class="border border-white/60 bg-white/70 backdrop-blur-xl dark:border-white/10 dark:bg-slate-900/70"
                >
                    <CardContent class="p-6">
                        <h3 class="mb-4 text-lg font-semibold text-slate-900 dark:text-white">
                            Unassigned Users ({{ unassignedUsers.length }})
                        </h3>
                        <div class="grid gap-2 md:grid-cols-2 lg:grid-cols-3">
                            <div
                                v-for="user in unassignedUsers"
                                :key="user.id"
                                class="rounded-lg bg-slate-100 p-3 dark:bg-slate-800"
                            >
                                <p class="font-medium text-slate-900 dark:text-white">
                                    {{ user.name }}
                                </p>
                                <p class="text-sm text-slate-600 dark:text-slate-400">
                                    {{ user.email }}
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </div>

    <!-- Edit Team Dialog -->
    <Dialog v-model:open="isEditDialogOpen">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Edit Team</DialogTitle>
            </DialogHeader>
            <form @submit.prevent="handleEditTeam" class="space-y-4">
                <div>
                    <Label for="edit-team-name">Team Name</Label>
                    <Input
                        id="edit-team-name"
                        v-model="editForm.name"
                        placeholder="Enter team name"
                        required
                    />
                </div>
                <div class="flex justify-end gap-2">
                    <Button
                        type="button"
                        variant="outline"
                        @click="isEditDialogOpen = false"
                    >
                        Cancel
                    </Button>
                    <Button
                        type="submit"
                        :disabled="editForm.processing"
                        class="bg-gradient-to-r from-orange-500 to-rose-500 text-white hover:from-orange-600 hover:to-rose-600"
                    >
                        Update Team
                    </Button>
                </div>
            </form>
        </DialogContent>
    </Dialog>

    <!-- Assign Users Dialog -->
    <Dialog v-model:open="isAssignDialogOpen">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Assign Users to {{ selectedTeam?.name }}</DialogTitle>
            </DialogHeader>
            <div class="space-y-4">
                <div class="max-h-96 space-y-2 overflow-y-auto">
                    <div
                        v-for="user in unassignedUsers"
                        :key="user.id"
                        @click="toggleUserSelection(user.id)"
                        :class="[
                            'cursor-pointer rounded-lg border p-3 transition-all',
                            selectedUserIds.includes(user.id)
                                ? 'border-orange-500 bg-orange-50 dark:border-orange-400 dark:bg-orange-900/20'
                                : 'border-slate-200 bg-white hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:hover:bg-slate-700',
                        ]"
                    >
                        <p class="font-medium text-slate-900 dark:text-white">
                            {{ user.name }}
                        </p>
                        <p class="text-sm text-slate-600 dark:text-slate-400">
                            {{ user.email }}
                        </p>
                    </div>
                    <div
                        v-if="unassignedUsers.length === 0"
                        class="py-8 text-center text-sm text-slate-600 dark:text-slate-400"
                    >
                        No unassigned users available
                    </div>
                </div>
                <div class="flex justify-end gap-2">
                    <Button type="button" variant="outline" @click="isAssignDialogOpen = false">
                        Cancel
                    </Button>
                    <Button
                        type="button"
                        @click="handleAssignUsers"
                        :disabled="selectedUserIds.length === 0"
                        class="bg-gradient-to-r from-orange-500 to-rose-500 text-white hover:from-orange-600 hover:to-rose-600"
                    >
                        Assign {{ selectedUserIds.length }}
                        {{ selectedUserIds.length === 1 ? 'User' : 'Users' }}
                    </Button>
                </div>
            </div>
        </DialogContent>
    </Dialog>
</template>
