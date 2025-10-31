<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { schedule, appointmentDetails, rescheduleAppointment, appointmentCalendar } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';

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
    documents?: Array<{
        name: string;
        type: string;
        date: string;
        size: string;
        url?: string;
    }>;
}

const props = defineProps<Props>();

// Debug logging to verify data reception
console.log('AppointmentDetails loaded with appointment:', props.appointment);
console.log('Appointment ID:', props.appointment?.id);
console.log('Appointment Status:', props.appointment?.status);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Schedule',
        href: schedule().url,
    },
    {
        title: 'Appointment Details',
        href: appointmentDetails(props.appointment.id).url,
    },
];

const activeTab = ref('details');

const tabs = [
    { id: 'details', name: 'Appointment Details', icon: '📋' },
    { id: 'pet', name: 'Pet Information', icon: '🐕' },
    { id: 'history', name: 'Visit History', icon: '📊' },
    { id: 'documents', name: 'Documents', icon: '📄' }
];

const goBack = () => {
    // Try to go back to schedule page, fallback to browser back
    try {
        router.visit(schedule().url);
    } catch (error) {
        window.history.back();
    }
};

const goToReschedule = () => {
    // Navigate to reschedule page using Inertia
    router.visit(rescheduleAppointment(props.appointment.id).url);
};

const cancelAppointment = () => {
    // Handle cancel logic with confirmation
    if (confirm('Are you sure you want to cancel this appointment?')) {
        router.delete(`/appointments/${props.appointment.id}`, {
            onSuccess: () => {
                router.visit(schedule().url);
            },
            onError: (errors) => {
                console.error('Error canceling appointment:', errors);
                alert('Failed to cancel appointment. Please try again.');
            }
        });
    }
};

const downloadDocument = (doc: any) => {
    // Handle document download
    console.log('Download document:', doc.name);
    // TODO: Implement actual document download
};

// Add a method to navigate back to calendar if user came from there
const goToCalendar = () => {
    router.visit(appointmentCalendar().url);
};

const callClinic = () => {
    const phone = props.appointment?.clinic?.phone || props.appointment?.owner?.phone || '';
    if (phone) window.open(`tel:${phone}`);
};

const getDirections = () => {
    const address = encodeURIComponent(props.appointment?.clinic?.address || '');
    if (address) window.open(`https://maps.google.com/?q=${address}`, '_blank');
};

// Returns status banner configuration based on appointment status
const getStatusBanner = (status?: string) => {
    switch (status) {
        case 'confirmed': 
            return { 
                bgClass: 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800',
                icon: '✅', 
                iconColor: 'text-green-500',
                titleColor: 'text-green-900 dark:text-green-100',
                descColor: 'text-green-700 dark:text-green-300'
            };
        case 'scheduled': 
            return { 
                bgClass: 'bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800',
                icon: '📅', 
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
        case 'cancelled': 
            return { 
                bgClass: 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800',
                icon: '❌', 
                iconColor: 'text-red-500',
                titleColor: 'text-red-900 dark:text-red-100',
                descColor: 'text-red-700 dark:text-red-300'
            };
        case 'completed': 
            return { 
                bgClass: 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800',
                icon: '✅', 
                iconColor: 'text-green-500',
                titleColor: 'text-green-900 dark:text-green-100',
                descColor: 'text-green-700 dark:text-green-300'
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

// Returns a text color class for status badges in this details view
const getStatusColor = (status?: string) => {
    switch (status) {
        case 'confirmed': return 'text-green-600 dark:text-green-400';
        case 'scheduled': return 'text-yellow-600 dark:text-yellow-400';
        case 'in_progress': return 'text-orange-600 dark:text-orange-400';
        case 'completed': return 'text-blue-600 dark:text-blue-400';
        case 'cancelled': return 'text-red-600 dark:text-red-400';
        default: return 'text-gray-600 dark:text-gray-400';
    }
};
</script>

<template>
    <Head title="Appointment Details" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <!-- Header -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Appointment Details</h1>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                            Confirmation #{{ appointment.confirmationNumber }}
                        </p>
                    </div>
                    <div class="flex gap-2">
                        <button @click="goBack" 
                                class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 text-sm font-medium dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                            ← Back to Schedule
                        </button>
                        <button @click="goToCalendar" 
                                class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 text-sm font-medium dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                            📅 Calendar
                        </button>
                        <button @click="goToReschedule" 
                                v-if="['scheduled', 'confirmed', 'pending'].includes(appointment.status)"
                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm font-medium">
                            Reschedule
                        </button>
                    </div>
                </div>

                <!-- Status Banner -->
                <div :class="getStatusBanner(appointment.status).bgClass" class="rounded-lg p-4">
                    <div class="flex items-center">
                        <span :class="getStatusBanner(appointment.status).iconColor" class="text-lg mr-2">
                            {{ getStatusBanner(appointment.status).icon }}
                        </span>
                        <div>
                            <p :class="getStatusBanner(appointment.status).titleColor" class="font-medium">
                                Appointment {{ appointment.statusDisplay }}
                            </p>
                            <p :class="getStatusBanner(appointment.status).descColor" class="text-sm">
                                <span v-if="appointment.status === 'cancelled'">
                                    This appointment was cancelled
                                </span>
                                <span v-else-if="appointment.status === 'completed'">
                                    This appointment was completed on {{ appointment.date }}
                                </span>
                                <span v-else-if="appointment.status === 'pending'">
                                    Your appointment is pending approval and scheduled for {{ appointment.date }} at {{ appointment.time }}
                                </span>
                                <span v-else>
                                    Your appointment is scheduled for {{ appointment.date }} at {{ appointment.time }}
                                </span>
                            </p>
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

                            <!-- Clinic Information -->
                            <div class="space-y-4">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                                    Clinic Information
                                </h3>
                                
                                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                    <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-2">
                                        {{ appointment.clinic.name }}
                                    </h4>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                        {{ appointment.clinic.address }}
                                    </p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                        {{ appointment.clinic.phone }}
                                    </p>
                                    
                                    <div class="flex gap-2">
                                        <button @click="callClinic"
                                                class="flex-1 bg-green-600 text-white py-2 px-3 rounded-md hover:bg-green-700 text-sm font-medium">
                                            Call Clinic
                                        </button>
                                        <button @click="getDirections"
                                                class="flex-1 border border-gray-300 text-gray-700 py-2 px-3 rounded-md hover:bg-gray-50 text-sm font-medium dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                                            Get Directions
                                        </button>
                                    </div>
                                </div>

                                <!-- Doctor Information -->
                                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                                    <h4 class="font-medium text-blue-900 dark:text-blue-100 mb-2">
                                        {{ appointment.veterinarian?.name || 'To Be Assigned' }}
                                    </h4>
                                    <p class="text-sm text-blue-700 dark:text-blue-300 mb-2">
                                        Veterinarian
                                    </p>
                                    <p class="text-xs text-blue-600 dark:text-blue-400">
                                        {{ appointment.veterinarian ? 'Specializes in small animal care and preventive medicine' : 'Veterinarian will be assigned closer to appointment date' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Notes Section -->
                        <div class="pt-4 border-t border-gray-200 dark:border-gray-600">
                            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">Appointment Notes</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                                {{ appointment.reason }}
                            </p>
                            <div v-if="appointment.notes" class="mt-3">
                                <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">Additional Notes</h4>
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

                        <!-- Action Buttons -->
                        <div class="flex gap-3 pt-4 border-t border-gray-200 dark:border-gray-600" 
                             v-if="['scheduled', 'confirmed', 'pending'].includes(appointment.status)">
                            <button @click="goToReschedule"
                                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm font-medium">
                                Reschedule Appointment
                            </button>
                            <button @click="cancelAppointment"
                                    class="px-4 py-2 border border-red-300 text-red-700 rounded-md hover:bg-red-50 text-sm font-medium dark:border-red-600 dark:text-red-400 dark:hover:bg-red-900/20">
                                Cancel Appointment
                            </button>
                        </div>
                        
                        <!-- Completed/Cancelled Status Info -->
                        <div v-else-if="appointment.status === 'completed'" 
                             class="flex items-center gap-2 pt-4 border-t border-gray-200 dark:border-gray-600">
                            <span class="text-green-600 dark:text-green-400">✅</span>
                            <span class="text-sm text-gray-600 dark:text-gray-400">
                                This appointment has been completed.
                            </span>
                        </div>
                        
                        <div v-else-if="appointment.status === 'cancelled'" 
                             class="flex items-center gap-2 pt-4 border-t border-gray-200 dark:border-gray-600">
                            <span class="text-red-600 dark:text-red-400">❌</span>
                            <span class="text-sm text-gray-600 dark:text-gray-400">
                                This appointment has been cancelled.
                            </span>
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
                                    <p class="text-blue-700 dark:text-blue-300">
                                        <strong>Owner:</strong> {{ appointment.owner.name }}
                                    </p>
                                    <p class="text-blue-700 dark:text-blue-300">
                                        <strong>Email:</strong> {{ appointment.owner.email }}
                                    </p>
                                    <p class="text-blue-700 dark:text-blue-300">
                                        <strong>Phone:</strong> {{ appointment.owner.phone }}
                                    </p>
                                    <div v-if="appointment.owner.emergencyContact?.name" class="pt-2 border-t border-blue-200 dark:border-blue-700">
                                        <p class="text-blue-700 dark:text-blue-300">
                                            <strong>Emergency Contact:</strong>
                                        </p>
                                        <p class="text-blue-700 dark:text-blue-300">
                                            {{ appointment.owner.emergencyContact.name }}
                                        </p>
                                        <p class="text-blue-700 dark:text-blue-300" v-if="appointment.owner.emergencyContact.phone">
                                            {{ appointment.owner.emergencyContact.phone }}
                                        </p>
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

                    <!-- Documents Tab -->
                    <div v-if="activeTab === 'documents'" class="space-y-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Related Documents</h3>
                        <div v-if="documents && documents.length > 0" class="space-y-3">
                            <div v-for="doc in documents" :key="doc.name"
                                 class="flex items-center justify-between bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                <div class="flex items-center gap-3">
                                    <span class="text-2xl">📄</span>
                                    <div>
                                        <h4 class="font-medium text-gray-900 dark:text-gray-100">{{ doc.name }}</h4>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ doc.type }} • {{ doc.date }} • {{ doc.size }}</p>
                                    </div>
                                </div>
                                <button @click="downloadDocument(doc)"
                                        class="px-3 py-1 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm">
                                    Download
                                </button>
                            </div>
                        </div>
                        <div v-else class="text-center py-8 text-gray-500 dark:text-gray-400">
                            <div class="text-4xl mb-2">📄</div>
                            <p class="text-lg font-medium mb-1">No Documents Available</p>
                            <p class="text-sm">Documents from this appointment will appear here once available.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Additional custom styles if needed */
</style>
