<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { schedule, rescheduleAppointment } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';

// Props from the route
const props = defineProps<{
    appointmentId?: string | number;
}>();

interface AppointmentData {
    id: number;
    petName: string;
    petType: string;
    petBreed: string;
    petAge: string;
    appointmentType: string;
    clinicName: string;
    doctorName: string;
    date: string;
    time: string;
    duration: string;
    status: string;
    statusColor: string;
    address: string;
    phoneNumber: string;
    notes: string;
    services: string[];
    estimatedCost: string;
    confirmationNumber: string;
}

// Sample appointment data - this would typically come from an API
const appointmentDatabase: Record<number, AppointmentData> = {
    1: {
        id: 1,
        petName: "Bella",
        petType: "Dog",
        petBreed: "Golden Retriever",
        petAge: "3 years",
        appointmentType: "Annual Checkup",
        clinicName: "Happy Paws Veterinary",
        doctorName: "Dr. Sarah Johnson",
        date: "October 27, 2025",
        time: "2:30 PM",
        duration: "60 minutes",
        status: "Confirmed",
        statusColor: "text-green-600 dark:text-green-400",
        address: "789 Elm Street, Westside",
        phoneNumber: "(555) 123-4567",
        notes: "Bella has been doing well since her last visit. Please check her weight and update vaccinations if needed.",
        services: ["General Checkup", "Vaccination Update", "Weight Check", "Dental Examination"],
        estimatedCost: "$120 - $150",
        confirmationNumber: "APT-2025-001"
    },
    2: {
        id: 2,
        petName: "Max",
        petType: "Dog",
        petBreed: "German Shepherd",
        petAge: "2 years",
        appointmentType: "Vaccination",
        clinicName: "Animal Hospital Plus",
        doctorName: "Dr. Michael Chen",
        date: "November 3, 2025",
        time: "10:00 AM",
        duration: "30 minutes",
        status: "Pending",
        statusColor: "text-yellow-600 dark:text-yellow-400",
        address: "456 Oak Avenue, Downtown",
        phoneNumber: "(555) 987-6543",
        notes: "Max needs his annual vaccinations. Please bring vaccination record from previous vet.",
        services: ["DHPP Vaccination", "Rabies Shot", "Health Check"],
        estimatedCost: "$80 - $100",
        confirmationNumber: "APT-2025-002"
    },
    3: {
        id: 3,
        petName: "Luna",
        petType: "Cat",
        petBreed: "Siamese",
        petAge: "4 years",
        appointmentType: "Dental Cleaning",
        clinicName: "Pet Care Veterinary Clinic",
        doctorName: "Dr. Emily Rodriguez",
        date: "November 10, 2025",
        time: "2:00 PM",
        duration: "90 minutes",
        status: "Scheduled",
        statusColor: "text-blue-600 dark:text-blue-400",
        address: "123 Main Street, City Center",
        phoneNumber: "(555) 234-5678",
        notes: "Luna will need pre-anesthetic bloodwork before the dental procedure. Please fast for 12 hours before appointment.",
        services: ["Dental Cleaning", "Pre-anesthetic Bloodwork", "Dental X-rays", "Fluoride Treatment"],
        estimatedCost: "$250 - $300",
        confirmationNumber: "APT-2025-003"
    },
    4: {
        id: 4,
        petName: "Charlie",
        petType: "Dog",
        petBreed: "Labrador Mix",
        petAge: "5 years",
        appointmentType: "Follow-up",
        clinicName: "Happy Paws Veterinary",
        doctorName: "Dr. Sarah Johnson",
        date: "November 15, 2025",
        time: "11:30 AM",
        duration: "45 minutes",
        status: "Confirmed",
        statusColor: "text-green-600 dark:text-green-400",
        address: "789 Elm Street, Westside",
        phoneNumber: "(555) 123-4567",
        notes: "Follow-up visit to check on Charlie's recovery from surgery. Monitor wound healing and remove stitches if ready.",
        services: ["Wound Check", "Stitch Removal", "Pain Assessment", "Recovery Evaluation"],
        estimatedCost: "$60 - $80",
        confirmationNumber: "APT-2025-004"
    }
};

// Get appointment data based on ID
const appointment = computed(() => {
    const id = Number(props.appointmentId) || 1;
    return appointmentDatabase[id] || appointmentDatabase[1];
});

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Schedule',
        href: schedule().url,
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
    { id: 'documents', name: 'Documents', icon: '📄' }
];

// Sample visit history
const visitHistory = ref([
    {
        date: "August 15, 2025",
        type: "Vaccination",
        doctor: "Dr. Sarah Johnson",
        notes: "Updated rabies and DHPP vaccines. Bella was very cooperative.",
        cost: "$85"
    },
    {
        date: "May 10, 2025",
        type: "Routine Checkup",
        doctor: "Dr. Michael Chen",
        notes: "Excellent health. Recommended dental cleaning in 6 months.",
        cost: "$95"
    },
    {
        date: "February 22, 2025",
        type: "Dental Cleaning",
        doctor: "Dr. Sarah Johnson",
        notes: "Professional cleaning completed. No issues found.",
        cost: "$180"
    }
]);

// Sample documents
const documents = ref([
    { name: "Vaccination Record", type: "PDF", date: "Aug 15, 2025", size: "156 KB" },
    { name: "Lab Results", type: "PDF", date: "May 10, 2025", size: "89 KB" },
    { name: "X-Ray Images", type: "Images", date: "Feb 22, 2025", size: "2.1 MB" }
]);

const goBack = () => {
    window.history.back();
};

const goToReschedule = () => {
    // Navigate to reschedule page using Inertia
    router.visit(rescheduleAppointment(appointment.value.id).url);
};

const cancelAppointment = () => {
    // Handle cancel logic
    console.log('Cancel appointment');
};

const downloadDocument = (doc: any) => {
    // Handle document download
    console.log('Download document:', doc.name);
};

const callClinic = () => {
    window.open(`tel:${appointment.value.phoneNumber}`);
};

const getDirections = () => {
    const address = encodeURIComponent(appointment.value.address);
    window.open(`https://maps.google.com/?q=${address}`, '_blank');
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
                            Back
                        </button>
                        <button @click="goToReschedule" 
                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm font-medium">
                            Reschedule
                        </button>
                    </div>
                </div>

                <!-- Status Banner -->
                <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                    <div class="flex items-center">
                        <span class="text-green-500 text-lg mr-2">✅</span>
                        <div>
                            <p class="font-medium text-green-900 dark:text-green-100">
                                Appointment {{ appointment.status }}
                            </p>
                            <p class="text-sm text-green-700 dark:text-green-300">
                                Your appointment is scheduled for {{ appointment.date }} at {{ appointment.time }}
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
                                            {{ appointment.appointmentType }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600 dark:text-gray-400">Status:</span>
                                        <span :class="['text-sm font-medium', appointment.statusColor]">
                                            {{ appointment.status }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600 dark:text-gray-400">Estimated Cost:</span>
                                        <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ appointment.estimatedCost }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Services -->
                                <div class="pt-4 border-t border-gray-200 dark:border-gray-600">
                                    <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">Services Included</h4>
                                    <div class="flex flex-wrap gap-2">
                                        <span v-for="service in appointment.services" :key="service"
                                              class="px-2 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded dark:bg-blue-900 dark:text-blue-200">
                                            {{ service }}
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
                                        {{ appointment.clinicName }}
                                    </h4>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                        {{ appointment.address }}
                                    </p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                        {{ appointment.phoneNumber }}
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
                                        {{ appointment.doctorName }}
                                    </h4>
                                    <p class="text-sm text-blue-700 dark:text-blue-300 mb-2">
                                        Veterinarian
                                    </p>
                                    <p class="text-xs text-blue-600 dark:text-blue-400">
                                        Specializes in small animal care and preventive medicine
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Notes Section -->
                        <div class="pt-4 border-t border-gray-200 dark:border-gray-600">
                            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">Appointment Notes</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                                {{ appointment.notes }}
                            </p>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex gap-3 pt-4 border-t border-gray-200 dark:border-gray-600">
                            <button @click="goToReschedule"
                                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm font-medium">
                                Reschedule Appointment
                            </button>
                            <button @click="cancelAppointment"
                                    class="px-4 py-2 border border-red-300 text-red-700 rounded-md hover:bg-red-50 text-sm font-medium dark:border-red-600 dark:text-red-400 dark:hover:bg-red-900/20">
                                Cancel Appointment
                            </button>
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
                                        <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ appointment.petName }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600 dark:text-gray-400">Type:</span>
                                        <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ appointment.petType }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600 dark:text-gray-400">Breed:</span>
                                        <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ appointment.petBreed }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600 dark:text-gray-400">Age:</span>
                                        <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ appointment.petAge }}</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                                <h3 class="text-lg font-semibold text-blue-900 dark:text-blue-100 mb-4">Health Summary</h3>
                                <div class="space-y-2 text-sm">
                                    <p class="text-blue-700 dark:text-blue-300">✅ Vaccinations up to date</p>
                                    <p class="text-blue-700 dark:text-blue-300">✅ No known allergies</p>
                                    <p class="text-blue-700 dark:text-blue-300">✅ Good overall health</p>
                                    <p class="text-blue-700 dark:text-blue-300">⚠️ Due for dental cleaning</p>
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
                    </div>

                    <!-- Documents Tab -->
                    <div v-if="activeTab === 'documents'" class="space-y-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Related Documents</h3>
                        <div class="space-y-3">
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
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Additional custom styles if needed */
</style>
