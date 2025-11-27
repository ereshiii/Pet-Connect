<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { schedule, appointmentDetails, rescheduleAppointment, appointmentCalendar } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import {
    Calendar,
    Clock,
    FileText,
    Stethoscope,
    History,
    File,
    CheckCircle2,
    XCircle,
    AlertCircle,
    Phone,
    MapPin,
    User,
    DollarSign,
    Activity,
    Info,
    Download,
    Shield,
    RotateCw
} from 'lucide-vue-next';

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
        isDisputed?: boolean;
        disputeReason?: string;
        disputedAt?: string;
        canBeDisputed?: boolean;
        disputeWindowEndsAt?: string;
        disputeHoursRemaining?: number | null;
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
    medicalRecord?: {
        id: number;
        title: string;
        description: string;
        treatment: string;
        medication: string;
        veterinarian: string;
        date: string;
        follow_up_date?: string;
        notes?: string;
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

const activeTab = ref('medical');

const tabs = [
    { id: 'medical', name: 'Medical Record', icon: Stethoscope },
    { id: 'details', name: 'Appointment Details', icon: FileText },
    { id: 'history', name: 'Visit History', icon: History },
    { id: 'documents', name: 'Documents', icon: File }
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

// Dispute handling
const showDisputeModal = ref(false);
const disputeForm = useForm({
    reason: '',
});

const openDisputeModal = () => {
    showDisputeModal.value = true;
    disputeForm.reset();
};

const closeDisputeModal = () => {
    showDisputeModal.value = false;
    disputeForm.reset();
};

const submitDispute = () => {
    disputeForm.post(`/appointments/${props.appointment.id}/dispute`, {
        onSuccess: () => {
            closeDisputeModal();
        },
        onError: (errors) => {
            console.error('Error submitting dispute:', errors);
        }
    });
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

// Real-time update functionality
const isAutoRefreshEnabled = ref(true);
const refreshInterval = ref<number | null>(null);
const lastUpdated = ref(new Date());
const isRefreshing = ref(false);
const statusUpdateAlert = ref(false);

// Real-time update methods
const refreshAppointment = async () => {
    if (isRefreshing.value) return;
    
    isRefreshing.value = true;
    
    try {
        router.reload({
            only: ['appointment'],
            onSuccess: (page) => {
                lastUpdated.value = new Date();
                
                // Check if status has changed
                const newAppointment = page.props.appointment as typeof props.appointment;
                if (newAppointment && props.appointment && newAppointment.status !== props.appointment.status) {
                    statusUpdateAlert.value = true;
                    setTimeout(() => {
                        statusUpdateAlert.value = false;
                    }, 5000);
                }
            },
            onError: (errors) => {
                console.error('Failed to refresh appointment:', errors);
            },
            onFinish: () => {
                isRefreshing.value = false;
            }
        });
    } catch (error) {
        console.error('Error refreshing appointment:', error);
        isRefreshing.value = false;
    }
};

const toggleAutoRefresh = () => {
    isAutoRefreshEnabled.value = !isAutoRefreshEnabled.value;
    
    if (isAutoRefreshEnabled.value) {
        startAutoRefresh();
    } else {
        stopAutoRefresh();
    }
};

const startAutoRefresh = () => {
    if (refreshInterval.value) {
        clearInterval(refreshInterval.value);
    }
    
    refreshInterval.value = window.setInterval(() => {
        if (isAutoRefreshEnabled.value && !document.hidden) {
            refreshAppointment();
        }
    }, 30000); // Refresh every 30 seconds
};

const stopAutoRefresh = () => {
    if (refreshInterval.value) {
        clearInterval(refreshInterval.value);
        refreshInterval.value = null;
    }
};

const manualRefresh = () => {
    refreshAppointment();
};

// Lifecycle hooks
onMounted(() => {
    if (isAutoRefreshEnabled.value) {
        startAutoRefresh();
    }
    
    // Listen for visibility changes
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            stopAutoRefresh();
        } else if (isAutoRefreshEnabled.value) {
            startAutoRefresh();
            refreshAppointment();
        }
    });
    
    // Listen for focus events
    window.addEventListener('focus', () => {
        if (isAutoRefreshEnabled.value) {
            refreshAppointment();
        }
    });
});

onUnmounted(() => {
    stopAutoRefresh();
});

// Returns status banner configuration based on appointment status
const getStatusBanner = (status?: string) => {
    switch (status) {
        case 'confirmed': 
            return { 
                bgClass: 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800',
                icon: CheckCircle2, 
                iconColor: 'text-green-500',
                titleColor: 'text-green-900 dark:text-green-100',
                descColor: 'text-green-700 dark:text-green-300'
            };
        case 'pending': 
            return { 
                bgClass: 'bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800',
                icon: Clock, 
                iconColor: 'text-yellow-500',
                titleColor: 'text-yellow-900 dark:text-yellow-100',
                descColor: 'text-yellow-700 dark:text-yellow-300'
            };
        case 'scheduled': 
            return { 
                bgClass: 'bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800',
                icon: Calendar, 
                iconColor: 'text-blue-500',
                titleColor: 'text-blue-900 dark:text-blue-100',
                descColor: 'text-blue-700 dark:text-blue-300'
            };
        case 'cancelled': 
            return { 
                bgClass: 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800',
                icon: XCircle, 
                iconColor: 'text-red-500',
                titleColor: 'text-red-900 dark:text-red-100',
                descColor: 'text-red-700 dark:text-red-300'
            };
        case 'completed': 
            return { 
                bgClass: 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800',
                icon: CheckCircle2, 
                iconColor: 'text-green-500',
                titleColor: 'text-green-900 dark:text-green-100',
                descColor: 'text-green-700 dark:text-green-300'
            };
        default: 
            return { 
                bgClass: 'bg-muted/50 border',
                icon: FileText, 
                iconColor: 'text-muted-foreground',
                titleColor: 'text-foreground',
                descColor: 'text-muted-foreground'
            };
    }
};

// Returns a text color class for status badges in this details view
const getStatusColor = (status?: string) => {
    switch (status) {
        case 'pending': return 'text-yellow-600 dark:text-yellow-400';
        case 'confirmed': return 'text-green-600 dark:text-green-400';
        case 'scheduled': return 'text-blue-600 dark:text-blue-400';
        case 'in_progress': return 'text-orange-600 dark:text-orange-400';
        case 'completed': return 'text-green-600 dark:text-green-400';
        case 'cancelled': return 'text-red-600 dark:text-red-400';
        default: return 'text-muted-foreground';
    }
};
</script>

<template>
    <Head title="Appointment Details" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <!-- Header -->
            <div class="rounded-lg border bg-card p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h1 class="text-2xl font-semibold">Appointment Details</h1>
                        <p class="text-sm text-muted-foreground mt-1">
                            Confirmation #{{ appointment.confirmationNumber }}
                        </p>
                    </div>
                    <div class="flex gap-2">
                        <button @click="goToCalendar" 
                                class="px-4 py-2 border rounded-md hover:bg-muted text-sm font-medium flex items-center gap-2">
                            <Calendar class="h-4 w-4" />
                            Calendar
                        </button>
                        <button @click="goToReschedule" 
                                v-if="['scheduled', 'confirmed', 'scheduled'].includes(appointment.status)"
                                class="px-4 py-2 bg-primary text-primary-foreground rounded-md hover:bg-primary/90 text-sm font-medium">
                            Reschedule
                        </button>
                    </div>
                </div>

                <!-- Status Update Alert -->
                <div v-if="statusUpdateAlert" class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 animate-pulse">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <AlertCircle class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                            <p class="text-sm font-medium text-blue-900 dark:text-blue-100">
                                Appointment status has been updated
                            </p>
                        </div>
                        <button @click="statusUpdateAlert = false" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-200">
                            <span class="text-xl">&times;</span>
                        </button>
                    </div>
                </div>

                <!-- Status Banner -->
                <div :class="getStatusBanner(appointment.status).bgClass" class="rounded-lg p-4 border">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <component :is="getStatusBanner(appointment.status).icon" 
                                       :class="getStatusBanner(appointment.status).iconColor" 
                                       class="h-5 w-5 mr-3" />
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
                                        Your appointment is awaiting clinic confirmation
                                    </span>
                                    <span v-else-if="appointment.status === 'confirmed'">
                                        Your appointment is confirmed for {{ appointment.date }} at {{ appointment.time }}
                                    </span>
                                    <span v-else-if="appointment.status === 'scheduled'">
                                        Your appointment is scheduled for {{ appointment.date }} at {{ appointment.time }}
                                    </span>
                                    <span v-else>
                                        Your appointment is scheduled for {{ appointment.date }} at {{ appointment.time }}
                                    </span>
                                </p>
                            </div>
                        </div>
                        <button 
                            @click="manualRefresh" 
                            :disabled="isRefreshing"
                            class="px-3 py-1 text-sm border rounded-md hover:bg-muted flex items-center gap-2 disabled:opacity-50"
                            title="Refresh status">
                            <RotateCw :class="['h-4 w-4', isRefreshing && 'animate-spin']" />
                            <span class="hidden sm:inline">Refresh</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tab Navigation -->
            <div class="rounded-lg border bg-card">
                <div class="border-b">
                    <nav class="flex space-x-8 px-6" aria-label="Tabs">
                        <button
                            v-for="tab in tabs"
                            :key="tab.id"
                            @click="activeTab = tab.id"
                            :class="[
                                'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center gap-2',
                                activeTab === tab.id
                                    ? 'border-primary text-primary'
                                    : 'border-transparent text-muted-foreground hover:text-foreground hover:border-border'
                            ]"
                        >
                            <component :is="tab.icon" class="h-4 w-4" />
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
                                <h3 class="text-lg font-semibold mb-4">
                                    Appointment Information
                                </h3>
                                
                                <div class="space-y-3">
                                    <div class="flex justify-between">
                                        <span class="text-sm text-muted-foreground">Date & Time:</span>
                                        <span class="text-sm font-medium">
                                            {{ appointment.date }} at {{ appointment.time }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-muted-foreground">Duration:</span>
                                        <span class="text-sm font-medium">
                                            {{ appointment.duration }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-muted-foreground">Type:</span>
                                        <span class="text-sm font-medium">
                                            {{ appointment.type }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-muted-foreground">Status:</span>
                                        <span :class="['text-sm font-medium', getStatusColor(appointment.status)]">
                                            {{ appointment.statusDisplay }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between" v-if="appointment.estimatedCost">
                                        <span class="text-sm text-muted-foreground">Estimated Cost:</span>
                                        <span class="text-sm font-medium">
                                            {{ appointment.estimatedCost }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between" v-if="appointment.actualCost">
                                        <span class="text-sm text-muted-foreground">Actual Cost:</span>
                                        <span class="text-sm font-medium">
                                            {{ appointment.actualCost }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Services -->
                                <div class="pt-4 border-t">
                                    <h4 class="text-sm font-medium mb-2">Services Included</h4>
                                    <div class="flex flex-wrap gap-2">
                                        <span v-if="appointment.service" 
                                              class="px-2 py-1 bg-primary/10 text-primary text-xs font-medium rounded border border-primary/20">
                                            {{ appointment.service.name }}
                                        </span>
                                        <span v-else
                                              class="px-2 py-1 bg-primary/10 text-primary text-xs font-medium rounded border border-primary/20">
                                            {{ appointment.type }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Pet Information -->
                            <div class="space-y-4">
                                <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
                                    <User class="h-5 w-5 text-primary" />
                                    Pet Information
                                </h3>
                                
                                <div class="bg-muted/50 rounded-lg p-4 border">
                                    <h4 class="font-medium mb-3">{{ appointment.pet.name }}</h4>
                                    <div class="space-y-2">
                                        <div class="flex justify-between text-sm">
                                            <span class="text-muted-foreground">Type:</span>
                                            <span class="font-medium">{{ appointment.pet.type }}</span>
                                        </div>
                                        <div class="flex justify-between text-sm">
                                            <span class="text-muted-foreground">Breed:</span>
                                            <span class="font-medium">{{ appointment.pet.breed }}</span>
                                        </div>
                                        <div class="flex justify-between text-sm">
                                            <span class="text-muted-foreground">Age:</span>
                                            <span class="font-medium">{{ appointment.pet.age }}</span>
                                        </div>
                                        <div v-if="appointment.pet.weight" class="flex justify-between text-sm">
                                            <span class="text-muted-foreground">Weight:</span>
                                            <span class="font-medium">{{ appointment.pet.weight }} kg</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Clinic Information -->
                            <div class="space-y-4">
                                <h3 class="text-lg font-semibold mb-4">
                                    Clinic Information
                                </h3>
                                
                                <div class="bg-muted/50 rounded-lg p-4">
                                    <h4 class="font-medium mb-2">
                                        {{ appointment.clinic.name }}
                                    </h4>
                                    <p class="text-sm text-muted-foreground mb-2">
                                        {{ appointment.clinic.address }}
                                    </p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                        {{ appointment.clinic.phone }}
                                    </p>
                                    
                                    <div class="flex gap-2">
                                        <button @click="callClinic"
                                                class="flex-1 bg-green-600 text-white py-2 px-3 rounded-md hover:bg-green-700 text-sm font-medium flex items-center justify-center gap-2">
                                            <Phone class="h-4 w-4" />
                                            Call Clinic
                                        </button>
                                        <button @click="getDirections"
                                                class="flex-1 border py-2 px-3 rounded-md hover:bg-muted text-sm font-medium flex items-center justify-center gap-2">
                                            <MapPin class="h-4 w-4" />
                                            Get Directions
                                        </button>
                                    </div>
                                </div>

                                <!-- Doctor Information -->
                                <div class="bg-primary/10 rounded-lg p-4 border border-primary/20">
                                    <h4 class="font-medium text-primary mb-2">
                                        {{ appointment.veterinarian?.name || 'To Be Assigned' }}
                                    </h4>
                                    <p class="text-sm text-primary/80 mb-2">
                                        Veterinarian
                                    </p>
                                    <p class="text-xs text-primary/70">
                                        {{ appointment.veterinarian ? 'Specializes in small animal care and preventive medicine' : 'Veterinarian will be assigned closer to appointment date' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Reason Section -->
                        <div class="pt-4 border-t">
                            <h4 class="text-sm font-medium mb-2">Reason for Visit</h4>
                            <p class="text-sm text-muted-foreground bg-muted/50 rounded-lg p-3">
                                {{ appointment.reason || 'No reason provided' }}
                            </p>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex gap-3 pt-4 border-t" 
                             v-if="['scheduled', 'confirmed', 'scheduled'].includes(appointment.status)">
                            <button @click="goToReschedule"
                                    class="px-4 py-2 bg-primary text-primary-foreground rounded-md hover:bg-primary/90 text-sm font-medium">
                                Reschedule Appointment
                            </button>
                            <button @click="cancelAppointment"
                                    class="px-4 py-2 border border-red-500 text-red-600 rounded-md hover:bg-red-50 dark:hover:bg-red-950 text-sm font-medium">
                                Cancel Appointment
                            </button>
                        </div>
                        
                        <!-- Completed/Cancelled Status Info -->
                        <div v-else-if="appointment.status === 'completed'" 
                             class="flex items-center gap-2 pt-4 border-t">
                            <CheckCircle2 class="h-5 w-5 text-green-600 dark:text-green-400" />
                            <span class="text-sm text-muted-foreground">
                                This appointment has been completed.
                            </span>
                        </div>
                        
                        <div v-else-if="appointment.status === 'cancelled'" 
                             class="flex items-center gap-2 pt-4 border-t">
                            <XCircle class="h-5 w-5 text-red-600 dark:text-red-400" />
                            <span class="text-sm text-muted-foreground">
                                This appointment has been cancelled.
                            </span>
                        </div>
                    </div>

                    <!-- Medical Record Tab (View Only) -->
                    <div v-if="activeTab === 'medical'" class="space-y-6">
                        <div class="bg-muted/30 rounded-lg p-6 border">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold">Medical Record</h3>
                                <span class="text-xs px-2 py-1 bg-primary/10 text-primary rounded-full flex items-center gap-1">
                                    <Shield class="h-3 w-3" />
                                    View Only
                                </span>
                            </div>
                            
                            <div v-if="medicalRecord" class="space-y-4">
                                <!-- Record Header -->
                                <div class="bg-card rounded-lg p-4 border">
                                    <h4 class="text-sm font-semibold mb-2 text-muted-foreground">Record Information</h4>
                                    <div class="grid grid-cols-2 gap-4 text-sm">
                                        <div>
                                            <span class="text-muted-foreground">Title:</span>
                                            <p class="font-medium">{{ medicalRecord.title }}</p>
                                        </div>
                                        <div>
                                            <span class="text-muted-foreground">Date:</span>
                                            <p class="font-medium">{{ medicalRecord.date }}</p>
                                        </div>
                                        <div>
                                            <span class="text-muted-foreground">Veterinarian:</span>
                                            <p class="font-medium">{{ medicalRecord.veterinarian }}</p>
                                        </div>
                                        <div v-if="medicalRecord.follow_up_date">
                                            <span class="text-muted-foreground">Follow-up:</span>
                                            <p class="font-medium">{{ medicalRecord.follow_up_date }}</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Diagnosis -->
                                <div class="bg-card rounded-lg p-4 border">
                                    <h4 class="text-sm font-semibold mb-2 text-muted-foreground">Diagnosis</h4>
                                    <p class="text-sm">
                                        {{ medicalRecord.description || 'No diagnosis recorded' }}
                                    </p>
                                </div>
                                
                                <!-- Treatment Plan -->
                                <div class="bg-card rounded-lg p-4 border">
                                    <h4 class="text-sm font-semibold mb-2 text-muted-foreground">Treatment Plan</h4>
                                    <p class="text-sm">
                                        {{ medicalRecord.treatment || 'No treatment plan recorded' }}
                                    </p>
                                </div>
                                
                                <!-- Prescriptions -->
                                <div class="bg-card rounded-lg p-4 border">
                                    <h4 class="text-sm font-semibold mb-2 text-muted-foreground">Prescriptions</h4>
                                    <p class="text-sm">
                                        {{ medicalRecord.medication || 'No prescriptions recorded' }}
                                    </p>
                                </div>
                                
                                <!-- Clinical Notes -->
                                <div v-if="medicalRecord.notes" class="bg-card rounded-lg p-4 border">
                                    <h4 class="text-sm font-semibold mb-2 text-muted-foreground">Clinical Notes</h4>
                                    <p class="text-sm">
                                        {{ medicalRecord.notes }}
                                    </p>
                                </div>
                            </div>

                            <!-- No Record Message -->
                            <div v-else class="text-center py-8">
                                <Stethoscope class="h-16 w-16 mx-auto mb-4 opacity-30 text-muted-foreground" />
                                <p class="text-lg font-medium mb-2 text-foreground">No Medical Record Yet</p>
                                <p class="text-sm text-muted-foreground">
                                    A medical record will be automatically created when the appointment is completed.
                                </p>
                            </div>
                            
                            <!-- Info Message -->
                            <div class="bg-primary/5 border border-primary/20 rounded-lg p-4 mt-6">
                                <div class="flex items-start gap-3">
                                    <Info class="h-5 w-5 text-primary flex-shrink-0 mt-0.5" />
                                    <div>
                                        <p class="text-sm text-primary/90 font-medium mb-1">Medical records are managed by the clinic</p>
                                        <p class="text-xs text-primary/70">
                                            {{ medicalRecord 
                                                ? 'View the full patient record for complete medical history.' 
                                                : 'A medical record will be automatically created when the appointment is marked as completed.' 
                                            }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Dispute Alert (if disputed) -->
                            <div v-if="appointment.isDisputed" class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4 mt-4">
                                <div class="flex items-start gap-3">
                                    <AlertCircle class="h-5 w-5 text-yellow-600 dark:text-yellow-400 flex-shrink-0 mt-0.5" />
                                    <div class="flex-1">
                                        <p class="text-sm text-yellow-900 dark:text-yellow-100 font-medium mb-1">Record Disputed</p>
                                        <p class="text-xs text-yellow-700 dark:text-yellow-300 mb-2">
                                            Disputed on {{ appointment.disputedAt }}
                                        </p>
                                        <p class="text-sm text-yellow-800 dark:text-yellow-200">
                                            {{ appointment.disputeReason }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Dispute Window (if within window) -->
                            <div v-if="appointment.canBeDisputed && appointment.disputeHoursRemaining" 
                                 class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mt-4">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex items-start gap-3 flex-1">
                                        <Info class="h-5 w-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" />
                                        <div>
                                            <p class="text-sm text-blue-900 dark:text-blue-100 font-medium mb-1">
                                                Review Medical Record
                                            </p>
                                            <p class="text-xs text-blue-700 dark:text-blue-300">
                                                You have {{ appointment.disputeHoursRemaining }} hours remaining to review and dispute this medical record if there are any inaccuracies.
                                            </p>
                                            <p class="text-xs text-blue-600 dark:text-blue-400 mt-1">
                                                Dispute window ends: {{ appointment.disputeWindowEndsAt }}
                                            </p>
                                        </div>
                                    </div>
                                    <button 
                                        @click="openDisputeModal"
                                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm font-medium whitespace-nowrap">
                                        Report Issue
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Visit History Tab -->
                    <div v-if="activeTab === 'history'" class="space-y-4">
                        <h3 class="text-lg font-semibold">Previous Visits</h3>
                        <div class="space-y-3">
                            <div v-for="visit in visitHistory" :key="visit.date"
                                 class="bg-muted/50 rounded-lg p-4 border">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <h4 class="font-medium">{{ visit.type }}</h4>
                                        <p class="text-sm text-muted-foreground">{{ visit.date }} • {{ visit.doctor }}</p>
                                    </div>
                                    <span class="text-sm font-medium">{{ visit.cost }}</span>
                                </div>
                                <p class="text-sm text-muted-foreground">{{ visit.notes }}</p>
                            </div>
                        </div>
                        <div v-if="visitHistory.length === 0" class="text-center py-8 text-muted-foreground">
                            <p>No previous visits found for this pet.</p>
                        </div>
                    </div>

                    <!-- Documents Tab -->
                    <div v-if="activeTab === 'documents'" class="space-y-4">
                        <h3 class="text-lg font-semibold">Related Documents</h3>
                        <div v-if="documents && documents.length > 0" class="space-y-3">
                            <div v-for="doc in documents" :key="doc.name"
                                 class="flex items-center justify-between bg-muted/50 rounded-lg p-4 border">
                                <div class="flex items-center gap-3">
                                    <File class="h-8 w-8 text-muted-foreground" />
                                    <div>
                                        <h4 class="font-medium">{{ doc.name }}</h4>
                                        <p class="text-sm text-muted-foreground">{{ doc.type }} • {{ doc.date }} • {{ doc.size }}</p>
                                    </div>
                                </div>
                                <button @click="downloadDocument(doc)"
                                        class="px-3 py-1 bg-primary text-primary-foreground rounded-md hover:bg-primary/90 text-sm flex items-center gap-2">
                                    <Download class="h-4 w-4" />
                                    Download
                                </button>
                            </div>
                        </div>
                        <div v-else class="text-center py-8 text-muted-foreground">
                            <File class="h-16 w-16 mx-auto mb-4 opacity-30" />
                            <p class="text-lg font-medium mb-1">No Documents Available</p>
                            <p class="text-sm">Documents from this appointment will appear here once available.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dispute Modal -->
        <div v-if="showDisputeModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
            <div class="bg-card rounded-lg shadow-xl max-w-lg w-full border">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Report Medical Record Issue</h3>
                    <p class="text-sm text-muted-foreground mb-4">
                        Please describe any inaccuracies or concerns with the medical record. The clinic will review your report and make necessary corrections.
                    </p>
                    
                    <form @submit.prevent="submitDispute">
                        <div class="mb-4">
                            <label for="reason" class="block text-sm font-medium mb-2">
                                Issue Description <span class="text-red-500">*</span>
                            </label>
                            <textarea
                                id="reason"
                                v-model="disputeForm.reason"
                                rows="5"
                                required
                                maxlength="1000"
                                class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary resize-none"
                                placeholder="Describe the issue with the medical record..."
                            ></textarea>
                            <div class="flex justify-between mt-1">
                                <span v-if="disputeForm.errors.reason" class="text-xs text-red-500">
                                    {{ disputeForm.errors.reason }}
                                </span>
                                <span class="text-xs text-muted-foreground ml-auto">
                                    {{ disputeForm.reason.length }}/1000
                                </span>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3">
                            <button
                                type="button"
                                @click="closeDisputeModal"
                                :disabled="disputeForm.processing"
                                class="px-4 py-2 border rounded-md hover:bg-muted disabled:opacity-50"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="disputeForm.processing || !disputeForm.reason"
                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50 flex items-center gap-2"
                            >
                                <span v-if="disputeForm.processing">Submitting...</span>
                                <span v-else>Submit Report</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Additional custom styles if needed */
</style>
