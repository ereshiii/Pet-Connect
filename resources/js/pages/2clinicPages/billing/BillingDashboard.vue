<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { type BreadcrumbItem } from '@/types';
import clinicBilling from '@/routes/clinicBilling';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Clinic Dashboard',
        href: '#',
    },
    {
        title: 'Billing & Invoicing',
        href: '#',
    },
];

// Props from backend
interface Invoice {
    id: number;
    invoice_number: string;
    patient_name: string;
    owner_name: string;
    services: string[];
    subtotal: number;
    tax: number;
    total: number;
    paid_amount: number;
    balance_due: number;
    status: 'draft' | 'sent' | 'paid' | 'overdue' | 'cancelled';
    date: string;
    due_date: string;
    formatted_total?: string;
    formatted_balance?: string;
    is_overdue?: boolean;
    days_overdue?: number;
    payment_method?: string;
    notes?: string;
}

interface Payment {
    id: number;
    invoice_id: number;
    amount: number;
    method: string;
    date: string;
    reference?: string;
    formatted_amount?: string;
}

interface Props {
    invoices?: Invoice[];
    recent_payments?: Payment[];
    stats?: {
        total_revenue: number;
        pending_invoices: number;
        overdue_amount: number;
        monthly_revenue: number;
    };
    filters?: {
        status: string;
        date_range: string;
        search: string;
    };
    clinic?: {
        id: number;
        name: string;
    };
}

const props = withDefaults(defineProps<Props>(), {
    invoices: () => [],
    recent_payments: () => [],
    stats: () => ({
        total_revenue: 0,
        pending_invoices: 0,
        overdue_amount: 0,
        monthly_revenue: 0,
    }),
    filters: () => ({
        status: 'all',
        date_range: 'this_month',
        search: '',
    }),
    clinic: () => ({
        id: 0,
        name: '',
    }),
});

const getStatusColor = (status: string) => {
    const colors = {
        draft: 'bg-gray-100 text-gray-800',
        sent: 'bg-blue-100 text-blue-800',
        paid: 'bg-green-100 text-green-800',
        overdue: 'bg-red-100 text-red-800',
        cancelled: 'bg-gray-100 text-gray-800',
    };
    return colors[status as keyof typeof colors] || 'bg-gray-100 text-gray-800';
};

const formatCurrency = (amount: number) => {
    return '₱' + new Intl.NumberFormat('en-PH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(amount);
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString();
};

// Reactive state for modals and forms
const showPaymentModal = ref(false);
const showInvoiceModal = ref(false);
const selectedInvoice = ref<Invoice | null>(null);

// Payment form
const paymentForm = ref({
    amount: 0,
    method: 'cash',
    payment_date: new Date().toISOString().split('T')[0],
    reference_number: '',
    notes: '',
});

// Filter state
const filters = ref({
    status: props.filters?.status || 'all',
    search: props.filters?.search || '',
});

// Methods
const openPaymentModal = (invoice: Invoice) => {
    if (!invoice) return;
    selectedInvoice.value = invoice;
    paymentForm.value.amount = (invoice.balance_due || invoice.total) || 0;
    showPaymentModal.value = true;
};

const closePaymentModal = () => {
    showPaymentModal.value = false;
    selectedInvoice.value = null;
    paymentForm.value = {
        amount: 0,
        method: 'cash',
        payment_date: new Date().toISOString().split('T')[0],
        reference_number: '',
        notes: '',
    };
};

const submitPayment = () => {
    if (!selectedInvoice.value) return;
    
    const form = useForm(paymentForm.value);
    form.post(clinicBilling.recordPayment.url(selectedInvoice.value.id), {
        onSuccess: () => {
            closePaymentModal();
        },
    });
};

const updateInvoiceStatus = (invoice: Invoice, status: string) => {
    const form = useForm({ status });
    form.patch(clinicBilling.updateStatus.url(invoice.id));
};
</script>

<template>
    <Head title="Billing & Invoicing" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
            <!-- Page Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-foreground">Billing & Invoicing</h1>
                    <p class="text-muted-foreground">Manage invoices, payments, and financial records</p>
                </div>
                <div class="flex gap-2">
                    <button class="btn btn-outline">📊 Reports</button>
                    <button class="btn btn-primary">+ New Invoice</button>
                </div>
            </div>

            <!-- Stats Dashboard -->
            <div class="grid gap-4 md:grid-cols-4">
                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Total Revenue</p>
                            <p class="text-2xl font-bold">{{ formatCurrency(stats.total_revenue) }}</p>
                        </div>
                        <div class="h-8 w-8 rounded-full bg-green-100 flex items-center justify-center">
                            💰
                        </div>
                    </div>
                </div>
                
                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Monthly Revenue</p>
                            <p class="text-2xl font-bold">{{ formatCurrency(stats.monthly_revenue) }}</p>
                        </div>
                        <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center">
                            📈
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Pending Invoices</p>
                            <p class="text-2xl font-bold">{{ stats.pending_invoices }}</p>
                        </div>
                        <div class="h-8 w-8 rounded-full bg-yellow-100 flex items-center justify-center">
                            📋
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Overdue Amount</p>
                            <p class="text-2xl font-bold text-red-600">{{ formatCurrency(stats.overdue_amount) }}</p>
                        </div>
                        <div class="h-8 w-8 rounded-full bg-red-100 flex items-center justify-center">
                            ⚠️
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="rounded-lg border bg-card p-6">
                <h2 class="text-lg font-semibold mb-4">Quick Actions</h2>
                <div class="grid gap-4 md:grid-cols-5">
                    <button class="btn btn-outline">💳 Record Payment</button>
                    <button class="btn btn-outline">📧 Send Reminders</button>
                    <button class="btn btn-outline">📊 Generate Report</button>
                    <button class="btn btn-outline">⚙️ Payment Settings</button>
                    <button class="btn btn-outline">💾 Export Data</button>
                </div>
            </div>

            <!-- Invoices Table -->
            <div class="rounded-lg border bg-card">
                <div class="p-6 border-b">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold">Recent Invoices</h2>
                        <div class="flex gap-2">
                            <select class="form-select">
                                <option value="">All Statuses</option>
                                <option value="draft">Draft</option>
                                <option value="sent">Sent</option>
                                <option value="paid">Paid</option>
                                <option value="overdue">Overdue</option>
                            </select>
                            <input 
                                type="search" 
                                placeholder="Search invoices..." 
                                class="form-input"
                            />
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-muted/50">
                            <tr>
                                <th class="text-left p-4 font-medium">Invoice #</th>
                                <th class="text-left p-4 font-medium">Patient</th>
                                <th class="text-left p-4 font-medium">Owner</th>
                                <th class="text-left p-4 font-medium">Services</th>
                                <th class="text-left p-4 font-medium">Date</th>
                                <th class="text-left p-4 font-medium">Due Date</th>
                                <th class="text-left p-4 font-medium">Amount</th>
                                <th class="text-left p-4 font-medium">Status</th>
                                <th class="text-left p-4 font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr 
                                v-for="invoice in invoices" 
                                :key="invoice?.id || 'unknown'"
                                v-if="invoice"
                                class="border-b hover:bg-muted/50"
                            >
                                <td class="p-4">
                                    <span class="font-mono text-sm">{{ invoice.invoice_number || 'N/A' }}</span>
                                </td>
                                <td class="p-4">
                                    <span class="font-medium">{{ invoice.patient_name || 'Unknown' }}</span>
                                </td>
                                <td class="p-4">{{ invoice.owner_name || 'Unknown' }}</td>
                                <td class="p-4">
                                    <div class="max-w-xs">
                                        <span class="text-sm">{{ (invoice.services || []).join(', ') || 'No services listed' }}</span>
                                    </div>
                                </td>
                                <td class="p-4 text-sm">{{ invoice.date ? formatDate(invoice.date) : 'N/A' }}</td>
                                <td class="p-4 text-sm">{{ invoice.due_date ? formatDate(invoice.due_date) : 'N/A' }}</td>
                                <td class="p-4">
                                    <div class="text-sm">
                                        <div class="font-medium">{{ formatCurrency(invoice.total || 0) }}</div>
                                        <div class="text-muted-foreground">
                                            Sub: {{ formatCurrency(invoice.subtotal || 0) }} + Tax: {{ formatCurrency(invoice.tax || 0) }}
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <span 
                                        :class="getStatusColor(invoice.status || 'draft')"
                                        class="px-2 py-1 rounded-full text-xs font-medium capitalize"
                                    >
                                        {{ invoice.status || 'draft' }}
                                    </span>
                                </td>
                                <td class="p-4">
                                    <div class="flex gap-1">
                                        <button class="btn btn-sm btn-outline">View</button>
                                        <button 
                                            v-if="invoice.status === 'draft'"
                                            @click="updateInvoiceStatus(invoice, 'sent')"
                                            class="btn btn-sm btn-primary"
                                        >
                                            Send
                                        </button>
                                        <button 
                                            v-if="invoice && ['sent', 'overdue', 'partial'].includes(invoice.status || '') && (invoice.balance_due || 0) > 0"
                                            @click="openPaymentModal(invoice)"
                                            class="btn btn-sm btn-outline"
                                        >
                                            Record Payment
                                        </button>
                                        <button class="btn btn-sm btn-outline">⋮</button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Empty state -->
                    <div v-if="invoices.length === 0" class="text-center py-12">
                        <div class="text-muted-foreground">
                            <p class="text-lg mb-2">No invoices found</p>
                            <p class="mb-4">Create your first invoice to start tracking payments</p>
                            <button class="btn btn-primary">Create Invoice</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Payments -->
            <div class="rounded-lg border bg-card p-6">
                <h2 class="text-lg font-semibold mb-4">Recent Payments</h2>
                <div class="space-y-3">
                    <div 
                        v-for="payment in recent_payments.slice(0, 5)" 
                        :key="payment?.id || 'unknown'"
                        v-if="payment"
                        class="flex items-center justify-between p-3 rounded-lg border"
                    >
                        <div class="flex items-center gap-3">
                            <div class="h-8 w-8 rounded-full bg-green-100 flex items-center justify-center">
                                💳
                            </div>
                            <div>
                                <p class="font-medium">{{ formatCurrency(payment.amount || 0) }}</p>
                                <p class="text-sm text-muted-foreground">
                                    {{ payment.method || 'Unknown' }} • {{ payment.date ? formatDate(payment.date) : 'N/A' }}
                                </p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-mono">INV-{{ payment.invoice_id || 'N/A' }}</p>
                            <p v-if="payment.reference" class="text-xs text-muted-foreground">{{ payment.reference }}</p>
                        </div>
                    </div>

                    <!-- Empty payments state -->
                    <div v-if="recent_payments.length === 0" class="text-center py-8">
                        <p class="text-muted-foreground">No recent payments</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Modal -->
        <div v-if="showPaymentModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 w-full max-w-md mx-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold">Record Payment</h3>
                    <button @click="closePaymentModal" class="text-gray-400 hover:text-gray-600">
                        ✕
                    </button>
                </div>

                <div v-if="selectedInvoice" class="mb-4 p-3 bg-gray-50 rounded">
                    <p class="text-sm text-gray-600">Invoice: {{ selectedInvoice.invoice_number || 'N/A' }}</p>
                    <p class="text-sm text-gray-600">Patient: {{ selectedInvoice.patient_name || 'Unknown' }}</p>
                    <p class="text-sm font-medium">Balance Due: {{ formatCurrency((selectedInvoice.balance_due || selectedInvoice.total) || 0) }}</p>
                </div>

                <form @submit.prevent="submitPayment" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Amount</label>
                        <input 
                            v-model.number="paymentForm.amount"
                            type="number" 
                            step="0.01"
                            min="0.01"
                            :max="selectedInvoice?.balance_due || selectedInvoice?.total"
                            required
                            class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Payment Method</label>
                        <select 
                            v-model="paymentForm.method"
                            class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="cash">Cash</option>
                            <option value="card">Credit/Debit Card</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="gcash">GCash</option>
                            <option value="paymaya">PayMaya</option>
                            <option value="check">Check</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Payment Date</label>
                        <input 
                            v-model="paymentForm.payment_date"
                            type="date" 
                            required
                            class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Reference Number (Optional)</label>
                        <input 
                            v-model="paymentForm.reference_number"
                            type="text" 
                            placeholder="Transaction ID, Check number, etc."
                            class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Notes (Optional)</label>
                        <textarea 
                            v-model="paymentForm.notes"
                            rows="3"
                            placeholder="Additional notes about this payment..."
                            class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        ></textarea>
                    </div>

                    <div class="flex gap-3 pt-4">
                        <button 
                            type="button" 
                            @click="closePaymentModal"
                            class="flex-1 px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
                        >
                            Cancel
                        </button>
                        <button 
                            type="submit"
                            class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                        >
                            Record Payment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>