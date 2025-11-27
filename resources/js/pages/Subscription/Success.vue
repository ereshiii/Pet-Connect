<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { CheckCircle, ArrowRight } from 'lucide-vue-next';

interface Props {
    planName: string;
    amount: number;
    billingCycle: string;
    transactionId?: string;
    redirectTo?: string;
}

const props = defineProps<Props>();

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
        minimumFractionDigits: 2,
    }).format(amount);
};
</script>

<template>
    <Head title="Payment Successful" />

    <AppLayout>
        <div class="flex h-full flex-1 items-center justify-center p-6">
            <div class="max-w-md w-full space-y-8 text-center">
                <!-- Success Icon -->
                <div class="flex justify-center">
                    <div class="rounded-full bg-green-100 dark:bg-green-900/20 p-6">
                        <CheckCircle class="h-16 w-16 text-green-600 dark:text-green-400" />
                    </div>
                </div>

                <!-- Success Message -->
                <div class="space-y-2">
                    <h1 class="text-3xl font-bold">Payment Successful!</h1>
                    <p class="text-muted-foreground">
                        Your subscription to <span class="font-medium text-foreground">{{ planName }}</span> has been activated.
                    </p>
                </div>

                <!-- Payment Details -->
                <div class="rounded-lg border bg-card p-6 space-y-3 text-left">
                    <div class="flex justify-between text-sm">
                        <span class="text-muted-foreground">Plan</span>
                        <span class="font-medium">{{ planName }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-muted-foreground">Billing Cycle</span>
                        <span class="font-medium capitalize">{{ billingCycle }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-muted-foreground">Amount Paid</span>
                        <span class="font-medium">{{ formatCurrency(amount) }}</span>
                    </div>
                    <div v-if="transactionId" class="flex justify-between text-sm">
                        <span class="text-muted-foreground">Transaction ID</span>
                        <span class="font-mono text-xs">{{ transactionId }}</span>
                    </div>
                </div>

                <!-- What's Next -->
                <div class="rounded-lg bg-muted p-6 text-left space-y-3">
                    <h3 class="font-medium">What's next?</h3>
                    <ul class="space-y-2 text-sm text-muted-foreground">
                        <li class="flex items-start gap-2">
                            <ArrowRight class="h-4 w-4 mt-0.5 flex-shrink-0 text-primary" />
                            <span>Access all features included in your plan</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <ArrowRight class="h-4 w-4 mt-0.5 flex-shrink-0 text-primary" />
                            <span>View your subscription details in the dashboard</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <ArrowRight class="h-4 w-4 mt-0.5 flex-shrink-0 text-primary" />
                            <span>A receipt has been sent to your email</span>
                        </li>
                    </ul>
                </div>

                <!-- Actions -->
                <div class="flex flex-col sm:flex-row gap-3">
                    <Button 
                        class="flex-1"
                        @click="$inertia.visit(redirectTo || '/subscription/dashboard')"
                    >
                        {{ redirectTo?.includes('settings') ? 'Back to Settings' : 'View Dashboard' }}
                    </Button>
                    <Button 
                        variant="outline"
                        class="flex-1"
                        @click="$inertia.visit('/clinic/dashboard')"
                    >
                        Go to Dashboard
                    </Button>
                </div>

                <!-- Support -->
                <p class="text-xs text-muted-foreground">
                    Having issues? <a href="/contact" class="text-primary hover:underline">Contact support</a>
                </p>
            </div>
        </div>
    </AppLayout>
</template>
