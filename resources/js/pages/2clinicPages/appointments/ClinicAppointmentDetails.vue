<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { clinicDashboard, clinicAppointments, clinicAppointmentDetails } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

// Props from the backend
interface Props {
    appointment: {
        id: number;
        confirmationNumber: string;
        status: string;
        statusDisplay: string;
        date: string;
        time: string;
        duration: string;
        type: string;
        priority: string;
        reason: string;
        notes?: string;
        specialInstructions?: string;
        estimatedCost?: string;
        actualCost?: string;
        pet: {
            id: number;
            name: string;
            type: string;
            breed: string;
            age: string;
            weight?: number;
        };
        clinic: {
            id: number;
            name: string;
            address: string;
            phone: string;
            email: string;
        };
        veterinarian?: {
            id: number;
            name: string;
        };
        service?: {
            id: number;
            name: string;
            cost: number;
            description: string;
        };
        owner: {
            name: string;
            email: string;
            phone: string;
            emergencyContact: {
                name?: string;
                phone?: string;
            };
        };
    };
    visitHistory: Array<{
        date: string;
        type: string;
        doctor: string;
        notes: string;
        cost: string;
    }>;
    clinic: {
        id: number;
        name: string;
    };
}

const props = defineProps<Props>();

// Debug logging to verify data reception
console.log('ClinicAppointmentDetails loaded with appointment:', props.appointment);
console.log('Clinic:', props.clinic);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Clinic Dashboard',
        href: clinicDashboard().url,
    },
    {
        title: 'Appointments',
        href: clinicAppointments().url,
    },
    {
        title: 'Appointment Details',
        href: '#',
    },
];

const activeTab = ref('details');

const tabs = [
    { id: 'details', name: 'Appointment Details', icon: '📋' },
    { id: 'pet', name: 'Pet Information', icon: '🐕' },
    { id: 'history', name: 'Visit History', icon: '📊' },
    { id: 'treatment', name: 'Treatment Notes', icon: '📝' }
];

// Form for updating appointment status
const statusForm = useForm({
    status: props.appointment.status,
    notes: props.appointment.notes || '',
    actualCost: props.appointment.actualCost || '',
});

const availableStatuses = [
    { value: 'pending', label: 'Pending', color: 'yellow' },
    { value: 'confirmed', label: 'Confirmed', color: 'blue' },
    { value: 'in_progress', label: 'In Progress', color: 'orange' },
    { value: 'completed', label: 'Completed', color: 'green' },
    { value: 'cancelled', label: 'Cancelled', color: 'red' },
    { value: 'no_show', label: 'No Show', color: 'gray' },
];

const goBack = () => {
    router.visit(clinicAppointments().url);
};

const updateAppointmentStatus = () => {
    statusForm.patch(`/clinic/appointments/${props.appointment.id}/status`, {
        onSuccess: () => {
            // Stay on the same page to see updated status
        },
        onError: (errors) => {
            console.error('Error updating appointment:', errors);
        }
    });
};

const callOwner = () => {
    const phone = props.appointment?.owner?.phone || '';
    if (phone) window.open(`tel:${phone}`);
};

// Returns status banner configuration based on appointment status
const getStatusBanner = (status?: string) => {
    switch (status) {
        case 'confirmed': 
            return { 
                bgClass: 'bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800',
                icon: '✅', 
                iconColor: 'text-blue-500',
                titleColor: 'text-blue-900 dark:text-blue-100',
                descColor: 'text-blue-700 dark:text-blue-300'
            };
        case 'pending': 
            return { 
                bgClass: 'bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800',
                icon: '⏳', 
                iconColor: 'text-yellow-500',
                titleColor: 'text-yellow-900 dark:text-yellow-100',
                descColor: 'text-yellow-700 dark:text-yellow-300'
            };
        case 'in_progress': 
            return { 
                bgClass: 'bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800',
                icon: '🔄', 
                iconColor: 'text-orange-500',
                titleColor: 'text-orange-900 dark:text-orange-100',
                descColor: 'text-orange-700 dark:text-orange-300'
            };
        case 'completed': 
            return { 
                bgClass: 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800',
                icon: '✅', 
                iconColor: 'text-green-500',
                titleColor: 'text-green-900 dark:text-green-100',
                descColor: 'text-green-700 dark:text-green-300'
            };
        case 'cancelled': 
        case 'no_show':
            return { 
                bgClass: 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800',
                icon: '❌', 
                iconColor: 'text-red-500',
                titleColor: 'text-red-900 dark:text-red-100',
                descColor: 'text-red-700 dark:text-red-300'
            };
        default: 
            return { 
                bgClass: 'bg-gray-50 dark:bg-gray-900/20 border border-gray-200 dark:border-gray-800',
                icon: '📋', 
                iconColor: 'text-gray-500',
                titleColor: 'text-gray-900 dark:text-gray-100',
                descColor: 'text-gray-700 dark:text-gray-300'
            };
    }
};

// Returns a text color class for status badges
const getStatusColor = (status?: string) => {
    switch (status) {
        case 'confirmed': return 'text-blue-600 dark:text-blue-400';
        case 'pending': return 'text-yellow-600 dark:text-yellow-400';
        case 'in_progress': return 'text-orange-600 dark:text-orange-400';
        case 'completed': return 'text-green-600 dark:text-green-400';
        case 'cancelled': 
        case 'no_show': return 'text-red-600 dark:text-red-400';
        default: return 'text-gray-600 dark:text-gray-400';
    }
};
</script>

<template>
    <Head title="Clinic - Appointment Details" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <!-- Header -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Appointment Details</h1>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                            Confirmation #{{ appointment.confirmationNumber }} • {{ clinic.name }}
                        </p>
                    </div>
                    <div class="flex gap-2">
                        <button @click="goBack" 
                                class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 text-sm font-medium dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                            ← Back to Appointments
                        </button>
                    </div>
                </div>

                <!-- Status Banner -->
                <div :class="getStatusBanner(appointment.status).bgClass" class="rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <span :class="getStatusBanner(appointment.status).iconColor" class="text-lg mr-2">
                                {{ getStatusBanner(appointment.status).icon }}
                            </span>
                            <div>
                                <p :class="getStatusBanner(appointment.status).titleColor" class="font-medium">
                                    Appointment {{ appointment.statusDisplay }}
                                </p>
                                <p :class="getStatusBanner(appointment.status).descColor" class="text-sm">
                                    {{ appointment.pet.name }} with {{ appointment.owner.name }} • {{ appointment.date }} at {{ appointment.time }}
                                </p>
                            </div>
                        </div>
                        
                        <!-- Quick Status Update -->
                        <div class="flex items-center gap-2">
                            <select v-model="statusForm.status" 
                                    class="text-sm border border-gray-300 rounded-md px-3 py-1 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                <option v-for="status in availableStatuses" :key="status.value" :value="status.value">
                                    {{ status.label }}
                                </option>
                            </select>
                            <button @click="updateAppointmentStatus" 
                                    :disabled="statusForm.processing"
                                    class="px-3 py-1 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm font-medium disabled:opacity-50">
                                Update
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab Navigation -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                <div class="border-b border-gray-200 dark:border-gray-600">
                    <nav class="flex space-x-8 px-6" aria-label="Tabs">
                        <button
                            v-for="tab in tabs"
                            :key="tab.id"
                            @click="activeTab = tab.id"
                            :class="[
                                'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center gap-2',
                                activeTab === tab.id
                                    ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'
                            ]"
                        >
                            <span>{{ tab.icon }}</span>
                            {{ tab.name }}
                        </button>
                    </nav>
                </div>

                <!-- Tab Content -->
                <div class="p-6">
                    <!-- Appointment Details Tab -->
                    <div v-if="activeTab === 'details'" class="space-y-6">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <!-- Appointment Information -->
                            <div class="space-y-4">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                                    Appointment Information
                                </h3>
                                
                                <div class="space-y-3">
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600 dark:text-gray-400">Date & Time:</span>
                                        <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ appointment.date }} at {{ appointment.time }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600 dark:text-gray-400">Duration:</span>
                                        <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ appointment.duration }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600 dark:text-gray-400">Type:</span>
                                        <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ appointment.type }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600 dark:text-gray-400">Status:</span>
                                        <span :class="['text-sm font-medium', getStatusColor(appointment.status)]">
                                            {{ appointment.statusDisplay }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between" v-if="appointment.estimatedCost">
                                        <span class="text-sm text-gray-600 dark:text-gray-400">Estimated Cost:</span>
                                        <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ appointment.estimatedCost }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between" v-if="appointment.actualCost">
                                        <span class="text-sm text-gray-600 dark:text-gray-400">Actual Cost:</span>
                                        <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ appointment.actualCost }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Services -->
                                <div class="pt-4 border-t border-gray-200 dark:border-gray-600">
                                    <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">Services Included</h4>
                                    <div class="flex flex-wrap gap-2">
                                        <span v-if="appointment.service" 
                                              class="px-2 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded dark:bg-blue-900 dark:text-blue-200">
                                            {{ appointment.service.name }}
                                        </span>
                                        <span v-else
                                              class="px-2 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded dark:bg-blue-900 dark:text-blue-200">
                                            {{ appointment.type }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Owner & Pet Information -->
                            <div class="space-y-4">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                                    Owner & Pet Information
                                </h3>
                                
                                <!-- Owner Info -->
                                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                    <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-2">
                                        Owner: {{ appointment.owner.name }}
                                    </h4>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">
                                        📧 {{ appointment.owner.email }}
                                    </p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                                        📞 {{ appointment.owner.phone }}
                                    </p>
                                    
                                    <button @click="callOwner"
                                            class="w-full bg-green-600 text-white py-2 px-3 rounded-md hover:bg-green-700 text-sm font-medium">
                                        📞 Call Owner
                                    </button>
                                </div>

                                <!-- Pet Info -->
                                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                                    <h4 class="font-medium text-blue-900 dark:text-blue-100 mb-2">
                                        Pet: {{ appointment.pet.name }}
                                    </h4>
                                    <div class="text-sm space-y-1">
                                        <p class="text-blue-700 dark:text-blue-300">
                                            Type: {{ appointment.pet.type }}
                                        </p>
                                        <p class="text-blue-700 dark:text-blue-300">
                                            Breed: {{ appointment.pet.breed }}
                                        </p>
                                        <p class="text-blue-700 dark:text-blue-300">
                                            Age: {{ appointment.pet.age }}
                                        </p>
                                        <p v-if="appointment.pet.weight" class="text-blue-700 dark:text-blue-300">
                                            Weight: {{ appointment.pet.weight }} lbs
                                        </p>
                                    </div>
                                </div>

                                <!-- Veterinarian Assignment -->
                                <div class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-4">
                                    <h4 class="font-medium text-purple-900 dark:text-purple-100 mb-2">
                                        Veterinarian
                                    </h4>
                                    <p class="text-sm text-purple-700 dark:text-purple-300">
                                        {{ appointment.veterinarian?.name || 'Not assigned yet' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Notes Section -->
                        <div class="pt-4 border-t border-gray-200 dark:border-gray-600">
                            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">Appointment Reason</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                                {{ appointment.reason }}
                            </p>
                            <div v-if="appointment.notes" class="mt-3">
                                <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">Notes</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                                    {{ appointment.notes }}
                                </p>
                            </div>
                            <div v-if="appointment.specialInstructions" class="mt-3">
                                <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">Special Instructions</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                                    {{ appointment.specialInstructions }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Pet Information Tab -->
                    <div v-if="activeTab === 'pet'" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Pet Details</h3>
                                <div class="space-y-3">
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600 dark:text-gray-400">Name:</span>
                                        <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ appointment.pet.name }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600 dark:text-gray-400">Type:</span>
                                        <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ appointment.pet.type }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600 dark:text-gray-400">Breed:</span>
                                        <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ appointment.pet.breed }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600 dark:text-gray-400">Age:</span>
                                        <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ appointment.pet.age }}</span>
                                    </div>
                                    <div v-if="appointment.pet.weight" class="flex justify-between">
                                        <span class="text-sm text-gray-600 dark:text-gray-400">Weight:</span>
                                        <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ appointment.pet.weight }} lbs</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                                <h3 class="text-lg font-semibold text-blue-900 dark:text-blue-100 mb-4">Owner Information</h3>
                                <div class="space-y-2 text-sm">
                                    <p class="text-blue-700 dark:text-blue-300"><strong>Name:</strong> {{ appointment.owner.name }}</p>
                                    <p class="text-blue-700 dark:text-blue-300"><strong>Email:</strong> {{ appointment.owner.email }}</p>
                                    <p class="text-blue-700 dark:text-blue-300"><strong>Phone:</strong> {{ appointment.owner.phone }}</p>
                                    <div v-if="appointment.owner.emergencyContact?.name" class="pt-2 border-t border-blue-200 dark:border-blue-700">
                                        <p class="text-blue-700 dark:text-blue-300"><strong>Emergency Contact:</strong></p>
                                        <p class="text-blue-700 dark:text-blue-300">{{ appointment.owner.emergencyContact.name }}</p>
                                        <p class="text-blue-700 dark:text-blue-300">{{ appointment.owner.emergencyContact.phone }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Visit History Tab -->
                    <div v-if="activeTab === 'history'" class="space-y-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Previous Visits</h3>
                        <div class="space-y-3">
                            <div v-for="visit in visitHistory" :key="visit.date"
                                 class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <h4 class="font-medium text-gray-900 dark:text-gray-100">{{ visit.type }}</h4>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ visit.date }} • {{ visit.doctor }}</p>
                                    </div>
                                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ visit.cost }}</span>
                                </div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ visit.notes }}</p>
                            </div>
                        </div>
                        <div v-if="visitHistory.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
                            <p>No previous visits found for this pet.</p>
                        </div>
                    </div>

                    <!-- Treatment Notes Tab -->
                    <div v-if="activeTab === 'treatment'" class="space-y-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Treatment Notes & Billing</h3>
                        
                        <form @submit.prevent="updateAppointmentStatus" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">
                                    Appointment Status
                                </label>
                                <select v-model="statusForm.status" 
                                        class="w-full border border-gray-300 rounded-md px-3 py-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                    <option v-for="status in availableStatuses" :key="status.value" :value="status.value">
                                        {{ status.label }}
                                    </option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">
                                    Treatment Notes
                                </label>
                                <textarea v-model="statusForm.notes" 
                                          rows="4"
                                          placeholder="Add treatment notes, observations, or recommendations..."
                                          class="w-full border border-gray-300 rounded-md px-3 py-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"></textarea>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">
                                    Actual Cost (₱)
                                </label>
                                <input v-model="statusForm.actualCost" 
                                       type="number" 
                                       step="0.01"
                                       placeholder="0.00"
                                       class="w-full border border-gray-300 rounded-md px-3 py-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300" />
                            </div>
                            
                            <button type="submit" 
                                    :disabled="statusForm.processing"
                                    class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 font-medium disabled:opacity-50">
                                {{ statusForm.processing ? 'Updating...' : 'Update Appointment' }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Additional custom styles if needed */
</style>