<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { useForm } from '@inertiajs/vue3';
import { Upload, FileText, Download, AlertCircle, CheckCircle2 } from 'lucide-vue-next';
import { ref, computed } from 'vue';

const emit = defineEmits<{
    success: [];
}>();

const form = useForm({
    file: null as File | null,
});

const fileInput = ref<HTMLInputElement>();
const isDragging = ref(false);

const handleFileSelect = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        form.file = target.files[0];
    }
};

const handleDrop = (event: DragEvent) => {
    isDragging.value = false;
    if (event.dataTransfer?.files && event.dataTransfer.files[0]) {
        form.file = event.dataTransfer.files[0];
    }
};

const removeFile = () => {
    form.file = null;
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

const submitImport = () => {
    form.post('/employees/import', {
        onSuccess: () => {
            form.reset();
            emit('success');
        },
    });
};

const downloadTemplate = () => {
    const csvContent = 'name,email,password,role\nJohn Doe,john@example.com,password123,employee\nJane Smith,jane@example.com,password123,manager';
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = 'employees_template.csv';
    link.click();
    window.URL.revokeObjectURL(url);
};

const fileName = computed(() => form.file?.name || '');
const fileSize = computed(() => {
    if (!form.file) return '';
    const bytes = form.file.size;
    if (bytes < 1024) return `${bytes} B`;
    if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`;
    return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
});
</script>

<template>
    <div class="space-y-6">
        <Card class="border-slate-200 bg-white dark:border-white/10 dark:bg-white/5">
            <CardHeader>
                <CardTitle class="flex items-center gap-2 text-slate-900 dark:text-white">
                    <Upload class="h-5 w-5 text-purple-500 dark:text-purple-400" />
                    Import Employees from CSV
                </CardTitle>
                <CardDescription class="text-slate-600 dark:text-white/60">
                    Upload a CSV file to import multiple employees at once
                </CardDescription>
            </CardHeader>
            <CardContent class="space-y-6">
                <!-- Download Template Button -->
                <div class="flex items-center justify-between rounded-lg border border-slate-200 bg-slate-50 p-4 dark:border-white/10 dark:bg-white/5">
                    <div class="flex items-center gap-3">
                        <FileText class="h-5 w-5 text-slate-600 dark:text-white/60" />
                        <div>
                            <p class="text-sm font-medium text-slate-900 dark:text-white">Need a template?</p>
                            <p class="text-xs text-slate-600 dark:text-white/60">Download a sample CSV file to get started</p>
                        </div>
                    </div>
                    <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        @click="downloadTemplate"
                        class="border-slate-300 hover:bg-slate-100 dark:border-white/20 dark:hover:bg-white/10"
                    >
                        <Download class="mr-2 h-4 w-4" />
                        Download Template
                    </Button>
                </div>

                <!-- File Upload Area -->
                <div
                    @dragover.prevent="isDragging = true"
                    @dragleave.prevent="isDragging = false"
                    @drop.prevent="handleDrop"
                    :class="[
                        'relative cursor-pointer rounded-xl border-2 border-dashed p-8 text-center transition-all',
                        isDragging
                            ? 'border-purple-500 bg-purple-50 dark:border-purple-400 dark:bg-purple-950/20'
                            : 'border-slate-300 bg-slate-50 hover:border-purple-400 hover:bg-slate-100 dark:border-white/20 dark:bg-white/5 dark:hover:border-purple-500/50 dark:hover:bg-white/10',
                    ]"
                    @click="fileInput?.click()"
                >
                    <input
                        ref="fileInput"
                        type="file"
                        accept=".csv"
                        class="hidden"
                        @change="handleFileSelect"
                    />
                    <div class="flex flex-col items-center gap-4">
                        <div class="rounded-full bg-white p-4 shadow-lg dark:bg-white/10">
                            <Upload class="h-8 w-8 text-purple-500 dark:text-purple-400" />
                        </div>
                        <div>
                            <p class="text-lg font-semibold text-slate-900 dark:text-white">
                                Drop CSV file here or click to browse
                            </p>
                            <p class="mt-1 text-sm text-slate-600 dark:text-white/60">
                                CSV format with headers: name, email, password, role
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Selected File Display -->
                <div v-if="form.file" class="rounded-lg border border-slate-200 bg-white p-4 dark:border-white/10 dark:bg-white/5">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="rounded-lg bg-purple-100 p-2 dark:bg-purple-950/30">
                                <FileText class="h-5 w-5 text-purple-600 dark:text-purple-400" />
                            </div>
                            <div>
                                <p class="font-medium text-slate-900 dark:text-white">{{ fileName }}</p>
                                <p class="text-sm text-slate-600 dark:text-white/60">{{ fileSize }}</p>
                            </div>
                        </div>
                        <Button
                            type="button"
                            variant="ghost"
                            size="sm"
                            @click.stop="removeFile"
                            class="text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-white/70 dark:hover:bg-white/10 dark:hover:text-white"
                        >
                            Remove
                        </Button>
                    </div>
                </div>

                <!-- Error Messages -->
                <Alert v-if="form.errors.file" variant="destructive">
                    <AlertCircle class="h-4 w-4" />
                    <AlertTitle>Error</AlertTitle>
                    <AlertDescription>{{ form.errors.file }}</AlertDescription>
                </Alert>

                <!-- Submit Button -->
                <div class="flex justify-end">
                    <Button
                        type="button"
                        @click="submitImport"
                        :disabled="!form.file || form.processing"
                        class="bg-gradient-to-r from-purple-500 to-indigo-600 text-white hover:from-purple-600 hover:to-indigo-700"
                    >
                        <Upload class="mr-2 h-4 w-4" />
                        {{ form.processing ? 'Importing...' : 'Import Employees' }}
                    </Button>
                </div>

                <!-- CSV Format Info -->
                <Alert class="border-slate-200 bg-slate-50 dark:border-white/10 dark:bg-white/5">
                    <CheckCircle2 class="h-4 w-4 text-blue-500 dark:text-blue-400" />
                    <AlertTitle class="text-slate-900 dark:text-white">CSV Format Requirements</AlertTitle>
                    <AlertDescription class="text-slate-600 dark:text-white/60">
                        <ul class="ml-4 mt-2 list-disc space-y-1 text-sm">
                            <li>First row must contain headers: name, email, password, role</li>
                            <li>Role must be one of: employee, manager, admin</li>
                            <li>Email addresses must be unique</li>
                            <li>Passwords must be at least 8 characters</li>
                        </ul>
                    </AlertDescription>
                </Alert>
            </CardContent>
        </Card>
    </div>
</template>
