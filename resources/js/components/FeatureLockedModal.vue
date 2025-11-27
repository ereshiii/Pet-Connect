<script setup lang="ts">
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Lock, Crown, Zap, TrendingUp, FileText, Users } from 'lucide-vue-next';
import { router } from '@inertiajs/vue3';

interface Props {
    open: boolean;
    featureName: string;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
}>();

const features: Record<string, {
    icon: any;
    title: string;
    description: string;
    benefits: string[];
}> = {
    'reports': {
        icon: FileText,
        title: 'Report Generation',
        description: 'Generate comprehensive reports for your clinic operations',
        benefits: [
            'Monthly revenue reports',
            'Appointment analytics',
            'Patient visit summaries',
            'Staff performance metrics',
            'Export to PDF and Excel'
        ]
    },
    'analytics': {
        icon: TrendingUp,
        title: 'Advanced Analytics',
        description: 'Get detailed insights into your clinic performance',
        benefits: [
            'Real-time dashboard metrics',
            'Revenue trends and forecasting',
            'Patient retention analysis',
            'Appointment conversion rates',
            'Custom date range reports'
        ]
    },
    'staff_management': {
        icon: Users,
        title: 'Staff Management',
        description: 'Manage multiple staff accounts and roles',
        benefits: [
            'Add up to 10 staff accounts',
            'Role-based permissions',
            'Activity tracking',
            'Schedule management',
            'Performance monitoring'
        ]
    },
    'multiple_veterinarians': {
        icon: Users,
        title: 'Multiple Veterinarians',
        description: 'Add multiple veterinarians to your clinic',
        benefits: [
            'Unlimited veterinarian profiles',
            'Individual schedules',
            'Specialized services per vet',
            'Patient assignment',
            'Availability management'
        ]
    }
};

const getFeatureData = () => {
    return features[props.featureName] || {
        icon: Lock,
        title: 'Premium Feature',
        description: 'This feature is only available on Pro Plus plan',
        benefits: ['Unlock with Pro Plus subscription']
    };
};

const handleUpgrade = () => {
    emit('update:open', false);
    router.visit('/subscription');
};
</script>

<template>
    <Dialog :open="open" @update:open="$emit('update:open', $event)">
        <DialogContent class="sm:max-w-[500px]">
            <DialogHeader>
                <div class="flex items-center justify-center mb-4">
                    <div class="rounded-full bg-primary/10 p-4">
                        <component :is="getFeatureData().icon" class="h-8 w-8 text-primary" />
                    </div>
                </div>
                
                <DialogTitle class="text-center text-2xl">
                    {{ getFeatureData().title }}
                </DialogTitle>
                
                <DialogDescription class="text-center">
                    {{ getFeatureData().description }}
                </DialogDescription>

                <div class="flex justify-center mt-2">
                    <Badge variant="secondary" class="gap-1">
                        <Crown class="h-3 w-3" />
                        Pro Plus Feature
                    </Badge>
                </div>
            </DialogHeader>

            <!-- Feature Benefits -->
            <div class="space-y-4 py-4">
                <div class="rounded-lg border bg-muted/50 p-4">
                    <p class="text-sm font-medium mb-3">With Pro Plus you get:</p>
                    <ul class="space-y-2">
                        <li 
                            v-for="(benefit, index) in getFeatureData().benefits" 
                            :key="index"
                            class="flex items-start gap-2 text-sm"
                        >
                            <Zap class="h-4 w-4 text-primary mt-0.5 flex-shrink-0" />
                            <span>{{ benefit }}</span>
                        </li>
                    </ul>
                </div>

                <!-- Pricing Info -->
                <div class="rounded-lg bg-primary/5 border border-primary/20 p-4">
                    <div class="flex items-baseline justify-center gap-1 mb-1">
                        <span class="text-3xl font-bold">₱1,499</span>
                        <span class="text-muted-foreground text-sm">/month</span>
                    </div>
                    <p class="text-center text-xs text-muted-foreground">
                        or ₱14,990/year (save 20%)
                    </p>
                    <p class="text-center text-xs text-primary mt-2">
                        30-day free trial included
                    </p>
                </div>

                <!-- Additional Pro Plus Features -->
                <div class="text-xs text-muted-foreground space-y-1">
                    <p class="font-medium text-foreground">Other Pro Plus features:</p>
                    <ul class="list-disc list-inside space-y-0.5 ml-2">
                        <li>Unlimited appointments per month</li>
                        <li>Lower transaction fees (2.5% vs 5%)</li>
                        <li>Priority support</li>
                        <li>Advanced scheduling</li>
                    </ul>
                </div>
            </div>

            <DialogFooter class="flex-col sm:flex-col gap-2">
                <Button 
                    class="w-full"
                    @click="handleUpgrade"
                >
                    <Crown class="h-4 w-4 mr-2" />
                    Upgrade to Pro Plus
                </Button>
                <Button 
                    variant="ghost" 
                    class="w-full"
                    @click="$emit('update:open', false)"
                >
                    Maybe Later
                </Button>
            </DialogFooter>

            <p class="text-center text-xs text-muted-foreground">
                Questions? <a href="/contact" class="text-primary hover:underline">Contact our sales team</a>
            </p>
        </DialogContent>
    </Dialog>
</template>
