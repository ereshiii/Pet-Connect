<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/clinicSettings/Layout.vue';
import { type BreadcrumbItem } from '@/types';
import { Receipt, Download, CheckCircle, XCircle } from 'lucide-vue-next';

interface BillingRecord {
    id: number;
    invoice_number: string;
    amount: number;
    status: string;
    payment_method: string | null;
    created_at: string;
    description: string;
}

interface Props {
    billingHistory?: BillingRecord[];
}

const props = withDefaults(defineProps<Props>(), {
    billingHistory: () => [],
});

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Billing History',
        href: '/clinic/settings/billing-history',
    },
];

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const formatAmount = (amount: number) => {
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
        minimumFractionDigits: 0,
    }).format(amount);
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Billing History" />

        <SettingsLayout>
            <div class="space-y-6">
                <HeadingSmall
                    title="Billing History"
                    description="View and download your past invoices"
                />

                <div v-if="billingHistory.length > 0" class="space-y-3">
                    <Card v-for="invoice in billingHistory" :key="invoice.id">
                        <CardHeader>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 rounded-lg bg-muted">
                                        <Receipt class="h-5 w-5" />
                                    </div>
                                    <div>
                                        <CardTitle class="text-base">
                                            {{ invoice.description }}
                                        </CardTitle>
                                        <CardDescription>
                                            {{ formatDate(invoice.created_at) }} • {{ invoice.invoice_number }}
                                        </CardDescription>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="text-right">
                                        <p class="text-lg font-semibold">{{ formatAmount(invoice.amount) }}</p>
                                        <Badge 
                                            :variant="invoice.status === 'paid' || invoice.status === 'completed' ? 'default' : 'destructive'"
                                            class="mt-1"
                                        >
                                            <component 
                                                :is="invoice.status === 'paid' || invoice.status === 'completed' ? CheckCircle : XCircle" 
                                                class="h-3 w-3 mr-1"
                                            />
                                            {{ invoice.status === 'paid' || invoice.status === 'completed' ? 'Paid' : invoice.status }}
                                        </Badge>
                                    </div>
                                    <Button variant="outline" size="sm">
                                        <Download class="h-4 w-4 mr-2" />
                                        Download
                                    </Button>
                                </div>
                            </div>
                        </CardHeader>
                    </Card>
                </div>

                <!-- Empty State -->
                <Card v-else>
                    <CardContent class="flex flex-col items-center justify-center py-12">
                        <Receipt class="h-12 w-12 text-muted-foreground mb-4" />
                        <p class="text-sm text-muted-foreground text-center">
                            No billing history available yet.<br />
                            Your invoices will appear here once you subscribe to a plan.
                        </p>
                    </CardContent>
                </Card>

                <!-- Billing Info -->
                <Card>
                    <CardHeader>
                        <CardTitle class="text-sm">Billing Information</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p class="text-sm text-muted-foreground mb-3">
                            Invoices are generated automatically on your billing date each month/year.
                        </p>
                        <p class="text-sm text-muted-foreground">
                            For billing inquiries, please contact our support team.
                        </p>
                    </CardContent>
                </Card>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
