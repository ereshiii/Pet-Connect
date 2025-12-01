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
            <div class="space-y-4 md:space-y-6">
                <HeadingSmall
                    title="Payment Methods"
                    description="Manage your payment methods for subscriptions"
                />

                <div class="space-y-3 md:space-y-4">
                    <!-- Add Payment Method Button -->
                    <Button class="w-full sm:w-auto">
                        <Plus class="h-3 w-3 md:h-4 md:w-4 mr-2" />
                        Add Payment Method
                    </Button>

                    <!-- Payment Methods List -->
                    <div v-if="paymentMethods.length > 0" class="space-y-2 md:space-y-3">
                        <Card v-for="method in paymentMethods" :key="method.id">
                            <CardHeader class="p-3 md:p-6">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                    <div class="flex items-center gap-2 md:gap-3">
                                        <div class="p-1.5 md:p-2 rounded-lg bg-muted">
                                            <CreditCard class="h-4 w-4 md:h-5 md:w-5" />
                                        </div>
                                        <div>
                                            <CardTitle class="text-sm md:text-base">
                                                {{ method.brand || method.type.toUpperCase() }} •••• {{ method.last_four }}
                                            </CardTitle>
                                            <CardDescription class="text-xs md:text-sm">
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
                                        <Badge v-if="method.is_default" variant="secondary" class="text-[10px] md:text-xs px-2">
                                            Default
                                        </Badge>
                                        <Button 
                                            v-if="!method.is_default"
                                            variant="outline" 
                                            size="sm"
                                            class="text-xs"
                                        >
                                            Set as Default
                                        </Button>
                                        <Button 
                                            variant="ghost" 
                                            size="icon"
                                            class="text-destructive h-8 w-8 md:h-9 md:w-9"
                                        >
                                            <Trash2 class="h-3 w-3 md:h-4 md:w-4" />
                                        </Button>
                                    </div>
                                </div>
                            </CardHeader>
                        </Card>
                    </div>

                    <!-- Empty State -->
                    <Card v-else>
                        <CardContent class="flex flex-col items-center justify-center py-8 md:py-12">
                            <CreditCard class="h-10 w-10 md:h-12 md:w-12 text-muted-foreground mb-3 md:mb-4" />
                            <p class="text-xs md:text-sm text-muted-foreground text-center px-4">
                                No payment methods added yet.<br />
                                Add a payment method to manage your subscription.
                            </p>
                        </CardContent>
                    </Card>
                </div>

                <!-- Info Box -->
                <Card>
                    <CardHeader class="p-3 md:p-6">
                        <CardTitle class="text-xs md:text-sm">Secure Payment Processing</CardTitle>
                    </CardHeader>
                    <CardContent class="p-3 md:p-6 pt-0">
                        <p class="text-xs md:text-sm text-muted-foreground">
                            All payments are securely processed through PayMongo. We never store your full card details on our servers.
                        </p>
                    </CardContent>
                </Card>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
