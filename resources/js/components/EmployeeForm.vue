<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { useForm } from '@inertiajs/vue3';
import { UserPlus, AlertCircle } from 'lucide-vue-next';
import { UserRole } from '@/enums/UserRole';

const emit = defineEmits<{
    success: [];
}>();

const form = useForm({
    name: '',
    email: '',
    password: '',
    role: UserRole.EMPLOYEE,
});

const submitForm = () => {
    form.post('/employees', {
        onSuccess: () => {
            form.reset();
            emit('success');
        },
    });
};
</script>

<template>
    <div class="space-y-6">
        <Card class="border-slate-200 bg-white dark:border-white/10 dark:bg-white/5">
            <CardHeader>
                <CardTitle class="flex items-center gap-2 text-slate-900 dark:text-white">
                    <UserPlus class="h-5 w-5 text-purple-500 dark:text-purple-400" />
                    Add New Employee
                </CardTitle>
                <CardDescription class="text-slate-600 dark:text-white/60">
                    Fill in the details to create a new employee account
                </CardDescription>
            </CardHeader>
            <CardContent>
                <form @submit.prevent="submitForm" class="space-y-6">
                    <!-- Name Field -->
                    <div class="space-y-2">
                        <Label for="name" class="text-slate-700 dark:text-white/80">Full Name</Label>
                        <Input
                            id="name"
                            v-model="form.name"
                            type="text"
                            placeholder="John Doe"
                            required
                            class="border-slate-300 bg-white focus:border-purple-500 focus:ring-purple-500 dark:border-white/20 dark:bg-white/5"
                        />
                        <Alert v-if="form.errors.name" variant="destructive" class="mt-2">
                            <AlertCircle class="h-4 w-4" />
                            <AlertDescription>{{ form.errors.name }}</AlertDescription>
                        </Alert>
                    </div>

                    <!-- Email Field -->
                    <div class="space-y-2">
                        <Label for="email" class="text-slate-700 dark:text-white/80">Email Address</Label>
                        <Input
                            id="email"
                            v-model="form.email"
                            type="email"
                            placeholder="john.doe@company.com"
                            required
                            class="border-slate-300 bg-white focus:border-purple-500 focus:ring-purple-500 dark:border-white/20 dark:bg-white/5"
                        />
                        <Alert v-if="form.errors.email" variant="destructive" class="mt-2">
                            <AlertCircle class="h-4 w-4" />
                            <AlertDescription>{{ form.errors.email }}</AlertDescription>
                        </Alert>
                    </div>

                    <!-- Password Field -->
                    <div class="space-y-2">
                        <Label for="password" class="text-slate-700 dark:text-white/80">Password</Label>
                        <Input
                            id="password"
                            v-model="form.password"
                            type="password"
                            placeholder="Min. 8 characters"
                            required
                            class="border-slate-300 bg-white focus:border-purple-500 focus:ring-purple-500 dark:border-white/20 dark:bg-white/5"
                        />
                        <p class="text-xs text-slate-600 dark:text-white/60">
                            Password must be at least 8 characters long
                        </p>
                        <Alert v-if="form.errors.password" variant="destructive" class="mt-2">
                            <AlertCircle class="h-4 w-4" />
                            <AlertDescription>{{ form.errors.password }}</AlertDescription>
                        </Alert>
                    </div>

                    <!-- Role Field -->
                    <div class="space-y-2">
                        <Label for="role" class="text-slate-700 dark:text-white/80">Role</Label>
                        <Select v-model="form.role" required>
                            <SelectTrigger class="border-slate-300 bg-white focus:border-purple-500 focus:ring-purple-500 dark:border-white/20 dark:bg-white/5">
                                <SelectValue placeholder="Select a role" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem :value="UserRole.EMPLOYEE">Employee</SelectItem>
                                <SelectItem :value="UserRole.MANAGER">Manager</SelectItem>
                                <SelectItem :value="UserRole.ADMIN">Admin</SelectItem>
                            </SelectContent>
                        </Select>
                        <Alert v-if="form.errors.role" variant="destructive" class="mt-2">
                            <AlertCircle class="h-4 w-4" />
                            <AlertDescription>{{ form.errors.role }}</AlertDescription>
                        </Alert>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end gap-3 pt-4">
                        <Button
                            type="button"
                            variant="outline"
                            @click="form.reset()"
                            class="border-slate-300 hover:bg-slate-100 dark:border-white/20 dark:hover:bg-white/10"
                        >
                            Clear
                        </Button>
                        <Button
                            type="submit"
                            :disabled="form.processing"
                            class="bg-gradient-to-r from-purple-500 to-indigo-600 text-white hover:from-purple-600 hover:to-indigo-700"
                        >
                            <UserPlus class="mr-2 h-4 w-4" />
                            {{ form.processing ? 'Creating...' : 'Create Employee' }}
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>

        <!-- Info Card -->
        <Card class="border-slate-200 bg-slate-50 dark:border-white/10 dark:bg-white/5">
            <CardContent class="pt-6">
                <div class="space-y-2 text-sm text-slate-600 dark:text-white/60">
                    <p class="font-semibold text-slate-900 dark:text-white">Employee Roles:</p>
                    <ul class="ml-4 list-disc space-y-1">
                        <li><span class="font-medium">Employee:</span> Can request time off and view their own data</li>
                        <li><span class="font-medium">Manager:</span> Can approve/reject requests for their team</li>
                        <li><span class="font-medium">Admin:</span> Full access to manage all employees and settings</li>
                    </ul>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
