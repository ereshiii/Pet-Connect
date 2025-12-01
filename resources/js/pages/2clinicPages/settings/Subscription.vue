<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/clinicSettings/Layout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import Progress from '@/components/ui/Progress.vue';
import { type BreadcrumbItem } from '@/types';
import { 
    Calendar, 
    Users, 
    TrendingUp, 
    AlertCircle, 
    Crown,
    CheckCircle2,
    XCircle
} from 'lucide-vue-next';

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
        title: 'Manage Subscription',
        href: '/clinic/settings/subscription',
    },
];

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
        minimumFractionDigits: 0,
    }).format(amount);
};

const formatDate = (date: string | null) => {
    if (!date) return 'N/A';
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const getUsagePercentage = (current: number, limit: number | string) => {
    if (limit === 'unlimited' || limit === -1) return 0;
    return Math.min((current / Number(limit)) * 100, 100);
};

const isTrialing = () => {
    return props.subscription?.stripe_status === 'trialing';
};

const isCanceled = () => {
    return props.subscription?.ends_at !== null;
};

const isProPlan = () => {
    return props.currentPlan?.slug === 'pro-plus';
};

const handleCancel = () => {
    if (confirm('Are you sure you want to cancel your subscription? You will still have access until the end of your billing period.')) {
        router.post('/subscription/cancel');
    }
};

const handleResume = () => {
    router.post('/subscription/resume');
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Manage Subscription" />

        <SettingsLayout>
            <div class="space-y-4 md:space-y-6">
                <!-- Header -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <h2 class="text-lg md:text-2xl font-semibold">Subscription Dashboard</h2>
                        <p class="text-xs md:text-sm text-muted-foreground">Manage your clinic subscription and features</p>
                    </div>
                    <Button 
                        v-if="!isProPlan()" 
                        @click="router.visit('/subscription')"
                        class="w-full sm:w-auto"
                    >
                        <Crown class="h-3 w-3 md:h-4 md:w-4 mr-2" />
                        Upgrade to Pro Plus
                    </Button>
                </div>

                <!-- Current Plan Card -->
                <div class="rounded-lg border bg-card p-3 md:p-6">
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3 mb-4">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <h3 class="text-base md:text-xl font-semibold">{{ currentPlan?.name || 'Free Plan' }}</h3>
                                <Badge :variant="isProPlan() ? 'default' : 'secondary'" class="text-[10px] md:text-xs px-2">
                                    {{ isProPlan() ? 'Pro Plus' : 'Basic' }}
                                </Badge>
                            </div>
                            <p class="text-xl md:text-2xl font-bold text-primary">
                                {{ currentPlan ? formatCurrency(currentPlan.price) : '₱0' }}
                                <span class="text-xs md:text-sm font-normal text-muted-foreground">/month</span>
                            </p>
                        </div>
                        
                        <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                            <Button 
                                v-if="subscription && !isCanceled()" 
                                variant="outline" 
                                size="sm"
                                @click="handleCancel"
                                class="text-xs w-full sm:w-auto"
                            >
                                Cancel Subscription
                            </Button>
                            <Button 
                                v-if="isCanceled()" 
                                variant="default" 
                                size="sm"
                                @click="handleResume"
                                class="text-xs w-full sm:w-auto"
                            >
                                Resume Subscription
                            </Button>
                        </div>
                    </div>

                    <!-- Status Alerts -->
                    <div v-if="isTrialing()" class="flex items-center gap-2 rounded-lg bg-blue-50 dark:bg-blue-950/20 p-2 md:p-3 mb-3 md:mb-4">
                        <AlertCircle class="h-4 w-4 md:h-5 md:w-5 text-blue-600 dark:text-blue-400" />
                        <div class="flex-1">
                            <p class="text-xs md:text-sm font-medium text-blue-900 dark:text-blue-100">
                                Trial Period Active
                            </p>
                            <p class="text-[10px] md:text-xs text-blue-700 dark:text-blue-300">
                                Your trial ends on {{ formatDate(subscription?.trial_ends_at || null) }}
                            </p>
                        </div>
                    </div>

                    <div v-if="isCanceled()" class="flex items-center gap-2 rounded-lg bg-orange-50 dark:bg-orange-950/20 p-2 md:p-3 mb-3 md:mb-4">
                        <XCircle class="h-4 w-4 md:h-5 md:w-5 text-orange-600 dark:text-orange-400" />
                        <div class="flex-1">
                            <p class="text-xs md:text-sm font-medium text-orange-900 dark:text-orange-100">
                                Subscription Ending
                            </p>
                            <p class="text-[10px] md:text-xs text-orange-700 dark:text-orange-300">
                                Access continues until {{ formatDate(subscription?.ends_at || null) }}
                            </p>
                        </div>
                    </div>

                    <!-- Subscription Details -->
                    <div class="grid grid-cols-2 gap-3 md:gap-4 text-xs md:text-sm">
                        <div>
                            <p class="text-muted-foreground">Status</p>
                            <div class="flex items-center gap-1.5 md:gap-2 mt-1">
                                <CheckCircle2 v-if="!isCanceled()" class="h-3 w-3 md:h-4 md:w-4 text-green-600" />
                                <XCircle v-else class="h-3 w-3 md:h-4 md:w-4 text-orange-600" />
                                <span class="font-medium capitalize">{{ subscription?.stripe_status || 'free' }}</span>
                            </div>
                        </div>
                        <div>
                            <p class="text-muted-foreground">Transaction Fee</p>
                            <p class="font-medium mt-1">{{ stats?.transaction_fee || 0 }}%</p>
                        </div>
                    </div>
                </div>

                <!-- Usage Stats -->
                <div class="grid md:grid-cols-2 gap-4 md:gap-6">
                    <!-- Services Usage -->
                    <div class="rounded-lg border bg-card p-3 md:p-6">
                        <div class="flex items-center gap-2 md:gap-3 mb-3 md:mb-4">
                            <div class="p-1.5 md:p-2 rounded-lg bg-primary/10">
                                <Calendar class="h-4 w-4 md:h-5 md:w-5 text-primary" />
                            </div>
                            <div class="flex-1">
                                <h3 class="text-sm md:text-base font-medium">Services Registered</h3>
                                <p class="text-xs md:text-sm text-muted-foreground">
                                    {{ stats?.services_count || 0 }} of {{ stats?.services_limit === -1 ? 'unlimited' : (stats?.services_limit || 0) }}
                                </p>
                            </div>
                        </div>
                        
                        <Progress 
                            v-if="stats?.services_limit !== 'unlimited' && stats?.services_limit !== -1"
                            :model-value="getUsagePercentage(stats?.services_count || 0, stats?.services_limit || 0)" 
                            class="h-2"
                        />
                        <div v-else class="h-2 rounded-full bg-primary/20"></div>

                        <p v-if="!isProPlan() && stats?.services_limit !== 'unlimited'" class="text-[10px] md:text-xs text-muted-foreground mt-2">
                            Upgrade to Pro Plus for more services
                        </p>
                    </div>

                    <!-- Vets Usage -->
                    <div class="rounded-lg border bg-card p-3 md:p-6">
                        <div class="flex items-center gap-2 md:gap-3 mb-3 md:mb-4">
                            <div class="p-1.5 md:p-2 rounded-lg bg-primary/10">
                                <Users class="h-4 w-4 md:h-5 md:w-5 text-primary" />
                            </div>
                            <div class="flex-1">
                                <h3 class="text-sm md:text-base font-medium">Vets</h3>
                                <p class="text-xs md:text-sm text-muted-foreground">
                                    {{ stats?.vets_count || 0 }} of {{ stats?.vets_limit === -1 ? 'unlimited' : (stats?.vets_limit || 0) }}
                                </p>
                            </div>
                        </div>
                        
                        <Progress 
                            v-if="stats?.vets_limit !== 'unlimited' && stats?.vets_limit !== -1"
                            :model-value="getUsagePercentage(stats?.vets_count || 0, stats?.vets_limit || 0)" 
                            class="h-2"
                        />
                        <div v-else class="h-2 rounded-full bg-primary/20"></div>

                        <p v-if="!isProPlan() && stats?.vets_limit !== 'unlimited'" class="text-[10px] md:text-xs text-muted-foreground mt-2">
                            Upgrade to Pro Plus for multiple vets
                        </p>
                    </div>
                </div>

                <!-- Feature Access -->
                <div class="rounded-lg border bg-card p-3 md:p-6">
                    <div class="flex items-center gap-2 md:gap-3 mb-4 md:mb-6">
                        <TrendingUp class="h-4 w-4 md:h-5 md:w-5 text-primary" />
                        <h3 class="text-sm md:text-base font-semibold">Feature Access</h3>
                    </div>

                    <div class="grid md:grid-cols-2 gap-3 md:gap-4">
                        <div class="flex items-start gap-2 md:gap-3">
                            <CheckCircle2 
                                :class="usageLimits?.reports ? 'text-green-600' : 'text-muted-foreground'" 
                                class="h-4 w-4 md:h-5 md:w-5 mt-0.5"
                            />
                            <div class="flex-1">
                                <p class="text-xs md:text-sm font-medium">Report Generation</p>
                                <p class="text-[10px] md:text-xs text-muted-foreground">
                                    {{ usageLimits?.reports ? 'Available' : 'Pro Plus only' }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start gap-2 md:gap-3">
                            <CheckCircle2 
                                :class="usageLimits?.analytics ? 'text-green-600' : 'text-muted-foreground'" 
                                class="h-4 w-4 md:h-5 md:w-5 mt-0.5"
                            />
                            <div class="flex-1">
                                <p class="text-xs md:text-sm font-medium">Analytics Dashboard</p>
                                <p class="text-[10px] md:text-xs text-muted-foreground">
                                    {{ usageLimits?.analytics ? 'Available' : 'Pro Plus only' }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start gap-2 md:gap-3">
                            <CheckCircle2 
                                :class="usageLimits?.multiple_vets ? 'text-green-600' : 'text-muted-foreground'" 
                                class="h-4 w-4 md:h-5 md:w-5 mt-0.5"
                            />
                            <div class="flex-1">
                                <p class="text-xs md:text-sm font-medium">Multiple Veterinarians</p>
                                <p class="text-[10px] md:text-xs text-muted-foreground">
                                    {{ usageLimits?.multiple_vets ? 'Available' : 'Pro Plus only' }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start gap-2 md:gap-3">
                            <CheckCircle2 class="h-4 w-4 md:h-5 md:w-5 mt-0.5 text-green-600" />
                            <div class="flex-1">
                                <p class="text-xs md:text-sm font-medium">Appointment Booking</p>
                                <p class="text-[10px] md:text-xs text-muted-foreground">Available</p>
                            </div>
                        </div>
                    </div>

                    <div v-if="!isProPlan()" class="mt-4 md:mt-6 pt-4 md:pt-6 border-t">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div>
                                <p class="text-xs md:text-sm font-medium">Want more features?</p>
                                <p class="text-[10px] md:text-xs text-muted-foreground">Upgrade to Pro Plus for advanced capabilities</p>
                            </div>
                            <Button @click="router.visit('/subscription')" class="w-full sm:w-auto">
                                Upgrade Now
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
