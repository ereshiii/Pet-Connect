<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Switch } from '@/components/ui/switch';
import { Check, Sparkles, Zap } from 'lucide-vue-next';
import PaymentModal from '@/components/PaymentModal.vue';

interface SubscriptionPlan {
    id: number;
    name: string;
    slug: string;
    description: string;
    price: number;
    annual_price: number;
    features: string[];
    limits: Record<string, any>;
    trial_days: number;
    is_active: boolean;
}

interface SavedPaymentMethod {
    type: string;
    last_four: string;
}

interface Props {
    plans: SubscriptionPlan[];
    currentPlan: SubscriptionPlan | null;
    hasActiveSubscription: boolean;
    savedPaymentMethod: SavedPaymentMethod | null;
}

const props = defineProps<Props>();

const isAnnual = ref(false);
const showPaymentModal = ref(false);
const selectedPlan = ref<SubscriptionPlan | null>(null);

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(amount);
};

const getPlanPrice = (plan: SubscriptionPlan) => {
    return isAnnual.value ? plan.annual_price : plan.price;
};

const getSavings = (plan: SubscriptionPlan) => {
    const monthlyTotal = plan.price * 12;
    const savings = monthlyTotal - plan.annual_price;
    return savings;
};

const isCurrentPlan = (plan: SubscriptionPlan) => {
    return props.currentPlan?.slug === plan.slug;
};

const handleSelectPlan = (plan: SubscriptionPlan) => {
    if (plan.price === 0) {
        // Free plan - subscribe directly
        router.post('/payment/process', {
            plan_slug: plan.slug,
            billing_cycle: 'monthly',
            payment_method: 'free',
        });
    } else {
        // Paid plan - show payment modal
        selectedPlan.value = plan;
        showPaymentModal.value = true;
    }
};

const getFeatureLabel = (feature: string) => {
    const labels: Record<string, string> = {
        'dashboard': 'Dashboard Access',
        'appointments': 'Appointment Management',
        'schedule_management': 'Schedule Management',
        'basic_patient_management': 'Patient Management',
        'analytics': 'Analytics & Insights',
        'history': 'Appointment History',
        'patient_records': 'Patient Records',
        'report_generation': 'Report Generation',
        'unlimited_appointments': 'Unlimited Appointments',
        'advanced_scheduling': 'Advanced Scheduling',
        'staff_management': 'Staff Management',
        'unlimited_staff': 'Unlimited Staff',
        'unlimited_services': 'Unlimited Services',
        'detailed_analytics': 'Detailed Analytics',
        'multiple_veterinarians': 'Multiple Veterinarians',
        'custom_forms': 'Custom Forms',
        'priority_listing': 'Priority Listing',
        'inventory_management': 'Inventory Management',
        'automated_reminders': 'Automated Reminders',
        'financial_reporting': 'Financial Reporting',
        'advanced_search': 'Advanced Search',
        'bulk_operations': 'Bulk Operations',
    };
    return labels[feature] || feature.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
};
</script>

<template>
    <Head title="Subscription Plans" />

    <AppLayout>
        <div class="flex h-full flex-1 flex-col gap-4 md:gap-6 rounded-xl p-3 md:p-6">
            <!-- Header -->
            <div class="text-center space-y-3 md:space-y-4">
                <div class="flex items-center justify-center gap-1.5 md:gap-2">
                    <Sparkles class="h-6 w-6 md:h-8 md:w-8 text-primary" />
                    <h1 class="text-xl md:text-3xl font-bold">Choose Your Plan</h1>
                </div>
                <p class="text-xs md:text-sm text-muted-foreground max-w-2xl mx-auto px-4">
                    Unlock powerful features to grow your veterinary practice
                </p>

                <!-- Billing Toggle -->
                <div class="flex items-center justify-center gap-2 md:gap-3 mt-4 md:mt-6">
                    <button 
                        @click="isAnnual = false"
                        :class="[
                            'transition-colors text-xs md:text-sm',
                            !isAnnual ? 'font-semibold text-foreground' : 'text-muted-foreground hover:text-foreground'
                        ]"
                    >
                        Monthly
                    </button>
                    <Switch v-model:checked="isAnnual" />
                    <button 
                        @click="isAnnual = true"
                        :class="[
                            'transition-colors text-xs md:text-sm',
                            isAnnual ? 'font-semibold text-foreground' : 'text-muted-foreground hover:text-foreground'
                        ]"
                    >
                        Annual
                    </button>
                    <Badge v-if="isAnnual" variant="secondary" class="ml-1 md:ml-2 text-[10px] md:text-xs">
                        Save up to 20%
                    </Badge>
                </div>
            </div>

            <!-- Plans Grid -->
            <div class="grid md:grid-cols-3 gap-4 md:gap-6 max-w-7xl mx-auto w-full mt-4 md:mt-8">
                <div 
                    v-for="plan in plans" 
                    :key="plan.id"
                    class="relative rounded-xl border bg-card p-4 md:p-8 shadow-sm transition-all hover:shadow-lg flex flex-col"
                    :class="{
                        'ring-2 ring-primary': isCurrentPlan(plan),
                        'border-primary': plan.slug === 'pro-plus',
                    }"
                >
                    <!-- Current Plan Badge -->
                    <Badge 
                        v-if="isCurrentPlan(plan)" 
                        class="absolute -top-2 md:-top-3 right-4 md:right-6 text-[10px] md:text-xs"
                        variant="default"
                    >
                        Current Plan
                    </Badge>

                    <!-- Popular Badge -->
                    <Badge 
                        v-if="plan.slug === 'pro-plus'" 
                        class="absolute -top-2 md:-top-3 left-4 md:left-6 text-[10px] md:text-xs"
                        variant="secondary"
                    >
                        <Zap class="h-2 w-2 md:h-3 md:w-3 mr-1" />
                        Most Popular
                    </Badge>

                    <!-- Plan Header -->
                    <div class="space-y-1.5 md:space-y-2 mb-4 md:mb-6">
                        <h3 class="text-lg md:text-2xl font-bold">{{ plan.name }}</h3>
                        <p class="text-muted-foreground text-xs md:text-sm">{{ plan.description }}</p>
                    </div>

                    <!-- Pricing -->
                    <div class="mb-4 md:mb-6">
                        <div class="flex items-baseline gap-1">
                            <span class="text-2xl md:text-4xl font-bold">{{ formatCurrency(getPlanPrice(plan)) }}</span>
                            <span class="text-xs md:text-sm text-muted-foreground">/ {{ isAnnual ? 'year' : 'month' }}</span>
                        </div>
                        <p v-if="isAnnual && plan.annual_price > 0" class="text-xs md:text-sm text-green-600 dark:text-green-400 mt-1">
                            Save {{ formatCurrency(getSavings(plan)) }} per year
                        </p>
                        <p v-if="plan.trial_days > 0" class="text-xs md:text-sm text-primary mt-1">
                            {{ plan.trial_days }}-day free trial
                        </p>
                    </div>

                    <!-- Features -->
                    <div class="space-y-2 md:space-y-3 mb-6 md:mb-8 flex-grow">
                        <p class="text-xs md:text-sm font-medium">Features included:</p>
                        <ul class="space-y-1.5 md:space-y-2">
                            <li 
                                v-for="feature in plan.features" 
                                :key="feature"
                                class="flex items-start gap-1.5 md:gap-2 text-xs md:text-sm"
                            >
                                <Check class="h-3 w-3 md:h-4 md:w-4 text-primary mt-0.5 flex-shrink-0" />
                                <span>{{ getFeatureLabel(feature) }}</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Limits -->
                    <div v-if="plan.limits" class="space-y-1.5 md:space-y-2 mb-6 md:mb-8 text-xs md:text-sm text-muted-foreground">
                        <p v-if="typeof plan.limits === 'object' && plan.limits.max_staff_accounts !== undefined">
                            <span v-if="plan.limits.max_staff_accounts !== -1">
                                {{ plan.limits.max_staff_accounts }} staff account{{ plan.limits.max_staff_accounts > 1 ? 's' : '' }}
                            </span>
                            <span v-else>
                                Unlimited staff accounts
                            </span>
                        </p>
                        <p v-if="typeof plan.limits === 'object' && plan.limits.max_services !== undefined">
                            <span v-if="plan.limits.max_services !== -1">
                                Up to {{ plan.limits.max_services }} service{{ plan.limits.max_services > 1 ? 's' : '' }}
                            </span>
                            <span v-else>
                                Unlimited services
                            </span>
                        </p>
                    </div>

                    <!-- CTA Button -->
                    <Button 
                        class="w-full"
                        :variant="plan.slug === 'pro-plus' ? 'default' : 'outline'"
                        :disabled="isCurrentPlan(plan)"
                        @click="handleSelectPlan(plan)"
                    >
                        {{ isCurrentPlan(plan) ? 'Current Plan' : plan.price === 0 ? 'Get Started' : 'Subscribe Now' }}
                    </Button>
                </div>
            </div>

            <!-- Feature Comparison Note -->
            <div class="text-center mt-12 text-sm text-muted-foreground max-w-2xl mx-auto">
                <p>All plans include secure hosting, regular updates, and technical support.</p>
                <p class="mt-2">Need a custom solution? <a href="/contact" class="text-primary hover:underline">Contact us</a></p>
            </div>
        </div>

        <!-- Payment Modal -->
        <PaymentModal 
            v-if="selectedPlan"
            v-model:open="showPaymentModal"
            :plan-slug="selectedPlan.slug"
            :amount="isAnnual ? selectedPlan.annual_price : selectedPlan.price"
            :billing-cycle="isAnnual ? 'annual' : 'monthly'"
            :public-key="$page.props.auth.paymongoPublicKey || ''"
            :saved-payment-method="savedPaymentMethod"
            @success="router.visit('/subscription/dashboard')"
        />
    </AppLayout>
</template>
