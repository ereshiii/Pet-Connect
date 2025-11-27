<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { adminDashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { ref } from 'vue';
import { CreditCard, Building2, TrendingUp, Plus, Edit, Trash2, X, Check } from 'lucide-vue-next';

interface TestCard {
    id: string;
    card_number: string;
    card_holder: string;
    expiry: string;
    cvv: string;
    balance: number;
    bank: string;
}

interface MerchantAccount {
    account_number: string;
    account_name: string;
    total_received: number;
    transaction_count: number;
}

const props = defineProps<{
    test_cards: TestCard[];
    merchant_account: MerchantAccount;
}>();

const showAddModal = ref(false);
const showEditModal = ref(false);
const editingCard = ref<TestCard | null>(null);

const cardForm = useForm({
    card_number: '',
    card_holder: '',
    expiry: '',
    cvv: '',
    balance: 0,
    bank: '',
});

const breadcrumbs: BreadcrumbItem[] = [
    { label: 'Dashboard', route: adminDashboard },
    { label: 'Testing Tools', route: null },
    { label: 'Mock Payment Banking', route: null },
];

const formatCardNumber = (number: string) => {
    return number.replace(/\s/g, '').replace(/(\d{4})/g, '$1 ').trim();
};

const openAddModal = () => {
    cardForm.reset();
    showAddModal.value = true;
};

const openEditModal = (card: TestCard) => {
    editingCard.value = card;
    cardForm.card_number = card.card_number;
    cardForm.card_holder = card.card_holder;
    cardForm.expiry = card.expiry;
    cardForm.cvv = card.cvv;
    cardForm.balance = card.balance;
    cardForm.bank = card.bank;
    showEditModal.value = true;
};

const closeModals = () => {
    showAddModal.value = false;
    showEditModal.value = false;
    editingCard.value = null;
    cardForm.reset();
};

const submitAddCard = () => {
    cardForm.post('/admin/testing-tools/mock-payment/cards', {
        onSuccess: () => {
            closeModals();
        },
    });
};

const submitEditCard = () => {
    if (!editingCard.value) return;
    
    cardForm.put(`/admin/testing-tools/mock-payment/cards/${editingCard.value.id}`, {
        onSuccess: () => {
            closeModals();
        },
    });
};

const deleteCard = (cardId: string) => {
    if (confirm('Are you sure you want to delete this test card?')) {
        router.delete(`/admin/testing-tools/mock-payment/cards/${cardId}`);
    }
};

const formatCardInput = (event: Event) => {
    const input = event.target as HTMLInputElement;
    let value = input.value.replace(/\s/g, '');
    const formatted = value.match(/.{1,4}/g)?.join(' ') || value;
    cardForm.card_number = formatted;
};

const formatExpiryInput = (event: Event) => {
    const input = event.target as HTMLInputElement;
    let value = input.value.replace(/\D/g, '');
    if (value.length >= 2) {
        value = value.slice(0, 2) + '/' + value.slice(2, 4);
    }
    cardForm.expiry = value;
};
</script>

<template>
    <Head title="Mock Payment Banking" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                        <CreditCard class="h-8 w-8 text-blue-600" />
                        Mock Payment Banking System
                    </h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Test subscription payments using virtual bank cards</p>
                </div>
                <button
                    @click="openAddModal"
                    class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                >
                    <Plus class="h-5 w-5" />
                    Add Test Card
                </button>
            </div>

            <!-- Banking Overview -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Merchant Account Card -->
                <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between mb-4">
                        <Building2 class="h-8 w-8" />
                        <span class="text-xs bg-white/20 px-3 py-1 rounded-full">Merchant Account</span>
                    </div>
                    <div class="space-y-2">
                        <p class="text-sm opacity-90">{{ merchant_account.account_name }}</p>
                        <p class="text-xs font-mono opacity-75">{{ merchant_account.account_number }}</p>
                        <div class="pt-4 border-t border-white/20">
                            <p class="text-xs opacity-75">Total Received</p>
                            <p class="text-3xl font-bold">₱{{ merchant_account.total_received.toLocaleString() }}</p>
                            <p class="text-xs opacity-75 mt-2">{{ merchant_account.transaction_count }} transactions</p>
                        </div>
                    </div>
                </div>

                <!-- Test Cards -->
                <div v-for="(card, index) in test_cards" :key="card.id" 
                    class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg p-6 text-white relative group"
                >
                    <!-- Card Actions -->
                    <div class="absolute top-2 right-2 flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button
                            @click="openEditModal(card)"
                            class="p-1.5 bg-white/20 hover:bg-white/30 rounded-lg transition-colors"
                        >
                            <Edit class="h-4 w-4" />
                        </button>
                        <button
                            @click="deleteCard(card.id)"
                            class="p-1.5 bg-white/20 hover:bg-red-600 rounded-lg transition-colors"
                        >
                            <Trash2 class="h-4 w-4" />
                        </button>
                    </div>

                    <div class="flex items-center justify-between mb-4">
                        <CreditCard class="h-8 w-8" />
                        <Check class="h-6 w-6" />
                    </div>
                    <div class="space-y-2">
                        <p class="text-xs opacity-75">{{ card.bank }}</p>
                        <p class="text-lg font-mono tracking-wider">{{ formatCardNumber(card.card_number) }}</p>
                        <div class="flex justify-between items-end pt-4">
                            <div>
                                <p class="text-xs opacity-75">Card Holder</p>
                                <p class="text-sm font-semibold">{{ card.card_holder }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs opacity-75">Balance</p>
                                <p class="text-lg font-bold">₱{{ card.balance.toLocaleString() }}</p>
                            </div>
                        </div>
                        <div class="flex gap-4 text-xs pt-2">
                            <span>Exp: {{ card.expiry }}</span>
                            <span>CVV: {{ card.cvv }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Information Section -->
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-blue-900 dark:text-blue-100 mb-3">How Mock Payment Works</h3>
                <div class="space-y-2 text-sm text-blue-800 dark:text-blue-200">
                    <p>• <strong>Test Cards (Blue)</strong>: Use these card numbers on clinic subscription pages to simulate payments. Balance will be deducted and added to the merchant account.</p>
                    <p>• <strong>Merchant Account (Green)</strong>: Shows total revenue from all mock subscriptions.</p>
                    <p>• <strong>Testing</strong>: Go to the clinic subscription page and use these test card numbers to process mock payments.</p>
                    <p>• <strong>Card Management</strong>: Hover over cards to edit or delete them. Click "Add Test Card" to create new test cards.</p>
                    <p>• <strong>Balance Check</strong>: Payments will only succeed if the card has sufficient balance for the subscription amount.</p>
                </div>
            </div>
        </div>

        <!-- Add Card Modal -->
        <div v-if="showAddModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full">
                <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Add Test Card</h3>
                    <button @click="closeModals" class="text-gray-400 hover:text-gray-600">
                        <X class="h-5 w-5" />
                    </button>
                </div>
                <form @submit.prevent="submitAddCard" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Card Number</label>
                        <input
                            v-model="cardForm.card_number"
                            @input="formatCardInput"
                            type="text"
                            maxlength="19"
                            placeholder="4532 1234 5678 9010"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                            required
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Card Holder</label>
                        <input
                            v-model="cardForm.card_holder"
                            type="text"
                            placeholder="John Doe"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                            required
                        />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Expiry</label>
                            <input
                                v-model="cardForm.expiry"
                                @input="formatExpiryInput"
                                type="text"
                                maxlength="5"
                                placeholder="12/25"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                                required
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">CVV</label>
                            <input
                                v-model="cardForm.cvv"
                                type="text"
                                maxlength="4"
                                placeholder="123"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                                required
                            />
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Balance (₱)</label>
                        <input
                            v-model="cardForm.balance"
                            type="number"
                            step="0.01"
                            min="0"
                            placeholder="50000"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                            required
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Bank Name</label>
                        <input
                            v-model="cardForm.bank"
                            type="text"
                            placeholder="PetConnect Test Bank"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                            required
                        />
                    </div>
                    <div class="flex gap-3 pt-4">
                        <button
                            type="button"
                            @click="closeModals"
                            class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="cardForm.processing"
                            class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 transition-colors"
                        >
                            {{ cardForm.processing ? 'Adding...' : 'Add Card' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Card Modal -->
        <div v-if="showEditModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full">
                <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Edit Test Card</h3>
                    <button @click="closeModals" class="text-gray-400 hover:text-gray-600">
                        <X class="h-5 w-5" />
                    </button>
                </div>
                <form @submit.prevent="submitEditCard" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Card Number</label>
                        <input
                            v-model="cardForm.card_number"
                            @input="formatCardInput"
                            type="text"
                            maxlength="19"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                            required
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Card Holder</label>
                        <input
                            v-model="cardForm.card_holder"
                            type="text"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                            required
                        />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Expiry</label>
                            <input
                                v-model="cardForm.expiry"
                                @input="formatExpiryInput"
                                type="text"
                                maxlength="5"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                                required
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">CVV</label>
                            <input
                                v-model="cardForm.cvv"
                                type="text"
                                maxlength="4"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                                required
                            />
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Balance (₱)</label>
                        <input
                            v-model="cardForm.balance"
                            type="number"
                            step="0.01"
                            min="0"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                            required
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Bank Name</label>
                        <input
                            v-model="cardForm.bank"
                            type="text"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                            required
                        />
                    </div>
                    <div class="flex gap-3 pt-4">
                        <button
                            type="button"
                            @click="closeModals"
                            class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="cardForm.processing"
                            class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 transition-colors"
                        >
                            {{ cardForm.processing ? 'Updating...' : 'Update Card' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
