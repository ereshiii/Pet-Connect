<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { clinicDashboard, clinicServices } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { ref, computed, onMounted } from 'vue';
import { 
    Search, 
    Plus, 
    Filter, 
    Eye, 
    EyeOff, 
    Edit, 
    Copy, 
    Trash2, 
    DollarSign, 
    Clock, 
    Users, 
    TrendingUp,
    Calendar,
    Settings,
    MoreVertical,
    X
} from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Clinic Dashboard',
        href: clinicDashboard().url,
    },
    {
        title: 'Services Management',
        href: clinicServices().url,
    },
];

// Interfaces
interface ServiceUsage {
    total_appointments: number;
    completed_appointments: number;
    total_revenue: number;
    formatted_revenue: string;
    last_used: string | null;
    formatted_last_used: string;
    recent_usage: number;
    completion_rate: number;
}

interface Service {
    id: number;
    name: string;
    description: string;
    category: string;
    category_display: string;
    base_price: number;
    formatted_price: string;
    duration_minutes: number;
    formatted_duration: string;
    is_active: boolean;
    requires_appointment: boolean;
    is_emergency_service: boolean;
    usage: ServiceUsage;
    created_at: string;
    updated_at: string;
}

interface ServiceStats {
    total_services: number;
    active_services: number;
    inactive_services: number;
    category_stats: Record<string, number>;
    popular_services: Array<{
        name: string;
        appointment_count: number;
        category: string;
    }>;
    total_revenue: number;
    formatted_revenue: string;
}

interface Filters {
    category: string;
    status: string;
    search: string;
}

interface Props {
    services: {
        data: Service[];
        links: any[];
        meta: any;
    };
    stats: ServiceStats;
    categories: Record<string, string>;
    filters: Filters;
    clinic: {
        id: number;
        name: string;
    };
}

const props = defineProps<Props>();

// Reactive state
const showCreateModal = ref(false);
const showEditModal = ref(false);
const editingService = ref<Service | null>(null);
const activeDropdown = ref<number | null>(null);

// Forms
const createForm = useForm({
    name: '',
    description: '',
    category: '',
    base_price: null as number | null,
    duration_minutes: 30,
    is_active: true,
    requires_appointment: true,
    is_emergency_service: false,
});

const editForm = useForm({
    name: '',
    description: '',
    category: '',
    base_price: null as number | null,
    duration_minutes: 30,
    is_active: true,
    requires_appointment: true,
    is_emergency_service: false,
});

const filterForm = useForm({
    category: props.filters.category,
    status: props.filters.status,
    search: props.filters.search,
});

// Computed
const categorizedServices = computed(() => {
    const grouped: Record<string, Service[]> = {};
    
    props.services.data.forEach(service => {
        if (!grouped[service.category]) {
            grouped[service.category] = [];
        }
        grouped[service.category].push(service);
    });
    
    return grouped;
});

const categoryOptions = computed(() => {
    return Object.entries(props.categories).map(([value, label]) => ({
        value,
        label,
        count: props.stats.category_stats[value] || 0
    }));
});

// Methods
const search = () => {
    router.get(route('clinicServices'), {
        category: filterForm.category,
        status: filterForm.status,
        search: filterForm.search,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const resetFilters = () => {
    filterForm.reset();
    search();
};

const openCreateModal = () => {
    createForm.reset();
    showCreateModal.value = true;
};

const openEditModal = (service: Service) => {
    editingService.value = service;
    editForm.reset();
    editForm.name = service.name;
    editForm.description = service.description;
    editForm.category = service.category;
    editForm.base_price = service.base_price;
    editForm.duration_minutes = service.duration_minutes;
    editForm.is_active = service.is_active;
    editForm.requires_appointment = service.requires_appointment;
    editForm.is_emergency_service = service.is_emergency_service;
    showEditModal.value = true;
    closeDropdown();
};

const createService = () => {
    createForm.post(route('clinicServices.store'), {
        onSuccess: () => {
            showCreateModal.value = false;
            createForm.reset();
        },
    });
};

const updateService = () => {
    if (!editingService.value) return;
    
    editForm.patch(route('clinicServices.update', editingService.value.id), {
        onSuccess: () => {
            showEditModal.value = false;
            editForm.reset();
            editingService.value = null;
        },
    });
};

const toggleServiceStatus = (service: Service) => {
    router.patch(route('clinicServices.toggleStatus', service.id), {}, {
        onSuccess: () => {
            closeDropdown();
        },
    });
};

const duplicateService = (service: Service) => {
    router.post(route('clinicServices.duplicate', service.id), {}, {
        onSuccess: () => {
            closeDropdown();
        },
    });
};

const deleteService = (service: Service) => {
    if (confirm(`Are you sure you want to delete "${service.name}"?`)) {
        router.delete(route('clinicServices.destroy', service.id), {
            onSuccess: () => {
                closeDropdown();
            },
        });
    }
};

const toggleDropdown = (serviceId: number) => {
    activeDropdown.value = activeDropdown.value === serviceId ? null : serviceId;
};

const closeDropdown = () => {
    activeDropdown.value = null;
};

const getCategoryIcon = (category: string) => {
    const icons: Record<string, string> = {
        consultation: '🩺',
        vaccination: '💉',
        surgery: '�',
        dental: '🦷',
        grooming: '✂️',
        boarding: '🏠',
        emergency: '�',
        diagnostic: '🧪',
        other: '⚕️',
    };
    return icons[category] || '🏥';
};

// Event listeners
onMounted(() => {
    const handleClickOutside = (event: Event) => {
        if (activeDropdown.value !== null) {
            closeDropdown();
        }
    };
    
    document.addEventListener('click', handleClickOutside);
    
    return () => {
        document.removeEventListener('click', handleClickOutside);
    };
});
</script>

<template>
    <Head title="Services Management" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-foreground">Services Management</h1>
                    <p class="text-muted-foreground">Manage your clinic services, pricing, and availability</p>
                </div>
                <button 
                    @click="openCreateModal"
                    class="inline-flex items-center gap-2 bg-primary text-primary-foreground hover:bg-primary/90 px-4 py-2 rounded-md text-sm font-medium transition-colors"
                >
                    <Plus class="h-4 w-4" />
                    Add Service
                </button>
            </div>

            <!-- Statistics -->
            <div class="grid gap-4 md:grid-cols-4">
                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Total Services</p>
                            <p class="text-2xl font-bold">{{ stats.total_services }}</p>
                        </div>
                        <div class="h-10 w-10 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center">
                            <Settings class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                        </div>
                    </div>
                </div>
                
                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Active Services</p>
                            <p class="text-2xl font-bold text-green-600">{{ stats.active_services }}</p>
                        </div>
                        <div class="h-10 w-10 rounded-full bg-green-100 dark:bg-green-900 flex items-center justify-center">
                            <Eye class="h-5 w-5 text-green-600 dark:text-green-400" />
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Inactive Services</p>
                            <p class="text-2xl font-bold text-gray-600">{{ stats.inactive_services }}</p>
                        </div>
                        <div class="h-10 w-10 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                            <EyeOff class="h-5 w-5 text-gray-600 dark:text-gray-400" />
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Total Revenue</p>
                            <p class="text-2xl font-bold text-green-600">{{ stats.formatted_revenue }}</p>
                        </div>
                        <div class="h-10 w-10 rounded-full bg-green-100 dark:bg-green-900 flex items-center justify-center">
                            <DollarSign class="h-5 w-5 text-green-600 dark:text-green-400" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="rounded-lg border bg-card p-4">
                <div class="flex flex-wrap items-center gap-4">
                    <div class="flex items-center gap-2">
                        <Search class="h-4 w-4 text-muted-foreground" />
                        <input
                            v-model="filterForm.search"
                            @keyup.enter="search"
                            type="text"
                            placeholder="Search services..."
                            class="border rounded-md px-3 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-primary"
                        />
                    </div>
                    
                    <select
                        v-model="filterForm.category"
                        @change="search"
                        class="border rounded-md px-3 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-primary"
                    >
                        <option value="all">All Categories</option>
                        <option 
                            v-for="category in categoryOptions" 
                            :key="category.value" 
                            :value="category.value"
                        >
                            {{ category.label }} ({{ category.count }})
                        </option>
                    </select>
                    
                    <select
                        v-model="filterForm.status"
                        @change="search"
                        class="border rounded-md px-3 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-primary"
                    >
                        <option value="all">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                    
                    <button 
                        @click="search"
                        class="inline-flex items-center gap-1 bg-primary text-primary-foreground hover:bg-primary/90 px-3 py-1 rounded-md text-sm font-medium transition-colors"
                    >
                        <Filter class="h-3 w-3" />
                        Filter
                    </button>
                    
                    <button 
                        @click="resetFilters"
                        class="text-sm text-muted-foreground hover:text-foreground"
                    >
                        Reset
                    </button>
                </div>
            </div>

            <!-- Services List -->
            <div class="space-y-6">
                <div 
                    v-for="(services, category) in categorizedServices"
                    :key="category"
                    class="rounded-lg border bg-card"
                >
                    <div class="flex items-center justify-between p-4 border-b">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">{{ getCategoryIcon(category) }}</span>
                            <div>
                                <h2 class="text-lg font-semibold">{{ categories[category] }}</h2>
                                <p class="text-sm text-muted-foreground">{{ services.length }} service(s)</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-4">
                        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                            <div 
                                v-for="service in services"
                                :key="service.id"
                                class="relative rounded-lg border p-4 hover:shadow-md transition-shadow"
                                :class="{ 'opacity-60': !service.is_active }"
                            >
                                <!-- Service Header -->
                                <div class="flex items-start justify-between mb-3">
                                    <div class="flex-1">
                                        <h3 class="font-semibold text-foreground flex items-center gap-2">
                                            {{ service.name }}
                                            <span 
                                                v-if="service.is_emergency_service"
                                                class="px-1.5 py-0.5 bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 rounded text-xs font-medium"
                                            >
                                                Emergency
                                            </span>
                                        </h3>
                                        <p class="text-sm text-muted-foreground mt-1">{{ service.description || 'No description' }}</p>
                                    </div>
                                    
                                    <div class="relative">
                                        <button 
                                            @click.stop="toggleDropdown(service.id)"
                                            class="p-1 hover:bg-muted rounded transition-colors"
                                        >
                                            <MoreVertical class="h-4 w-4" />
                                        </button>
                                        
                                        <!-- Dropdown Menu -->
                                        <div 
                                            v-if="activeDropdown === service.id"
                                            class="absolute right-0 top-8 w-48 bg-background border rounded-md shadow-lg z-10"
                                        >
                                            <button 
                                                @click="openEditModal(service)"
                                                class="flex items-center gap-2 w-full px-3 py-2 text-sm hover:bg-muted text-left"
                                            >
                                                <Edit class="h-4 w-4" />
                                                Edit Service
                                            </button>
                                            <button 
                                                @click="duplicateService(service)"
                                                class="flex items-center gap-2 w-full px-3 py-2 text-sm hover:bg-muted text-left"
                                            >
                                                <Copy class="h-4 w-4" />
                                                Duplicate
                                            </button>
                                            <button 
                                                @click="toggleServiceStatus(service)"
                                                class="flex items-center gap-2 w-full px-3 py-2 text-sm hover:bg-muted text-left"
                                                :class="service.is_active ? 'text-yellow-600' : 'text-green-600'"
                                            >
                                                <component :is="service.is_active ? EyeOff : Eye" class="h-4 w-4" />
                                                {{ service.is_active ? 'Deactivate' : 'Activate' }}
                                            </button>
                                            <hr class="my-1" />
                                            <button 
                                                @click="deleteService(service)"
                                                class="flex items-center gap-2 w-full px-3 py-2 text-sm hover:bg-muted text-left text-red-600"
                                            >
                                                <Trash2 class="h-4 w-4" />
                                                Delete
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Service Details -->
                                <div class="space-y-2 mb-4">
                                    <div class="flex justify-between">
                                        <span class="text-sm text-muted-foreground">Price:</span>
                                        <span class="text-sm font-semibold text-green-600">{{ service.formatted_price }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-muted-foreground">Duration:</span>
                                        <span class="text-sm">{{ service.formatted_duration }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-muted-foreground">Appointment:</span>
                                        <span class="text-sm">{{ service.requires_appointment ? 'Required' : 'Walk-in OK' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-muted-foreground">Status:</span>
                                        <span 
                                            class="text-xs px-2 py-1 rounded-full font-medium"
                                            :class="{
                                                'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200': service.is_active,
                                                'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200': !service.is_active
                                            }"
                                        >
                                            {{ service.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Usage Statistics -->
                                <div class="border-t pt-3 space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-xs text-muted-foreground">Total Bookings:</span>
                                        <span class="text-xs font-medium">{{ service.usage.total_appointments }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-xs text-muted-foreground">Revenue:</span>
                                        <span class="text-xs font-medium text-green-600">{{ service.usage.formatted_revenue }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-xs text-muted-foreground">Last Used:</span>
                                        <span class="text-xs">{{ service.usage.formatted_last_used }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="services.data.length === 0" class="text-center py-12">
                    <div class="text-muted-foreground">
                        <Settings class="h-12 w-12 mx-auto mb-4 opacity-50" />
                        <p class="text-lg mb-2">No services found</p>
                        <p class="mb-4">{{ filters.search || filters.category !== 'all' || filters.status !== 'all' ? 'Try adjusting your filters' : 'Start by creating your first service' }}</p>
                        <button 
                            @click="filters.search || filters.category !== 'all' || filters.status !== 'all' ? resetFilters() : openCreateModal()"
                            class="bg-primary text-primary-foreground hover:bg-primary/90 px-4 py-2 rounded-md text-sm font-medium transition-colors"
                        >
                            {{ filters.search || filters.category !== 'all' || filters.status !== 'all' ? 'Clear Filters' : 'Create First Service' }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Popular Services -->
            <div v-if="stats.popular_services.length > 0" class="rounded-lg border bg-card p-6">
                <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
                    <TrendingUp class="h-5 w-5" />
                    Most Popular Services
                </h3>
                <div class="space-y-3">
                    <div 
                        v-for="service in stats.popular_services"
                        :key="service.name"
                        class="flex items-center justify-between p-3 bg-muted/50 rounded-lg"
                    >
                        <div>
                            <p class="font-medium">{{ service.name }}</p>
                            <p class="text-sm text-muted-foreground">{{ service.category }}</p>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold">{{ service.appointment_count }}</p>
                            <p class="text-xs text-muted-foreground">bookings</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="services.links.length > 3" class="flex justify-center">
                <nav class="flex items-center gap-1">
                    <template v-for="link in services.links" :key="link.label">
                        <button
                            v-if="link.url"
                            @click="router.get(link.url)"
                            v-html="link.label"
                            class="px-3 py-1 text-sm rounded transition-colors"
                            :class="{
                                'bg-primary text-primary-foreground': link.active,
                                'hover:bg-muted': !link.active
                            }"
                        />
                        <span
                            v-else
                            v-html="link.label"
                            class="px-3 py-1 text-sm text-muted-foreground"
                        />
                    </template>
                </nav>
            </div>
        </div>

        <!-- Create Service Modal -->
        <div v-if="showCreateModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-background rounded-lg p-6 w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold">Create New Service</h2>
                    <button @click="showCreateModal = false" class="p-1 hover:bg-muted rounded">
                        <X class="h-5 w-5" />
                    </button>
                </div>

                <form @submit.prevent="createService" class="space-y-4">
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium mb-1">Service Name *</label>
                            <input
                                v-model="createForm.name"
                                type="text"
                                required
                                class="w-full border rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary"
                                placeholder="e.g., General Consultation"
                            />
                            <div v-if="createForm.errors.name" class="text-red-500 text-sm mt-1">
                                {{ createForm.errors.name }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Category *</label>
                            <select
                                v-model="createForm.category"
                                required
                                class="w-full border rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary"
                            >
                                <option value="">Select category</option>
                                <option v-for="(label, value) in categories" :key="value" :value="value">
                                    {{ label }}
                                </option>
                            </select>
                            <div v-if="createForm.errors.category" class="text-red-500 text-sm mt-1">
                                {{ createForm.errors.category }}
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Description</label>
                        <textarea
                            v-model="createForm.description"
                            rows="3"
                            class="w-full border rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary"
                            placeholder="Describe what this service includes..."
                        ></textarea>
                        <div v-if="createForm.errors.description" class="text-red-500 text-sm mt-1">
                            {{ createForm.errors.description }}
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium mb-1">Base Price (₱)</label>
                            <input
                                v-model.number="createForm.base_price"
                                type="number"
                                step="0.01"
                                min="0"
                                class="w-full border rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary"
                                placeholder="0.00"
                            />
                            <div v-if="createForm.errors.base_price" class="text-red-500 text-sm mt-1">
                                {{ createForm.errors.base_price }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Duration (minutes)</label>
                            <input
                                v-model.number="createForm.duration_minutes"
                                type="number"
                                min="1"
                                max="1440"
                                class="w-full border rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary"
                            />
                            <div v-if="createForm.errors.duration_minutes" class="text-red-500 text-sm mt-1">
                                {{ createForm.errors.duration_minutes }}
                            </div>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <label class="flex items-center gap-2">
                            <input
                                v-model="createForm.is_active"
                                type="checkbox"
                                class="rounded border-gray-300 text-primary focus:ring-primary"
                            />
                            <span class="text-sm">Service is active and bookable</span>
                        </label>

                        <label class="flex items-center gap-2">
                            <input
                                v-model="createForm.requires_appointment"
                                type="checkbox"
                                class="rounded border-gray-300 text-primary focus:ring-primary"
                            />
                            <span class="text-sm">Requires appointment (not walk-in)</span>
                        </label>

                        <label class="flex items-center gap-2">
                            <input
                                v-model="createForm.is_emergency_service"
                                type="checkbox"
                                class="rounded border-gray-300 text-primary focus:ring-primary"
                            />
                            <span class="text-sm">Emergency service (available 24/7)</span>
                        </label>
                    </div>

                    <div class="flex gap-3 pt-4">
                        <button
                            type="submit"
                            :disabled="createForm.processing"
                            class="flex-1 bg-primary text-primary-foreground hover:bg-primary/90 disabled:opacity-50 px-4 py-2 rounded-md font-medium transition-colors"
                        >
                            {{ createForm.processing ? 'Creating...' : 'Create Service' }}
                        </button>
                        <button
                            type="button"
                            @click="showCreateModal = false"
                            class="px-4 py-2 border rounded-md hover:bg-muted transition-colors"
                        >
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Service Modal -->
        <div v-if="showEditModal && editingService" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-background rounded-lg p-6 w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold">Edit Service</h2>
                    <button @click="showEditModal = false" class="p-1 hover:bg-muted rounded">
                        <X class="h-5 w-5" />
                    </button>
                </div>

                <form @submit.prevent="updateService" class="space-y-4">
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium mb-1">Service Name *</label>
                            <input
                                v-model="editForm.name"
                                type="text"
                                required
                                class="w-full border rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary"
                            />
                            <div v-if="editForm.errors.name" class="text-red-500 text-sm mt-1">
                                {{ editForm.errors.name }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Category *</label>
                            <select
                                v-model="editForm.category"
                                required
                                class="w-full border rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary"
                            >
                                <option v-for="(label, value) in categories" :key="value" :value="value">
                                    {{ label }}
                                </option>
                            </select>
                            <div v-if="editForm.errors.category" class="text-red-500 text-sm mt-1">
                                {{ editForm.errors.category }}
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Description</label>
                        <textarea
                            v-model="editForm.description"
                            rows="3"
                            class="w-full border rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary"
                        ></textarea>
                        <div v-if="editForm.errors.description" class="text-red-500 text-sm mt-1">
                            {{ editForm.errors.description }}
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium mb-1">Base Price (₱)</label>
                            <input
                                v-model.number="editForm.base_price"
                                type="number"
                                step="0.01"
                                min="0"
                                class="w-full border rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary"
                            />
                            <div v-if="editForm.errors.base_price" class="text-red-500 text-sm mt-1">
                                {{ editForm.errors.base_price }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Duration (minutes)</label>
                            <input
                                v-model.number="editForm.duration_minutes"
                                type="number"
                                min="1"
                                max="1440"
                                class="w-full border rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary"
                            />
                            <div v-if="editForm.errors.duration_minutes" class="text-red-500 text-sm mt-1">
                                {{ editForm.errors.duration_minutes }}
                            </div>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <label class="flex items-center gap-2">
                            <input
                                v-model="editForm.is_active"
                                type="checkbox"
                                class="rounded border-gray-300 text-primary focus:ring-primary"
                            />
                            <span class="text-sm">Service is active and bookable</span>
                        </label>

                        <label class="flex items-center gap-2">
                            <input
                                v-model="editForm.requires_appointment"
                                type="checkbox"
                                class="rounded border-gray-300 text-primary focus:ring-primary"
                            />
                            <span class="text-sm">Requires appointment (not walk-in)</span>
                        </label>

                        <label class="flex items-center gap-2">
                            <input
                                v-model="editForm.is_emergency_service"
                                type="checkbox"
                                class="rounded border-gray-300 text-primary focus:ring-primary"
                            />
                            <span class="text-sm">Emergency service (available 24/7)</span>
                        </label>
                    </div>

                    <div class="flex gap-3 pt-4">
                        <button
                            type="submit"
                            :disabled="editForm.processing"
                            class="flex-1 bg-primary text-primary-foreground hover:bg-primary/90 disabled:opacity-50 px-4 py-2 rounded-md font-medium transition-colors"
                        >
                            {{ editForm.processing ? 'Updating...' : 'Update Service' }}
                        </button>
                        <button
                            type="button"
                            @click="showEditModal = false"
                            class="px-4 py-2 border rounded-md hover:bg-muted transition-colors"
                        >
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>