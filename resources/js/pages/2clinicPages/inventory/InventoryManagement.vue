<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Clinic Dashboard',
        href: '#',
    },
    {
        title: 'Inventory Management',
        href: '#',
    },
];

// Props from backend
interface InventoryItem {
    id: number;
    name: string;
    category: string;
    sku: string;
    current_stock: number;
    min_stock: number;
    max_stock: number;
    unit_cost: number;
    selling_price: number;
    supplier: string;
    expiry_date?: string;
    last_restocked: string;
    usage_rate: number; // per month
    status: 'in_stock' | 'low_stock' | 'out_of_stock' | 'expired';
}

interface InventoryCategory {
    name: string;
    items: InventoryItem[];
    total_value: number;
}

interface Props {
    inventory_categories?: InventoryCategory[];
    low_stock_alerts?: InventoryItem[];
    stats?: {
        total_items: number;
        total_value: number;
        low_stock_count: number;
        expired_items: number;
    };
}

const props = withDefaults(defineProps<Props>(), {
    inventory_categories: () => [],
    low_stock_alerts: () => [],
    stats: () => ({
        total_items: 0,
        total_value: 0,
        low_stock_count: 0,
        expired_items: 0,
    }),
});

const getStatusColor = (status: string) => {
    const colors = {
        in_stock: 'bg-green-100 text-green-800',
        low_stock: 'bg-yellow-100 text-yellow-800',
        out_of_stock: 'bg-red-100 text-red-800',
        expired: 'bg-gray-100 text-gray-800',
    };
    return colors[status as keyof typeof colors] || 'bg-gray-100 text-gray-800';
};

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(amount);
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString();
};

const getStockLevel = (current: number, min: number, max: number) => {
    const percentage = (current / max) * 100;
    return Math.min(100, Math.max(0, percentage));
};

const getStockColor = (current: number, min: number) => {
    if (current <= 0) return 'bg-red-500';
    if (current <= min) return 'bg-yellow-500';
    return 'bg-green-500';
};
</script>

<template>
    <Head title="Inventory Management" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
            <!-- Page Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-foreground">Inventory Management</h1>
                    <p class="text-muted-foreground">Track supplies, medications, and equipment</p>
                </div>
                <div class="flex gap-2">
                    <button class="btn btn-outline">📊 Reports</button>
                    <button class="btn btn-outline">📦 Restock Items</button>
                    <button class="btn btn-primary">+ Add Item</button>
                </div>
            </div>

            <!-- Stats Dashboard -->
            <div class="grid gap-4 md:grid-cols-4">
                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Total Items</p>
                            <p class="text-2xl font-bold">{{ stats.total_items }}</p>
                        </div>
                        <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center">
                            📦
                        </div>
                    </div>
                </div>
                
                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Total Value</p>
                            <p class="text-2xl font-bold">{{ formatCurrency(stats.total_value) }}</p>
                        </div>
                        <div class="h-8 w-8 rounded-full bg-green-100 flex items-center justify-center">
                            💰
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Low Stock Alerts</p>
                            <p class="text-2xl font-bold text-yellow-600">{{ stats.low_stock_count }}</p>
                        </div>
                        <div class="h-8 w-8 rounded-full bg-yellow-100 flex items-center justify-center">
                            ⚠️
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Expired Items</p>
                            <p class="text-2xl font-bold text-red-600">{{ stats.expired_items }}</p>
                        </div>
                        <div class="h-8 w-8 rounded-full bg-red-100 flex items-center justify-center">
                            🚫
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="rounded-lg border bg-card p-6">
                <h2 class="text-lg font-semibold mb-4">Quick Actions</h2>
                <div class="grid gap-4 md:grid-cols-5">
                    <button class="btn btn-outline">📝 Stock Count</button>
                    <button class="btn btn-outline">🛒 Create Order</button>
                    <button class="btn btn-outline">📋 Print Labels</button>
                    <button class="btn btn-outline">📊 Usage Report</button>
                    <button class="btn btn-outline">⚙️ Settings</button>
                </div>
            </div>

            <!-- Low Stock Alerts -->
            <div v-if="low_stock_alerts.length > 0" class="rounded-lg border border-yellow-200 bg-yellow-50 p-6">
                <div class="flex items-center gap-2 mb-4">
                    <span class="text-yellow-600">⚠️</span>
                    <h2 class="text-lg font-semibold text-yellow-800">Low Stock Alerts</h2>
                </div>
                <div class="grid gap-4 md:grid-cols-3">
                    <div 
                        v-for="item in low_stock_alerts" 
                        :key="item.id"
                        class="rounded-lg border border-yellow-200 bg-white p-4"
                    >
                        <div class="flex justify-between items-start mb-2">
                            <h3 class="font-semibold">{{ item.name }}</h3>
                            <span class="text-sm text-yellow-600 font-medium">{{ item.current_stock }} left</span>
                        </div>
                        <p class="text-sm text-muted-foreground mb-2">{{ item.category }}</p>
                        <div class="flex justify-between">
                            <span class="text-sm">Min: {{ item.min_stock }}</span>
                            <button class="btn btn-sm btn-outline">Restock</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Inventory Categories -->
            <div class="space-y-6">
                <div 
                    v-for="category in inventory_categories" 
                    :key="category.name"
                    class="rounded-lg border bg-card p-6"
                >
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h2 class="text-xl font-semibold">{{ category.name }}</h2>
                            <p class="text-sm text-muted-foreground">
                                {{ category.items.length }} items • Value: {{ formatCurrency(category.total_value) }}
                            </p>
                        </div>
                        <button class="btn btn-outline btn-sm">+ Add to {{ category.name }}</button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-muted/50">
                                <tr>
                                    <th class="text-left p-3 font-medium">Item</th>
                                    <th class="text-left p-3 font-medium">SKU</th>
                                    <th class="text-left p-3 font-medium">Stock Level</th>
                                    <th class="text-left p-3 font-medium">Unit Cost</th>
                                    <th class="text-left p-3 font-medium">Selling Price</th>
                                    <th class="text-left p-3 font-medium">Supplier</th>
                                    <th class="text-left p-3 font-medium">Expiry</th>
                                    <th class="text-left p-3 font-medium">Status</th>
                                    <th class="text-left p-3 font-medium">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr 
                                    v-for="item in category.items" 
                                    :key="item.id"
                                    class="border-b hover:bg-muted/50"
                                >
                                    <td class="p-3">
                                        <div>
                                            <span class="font-medium">{{ item.name }}</span>
                                            <p class="text-sm text-muted-foreground">{{ item.category }}</p>
                                        </div>
                                    </td>
                                    <td class="p-3">
                                        <span class="font-mono text-sm">{{ item.sku }}</span>
                                    </td>
                                    <td class="p-3">
                                        <div class="space-y-1">
                                            <div class="flex justify-between text-sm">
                                                <span>{{ item.current_stock }} / {{ item.max_stock }}</span>
                                                <span class="text-muted-foreground">{{ Math.round(getStockLevel(item.current_stock, item.min_stock, item.max_stock)) }}%</span>
                                            </div>
                                            <div class="w-full bg-muted rounded-full h-2">
                                                <div 
                                                    :class="getStockColor(item.current_stock, item.min_stock)"
                                                    class="h-2 rounded-full transition-all"
                                                    :style="{ width: `${getStockLevel(item.current_stock, item.min_stock, item.max_stock)}%` }"
                                                ></div>
                                            </div>
                                            <div class="text-xs text-muted-foreground">
                                                Min: {{ item.min_stock }} | Usage: {{ item.usage_rate }}/month
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-3">{{ formatCurrency(item.unit_cost) }}</td>
                                    <td class="p-3">{{ formatCurrency(item.selling_price) }}</td>
                                    <td class="p-3">{{ item.supplier }}</td>
                                    <td class="p-3">
                                        <span v-if="item.expiry_date" class="text-sm">
                                            {{ formatDate(item.expiry_date) }}
                                        </span>
                                        <span v-else class="text-muted-foreground">-</span>
                                    </td>
                                    <td class="p-3">
                                        <span 
                                            :class="getStatusColor(item.status)"
                                            class="px-2 py-1 rounded-full text-xs font-medium capitalize"
                                        >
                                            {{ item.status.replace('_', ' ') }}
                                        </span>
                                    </td>
                                    <td class="p-3">
                                        <div class="flex gap-1">
                                            <button class="btn btn-sm btn-outline">Edit</button>
                                            <button 
                                                v-if="item.current_stock <= item.min_stock"
                                                class="btn btn-sm btn-primary"
                                            >
                                                Restock
                                            </button>
                                            <button class="btn btn-sm btn-outline">⋮</button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- Empty category state -->
                        <div v-if="category.items.length === 0" class="text-center py-8">
                            <p class="text-muted-foreground mb-2">No items in this category</p>
                            <button class="btn btn-primary btn-sm">Add First Item</button>
                        </div>
                    </div>
                </div>

                <!-- Empty state -->
                <div v-if="inventory_categories.length === 0" class="text-center py-12">
                    <div class="text-muted-foreground">
                        <p class="text-lg mb-2">No inventory items</p>
                        <p class="mb-4">Start tracking your clinic supplies and medications</p>
                        <button class="btn btn-primary">Add First Item</button>
                    </div>
                </div>
            </div>

            <!-- Inventory Tools -->
            <div class="rounded-lg border bg-card p-6">
                <h2 class="text-lg font-semibold mb-4">Inventory Tools</h2>
                <div class="grid gap-4 md:grid-cols-4">
                    <button class="btn btn-outline">📱 Barcode Scanner</button>
                    <button class="btn btn-outline">📋 Stock Audit</button>
                    <button class="btn btn-outline">📈 Demand Forecast</button>
                    <button class="btn btn-outline">🔄 Auto-Reorder Setup</button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>