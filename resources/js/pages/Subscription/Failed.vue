<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { XCircle, RefreshCw, ArrowLeft } from 'lucide-vue-next';

interface Props {
    error?: string;
    planName?: string;
    amount?: number;
}

const props = defineProps<Props>();

const formatCurrency = (amount?: number) => {
    if (!amount) return '';
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
        minimumFractionDigits: 2,
    }).format(amount);
};

const getErrorMessage = () => {
    if (props.error) return props.error;
    return 'Your payment could not be processed. Please try again or use a different payment method.';
};
</script>

<template>
    <Head title="Payment Failed" />

    <AppLayout>
        <div class="flex h-full flex-1 items-center justify-center p-6">
            <div class="max-w-md w-full space-y-8 text-center">
                <!-- Error Icon -->
                <div class="flex justify-center">
                    <div class="rounded-full bg-red-100 dark:bg-red-900/20 p-6">
                        <XCircle class="h-16 w-16 text-red-600 dark:text-red-400" />
                    </div>
                </div>

                <!-- Error Message -->
                <div class="space-y-2">
                    <h1 class="text-3xl font-bold">Payment Failed</h1>
                    <p class="text-muted-foreground">
                        {{ getErrorMessage() }}
                    </p>
                </div>

                <!-- Payment Details (if available) -->
                <div v-if="planName && amount" class="rounded-lg border bg-card p-6 space-y-3 text-left">
                    <div class="flex justify-between text-sm">
                        <span class="text-muted-foreground">Attempted Plan</span>
                        <span class="font-medium">{{ planName }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-muted-foreground">Amount</span>
                        <span class="font-medium">{{ formatCurrency(amount) }}</span>
                    </div>
                </div>

                <!-- Common Reasons -->
                <div class="rounded-lg bg-muted p-6 text-left space-y-3">
                    <h3 class="font-medium">Common reasons for payment failure:</h3>
                    <ul class="space-y-2 text-sm text-muted-foreground list-disc list-inside">
                        <li>Insufficient funds in your account</li>
                        <li>Card has expired or been declined</li>
                        <li>Incorrect card details entered</li>
                        <li>Payment method not supported</li>
                        <li>Transaction was flagged by your bank</li>
                    </ul>
                </div>

                <!-- Actions -->
                <div class="flex flex-col sm:flex-row gap-3">
                    <Button 
                        class="flex-1"
                        @click="$inertia.visit('/subscription')"
                    >
                        <RefreshCw class="h-4 w-4 mr-2" />
                        Try Again
                    </Button>
                    <Button 
                        variant="outline"
                        class="flex-1"
                        @click="$inertia.visit('/clinic/dashboard')"
                    >
                        <ArrowLeft class="h-4 w-4 mr-2" />
                        Go Back
                    </Button>
                </div>

                <!-- Support -->
                <div class="space-y-2">
                    <p class="text-sm text-muted-foreground">
                        Need help? Our support team is here to assist you.
                    </p>
                    <Button 
                        variant="link" 
                        size="sm"
                        @click="$inertia.visit('/contact')"
                    >
                        Contact Support
                    </Button>
                </div>

                <!-- Payment Method Alternatives -->
                <div class="pt-6 border-t">
                    <p class="text-xs text-muted-foreground">
                        Having trouble with your payment method? Try using:
                    </p>
                    <div class="flex justify-center gap-4 mt-3">
                        <span class="text-xs font-medium">GCash</span>
                        <span class="text-xs text-muted-foreground">•</span>
                        <span class="text-xs font-medium">Credit/Debit Card</span>
                        <span class="text-xs text-muted-foreground">•</span>
                        <span class="text-xs font-medium">GrabPay</span>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
