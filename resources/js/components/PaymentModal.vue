<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { CreditCard, Smartphone, Wallet, Loader2, AlertCircle } from 'lucide-vue-next';
import axios from 'axios';

interface SavedPaymentMethod {
    type: string;
    last_four: string;
}

interface Props {
    open: boolean;
    planSlug: string;
    amount: number;
    billingCycle: 'monthly' | 'annual';
    publicKey: string;
    savedPaymentMethod?: SavedPaymentMethod | null;
}

const props = defineProps<Props>();
const emit = defineEmits(['update:open', 'success', 'error']);

const selectedMethod = ref<'card' | 'gcash' | 'grab_pay' | 'paymaya'>('card');
const processing = ref(false);
const error = ref<string | null>(null);
const usingSavedCard = ref(false);

// Card details
const cardNumber = ref('');
const cardExpMonth = ref('');
const cardExpYear = ref('');
const cardCvc = ref('');
const cardName = ref('');

// Payment methods with icons
const paymentMethods = [
    { value: 'card', label: 'Credit/Debit Card', icon: CreditCard, fee: 3.5 },
    { value: 'gcash', label: 'GCash', icon: Smartphone, fee: 2.5 },
    { value: 'grab_pay', label: 'GrabPay', icon: Wallet, fee: 2.5 },
    { value: 'paymaya', label: 'Maya', icon: Wallet, fee: 3.5 },
];

const totalWithFees = ref({ amount: props.amount, fee: 0, total: props.amount });

// Calculate fees when method changes
const calculateFees = async () => {
    try {
        const response = await axios.post('/payment/calculate-total', {
            amount: props.amount,
            payment_method: selectedMethod.value,
        });
        totalWithFees.value = response.data;
    } catch (err) {
        console.error('Fee calculation error:', err);
    }
};

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
    }).format(amount);
};

const processPayment = async () => {
    error.value = null;
    processing.value = true;

    try {
        if (selectedMethod.value === 'card') {
            await processCardPayment();
        } else {
            await processEWalletPayment();
        }
    } catch (err: any) {
        error.value = err.response?.data?.message || err.message || 'Payment failed';
        processing.value = false;
    }
};

const processCardPayment = async () => {
    try {
        // Validate card details
        if (!cardNumber.value || !cardExpMonth.value || !cardExpYear.value || !cardCvc.value || !cardName.value) {
            throw new Error('Please fill in all card details');
        }

        // Use the actual card number for validation (remove spaces)
        const cleanCardNumber = cardNumber.value.replace(/\s/g, '');
        
        // Submit to backend with card number for mock payment validation
        router.post('/payment/process', {
            plan_slug: props.planSlug,
            billing_cycle: props.billingCycle,
            payment_method: 'card',
            payment_method_id: cleanCardNumber,  // Send actual card number for validation
        }, {
            onSuccess: () => {
                emit('success');
                emit('update:open', false);
            },
            onError: (errors) => {
                error.value = Object.values(errors).flat().join(', ');
                processing.value = false;
            },
        });
    } catch (err: any) {
        throw new Error(err.message || 'Card payment failed');
    }
};

const processEWalletPayment = async () => {
    router.post('/payment/process', {
        plan_slug: props.planSlug,
        billing_cycle: props.billingCycle,
        payment_method: selectedMethod.value,
        payment_method_id: null,
    }, {
        onError: (errors) => {
            error.value = Object.values(errors).flat().join(', ');
            processing.value = false;
        },
    });
    // User will be redirected to e-wallet provider
};

const formatCardNumber = () => {
    cardNumber.value = cardNumber.value.replace(/\s/g, '').replace(/(\d{4})/g, '$1 ').trim();
};

const loadSavedCard = () => {
    if (props.savedPaymentMethod && props.savedPaymentMethod.type === 'card') {
        usingSavedCard.value = true;
        selectedMethod.value = 'card';
        // Pre-fill with masked card number ending with saved last 4 digits
        cardNumber.value = '•••• •••• •••• ' + props.savedPaymentMethod.last_four;
    }
};

onMounted(() => {
    calculateFees();
    loadSavedCard();
});
</script>

<template>
    <Dialog :open="open" @update:open="(val) => emit('update:open', val)">
        <DialogContent class="max-w-md">
            <DialogHeader>
                <DialogTitle>Complete Payment</DialogTitle>
                <DialogDescription>
                    Choose your preferred payment method
                </DialogDescription>
            </DialogHeader>

            <div class="space-y-6">
                <!-- Payment Method Selection -->
                <div class="space-y-3">
                    <Label>Payment Method</Label>
                    <div class="space-y-2">
                        <div 
                            v-for="method in paymentMethods" 
                            :key="method.value"
                            class="flex items-center space-x-3 rounded-lg border p-3 cursor-pointer hover:bg-accent transition-colors"
                            :class="{ 'border-primary bg-accent': selectedMethod === method.value }"
                            @click="selectedMethod = method.value; calculateFees()"
                        >
                            <input 
                                type="radio" 
                                :value="method.value" 
                                v-model="selectedMethod"
                                :id="method.value"
                                class="h-4 w-4 text-primary"
                            />
                            <Label 
                                :for="method.value" 
                                class="flex items-center gap-2 flex-1 cursor-pointer"
                            >
                                <component :is="method.icon" class="h-5 w-5" />
                                <span class="flex-1">{{ method.label }}</span>
                                <span class="text-xs text-muted-foreground">{{ method.fee }}% + ₱15</span>
                            </Label>
                        </div>
                    </div>
                </div>

                <!-- Card Payment Form -->
                <div v-if="selectedMethod === 'card'" class="space-y-4">
                    <!-- Saved Card Notice -->
                    <div v-if="usingSavedCard && savedPaymentMethod" class="rounded-lg bg-blue-50 dark:bg-blue-950/20 p-3 text-sm">
                        <p class="text-blue-900 dark:text-blue-100 flex items-center gap-2">
                            <CreditCard class="h-4 w-4" />
                            Using saved card ending in {{ savedPaymentMethod.last_four }}
                        </p>
                        <button 
                            type="button"
                            @click="usingSavedCard = false; cardNumber = ''; cardName = ''; cardExpMonth = ''; cardExpYear = ''; cardCvc = '';"
                            class="text-xs text-blue-700 dark:text-blue-300 hover:underline mt-1"
                        >
                            Use a different card
                        </button>
                    </div>

                    <div class="space-y-2">
                        <Label for="cardName">Cardholder Name</Label>
                        <Input 
                            id="cardName"
                            v-model="cardName" 
                            placeholder="JUAN DELA CRUZ" 
                            :disabled="processing || usingSavedCard"
                        />
                    </div>

                    <div class="space-y-2">
                        <Label for="cardNumber">Card Number</Label>
                        <Input 
                            id="cardNumber"
                            v-model="cardNumber" 
                            @input="formatCardNumber"
                            placeholder="4343 4343 4343 4345" 
                            maxlength="19"
                            :disabled="processing || usingSavedCard"
                            :readonly="usingSavedCard"
                        />
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                        <div class="space-y-2">
                            <Label for="expMonth">Month</Label>
                            <Input 
                                id="expMonth"
                                v-model="cardExpMonth" 
                                placeholder="MM" 
                                maxlength="2"
                                :disabled="processing || usingSavedCard"
                            />
                        </div>
                        <div class="space-y-2">
                            <Label for="expYear">Year</Label>
                            <Input 
                                id="expYear"
                                v-model="cardExpYear" 
                                placeholder="YY" 
                                maxlength="2"
                                :disabled="processing || usingSavedCard"
                            />
                        </div>
                        <div class="space-y-2">
                            <Label for="cvc">CVC</Label>
                            <Input 
                                id="cvc"
                                v-model="cardCvc" 
                                placeholder="123" 
                                maxlength="4"
                                type="password"
                                :disabled="processing"
                            />
                        </div>
                    </div>
                </div>

                <!-- E-Wallet Notice -->
                <div v-else class="rounded-lg bg-blue-50 dark:bg-blue-950/20 p-4 text-sm">
                    <p class="text-blue-900 dark:text-blue-100">
                        You will be redirected to {{ paymentMethods.find(m => m.value === selectedMethod)?.label }} to complete your payment.
                    </p>
                </div>

                <!-- Payment Summary -->
                <div class="space-y-2 rounded-lg border p-4 bg-muted/50">
                    <div class="flex justify-between text-sm">
                        <span>Subscription ({{ billingCycle }})</span>
                        <span>{{ formatCurrency(totalWithFees.amount) }}</span>
                    </div>
                    <div class="flex justify-between text-sm text-muted-foreground">
                        <span>Payment Processing Fee</span>
                        <span>{{ formatCurrency(totalWithFees.fee) }}</span>
                    </div>
                    <div class="h-px bg-border my-2"></div>
                    <div class="flex justify-between font-semibold">
                        <span>Total</span>
                        <span>{{ formatCurrency(totalWithFees.total) }}</span>
                    </div>
                </div>

                <!-- Error Message -->
                <div v-if="error" class="flex items-center gap-2 rounded-lg bg-destructive/10 p-3 text-sm text-destructive">
                    <AlertCircle class="h-4 w-4" />
                    <span>{{ error }}</span>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-3">
                    <Button 
                        variant="outline" 
                        class="flex-1" 
                        @click="emit('update:open', false)"
                        :disabled="processing"
                    >
                        Cancel
                    </Button>
                    <Button 
                        class="flex-1" 
                        @click="processPayment"
                        :disabled="processing"
                    >
                        <Loader2 v-if="processing" class="h-4 w-4 mr-2 animate-spin" />
                        {{ processing ? 'Processing...' : `Pay ${formatCurrency(totalWithFees.total)}` }}
                    </Button>
                </div>

                <!-- Security Notice -->
                <p class="text-xs text-center text-muted-foreground">
                    🔒 Secured by PayMongo. Your payment information is encrypted.
                </p>
            </div>
        </DialogContent>
    </Dialog>
</template>
