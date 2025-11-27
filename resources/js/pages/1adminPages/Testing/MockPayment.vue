<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { adminDashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { CreditCard, AlertCircle, CheckCircle2 } from 'lucide-vue-next';
import { ref } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin Dashboard', href: adminDashboard().url },
    { title: 'Testing Tools', href: '#' },
    { title: 'Mock Payment System', href: '#' },
];

const testCards = [
    { number: '4120000000000007', type: 'Visa', result: 'Success' },
    { number: '4571736000000075', type: 'Visa Debit', result: 'Success' },
    { number: '5339080000000003', type: 'Mastercard', result: 'Success' },
    { number: '4120000000000000', type: 'Visa', result: 'Generic Decline' },
    { number: '4120000000000001', type: 'Visa', result: 'Insufficient Funds' },
];

const paymentForm = useForm({
    card_number: '',
    amount: '',
    description: '',
});

const submitPayment = () => {
    paymentForm.post('/admin/testing/mock-payment', {
        onSuccess: () => {
            paymentForm.reset();
        },
    });
};
</script>

<template>
    <Head title="Mock Payment System" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div>
                <h1 class="text-2xl font-semibold flex items-center gap-2">
                    <CreditCard class="h-6 w-6" />
                    Mock Payment System
                </h1>
                <p class="text-muted-foreground">Test payment flows without real transactions</p>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <!-- Test Cards Reference -->
                <div class="rounded-lg border bg-card p-6">
                    <h2 class="text-lg font-semibold mb-4">Test Card Numbers</h2>
                    <div class="space-y-3">
                        <div v-for="card in testCards" :key="card.number" class="flex items-center justify-between p-3 rounded-lg border">
                            <div>
                                <p class="font-mono text-sm">{{ card.number }}</p>
                                <p class="text-xs text-muted-foreground">{{ card.type }}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <component :is="card.result === 'Success' ? CheckCircle2 : AlertCircle" 
                                           :class="card.result === 'Success' ? 'text-green-600' : 'text-red-600'" 
                                           class="h-4 w-4" />
                                <span class="text-xs">{{ card.result }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Form -->
                <div class="rounded-lg border bg-card p-6">
                    <h2 class="text-lg font-semibold mb-4">Test Payment</h2>
                    <form @submit.prevent="submitPayment" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">Card Number</label>
                            <input v-model="paymentForm.card_number" type="text" class="form-input w-full" placeholder="4120000000000007" />
                            <p v-if="paymentForm.errors.card_number" class="text-xs text-red-600 mt-1">{{ paymentForm.errors.card_number }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">Amount (₱)</label>
                            <input v-model="paymentForm.amount" type="number" step="0.01" class="form-input w-full" placeholder="599.00" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">Description</label>
                            <input v-model="paymentForm.description" type="text" class="form-input w-full" placeholder="Test payment" />
                        </div>
                        <button type="submit" :disabled="paymentForm.processing" class="btn btn-primary w-full">
                            {{ paymentForm.processing ? 'Processing...' : 'Process Test Payment' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
