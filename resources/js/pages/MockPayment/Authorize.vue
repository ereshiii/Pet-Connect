<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { CheckCircle, XCircle, Smartphone, Wallet } from 'lucide-vue-next';

interface Props {
    sourceId: string;
    paymentType: string;
    callbackUrl: string;
    amount: number;
}

const props = defineProps<Props>();

const getProviderName = () => {
    const names: Record<string, string> = {
        'gcash': 'GCash',
        'grab_pay': 'GrabPay',
        'paymaya': 'Maya',
    };
    return names[props.paymentType] || 'E-Wallet';
};

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
        minimumFractionDigits: 2,
    }).format(amount);
};

const handleAuthorize = () => {
    // Simulate payment completion
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/mock-payment/complete';
    
    const statusInput = document.createElement('input');
    statusInput.type = 'hidden';
    statusInput.name = 'status';
    statusInput.value = 'success';
    
    const sourceInput = document.createElement('input');
    sourceInput.type = 'hidden';
    sourceInput.name = 'source';
    sourceInput.value = props.sourceId;
    
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '';
    
    form.appendChild(statusInput);
    form.appendChild(sourceInput);
    form.appendChild(csrfInput);
    document.body.appendChild(form);
    form.submit();
};

const handleCancel = () => {
    // Simulate payment cancellation
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/mock-payment/complete';
    
    const statusInput = document.createElement('input');
    statusInput.type = 'hidden';
    statusInput.name = 'status';
    statusInput.value = 'failed';
    
    const sourceInput = document.createElement('input');
    sourceInput.type = 'hidden';
    sourceInput.name = 'source';
    sourceInput.value = props.sourceId;
    
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '';
    
    form.appendChild(statusInput);
    form.appendChild(sourceInput);
    form.appendChild(csrfInput);
    document.body.appendChild(form);
    form.submit();
};
</script>

<template>
    <Head :title="`${getProviderName()} Payment`" />

    <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 dark:from-gray-900 dark:to-gray-800 flex items-center justify-center p-4">
        <div class="max-w-md w-full space-y-6">
            <!-- Mock Payment Notice -->
            <div class="rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 p-4 text-center">
                <p class="text-sm font-medium text-amber-900 dark:text-amber-100">
                    🧪 Mock Payment Mode
                </p>
                <p class="text-xs text-amber-700 dark:text-amber-300 mt-1">
                    This is a simulated payment page. No real money will be charged.
                </p>
            </div>

            <!-- Payment Card -->
            <div class="rounded-2xl bg-white dark:bg-gray-800 shadow-xl p-8 space-y-6">
                <!-- Provider Logo/Icon -->
                <div class="flex justify-center">
                    <div class="rounded-full bg-primary/10 p-6">
                        <Wallet class="h-12 w-12 text-primary" />
                    </div>
                </div>

                <!-- Provider Name -->
                <div class="text-center">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ getProviderName() }}
                    </h1>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        Authorize Payment
                    </p>
                </div>

                <!-- Payment Details -->
                <div class="rounded-lg bg-gray-50 dark:bg-gray-700/50 p-6 space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Merchant</span>
                        <span class="font-medium text-gray-900 dark:text-white">PetConnect</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Description</span>
                        <span class="font-medium text-gray-900 dark:text-white">Subscription</span>
                    </div>
                    <div class="border-t border-gray-200 dark:border-gray-600 pt-4">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Amount to Pay</span>
                            <span class="text-2xl font-bold text-gray-900 dark:text-white">
                                {{ formatCurrency(amount) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Instructions -->
                <div class="rounded-lg bg-blue-50 dark:bg-blue-900/20 p-4">
                    <div class="flex gap-3">
                        <Smartphone class="h-5 w-5 text-blue-600 dark:text-blue-400 mt-0.5 flex-shrink-0" />
                        <div class="space-y-1">
                            <p class="text-sm font-medium text-blue-900 dark:text-blue-100">
                                Simulated Payment
                            </p>
                            <p class="text-xs text-blue-700 dark:text-blue-300">
                                In real mode, you would be redirected to {{ getProviderName() }} app to complete the payment.
                                For testing, just click "Authorize Payment" below.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="space-y-3">
                    <Button 
                        class="w-full" 
                        size="lg"
                        @click="handleAuthorize"
                    >
                        <CheckCircle class="h-5 w-5 mr-2" />
                        Authorize Test Payment
                    </Button>
                    <Button 
                        variant="outline" 
                        class="w-full" 
                        size="lg"
                        @click="handleCancel"
                    >
                        <XCircle class="h-5 w-5 mr-2" />
                        Cancel Payment
                    </Button>
                </div>

                <!-- Security Notice -->
                <p class="text-xs text-center text-gray-500 dark:text-gray-400">
                    🔒 This is a secure test environment
                </p>
            </div>

            <!-- Help Text -->
            <div class="text-center space-y-2">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Testing payment flow for PetConnect
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-500">
                    Transaction ID: <code class="font-mono">{{ sourceId }}</code>
                </p>
            </div>
        </div>
    </div>
</template>
