<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/clinicSettings/Layout.vue';
import { type BreadcrumbItem } from '@/types';
import { CreditCard, Plus, Trash2 } from 'lucide-vue-next';

interface PaymentMethod {
    id: number;
    type: string;
    last_four: string;
    brand: string | null;
    exp_month: string | null;
    exp_year: string | null;
    is_default: boolean;
    created_at: string;
}

interface Props {
    paymentMethods?: PaymentMethod[];
}

const props = withDefaults(defineProps<Props>(), {
    paymentMethods: () => [],
});

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Payment Methods',
        href: '/clinic/settings/payment-methods',
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Payment Methods" />

        <SettingsLayout>
            <div class="space-y-6">
                <HeadingSmall
                    title="Payment Methods"
                    description="Manage your payment methods for subscriptions"
                />

                <div class="space-y-4">
                    <!-- Add Payment Method Button -->
                    <Button>
                        <Plus class="h-4 w-4 mr-2" />
                        Add Payment Method
                    </Button>

                    <!-- Payment Methods List -->
                    <div v-if="paymentMethods.length > 0" class="space-y-3">
                        <Card v-for="method in paymentMethods" :key="method.id">
                            <CardHeader>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="p-2 rounded-lg bg-muted">
                                            <CreditCard class="h-5 w-5" />
                                        </div>
                                        <div>
                                            <CardTitle class="text-base">
                                                {{ method.brand || method.type.toUpperCase() }} •••• {{ method.last_four }}
                                            </CardTitle>
                                            <CardDescription>
                                                <template v-if="method.exp_month && method.exp_year">
                                                    Expires {{ method.exp_month }}/{{ method.exp_year }}
                                                </template>
                                                <template v-else>
                                                    {{ method.type.toUpperCase() }} Payment Method
                                                </template>
                                            </CardDescription>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <Badge v-if="method.is_default" variant="secondary">
                                            Default
                                        </Badge>
                                        <Button 
                                            v-if="!method.is_default"
                                            variant="outline" 
                                            size="sm"
                                        >
                                            Set as Default
                                        </Button>
                                        <Button 
                                            variant="ghost" 
                                            size="icon"
                                            class="text-destructive"
                                        >
                                            <Trash2 class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </div>
                            </CardHeader>
                        </Card>
                    </div>

                    <!-- Empty State -->
                    <Card v-else>
                        <CardContent class="flex flex-col items-center justify-center py-12">
                            <CreditCard class="h-12 w-12 text-muted-foreground mb-4" />
                            <p class="text-sm text-muted-foreground text-center">
                                No payment methods added yet.<br />
                                Add a payment method to manage your subscription.
                            </p>
                        </CardContent>
                    </Card>
                </div>

                <!-- Info Box -->
                <Card>
                    <CardHeader>
                        <CardTitle class="text-sm">Secure Payment Processing</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p class="text-sm text-muted-foreground">
                            All payments are securely processed through PayMongo. We never store your full card details on our servers.
                        </p>
                    </CardContent>
                </Card>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
