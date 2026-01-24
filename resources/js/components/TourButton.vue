<script setup lang="ts">
import { useOnboardingTour } from '@/composables/useOnboardingTour';
import { Button } from '@/components/ui/button';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import { HelpCircle } from 'lucide-vue-next';

interface Props {
    role?: 'employee' | 'manager' | 'admin';
}

const props = withDefaults(defineProps<Props>(), {
    role: 'employee',
});

const { startTourForRole } = useOnboardingTour();

const handleClick = () => {
    startTourForRole(props.role);
};
</script>

<template>
    <TooltipProvider>
        <Tooltip>
            <TooltipTrigger as-child>
                <Button
                    @click="handleClick"
                    variant="ghost"
                    size="icon"
                    class="relative"
                >
                    <HelpCircle class="h-5 w-5" />
                    <span class="sr-only">{{ $t('tour.restart') }}</span>
                </Button>
            </TooltipTrigger>
            <TooltipContent>
                <p>{{ $t('tour.restart') }}</p>
            </TooltipContent>
        </Tooltip>
    </TooltipProvider>
</template>
