<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import MedicalRecordFormFields from '@/components/MedicalRecordFormFields.vue';
import MedicalRecordView from '@/components/MedicalRecordView.vue';
import { clinicDashboard, clinicAppointments, clinicAppointmentDetails } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { 
    Calendar, 
    Clock, 
    FileText, 
    Stethoscope, 
    ChartBar, 
    Mail, 
    Phone, 
    User,
    PawPrint,
    CheckCircle2,
    XCircle,
    AlertCircle,
    RotateCw,
    ClipboardList,
    ArrowLeft,
    MoreVertical,
    Edit,
    UserX,
    Info
} from 'lucide-vue-next';

// Medical Record Templates for different types
const medicalRecordTemplates = {
    checkup: {
        label: 'General Checkup',
        icon: '🏥',
        color: 'blue',
        fields: ['diagnosis', 'physical_exam', 'vital_signs', 'treatment', 'medications', 'clinical_notes', 'follow_up_date']
    },
    vaccination: {
        label: 'Vaccination',
        icon: '💉',
        color: 'green',
        fields: ['vaccine_name', 'vaccine_batch', 'administration_site', 'next_due_date', 'adverse_reactions', 'clinical_notes']
    },
    treatment: {
        label: 'Treatment',
        icon: '⚕️',
        color: 'purple',
        fields: ['diagnosis', 'treatment', 'procedures_performed', 'medications', 'treatment_response', 'clinical_notes', 'follow_up_date']
    },
    surgery: {
        label: 'Surgery',
        icon: '🔪',
        color: 'red',
        fields: ['diagnosis', 'surgery_type', 'procedure_details', 'anesthesia_used', 'complications', 'post_op_instructions', 'medications', 'follow_up_date']
    },
    emergency: {
        label: 'Emergency',
        icon: '🚨',
        color: 'orange',
        fields: ['presenting_complaint', 'triage_level', 'diagnosis', 'emergency_treatment', 'stabilization_measures', 'medications', 'disposition', 'follow_up_date']
    },
    other: {
        label: 'Other',
        icon: '📋',
        color: 'gray',
        fields: ['diagnosis', 'treatment', 'medications', 'clinical_notes', 'follow_up_date']
    }
};

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
        canChangeVet?: boolean;
        isDisputed?: boolean;
        disputeReason?: string;
        disputedAt?: string;
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
            specializations?: string;
            license_number?: string;
        };
        service?: {
            id: number;
            name: string;
            cost: number;
            description: string;
        };
        canChangeVet?: boolean;
        isVetAutoAssigned?: boolean;
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
    availableVeterinarians?: Array<{
        id: number;
        name: string;
        specializations?: string;
        license_number?: string;
    }>;
}

const props = withDefaults(defineProps<Props>(), {
    availableVeterinarians: () => [],
});

// Debug logging to verify data reception
console.log('ClinicAppointmentDetails loaded with appointment:', props.appointment);
console.log('Clinic:', props.clinic);
console.log('Available Veterinarians:', props.availableVeterinarians);
console.log('Current vet ID:', props.appointment.veterinarian?.id);

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
const showMedicalRecordModal = ref(false);
const showRecordTypeSelection = ref(false);
const selectedRecordType = ref<string | null>(null);
const showConfirmModal = ref(false);
const showOverdueActionModal = ref(false);
const activeDropdown = ref(false);

const isCompleted = computed(() => {
    return ['completed', 'cancelled', 'no_show'].includes(props.appointment.status);
});

const isPending = computed(() => {
    return props.appointment.status === 'pending';
});

const isScheduled = computed(() => {
    return props.appointment.status === 'scheduled';
});

const isInProgress = computed(() => {
    return props.appointment.status === 'in_progress';
});

// Check if appointment datetime has passed
const appointmentDateTimePassed = computed(() => {
    const appointmentDateTime = new Date(`${props.appointment.date} ${props.appointment.time}`);
    return new Date() >= appointmentDateTime;
});

// Can start appointment (move to in_progress) when scheduled and time has passed
const canStartAppointment = computed(() => {
    return isScheduled.value && appointmentDateTimePassed.value && !isCompleted.value;
});

// Can mark as complete when in_progress
const canMarkComplete = computed(() => {
    return isInProgress.value && !isCompleted.value;
});

// Medical records can only be edited when appointment is in_progress
const canEditMedicalRecords = computed(() => {
    return isInProgress.value;
});

// Get active template based on selected record type
const activeTemplate = computed(() => {
    return medicalRecordTemplates[medicalForm.record_type as keyof typeof medicalRecordTemplates] || medicalRecordTemplates.checkup;
});

// Validate form based on record type requirements
const isFormValid = computed(() => {
    const type = medicalForm.record_type;
    
    // Type-specific required fields validation
    switch (type) {
        case 'checkup':
            return !!medicalForm.diagnosis && !!medicalForm.treatment;
        case 'vaccination':
            return !!medicalForm.vaccine_name;
        case 'treatment':
            return !!medicalForm.diagnosis && !!medicalForm.treatment;
        case 'surgery':
            return !!medicalForm.surgery_type && !!medicalForm.post_op_instructions;
        case 'emergency':
            return !!medicalForm.presenting_complaint && !!medicalForm.emergency_treatment;
        case 'other':
            return !!medicalForm.diagnosis && !!medicalForm.treatment;
        default:
            return !!medicalForm.diagnosis && !!medicalForm.treatment;
    }
});

const tabs = computed(() => {
    const baseTabs = [
        { id: 'details', name: 'Appointment Details', icon: ClipboardList },
        { id: 'medical', name: 'Medical Record', icon: Stethoscope },
        { id: 'history', name: 'Visit History', icon: ChartBar }
    ];
    
    return baseTabs;
});

// Form for updating appointment status
const statusForm = useForm({
    status: props.appointment.status,
    notes: props.appointment.notes || '',
    actualCost: props.appointment.actualCost || '',
});

// Form for medical record
const medicalForm = useForm({
    record_type: props.appointment.medicalRecord?.record_type || 'checkup',
    // Common fields
    diagnosis: props.appointment.medicalRecord?.diagnosis || '',
    treatment: props.appointment.medicalRecord?.treatment || '',
    medications: props.appointment.medicalRecord?.medications || '',
    clinical_notes: props.appointment.medicalRecord?.clinical_notes || '',
    follow_up_date: props.appointment.medicalRecord?.follow_up_date || '',
    // Checkup specific
    physical_exam: props.appointment.medicalRecord?.physical_exam || '',
    vital_signs: props.appointment.medicalRecord?.vital_signs || '',
    // Vaccination specific
    vaccine_name: props.appointment.medicalRecord?.vaccine_name || '',
    vaccine_batch: props.appointment.medicalRecord?.vaccine_batch || '',
    administration_site: props.appointment.medicalRecord?.administration_site || '',
    next_due_date: props.appointment.medicalRecord?.next_due_date || '',
    adverse_reactions: props.appointment.medicalRecord?.adverse_reactions || '',
    // Treatment specific
    procedures_performed: props.appointment.medicalRecord?.procedures_performed || '',
    treatment_response: props.appointment.medicalRecord?.treatment_response || '',
    // Surgery specific
    surgery_type: props.appointment.medicalRecord?.surgery_type || '',
    procedure_details: props.appointment.medicalRecord?.procedure_details || '',
    anesthesia_used: props.appointment.medicalRecord?.anesthesia_used || '',
    complications: props.appointment.medicalRecord?.complications || '',
    post_op_instructions: props.appointment.medicalRecord?.post_op_instructions || '',
    // Emergency specific
    presenting_complaint: props.appointment.medicalRecord?.presenting_complaint || '',
    triage_level: props.appointment.medicalRecord?.triage_level || '',
    emergency_treatment: props.appointment.medicalRecord?.emergency_treatment || '',
    stabilization_measures: props.appointment.medicalRecord?.stabilization_measures || '',
    disposition: props.appointment.medicalRecord?.disposition || '',
});

// Form for assigning veterinarian
const vetForm = useForm({
    veterinarian_id: props.appointment.veterinarian?.id || null,
});

console.log('VetForm initialized with:', vetForm.veterinarian_id);

const availableStatuses = [
    { value: 'pending', label: 'Pending', color: 'yellow' },
    { value: 'scheduled', label: 'Scheduled', color: 'blue' },
    { value: 'in_progress', label: 'In Progress', color: 'orange' },
    { value: 'completed', label: 'Completed', color: 'green' },
    { value: 'cancelled', label: 'Cancelled', color: 'red' },
    { value: 'no_show', label: 'No Show', color: 'gray' },
];

const goBack = () => {
    router.visit(clinicAppointments().url);
};

const confirmAppointment = () => {
    showConfirmModal.value = true;
};

const submitConfirmAppointment = () => {
    router.patch(`/clinic/appointments/${props.appointment.id}/confirm`, {}, {
        onSuccess: () => {
            showConfirmModal.value = false;
            console.log('Appointment confirmed successfully');
        },
        onError: (errors) => {
            console.error('Error confirming appointment:', errors);
        }
    });
};

const startAppointment = () => {
    statusForm.status = 'in_progress';
    statusForm.patch(`/clinic/appointments/${props.appointment.id}/status`, {
        onSuccess: () => {
            console.log('Appointment started - now in progress');
        },
        onError: (errors) => {
            console.error('Error starting appointment:', errors);
        }
    });
};

const markAsComplete = () => {
    // Step 1: Show record type selection
    showRecordTypeSelection.value = true;
};

const selectRecordType = (type: string) => {
    selectedRecordType.value = type;
    medicalForm.record_type = type;
    // Close type selection and open medical form
    showRecordTypeSelection.value = false;
    showMedicalRecordModal.value = true;
    activeTab.value = 'medical';
};

const confirmComplete = () => {
    // Validate using the isFormValid computed property
    if (!isFormValid.value) {
        alert('Please complete all required fields for this record type before completing.');
        return;
    }

    // Send all medical form data
    const completeData = {
        status: 'completed',
        notes: statusForm.notes,
        actualCost: statusForm.actualCost,
        medical_record: {
            // Record type
            record_type: medicalForm.record_type,
            // Common fields
            diagnosis: medicalForm.diagnosis,
            treatment: medicalForm.treatment,
            medications: medicalForm.medications,
            clinical_notes: medicalForm.clinical_notes,
            follow_up_date: medicalForm.follow_up_date,
            // Checkup specific
            physical_exam: medicalForm.physical_exam,
            vital_signs: medicalForm.vital_signs,
            // Vaccination specific
            vaccine_name: medicalForm.vaccine_name,
            vaccine_batch: medicalForm.vaccine_batch,
            administration_site: medicalForm.administration_site,
            next_due_date: medicalForm.next_due_date,
            adverse_reactions: medicalForm.adverse_reactions,
            // Treatment specific
            procedures_performed: medicalForm.procedures_performed,
            treatment_response: medicalForm.treatment_response,
            // Surgery specific
            surgery_type: medicalForm.surgery_type,
            procedure_details: medicalForm.procedure_details,
            anesthesia_used: medicalForm.anesthesia_used,
            complications: medicalForm.complications,
            post_op_instructions: medicalForm.post_op_instructions,
            // Emergency specific
            presenting_complaint: medicalForm.presenting_complaint,
            triage_level: medicalForm.triage_level,
            emergency_treatment: medicalForm.emergency_treatment,
            stabilization_measures: medicalForm.stabilization_measures,
            disposition: medicalForm.disposition,
        }
    };
    
    router.post(`/clinic/appointments/${props.appointment.id}/complete`, completeData, {
        onSuccess: () => {
            showMedicalRecordModal.value = false;
            router.visit('/clinic/history', {
                onSuccess: () => {
                    console.log('Appointment completed with medical records saved');
                }
            });
        },
        onError: (errors) => {
            console.error('Error completing appointment:', errors);
            alert('Failed to complete appointment. Please ensure all required fields are filled.');
        }
    });
};

const updateAppointmentStatus = () => {
    const newStatus = statusForm.status;
    const historicalStatuses = ['completed', 'cancelled', 'no_show'];
    
    statusForm.patch(`/clinic/appointments/${props.appointment.id}/status`, {
        onSuccess: () => {
            // If the appointment is now in a historical status, redirect to history
            if (historicalStatuses.includes(newStatus)) {
                // Show a success message and redirect to history
                router.visit('/clinic/history', {
                    onSuccess: () => {
                        // You could add a toast notification here if available
                        console.log(`Appointment marked as ${newStatus} and moved to history`);
                    }
                });
            }
            // Otherwise stay on the same page to see updated status
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

const assignVeterinarian = () => {
    if (!vetForm.veterinarian_id) return;
    
    vetForm.patch(`/clinic/appointments/${props.appointment.id}/assign-vet`, {
        onSuccess: () => {
            console.log('Veterinarian assigned successfully');
        },
        onError: (errors) => {
            console.error('Error assigning veterinarian:', errors);
        }
    });
};

const toggleDropdown = () => {
    activeDropdown.value = !activeDropdown.value;
};

const closeDropdown = () => {
    activeDropdown.value = false;
};

const changeVeterinarian = () => {
    // Don't allow changing vet if only one vet is available
    if (props.appointment.canChangeVet === false) {
        alert('Cannot change veterinarian assignment when only one veterinarian is available.');
        return;
    }
    vetForm.veterinarian_id = null;
    closeDropdown();
};

const removeVeterinarian = () => {
    if (confirm('Are you sure you want to remove the assigned veterinarian?')) {
        vetForm.veterinarian_id = null;
        vetForm.patch(`/clinic/appointments/${props.appointment.id}/assign-vet`, {
            onSuccess: () => {
                console.log('Veterinarian removed successfully');
                closeDropdown();
            },
            onError: (errors) => {
                console.error('Error removing veterinarian:', errors);
            }
        });
    }
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
            only: ['appointment', 'visitHistory'],
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
    }, 45000); // Refresh every 45 seconds (less frequent for details page)
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
    
    // Close dropdown when clicking outside
    document.addEventListener('click', (event) => {
        if (activeDropdown.value) {
            const target = event.target as HTMLElement;
            if (!target.closest('.vet-dropdown-container')) {
                closeDropdown();
            }
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
                bgClass: 'bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800',
                icon: CheckCircle2, 
                iconColor: 'text-blue-500',
                titleColor: 'text-blue-900 dark:text-blue-100',
                descColor: 'text-blue-700 dark:text-blue-300'
            };
        case 'scheduled': 
            return { 
                bgClass: 'bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800',
                icon: Clock, 
                iconColor: 'text-yellow-500',
                titleColor: 'text-yellow-900 dark:text-yellow-100',
                descColor: 'text-yellow-700 dark:text-yellow-300'
            };
        case 'in_progress': 
            return { 
                bgClass: 'bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800',
                icon: RotateCw, 
                iconColor: 'text-orange-500',
                titleColor: 'text-orange-900 dark:text-orange-100',
                descColor: 'text-orange-700 dark:text-orange-300'
            };
        case 'completed': 
            return { 
                bgClass: 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800',
                icon: CheckCircle2, 
                iconColor: 'text-green-500',
                titleColor: 'text-green-900 dark:text-green-100',
                descColor: 'text-green-700 dark:text-green-300'
            };
        case 'cancelled': 
        case 'no_show':
            return { 
                bgClass: 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800',
                icon: XCircle, 
                iconColor: 'text-red-500',
                titleColor: 'text-red-900 dark:text-red-100',
                descColor: 'text-red-700 dark:text-red-300'
            };
        default: 
            return { 
                bgClass: 'bg-gray-50 dark:bg-gray-900/20 border border-gray-200 dark:border-gray-800',
                icon: ClipboardList, 
                iconColor: 'text-gray-500',
                titleColor: 'text-gray-900 dark:text-gray-100',
                descColor: 'text-gray-700 dark:text-gray-300'
            };
    }
};

// Returns a text color class for status badges
const getStatusColor = (status?: string) => {
    switch (status) {
        case 'scheduled': return 'text-gray-600 dark:text-gray-400';
        case 'pending': return 'text-yellow-600 dark:text-yellow-400';
        case 'confirmed': return 'text-blue-600 dark:text-blue-400';
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
            <div class="rounded-lg border bg-card p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h1 class="text-2xl font-semibold">Appointment Details</h1>
                        <p class="text-sm text-muted-foreground mt-1">
                            Confirmation #{{ appointment.confirmationNumber }} • {{ clinic.name }}
                        </p>
                    </div>
                    <div>
                        <button @click="goBack" 
                                class="btn btn-outline flex items-center gap-2">
                            <ArrowLeft class="h-4 w-4" />
                            Back to Appointments
                        </button>
                    </div>
                </div>

                <!-- Status Banner -->
                <div :class="getStatusBanner(appointment.status).bgClass" class="rounded-lg p-4">
                    <div class="flex items-center">
                        <component :is="getStatusBanner(appointment.status).icon" :class="getStatusBanner(appointment.status).iconColor" class="h-5 w-5 mr-2" />
                        <div>
                            <p :class="getStatusBanner(appointment.status).titleColor" class="font-medium">
                                Appointment {{ appointment.statusDisplay }}
                            </p>
                            <p :class="getStatusBanner(appointment.status).descColor" class="text-sm">
                                {{ appointment.pet.name }} with {{ appointment.owner.name }} • {{ appointment.date }} at {{ appointment.time }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Dispute Alert (if disputed) -->
                <div v-if="appointment.isDisputed" class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                    <div class="flex items-start gap-3">
                        <AlertCircle class="h-5 w-5 text-yellow-600 dark:text-yellow-400 flex-shrink-0 mt-0.5" />
                        <div class="flex-1">
                            <p class="text-sm text-yellow-900 dark:text-yellow-100 font-medium mb-1">
                                🚨 Owner Disputed Medical Record
                            </p>
                            <p class="text-xs text-yellow-700 dark:text-yellow-300 mb-2">
                                Disputed on {{ appointment.disputedAt }}
                            </p>
                            <p class="text-sm text-yellow-800 dark:text-yellow-200 bg-yellow-100 dark:bg-yellow-900/40 rounded p-2">
                                <strong>Reason:</strong> {{ appointment.disputeReason }}
                            </p>
                            <p class="text-xs text-yellow-600 dark:text-yellow-400 mt-2">
                                Please review the medical record and contact the pet owner to resolve the issue.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Dispute Window Info (if within window and not disputed) -->
                <div v-if="appointment.status === 'completed' && !appointment.isDisputed && appointment.disputeHoursRemaining" 
                     class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                    <div class="flex items-start gap-3">
                        <Info class="h-5 w-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" />
                        <div>
                            <p class="text-sm text-blue-900 dark:text-blue-100 font-medium mb-1">
                                Owner Review Period Active
                            </p>
                            <p class="text-xs text-blue-700 dark:text-blue-300">
                                The pet owner has {{ appointment.disputeHoursRemaining }} hours remaining to review and dispute the medical record.
                            </p>
                            <p class="text-xs text-blue-600 dark:text-blue-400 mt-1">
                                Review period ends: {{ appointment.disputeWindowEndsAt }}
                            </p>
                        </div>
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
                                'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center gap-2 transition-colors',
                                activeTab === tab.id
                                    ? 'border-primary text-primary'
                                    : 'border-transparent text-muted-foreground hover:text-foreground hover:border-muted'
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
                                <h3 class="text-lg font-semibold mb-4">
                                    Owner & Pet Information
                                </h3>
                                
                                <!-- Owner Info -->
                                <div class="bg-muted/50 rounded-lg p-4">
                                    <h4 class="font-medium mb-2">
                                        Owner: {{ appointment.owner.name }}
                                    </h4>
                                    <div class="flex items-center gap-2 text-sm text-muted-foreground mb-1">
                                        <Mail class="h-4 w-4" />
                                        <span>{{ appointment.owner.email }}</span>
                                    </div>
                                    <div class="flex items-center gap-2 text-sm text-muted-foreground mb-3">
                                        <Phone class="h-4 w-4" />
                                        <span>{{ appointment.owner.phone }}</span>
                                    </div>
                                    
                                    <button @click="callOwner"
                                            class="btn btn-primary text-sm w-full flex items-center justify-center gap-2">
                                        <Phone class="h-4 w-4" />
                                        Call Owner
                                    </button>
                                </div>

                                <!-- Pet Info -->
                                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                                    <div class="flex items-center gap-2 mb-2">
                                        <PawPrint class="h-5 w-5 text-blue-700 dark:text-blue-300" />
                                        <h4 class="font-medium text-blue-900 dark:text-blue-100">
                                            Pet: {{ appointment.pet.name }}
                                        </h4>
                                    </div>
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
                                    <div class="flex items-center justify-between mb-3">
                                        <div class="flex items-center gap-2">
                                            <Stethoscope class="h-5 w-5 text-purple-700 dark:text-purple-300" />
                                            <h4 class="font-medium text-purple-900 dark:text-purple-100">
                                                Veterinarian
                                            </h4>
                                        </div>
                                        
                                        <!-- Kebab Menu -->
                                        <div v-if="!isCompleted" class="relative vet-dropdown-container">
                                            <button
                                                @click.stop="toggleDropdown"
                                                class="p-1 hover:bg-purple-200 dark:hover:bg-purple-800 rounded transition-colors"
                                            >
                                                <MoreVertical class="h-4 w-4 text-purple-700 dark:text-purple-300" />
                                            </button>
                                            
                                            <!-- Dropdown Menu -->
                                            <div
                                                v-if="activeDropdown"
                                                class="absolute right-0 top-8 w-48 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-md shadow-lg z-10"
                                            >
                                                <button
                                                    v-if="appointment.veterinarian && availableVeterinarians.length > 1"
                                                    @click="changeVeterinarian"
                                                    class="flex items-center gap-2 w-full px-3 py-2 text-sm hover:bg-muted text-left text-purple-700 dark:text-purple-300"
                                                >
                                                    <Edit class="h-4 w-4" />
                                                    Change Veterinarian
                                                </button>
                                                <button
                                                    v-if="appointment.veterinarian && !appointment.isVetAutoAssigned"
                                                    @click="removeVeterinarian"
                                                    class="flex items-center gap-2 w-full px-3 py-2 text-sm hover:bg-muted text-left text-red-600 dark:text-red-400"
                                                >
                                                    <UserX class="h-4 w-4" />
                                                    Remove Assignment
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Show assigned vet if exists -->
                                    <div v-if="appointment.veterinarian && !vetForm.processing" class="space-y-2">
                                        <p class="text-sm text-purple-700 dark:text-purple-300">
                                            <strong>{{ appointment.veterinarian.name }}</strong>
                                        </p>
                                        <p v-if="appointment.veterinarian.license_number" class="text-xs text-purple-600 dark:text-purple-400">
                                            License: {{ appointment.veterinarian.license_number }}
                                        </p>
                                        <p v-if="appointment.veterinarian.specializations" class="text-xs text-purple-600 dark:text-purple-400">
                                            Specializations: {{ appointment.veterinarian.specializations }}
                                        </p>
                                    </div>
                                    
                                    <!-- Show dropdown if multiple vets and (no vet assigned or changing) -->
                                    <div v-else-if="(availableVeterinarians?.length ?? 0) > 0 && !isCompleted" class="space-y-2">
                                        <select
                                            v-model="vetForm.veterinarian_id"
                                            class="w-full px-3 py-2 text-sm border border-purple-300 dark:border-purple-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-purple-500 dark:focus:ring-purple-400"
                                            :disabled="vetForm.processing"
                                        >
                                            <option :value="null">Select a veterinarian</option>
                                            <option
                                                v-for="vet in availableVeterinarians"
                                                :key="vet.id"
                                                :value="vet.id"
                                            >
                                                Dr. {{ vet.name }}<template v-if="vet.specializations && vet.specializations !== 'General Practice'"> - {{ vet.specializations }}</template>
                                            </option>
                                        </select>
                                        <button
                                            @click="assignVeterinarian"
                                            :disabled="!vetForm.veterinarian_id || vetForm.processing"
                                            class="w-full px-4 py-2 text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 dark:bg-purple-500 dark:hover:bg-purple-600 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                                        >
                                            {{ vetForm.processing ? 'Assigning...' : 'Assign Veterinarian' }}
                                        </button>
                                    </div>
                                    
                                    <!-- Show "not assigned" if no vets available -->
                                    <div v-else class="space-y-2">
                                        <p class="text-sm text-purple-700 dark:text-purple-300">
                                            {{ (availableVeterinarians?.length ?? 0) === 0 ? 'Not assigned yet' : 'Completed - No changes allowed' }}
                                        </p>
                                    </div>
                                    
                                    <!-- Show error if any -->
                                    <p v-if="vetForm.errors.veterinarian_id" class="mt-2 text-xs text-red-600 dark:text-red-400">
                                        {{ vetForm.errors.veterinarian_id }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons Section -->
                        <div v-if="!isCompleted" class="pt-4 border-t">
                            <h4 class="text-sm font-medium mb-3">Appointment Actions</h4>
                            <div class="space-y-3">
                                <!-- Confirm button for pending appointments -->
                                <button v-if="isPending" 
                                        @click="confirmAppointment"
                                        class="w-full px-4 py-3 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 rounded-lg transition-colors flex items-center justify-center gap-2">
                                    <CheckCircle2 class="h-4 w-4" />
                                    Confirm Appointment
                                </button>
                                
                                <!-- Start Appointment button for scheduled appointments -->
                                <button v-if="isScheduled && appointmentDateTimePassed"
                                        @click="startAppointment"
                                        :disabled="statusForm.processing"
                                        class="w-full px-4 py-3 text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 dark:bg-orange-500 dark:hover:bg-orange-600 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed transition-colors flex items-center justify-center gap-2">
                                    <RotateCw class="h-4 w-4" />
                                    {{ statusForm.processing ? 'Starting...' : 'Start Appointment' }}
                                </button>
                                
                                <!-- Info for scheduled but time not arrived -->
                                <div v-if="isScheduled && !appointmentDateTimePassed" class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-3 text-sm">
                                    <div class="flex items-start gap-2">
                                        <AlertCircle class="h-4 w-4 text-yellow-600 dark:text-yellow-400 mt-0.5 flex-shrink-0" />
                                        <p class="text-yellow-800 dark:text-yellow-300">
                                            Appointment can be started on {{ appointment.date }} at {{ appointment.time }}
                                        </p>
                                    </div>
                                </div>
                                
                                <!-- Mark Complete button for in_progress appointments -->
                                <button v-if="isInProgress"
                                        @click="markAsComplete"
                                        class="w-full px-4 py-3 text-sm font-medium text-white bg-green-600 hover:bg-green-700 dark:bg-green-500 dark:hover:bg-green-600 rounded-lg transition-colors flex items-center justify-center gap-2">
                                    <CheckCircle2 class="h-4 w-4" />
                                    Complete Appointment & Add Medical Record
                                </button>
                            </div>
                        </div>

                        <!-- Notes Section -->
                        <div class="pt-4 border-t">
                            <h4 class="text-sm font-medium mb-2">Appointment Reason</h4>
                            <p class="text-sm text-muted-foreground bg-muted/50 rounded-lg p-3">
                                {{ appointment.reason }}
                            </p>
                            <div v-if="appointment.notes" class="mt-3">
                                <h4 class="text-sm font-medium mb-2">Notes</h4>
                                <p class="text-sm text-muted-foreground bg-muted/50 rounded-lg p-3">
                                    {{ appointment.notes }}
                                </p>
                            </div>
                            <div v-if="appointment.specialInstructions" class="mt-3">
                                <h4 class="text-sm font-medium mb-2">Special Instructions</h4>
                                <p class="text-sm text-muted-foreground bg-muted/50 rounded-lg p-3">
                                    {{ appointment.specialInstructions }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Medical Record Tab -->
                    <div v-if="activeTab === 'medical'" class="space-y-4">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold">Medical Records</h3>
                            <span v-if="canEditMedicalRecords" class="text-xs px-3 py-1 bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300 rounded-full">
                                In Progress
                            </span>
                            <span v-else-if="isCompleted" class="text-xs px-3 py-1 bg-gray-100 dark:bg-gray-900 text-gray-700 dark:text-gray-300 rounded-full">
                                Completed
                            </span>
                        </div>
                        
                        <!-- Info banner based on status -->
                        <div v-if="isPending || isScheduled" class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                            <div class="flex items-start gap-2">
                                <AlertCircle class="h-4 w-4 text-yellow-600 dark:text-yellow-400 mt-0.5 flex-shrink-0" />
                                <div>
                                    <p class="text-yellow-800 dark:text-yellow-300 font-medium mb-1">Medical Records Not Yet Available</p>
                                    <p class="text-yellow-700 dark:text-yellow-400 text-sm">
                                        Medical records will be added when the appointment is completed. 
                                        Start the appointment when the patient arrives.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- In Progress: Show instruction to use Mark Complete button -->
                        <div v-else-if="isInProgress" class="text-center py-12">
                            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-8 border-2 border-dashed border-blue-300 dark:border-blue-600">
                                <Stethoscope class="h-12 w-12 text-blue-500 dark:text-blue-400 mx-auto mb-4" />
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">
                                    Appointment In Progress
                                </h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400 max-w-md mx-auto mb-4">
                                    Medical records will be added when you mark this appointment as complete. 
                                    Click the <strong>"Mark Complete"</strong> button below to add medical records and complete the appointment.
                                </p>
                            </div>
                        </div>

                        <!-- No medical records placeholder for completed appointments without data -->
                        <div v-if="isCompleted && !appointment.medicalRecord" class="text-center py-12">
                            <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-8 border-2 border-dashed border-gray-300 dark:border-gray-600">
                                <Stethoscope class="h-12 w-12 text-gray-400 dark:text-gray-500 mx-auto mb-4" />
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">
                                    No Medical Records Yet
                                </h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400 max-w-md mx-auto">
                                    No medical records were added for this appointment. This appointment was completed without documented medical notes.
                                </p>
                            </div>
                        </div>
                        
                        <!-- Read-only view for completed appointments with data -->
                        <div v-else-if="isCompleted && appointment.medicalRecord">
                            <MedicalRecordView :medicalRecord="appointment.medicalRecord" :date="appointment.date" />
                        </div>

                    </div>

                    <!-- Visit History Tab -->
                    <div v-if="activeTab === 'history'" class="space-y-4">
                        <h3 class="text-lg font-semibold">Previous Visits</h3>
                        <div class="space-y-3">
                            <div v-for="visit in visitHistory" :key="visit.date"
                                 class="bg-muted/50 rounded-lg p-4">
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
                </div>
            </div>
        </div>

        <!-- Record Type Selection Modal -->
        <div v-if="showRecordTypeSelection" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div class="bg-card rounded-lg p-6 w-full max-w-2xl shadow-xl">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold">Select Medical Record Type</h2>
                    <button @click="showRecordTypeSelection = false" class="text-muted-foreground hover:text-foreground">
                        <span class="text-2xl">&times;</span>
                    </button>
                </div>

                <div class="mb-6">
                    <p class="text-muted-foreground mb-4">
                        Choose the type of medical record for this appointment:
                    </p>
                    
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                        <button
                            v-for="(template, key) in medicalRecordTemplates"
                            :key="key"
                            @click="selectRecordType(key)"
                            :class="[
                                'p-4 rounded-lg border-2 transition-all hover:scale-105',
                                template.color === 'blue' ? 'border-blue-300 hover:border-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/20' :
                                template.color === 'green' ? 'border-green-300 hover:border-green-500 hover:bg-green-50 dark:hover:bg-green-900/20' :
                                template.color === 'purple' ? 'border-purple-300 hover:border-purple-500 hover:bg-purple-50 dark:hover:bg-purple-900/20' :
                                template.color === 'red' ? 'border-red-300 hover:border-red-500 hover:bg-red-50 dark:hover:bg-red-900/20' :
                                template.color === 'orange' ? 'border-orange-300 hover:border-orange-500 hover:bg-orange-50 dark:hover:bg-orange-900/20' :
                                'border-gray-300 hover:border-gray-500 hover:bg-gray-50 dark:hover:bg-gray-800'
                            ]"
                        >
                            <div class="text-center">
                                <div class="text-4xl mb-2">{{ template.icon }}</div>
                                <div class="font-medium text-sm">{{ template.label }}</div>
                            </div>
                        </button>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button 
                        @click="showRecordTypeSelection = false"
                        type="button"
                        class="btn btn-outline"
                    >
                        Cancel
                    </button>
                </div>
            </div>
        </div>

        <!-- Medical Record Form Modal -->
        <div v-if="showMedicalRecordModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4 overflow-y-auto">
            <div class="bg-card rounded-lg p-6 w-full max-w-3xl shadow-xl my-8">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <span class="text-3xl">{{ activeTemplate.icon }}</span>
                        <div>
                            <h2 class="text-xl font-semibold">{{ activeTemplate.label }} Record</h2>
                            <p class="text-sm text-muted-foreground">Complete appointment and save medical record</p>
                        </div>
                    </div>
                    <button @click="showMedicalRecordModal = false" class="text-muted-foreground hover:text-foreground">
                        <span class="text-2xl">&times;</span>
                    </button>
                </div>

                <div class="mb-6">
                    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 mb-6">
                        <p class="text-blue-900 dark:text-blue-100 font-medium">{{ appointment.pet.name }}</p>
                        <p class="text-blue-700 dark:text-blue-300">{{ appointment.date }} at {{ appointment.time }}</p>
                        <p class="text-blue-600 dark:text-blue-400 mt-1">Owner: {{ appointment.owner.name }}</p>
                    </div>

                    <!-- Dynamic form fields based on selected type -->
                    <MedicalRecordFormFields 
                        :form="medicalForm" 
                        :fields="activeTemplate.fields" 
                    />

                    <p class="text-sm text-amber-600 dark:text-amber-400 mt-6 flex items-center gap-2">
                        <AlertCircle class="h-4 w-4" />
                        Once completed, this appointment cannot be edited and will be moved to history.
                    </p>
                </div>

                <div class="flex gap-3">
                    <button 
                        @click="showMedicalRecordModal = false"
                        type="button"
                        class="btn btn-outline flex-1"
                    >
                        Cancel
                    </button>
                    <button 
                        @click="confirmComplete"
                        :disabled="!isFormValid"
                        type="button"
                        class="btn btn-primary flex-1 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Complete Appointment
                    </button>
                </div>
            </div>
        </div>

        <!-- Confirm Appointment Modal -->
        <div v-if="showConfirmModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div class="bg-card rounded-lg p-6 w-full max-w-md shadow-xl">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold">Confirm Appointment</h2>
                    <button @click="showConfirmModal = false" class="text-muted-foreground hover:text-foreground">
                        <span class="text-2xl">&times;</span>
                    </button>
                </div>

                <div class="mb-6">
                    <p class="text-muted-foreground mb-4">
                        Are you sure you want to confirm this appointment? The pet owner will be notified.
                    </p>
                    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-3 text-sm">
                        <p class="text-blue-900 dark:text-blue-100 font-medium">{{ appointment.pet.name }}</p>
                        <p class="text-blue-700 dark:text-blue-300">{{ appointment.date }} at {{ appointment.time }}</p>
                        <p class="text-blue-600 dark:text-blue-400 mt-2">Owner: {{ appointment.owner.name }}</p>
                        <p v-if="appointment.veterinarian" class="text-blue-600 dark:text-blue-400 mt-1">
                            Veterinarian: {{ appointment.veterinarian.name }}
                        </p>
                    </div>
                </div>

                <div class="flex gap-3">
                    <button 
                        @click="showConfirmModal = false"
                        type="button"
                        class="btn btn-outline flex-1"
                    >
                        Cancel
                    </button>
                    <button 
                        @click="submitConfirmAppointment"
                        type="button"
                        class="btn btn-primary flex-1"
                    >
                        Yes, Confirm Appointment
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Additional custom styles if needed */
</style>