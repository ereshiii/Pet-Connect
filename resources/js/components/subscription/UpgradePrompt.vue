<script setup lang="ts">
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Crown, Lock, ArrowRight, Check, X } from 'lucide-vue-next';
import { router } from '@inertiajs/vue3';

interface Props {
    feature: string;
    requiredPlan?: 'professional' | 'pro-plus';
    isModal?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    requiredPlan: 'professional',
    isModal: false
});

const emit = defineEmits<{
    close: []
}>();

const planDetails = {
    professional: {
        name: 'Professional',
        price: '₱599',
        period: '/month',
        features: [
            'Analytics Dashboard',
            'Appointment History',
            'Patient Records',
            'Up to 3 Staff Members',
            'Up to 10 Services',
            '200 Appointments/month',
            '3.5% Transaction Fee'
        ],
        trial: '14-day free trial'
    },
    'pro-plus': {
        name: 'Pro Plus',
        price: '₱1,499',
        period: '/month',
        features: [
            'Everything in Professional',
            'Report Generation',
            'Unlimited Staff Members',
            'Unlimited Services',
            'Unlimited Appointments',
            '2.5% Transaction Fee',
            'Priority Support'
        ],
        trial: '30-day free trial'
    }
};

const plan = planDetails[props.requiredPlan];

const handleUpgrade = () => {
    router.visit('/subscription');
};

const handleClose = () => {
    emit('close');
};
</script>

<template>
    <div :class="isModal ? '' : 'flex items-center justify-center min-h-[60vh] p-4'">
        <Card class="max-w-2xl w-full">
            <CardHeader class="text-center pb-4">
                <div v-if="isModal" class="absolute right-4 top-4">
                    <button 
                        @click="handleClose"
                        class="p-1 hover:bg-muted rounded-md transition-colors"
                    >
                        <X class="h-5 w-5" />
                    </button>
                </div>
                
                <div class="flex items-center justify-center mb-4">
                    <div class="rounded-full bg-primary/10 p-4">
                        <Lock class="h-10 w-10 text-primary" />
                    </div>
                </div>
                
                <CardTitle class="text-3xl">
                    Upgrade Required
                </CardTitle>
                
                <CardDescription class="text-base mt-2">
                    <strong>{{ feature }}</strong> requires the <strong>{{ plan.name }}</strong> plan or higher
                </CardDescription>

                <div class="flex justify-center mt-3">
                    <Badge variant="secondary" class="gap-1 text-sm px-3 py-1">
                        <Crown class="h-4 w-4" />
                        {{ plan.name }} Feature
                    </Badge>
                </div>
            </CardHeader>

            <CardContent class="space-y-6">
                <!-- Pricing -->
                <div class="rounded-lg border bg-gradient-to-br from-primary/5 to-primary/10 p-6 text-center">
                    <div class="flex items-baseline justify-center gap-1 mb-2">
                        <span class="text-4xl font-bold">{{ plan.price }}</span>
                        <span class="text-muted-foreground">{{ plan.period }}</span>
                    </div>
                    <Badge variant="outline" class="mb-1">
                        {{ plan.trial }}
                    </Badge>
                    <p class="text-xs text-muted-foreground mt-2">
                        No credit card required for trial
                    </p>
                </div>

                <!-- Features -->
                <div>
                    <p class="font-semibold mb-3">What's included:</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        <div 
                            v-for="(feature, index) in plan.features" 
                            :key="index"
                            class="flex items-start gap-2 text-sm"
                        >
                            <div class="rounded-full bg-primary/10 p-1 mt-0.5">
                                <Check class="h-3 w-3 text-primary" />
                            </div>
                            <span>{{ feature }}</span>
                        </div>
                    </div>
                </div>
            </CardContent>

            <CardFooter class="flex flex-col gap-3 pt-2">
                <Button 
                    class="w-full" 
                    size="lg"
                    @click="handleUpgrade"
                >
                    <Crown class="h-5 w-5 mr-2" />
                    Upgrade to {{ plan.name }}
                    <ArrowRight class="h-5 w-5 ml-2" />
                </Button>
                
                <Button 
                    v-if="!isModal"
                    variant="ghost" 
                    class="w-full"
                    @click="() => router.visit('/clinic/dashboard')"
                >
                    Back to Dashboard
                </Button>
                
                <Button 
                    v-else
                    variant="ghost" 
                    class="w-full"
                    @click="handleClose"
                >
                    Maybe Later
                </Button>

                <p class="text-center text-xs text-muted-foreground mt-2">
                    Questions? <a href="/contact" class="text-primary hover:underline font-medium">Contact our team</a>
                </p>
            </CardFooter>
        </Card>
    </div>
</template>
