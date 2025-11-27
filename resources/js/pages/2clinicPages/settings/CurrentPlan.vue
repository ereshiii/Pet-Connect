<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import Progress from '@/components/ui/Progress.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/clinicSettings/Layout.vue';
import { type BreadcrumbItem } from '@/types';
import { Check, Crown, Zap } from 'lucide-vue-next';

interface SubscriptionPlan {
    name: string;
    slug: string;
    price: number;
}

interface Subscription {
    id: number;
    stripe_status: string;
    trial_ends_at: string | null;
    ends_at: string | null;
    created_at: string;
}

interface Stats {
    services_count: number;
    services_limit: number | string;
    vets_count: number;
    vets_limit: number | string;
    transaction_fee: number;
}

interface Props {
    currentPlan?: SubscriptionPlan | null;
    subscription?: Subscription | null;
    stats?: Stats;
    usageLimits?: Record<string, any>;
}

const props = withDefaults(defineProps<Props>(), {
    currentPlan: null,
    subscription: null,
    stats: () => ({
        services_count: 0,
        services_limit: 0,
        vets_count: 0,
        vets_limit: 0,
        transaction_fee: 0,
    }),
    usageLimits: () => ({}),
});

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Current Plan',
        href: '/clinic/settings/current-plan',
    },
];

const getUsagePercentage = (current: number, limit: number | string) => {
    if (limit === 'unlimited' || limit === -1) return 0;
    return Math.min((current / Number(limit)) * 100, 100);
};

const features = {
    basic: [
        'Dashboard Access',
        'Appointment Management',
        'Schedule Management',
    ],
    professional: [
        'Dashboard Access',
        'Appointment Management',
        'Schedule Management',
        'Analytics & Insights',
        'Appointment History',
        'Patient Records',
    ],
    'pro-plus': [
        'Dashboard Access',
        'Appointment Management',
        'Schedule Management',
        'Analytics & Insights',
        'Appointment History',
        'Patient Records',
        'Report Generation',
    ],
};

const getPlanFeatures = computed(() => {
    if (!props.currentPlan) return features.basic;
    return features[props.currentPlan.slug as keyof typeof features] || features.basic;
});

const billingCycle = computed(() => {
    return 'Monthly';
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Current Plan" />

        <SettingsLayout>
            <div class="space-y-6">
                <HeadingSmall
                    title="Current Plan"
                    description="Manage your subscription plan and view usage"
                />

                <!-- Current Plan Card -->
                <Card>
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <div>
                                <CardTitle class="flex items-center gap-2">
                                    <component 
                                        :is="currentPlan?.slug === 'basic' ? Zap : Crown" 
                                        class="h-5 w-5"
                                    />
                                    {{ currentPlan?.name || 'Basic' }} Plan
                                </CardTitle>
                                <CardDescription>
                                    Billed {{ billingCycle }}
                                    <template v-if="currentPlan && currentPlan.slug !== 'basic'">
                                        • ₱{{ currentPlan.price }}/{{ billingCycle === 'Monthly' ? 'month' : 'year' }}
                                    </template>
                                </CardDescription>
                            </div>
                            <Badge 
                                :variant="currentPlan?.slug === 'basic' ? 'secondary' : 'default'"
                                class="text-sm"
                            >
                                {{ currentPlan?.slug === 'basic' ? 'Free Plan' : 'Active' }}
                            </Badge>
                        </div>
                    </CardHeader>
                    <CardContent class="space-y-6">
                        <!-- Usage Stats -->
                        <div v-if="stats" class="space-y-4">
                            <h4 class="text-sm font-semibold">Usage Statistics</h4>
                            <div class="grid gap-4 md:grid-cols-2">
                                <div class="space-y-2">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-muted-foreground">Services Registered</span>
                                        <span class="font-medium">
                                            {{ stats.services_count || 0 }} / {{ stats.services_limit === -1 ? '∞' : (stats.services_limit || 0) }}
                                        </span>
                                    </div>
                                    <Progress 
                                        v-if="stats.services_limit !== 'unlimited' && stats.services_limit !== -1"
                                        :model-value="getUsagePercentage(stats.services_count || 0, stats.services_limit || 0)" 
                                        class="h-2"
                                    />
                                    <div v-else class="h-2 rounded-full bg-primary/20"></div>
                                </div>
                                <div class="space-y-2">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-muted-foreground">Vets</span>
                                        <span class="font-medium">
                                            {{ stats.vets_count || 0 }} / {{ stats.vets_limit === -1 ? '∞' : (stats.vets_limit || 0) }}
                                        </span>
                                    </div>
                                    <Progress 
                                        v-if="stats.vets_limit !== 'unlimited' && stats.vets_limit !== -1"
                                        :model-value="getUsagePercentage(stats.vets_count || 0, stats.vets_limit || 0)" 
                                        class="h-2"
                                    />
                                    <div v-else class="h-2 rounded-full bg-primary/20"></div>
                                </div>
                            </div>
                            <div class="pt-2">
                                <div class="flex justify-between text-sm">
                                    <span class="text-muted-foreground">Transaction Fee</span>
                                    <span class="font-medium">{{ stats.transaction_fee || 0 }}%</span>
                                </div>
                            </div>
                        </div>

                        <!-- Features -->
                        <div class="space-y-4">
                            <h4 class="text-sm font-semibold">Plan Features</h4>
                            <ul class="space-y-2">
                                <li 
                                    v-for="feature in getPlanFeatures" 
                                    :key="feature"
                                    class="flex items-start gap-2 text-sm"
                                >
                                    <Check class="h-4 w-4 mt-0.5 text-primary flex-shrink-0" />
                                    <span>{{ feature }}</span>
                                </li>
                            </ul>
                        </div>

                        <!-- Actions -->
                        <div class="flex gap-3 pt-4 border-t">
                            <Button 
                                v-if="!currentPlan || currentPlan.slug === 'basic'"
                                as-child
                            >
                                <a href="/subscription">Upgrade Plan</a>
                            </Button>
                            <Button 
                                v-else
                                as-child
                                variant="outline"
                            >
                                <a href="/subscription">Change Plan</a>
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
