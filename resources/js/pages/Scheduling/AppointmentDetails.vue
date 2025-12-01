<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import MedicalRecordFormFields from '@/components/MedicalRecordFormFields.vue';
import MedicalRecordView from '@/components/MedicalRecordView.vue';
import { schedule, appointmentDetails, rescheduleAppointment, appointmentCalendar } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
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
    RotateCw,
    ClipboardList,
    ArrowLeft,
    MoreVertical,
    Edit,
    UserX,
    ChartBar,
    Star,
    Send,
    X,
    Upload,
    FileUp,
    Paperclip,
    Plus
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
        scheduledAt: string;
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
        canChangeVet?: boolean;
        appointment_type?: 'scheduled' | 'walk-in';
        is_follow_up?: boolean;
        parent_appointment_id?: number | null;
        can_create_follow_up?: boolean;
        confirmation_window_ends_at?: string | null;
        confirmed_at?: string | null;
        can_owner_reschedule_or_cancel?: boolean;
        can_clinic_reschedule?: boolean;
        can_clinic_cancel?: boolean;
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
        owner: {
            name: string;
            email: string;
            phone: string;
            emergencyContact: {
                name?: string;
                phone?: string;
            };
        };
        medicalRecord?: {
            id: number;
            record_type: string;
            title: string;
            description: string;
            diagnosis?: string;
            treatment?: string;
            medications?: string;
            clinical_notes?: string;
            physical_exam?: string;
            vital_signs?: string;
            vaccine_name?: string;
            vaccine_batch?: string;
            administration_site?: string;
            next_due_date?: string;
            adverse_reactions?: string;
            procedures_performed?: string;
            treatment_response?: string;
            surgery_type?: string;
            procedure_details?: string;
            anesthesia_used?: string;
            complications?: string;
            post_op_instructions?: string;
            presenting_complaint?: string;
            triage_level?: string;
            emergency_treatment?: string;
            stabilization_measures?: string;
            disposition?: string;
            follow_up_date?: string;
            veterinarian: string;
            date: string;
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
    availableVeterinarians?: Array<{
        id: number;
        name: string;
        specializations?: string;
        license_number?: string;
    }>;
    canChangeVet?: boolean;
    canEditMedicalRecords?: boolean;
    medicalRecordEditableUntil?: string;
    clinic?: {
        id: number;
        name: string;
    };
    userRole?: string;
    hasRating?: boolean;
    clinicRating?: {
        id: number;
        rating: number;
        comment?: string;
        created_at: string;
    };
}

const props = withDefaults(defineProps<Props>(), {
    availableVeterinarians: () => [],
    canChangeVet: false,
    canEditMedicalRecords: false,
    userRole: 'user',
});

const page = usePage();
const auth = computed(() => page.props.auth);

// Role-based computed properties
const isClinic = computed(() => props.userRole === 'clinic' || auth.value.user?.account_type === 'clinic');
const isPetOwner = computed(() => props.userRole === 'user' || auth.value.user?.account_type === 'user');
const isAdmin = computed(() => auth.value.user?.is_admin === true);

// Clinic-specific computed properties
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

const canMarkComplete = computed(() => {
    return isClinic.value && isInProgress.value && !isCompleted.value;
});

const canEditMedicalRecordsComputed = computed(() => {
    if (!isClinic.value) return false;
    return props.canEditMedicalRecords || isInProgress.value;
});

const isFormValid = computed(() => {
    // Only diagnosis is required
    return !!medicalForm.diagnosis;
});

// Debug logging to verify data reception
console.log('AppointmentDetails loaded with appointment:', props.appointment);
console.log('Appointment ID:', props.appointment?.id);
console.log('Appointment Status:', props.appointment?.status);
console.log('User Role:', props.userRole);
console.log('Is Clinic:', isClinic.value);
console.log('Is Pet Owner:', isPetOwner.value);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: isClinic.value ? 'Clinic Dashboard' : 'Schedule',
        href: isClinic.value ? '/clinic/dashboard' : schedule().url,
    },
    ...(isClinic.value ? [{
        title: 'Appointments',
        href: '/clinic/appointments',
    }] : []),
    {
        title: 'Appointment Details',
        href: appointmentDetails(props.appointment.id).url,
    },
];

const activeTab = ref('details');
const showMedicalRecordModal = ref(false);
const showConfirmModal = ref(false);
const showNoShowModal = ref(false);
const noShowReason = ref('');
const activeDropdown = ref(false);

// New state for follow-up, reschedule, cancel, and documents
const showFollowUpModal = ref(false);
const showClinicRescheduleModal = ref(false);
const showClinicCancelModal = ref(false);
const followUpForm = ref({
    scheduled_at: '',
    reason: '',
    notes: '',
});
const clinicRescheduleForm = ref({
    scheduled_at: '',
    reason: '',
});
const clinicCancelForm = ref({
    reason: '',
});
const isSubmittingFollowUp = ref(false);
const isSubmittingReschedule = ref(false);
const isSubmittingCancel = ref(false);

const tabs = computed(() => {
    const baseTabs = [
        { id: 'details', name: 'Appointment Details', icon: ClipboardList },
        { id: 'medical', name: 'Medical Record', icon: Stethoscope },
    ];
    
    return baseTabs;
});

// Form for updating appointment status (clinic only)
const statusForm = useForm({
    status: props.appointment.status,
    notes: props.appointment.notes || '',
    actualCost: props.appointment.actualCost || '',
});

// Form for medical record (clinic only)
const medicalForm = useForm({
    record_type: props.appointment.medicalRecord?.record_type || 'checkup',
    diagnosis: props.appointment.medicalRecord?.diagnosis || '',
    treatment: props.appointment.medicalRecord?.treatment || '',
    medications: props.appointment.medicalRecord?.medications || '',
    clinical_notes: props.appointment.medicalRecord?.clinical_notes || '',
    follow_up_date: props.appointment.medicalRecord?.follow_up_date || '',
    physical_exam: props.appointment.medicalRecord?.physical_exam || '',
    vital_signs: props.appointment.medicalRecord?.vital_signs || '',
    vaccine_name: props.appointment.medicalRecord?.vaccine_name || '',
    vaccine_batch: props.appointment.medicalRecord?.vaccine_batch || '',
    administration_site: props.appointment.medicalRecord?.administration_site || '',
    next_due_date: props.appointment.medicalRecord?.next_due_date || '',
    adverse_reactions: props.appointment.medicalRecord?.adverse_reactions || '',
    procedures_performed: props.appointment.medicalRecord?.procedures_performed || '',
    treatment_response: props.appointment.medicalRecord?.treatment_response || '',
    surgery_type: props.appointment.medicalRecord?.surgery_type || '',
    procedure_details: props.appointment.medicalRecord?.procedure_details || '',
    anesthesia_used: props.appointment.medicalRecord?.anesthesia_used || '',
    complications: props.appointment.medicalRecord?.complications || '',
    post_op_instructions: props.appointment.medicalRecord?.post_op_instructions || '',
    presenting_complaint: props.appointment.medicalRecord?.presenting_complaint || '',
    triage_level: props.appointment.medicalRecord?.triage_level || '',
    emergency_treatment: props.appointment.medicalRecord?.emergency_treatment || '',
    stabilization_measures: props.appointment.medicalRecord?.stabilization_measures || '',
    disposition: props.appointment.medicalRecord?.disposition || '',
});

// Form for assigning veterinarian (clinic only)
const vetForm = useForm({
    veterinarian_id: props.appointment.veterinarian?.id || null,
});

const goBack = () => {
    if (isClinic.value) {
        router.visit('/clinic/appointments');
    } else {
        try {
            router.visit(schedule().url);
        } catch (error) {
            window.history.back();
        }
    }
};

const goToReschedule = () => {
    showRescheduleConfirmModal.value = true;
};

const confirmReschedule = () => {
    showRescheduleConfirmModal.value = false;
    router.visit(rescheduleAppointment(props.appointment.id).url);
};

const cancelReschedule = () => {
    showRescheduleConfirmModal.value = false;
};

const cancelAppointment = () => {
    showCancelModal.value = true;
};

const confirmCancel = () => {
    cancelForm.delete(`/appointments/${props.appointment.id}`, {
        onSuccess: () => {
            showCancelModal.value = false;
            cancelForm.reset();
            router.visit(schedule().url);
        },
        onError: (errors) => {
            console.error('Error canceling appointment:', errors);
        }
    });
};

const closeCancelModal = () => {
    showCancelModal.value = false;
    cancelForm.reset();
};

// Clinic-specific action methods
const confirmAppointment = () => {
    showConfirmModal.value = true;
};

const submitConfirmAppointment = () => {
    router.post(`/clinic/appointments/${props.appointment.id}/confirm`, {}, {
        onSuccess: () => {
            showConfirmModal.value = false;
            console.log('Appointment confirmed successfully');
        },
        onError: (errors) => {
            console.error('Error confirming appointment:', errors);
        }
    });
};

// Mark appointment as no-show (clinic only)
const openNoShowModal = () => {
    showNoShowModal.value = true;
};

const submitNoShow = () => {
    router.post(`/clinic/appointments/${props.appointment.id}/no-show`, {
        reason: noShowReason.value || 'Patient did not show up for appointment',
    }, {
        onSuccess: () => {
            showNoShowModal.value = false;
            noShowReason.value = '';
            console.log('Appointment marked as no-show successfully');
        },
        onError: (errors) => {
            console.error('Error marking appointment as no-show:', errors);
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
    showMedicalRecordModal.value = true;
    activeTab.value = 'medical';
};

const confirmComplete = () => {
    if (!isFormValid.value) {
        alert('Please complete the diagnosis field before completing.');
        return;
    }

    // Include only the 4 simplified fields
    const cleanedMedicalRecord: Record<string, any> = {};

    const fieldsToInclude = ['diagnosis', 'findings', 'treatment_given', 'prescriptions'];

    fieldsToInclude.forEach(field => {
        const value = medicalForm[field as keyof typeof medicalForm];
        if (value !== null && value !== undefined && value !== '') {
            cleanedMedicalRecord[field] = value;
        }
    });

    const completeData = {
        status: 'completed',
        notes: statusForm.notes || null,
        actualCost: statusForm.actualCost || null,
        medical_record: cleanedMedicalRecord
    };
    
    console.log('Submitting appointment completion with data:', completeData);
    
    router.post(`/clinic/appointments/${props.appointment.id}/complete`, completeData, {
        onSuccess: (response) => {
            console.log('Appointment completed successfully:', response);
            showMedicalRecordModal.value = false;
            router.visit('/clinic/history', {
                onSuccess: () => {
                    console.log('Appointment completed with medical records saved');
                }
            });
        },
        onError: (errors) => {
            console.error('Error completing appointment:', errors);
            
            // Display detailed error messages
            let errorMessage = 'Failed to complete appointment:\n\n';
            if (typeof errors === 'object') {
                Object.keys(errors).forEach(key => {
                    errorMessage += `${key}: ${errors[key]}\n`;
                });
            } else {
                errorMessage += 'Please ensure all required fields are filled.';
            }
            
            alert(errorMessage);
        }
    });
};

const updateAppointmentStatus = () => {
    const newStatus = statusForm.status;
    const historicalStatuses = ['completed', 'cancelled', 'no_show'];
    
    statusForm.patch(`/clinic/appointments/${props.appointment.id}/status`, {
        onSuccess: () => {
            if (historicalStatuses.includes(newStatus)) {
                router.visit('/clinic/history', {
                    onSuccess: () => {
                        console.log(`Appointment marked as ${newStatus} and moved to history`);
                    }
                });
            }
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

// Dispute handling
const showDisputeModal = ref(false);
const disputeForm = useForm({
    reason: '',
});

// Cancel and Reschedule modals
const showCancelModal = ref(false);
const showRescheduleConfirmModal = ref(false);
const cancelForm = useForm({
    cancellation_reason: '',
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

// Rating handling
const showRatingForm = ref(false);
const ratingForm = useForm({
    rating: 5,
    comment: '',
    appointment_id: props.appointment.id,
});

const openRatingForm = () => {
    if (props.clinicRating) {
        ratingForm.rating = props.clinicRating.rating;
        ratingForm.comment = props.clinicRating.comment || '';
    }
    showRatingForm.value = true;
};

const closeRatingForm = () => {
    showRatingForm.value = false;
    ratingForm.reset();
};

const submitRating = () => {
    const url = `/clinic/${props.appointment.clinic.id}/reviews`;
    
    ratingForm.post(url, {
        preserveScroll: true,
        onSuccess: () => {
            closeRatingForm();
            // Show success message using toast if available
            console.log('Rating submitted successfully');
        },
        onError: (errors) => {
            console.error('Error submitting rating:', errors);
        }
    });
};

const downloadDocument = (doc: any) => {
    // Handle document download
    console.log('Download document:', doc.name);
    if (doc.url) {
        window.open(doc.url, '_blank');
    }
};

// Follow-up appointment functions
const openFollowUpModal = () => {
    showFollowUpModal.value = true;
    followUpForm.value = {
        follow_up_date: '',
        follow_up_time: '',
        reason: props.appointment.reason || '',
        notes: '',
    };
};

const closeFollowUpModal = () => {
    showFollowUpModal.value = false;
    followUpForm.value = {
        follow_up_date: '',
        follow_up_time: '',
        reason: '',
        notes: '',
    };
};

const submitFollowUp = async () => {
    if (!followUpForm.value.follow_up_date || !followUpForm.value.follow_up_time) {
        alert('Please select a date and time for the follow-up appointment');
        return;
    }
    
    isSubmittingFollowUp.value = true;
    
    try {
        // Combine date and time into scheduled_at
        const scheduledAt = `${followUpForm.value.follow_up_date} ${followUpForm.value.follow_up_time}:00`;
        
        router.post(`/clinic/appointments/${props.appointment.id}/follow-up`, {
            scheduled_at: scheduledAt,
            reason: followUpForm.value.reason,
            notes: followUpForm.value.notes
        }, {
            onSuccess: (page) => {
                closeFollowUpModal();
                
                // Show success notification
                const successMessage = page.props.flash?.success || 'Follow-up appointment scheduled successfully!';
                alert(successMessage);
                
                refreshAppointment();
            },
            onError: (errors) => {
                console.error('Follow-up creation failed:', errors);
                const errorMessage = errors.error || 'Failed to create follow-up appointment. Please try again.';
                alert(errorMessage);
            },
            onFinish: () => {
                isSubmittingFollowUp.value = false;
            }
        });
    } catch (error) {
        console.error('Error creating follow-up:', error);
        isSubmittingFollowUp.value = false;
    }
};

// Clinic reschedule functions
const openClinicRescheduleModal = () => {
    showClinicRescheduleModal.value = true;
    clinicRescheduleForm.value = {
        new_date: '',
        new_time: '',
        reason: '',
    };
};

const closeClinicRescheduleModal = () => {
    showClinicRescheduleModal.value = false;
    clinicRescheduleForm.value = {
        new_date: '',
        new_time: '',
        reason: '',
    };
};

const submitClinicReschedule = async () => {
    if (!clinicRescheduleForm.value.new_date || !clinicRescheduleForm.value.new_time || !clinicRescheduleForm.value.reason) {
        alert('Please provide both a new date/time and a reason for rescheduling');
        return;
    }
    
    isSubmittingReschedule.value = true;
    
    try {
        // Combine date and time into scheduled_at
        const scheduledAt = `${clinicRescheduleForm.value.new_date} ${clinicRescheduleForm.value.new_time}:00`;
        
        router.post(`/clinic/appointments/${props.appointment.id}/reschedule`, {
            scheduled_at: scheduledAt,
            reason: clinicRescheduleForm.value.reason
        }, {
            onSuccess: (page) => {
                closeClinicRescheduleModal();
                
                // Show success notification
                const successMessage = page.props.flash?.success || 'Appointment rescheduled successfully!';
                alert(successMessage);
                
                refreshAppointment();
            },
            onError: (errors) => {
                console.error('Reschedule failed:', errors);
                const errorMessage = errors.error || 'Failed to reschedule appointment. Please try again.';
                alert(errorMessage);
            },
            onFinish: () => {
                isSubmittingReschedule.value = false;
            }
        });
    } catch (error) {
        console.error('Error rescheduling:', error);
        isSubmittingReschedule.value = false;
    }
};

// Clinic cancel functions
const openClinicCancelModal = () => {
    showClinicCancelModal.value = true;
    clinicCancelForm.value = {
        reason: '',
    };
};

const closeClinicCancelModal = () => {
    showClinicCancelModal.value = false;
    clinicCancelForm.value = {
        reason: '',
    };
};

const submitClinicCancel = async () => {
    if (!clinicCancelForm.value.reason) {
        alert('Please provide a reason for cancelling this appointment');
        return;
    }
    
    isSubmittingCancel.value = true;
    
    try {
        router.post(`/clinic/appointments/${props.appointment.id}/cancel`, clinicCancelForm.value, {
            onSuccess: (page) => {
                closeClinicCancelModal();
                
                // Show success notification
                const successMessage = page.props.flash?.success || 'Appointment cancelled successfully!';
                alert(successMessage);
                
                refreshAppointment();
            },
            onError: (errors) => {
                console.error('Cancel failed:', errors);
                const errorMessage = errors.error || 'Failed to cancel appointment. Please try again.';
                alert(errorMessage);
            },
            onFinish: () => {
                isSubmittingCancel.value = false;
            }
        });
    } catch (error) {
        console.error('Error cancelling:', error);
        isSubmittingCancel.value = false;
    }
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
    
    // Close dropdown when clicking outside (clinic only)
    if (isClinic.value) {
        document.addEventListener('click', (event) => {
            if (activeDropdown.value) {
                const target = event.target as HTMLElement;
                if (!target.closest('.vet-dropdown-container')) {
                    closeDropdown();
                }
            }
        });
    }
});

onUnmounted(() => {
    stopAutoRefresh();
});

// Returns status banner configuration based on appointment status
const getStatusBanner = (status?: string) => {
    switch (status) {
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
                bgClass: 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800',
                titleColor: 'text-green-900 dark:text-green-100',
                descColor: 'text-green-700 dark:text-green-300',
                icon: Calendar,
                iconColor: 'text-green-600 dark:text-green-400'
            };
        case 'in_progress': 
            return { 
                bgClass: 'bg-purple-50 dark:bg-purple-900/20 border-purple-200 dark:border-purple-800',
                titleColor: 'text-purple-900 dark:text-purple-100',
                descColor: 'text-purple-700 dark:text-purple-300',
                icon: AlertCircle,
                iconColor: 'text-purple-600 dark:text-purple-400'
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
        <div class="flex h-full flex-1 flex-col gap-3 sm:gap-4 overflow-x-auto rounded-xl p-3 sm:p-4">
            <!-- Header -->
            <div class="rounded-lg border bg-card p-3 sm:p-4 md:p-6">
                <div class="mb-3 sm:mb-4">
                    <h1 class="text-lg sm:text-xl md:text-2xl font-semibold">Appointment Details</h1>
                    <p class="text-xs sm:text-sm text-muted-foreground mt-1">
                        Confirmation #{{ appointment.confirmationNumber }}
                    </p>
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
                                <span v-else-if="appointment.status === 'scheduled'">
                                    Your appointment is scheduled for {{ appointment.date }} at {{ appointment.time }}
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
            <div class="rounded-lg border bg-card">
                <div class="border-b overflow-x-auto">
                    <nav class="flex space-x-4 sm:space-x-8 px-4 sm:px-6 min-w-max sm:min-w-0" aria-label="Tabs">
                        <button
                            v-for="tab in tabs"
                            :key="tab.id"
                            @click="activeTab = tab.id"
                            :class="[
                                'whitespace-nowrap py-3 sm:py-4 px-2 sm:px-1 border-b-2 font-medium text-sm flex items-center gap-2',
                                activeTab === tab.id
                                    ? 'border-primary text-primary'
                                    : 'border-transparent text-muted-foreground hover:text-foreground hover:border-border'
                            ]"
                        >
                            <component :is="tab.icon" class="h-4 w-4" />
                            <span class="hidden sm:inline">{{ tab.name }}</span>
                        </button>
                    </nav>
                </div>

                <!-- Tab Content -->
                <div class="p-4 sm:p-6">
                    <!-- Appointment Details Tab -->
                    <div v-if="activeTab === 'details'" class="space-y-6">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <!-- Left Column -->
                            <div class="space-y-6">
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
                            </div>

                            <!-- Right Column -->
                            <div class="space-y-6">
                                <!-- Clinic Information (Pet Owner View) / Owner Information (Clinic View) -->
                                <div class="space-y-4" v-if="isPetOwner">
                                    <h3 class="text-lg font-semibold mb-4">
                                        Clinic Information
                                    </h3>
                                    
                                    <div class="bg-muted/50 rounded-lg p-4 border">
                                        <h4 class="font-medium mb-2">
                                            {{ appointment.clinic.name }}
                                        </h4>
                                        <p class="text-sm text-muted-foreground mb-2">
                                            {{ appointment.clinic.address }}
                                        </p>
                                        <p class="text-sm text-muted-foreground mb-4">
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

                                    <!-- Veterinarian Information (Pet Owner) -->
                                    <div class="bg-primary/10 rounded-lg p-4 border border-primary/20">
                                        <h4 class="font-medium text-primary mb-2">
                                            {{ appointment.veterinarian?.name || 'To Be Assigned' }}
                                        </h4>
                                        <p class="text-sm text-primary/80 mb-2">
                                            Veterinarian
                                        </p>
                                        <p class="text-xs text-primary/70">
                                            {{ appointment.veterinarian?.specializations || 'Specializes in small animal care and preventive medicine' }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Owner Information (Clinic View) -->
                                <div class="space-y-4" v-else-if="isClinic">
                                    <h3 class="text-lg font-semibold mb-4">
                                        Owner Information
                                    </h3>
                                    
                                    <div class="bg-muted/50 rounded-lg p-4 border">
                                        <h4 class="font-medium mb-2">
                                            {{ appointment.owner.name }}
                                        </h4>
                                        <p class="text-sm text-muted-foreground mb-2">
                                            {{ appointment.owner.email }}
                                        </p>
                                        <p class="text-sm text-muted-foreground mb-4">
                                            {{ appointment.owner.phone }}
                                        </p>
                                        
                                        <button @click="callOwner"
                                                class="w-full bg-green-600 text-white py-2 px-3 rounded-md hover:bg-green-700 text-sm font-medium flex items-center justify-center gap-2">
                                            <Phone class="h-4 w-4" />
                                            Call Owner
                                        </button>
                                    </div>

                                    <!-- Clinic Information with Vet Assignment (Clinic View) -->
                                    <div class="bg-primary/10 rounded-lg p-4 border border-primary/20">
                                        <h4 class="font-medium text-primary mb-3">
                                            {{ appointment.clinic.name }}
                                        </h4>
                                        
                                        <div class="border-t border-primary/20 pt-3 mt-3">
                                            <div class="flex items-center justify-between mb-2">
                                                <label class="text-sm font-medium text-primary/80">Assigned Veterinarian</label>
                                                <div class="relative vet-dropdown-container" v-if="appointment.veterinarian && !isCompleted">
                                                    <button @click.stop="toggleDropdown" 
                                                            class="p-1 hover:bg-primary/20 rounded">
                                                        <MoreVertical class="h-4 w-4" />
                                                    </button>
                                                    <div v-if="activeDropdown" 
                                                         class="absolute right-0 mt-2 w-48 bg-card border rounded-lg shadow-lg z-10">
                                                        <button @click="changeVeterinarian" 
                                                                class="w-full px-4 py-2 text-left hover:bg-muted flex items-center gap-2">
                                                            <Edit class="h-4 w-4" />
                                                            Change Veterinarian
                                                        </button>
                                                        <button @click="removeVeterinarian" 
                                                                class="w-full px-4 py-2 text-left hover:bg-muted flex items-center gap-2 text-red-600">
                                                            <UserX class="h-4 w-4" />
                                                            Remove Assignment
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Assign Veterinarian -->
                                            <div v-if="!appointment.veterinarian && !isCompleted && availableVeterinarians.length > 0" class="space-y-2">
                                                <select v-model="vetForm.veterinarian_id" 
                                                        @change="assignVeterinarian"
                                                        class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary text-sm bg-background">
                                                    <option :value="null">Select a veterinarian...</option>
                                                    <option v-for="vet in availableVeterinarians" :key="vet.id" :value="vet.id">
                                                        {{ vet.name }} {{ vet.license_number ? `(${vet.license_number})` : '' }}
                                                    </option>
                                                </select>
                                            </div>
                                            
                                            <div v-else-if="appointment.veterinarian">
                                                <p class="text-sm font-medium text-primary mb-1">
                                                    {{ appointment.veterinarian.name }}
                                                </p>
                                                <p class="text-xs text-primary/70">
                                                    {{ appointment.veterinarian.specializations || 'Veterinarian' }}
                                                </p>
                                                <p class="text-xs text-primary/70 mt-1" v-if="appointment.veterinarian.license_number">
                                                    License: {{ appointment.veterinarian.license_number }}
                                                </p>
                                            </div>
                                            
                                            <p v-else class="text-xs text-primary/70">
                                                {{ availableVeterinarians.length > 0 ? 'Assign a veterinarian to this appointment' : 'No veterinarians available' }}
                                            </p>
                                        </div>
                                    </div>
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
                        <!-- Pet Owner Actions -->
                        <div v-if="isPetOwner && appointment.status === 'pending'" 
                             class="flex flex-col sm:flex-row justify-end gap-3 pt-4 border-t">
                            <button @click="goToReschedule"
                                    class="px-4 py-2 bg-primary text-primary-foreground rounded-md hover:bg-primary/90 text-sm font-medium">
                                Reschedule Appointment
                            </button>
                            <button @click="cancelAppointment"
                                    class="px-4 py-2 border border-red-500 text-red-600 rounded-md hover:bg-red-50 dark:hover:bg-red-950 text-sm font-medium">
                                Cancel Appointment
                            </button>
                        </div>
                        
                        <!-- 24-Hour Confirmation Window for Pet Owners -->
                        <div v-if="isPetOwner && appointment.status === 'scheduled' && appointment.can_owner_reschedule_or_cancel && appointment.confirmation_window_ends_at" 
                             class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mt-4">
                            <div class="flex items-start gap-3">
                                <AlertCircle class="h-5 w-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" />
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-blue-900 dark:text-blue-100 mb-1">
                                        24-Hour Window Active
                                    </p>
                                    <p class="text-xs text-blue-700 dark:text-blue-300">
                                        You can reschedule or cancel this appointment until {{ appointment.confirmation_window_ends_at }}
                                    </p>
                                    <div class="flex gap-2 mt-3">
                                        <button @click="goToReschedule"
                                                class="px-3 py-1.5 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-xs font-medium">
                                            Reschedule
                                        </button>
                                        <button @click="cancelAppointment"
                                                class="px-3 py-1.5 border border-blue-600 text-blue-600 dark:text-blue-400 rounded-md hover:bg-blue-50 dark:hover:bg-blue-950 text-xs font-medium">
                                            Cancel
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Clinic Actions -->
                        <div v-if="isClinic && !isCompleted" class="pt-4 border-t">
                            <div class="flex justify-end gap-3 flex-wrap">
                                <!-- Confirm Button (for pending appointments) -->
                                <button v-if="isPending" 
                                        @click="confirmAppointment"
                                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm font-medium">
                                    Confirm Appointment
                                </button>
                                
                                <!-- Reschedule Button (for pending/scheduled appointments) -->
                                <button v-if="isPending || isScheduled" 
                                        @click="openClinicRescheduleModal"
                                        class="px-4 py-2 border border-blue-500 text-blue-600 dark:text-blue-400 rounded-md hover:bg-blue-50 dark:hover:bg-blue-950 text-sm font-medium flex items-center gap-2">
                                    <Calendar class="h-4 w-4" />
                                    Reschedule
                                </button>
                                
                                <!-- Cancel Button (for pending/scheduled appointments) -->
                                <button v-if="isPending || isScheduled" 
                                        @click="openClinicCancelModal"
                                        class="px-4 py-2 border border-red-500 text-red-600 dark:text-red-400 rounded-md hover:bg-red-50 dark:hover:bg-red-950 text-sm font-medium flex items-center gap-2">
                                    <XCircle class="h-4 w-4" />
                                    Cancel
                                </button>
                                
                                <!-- Start Button (for scheduled appointments) -->
                                <button v-if="isScheduled" 
                                        @click="startAppointment"
                                        class="px-4 py-2 bg-orange-600 text-white rounded-md hover:bg-orange-700 text-sm font-medium">
                                    Start Appointment
                                </button>
                                
                                <!-- In Progress Status Actions -->
                                <button v-if="canMarkComplete" 
                                        @click="openNoShowModal"
                                        class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 text-sm font-medium">
                                    No Show
                                </button>
                                <button v-if="canMarkComplete" 
                                        @click="markAsComplete"
                                        class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 text-sm font-medium">
                                    Complete & Add Record
                                </button>
                            </div>
                        </div>
                        
                        <!-- Follow-Up Button (for completed appointments) -->
                        <div v-if="isClinic && !isPetOwner && appointment.status === 'completed' && appointment.can_create_follow_up" class="pt-4 border-t">
                            <div class="flex justify-end">
                                <button 
                                    type="button"
                                    @click="openFollowUpModal"
                                    class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 text-sm font-medium flex items-center gap-2">
                                    <Plus class="h-4 w-4" />
                                    Schedule Follow-Up Appointment
                                </button>
                            </div>
                        </div>
                        
                        <!-- Completed/Cancelled Status Info -->
                        <div v-if="appointment.status === 'completed'" 
                             class="flex items-center gap-2 pt-4 border-t">
                            <CheckCircle2 class="h-5 w-5 text-green-600 dark:text-green-400" />
                            <span class="text-sm text-muted-foreground">
                                This appointment has been completed.
                            </span>
                        </div>

                        <!-- Rating Section for Completed Appointments (Pet Owner Only) -->
                        <div v-if="isPetOwner && appointment.status === 'completed'" class="pt-4 border-t">
                            <div v-if="!hasRating && !showRatingForm" class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <Star class="h-5 w-5 text-yellow-500" />
                                        <div>
                                            <p class="text-sm font-medium text-blue-900 dark:text-blue-100">
                                                Rate Your Experience
                                            </p>
                                            <p class="text-xs text-blue-700 dark:text-blue-300 mt-0.5">
                                                Help others by sharing your experience with {{ appointment.clinic.name }}
                                            </p>
                                        </div>
                                    </div>
                                    <button @click="openRatingForm"
                                            class="px-4 py-2 bg-primary text-primary-foreground rounded-md hover:bg-primary/90 text-sm font-medium flex items-center gap-2">
                                        <Star class="h-4 w-4" />
                                        Rate Now
                                    </button>
                                </div>
                            </div>

                            <!-- Existing Rating Display -->
                            <div v-if="clinicRating && !showRatingForm" class="bg-gradient-to-br from-blue-50 to-purple-50 dark:from-blue-900/30 dark:to-purple-900/30 border-2 border-blue-200 dark:border-blue-500/50 rounded-lg p-4">
                                <div class="flex items-start justify-between mb-3">
                                    <div>
                                        <div class="flex items-center gap-2 mb-2">
                                            <span class="bg-blue-500/20 text-blue-700 dark:text-blue-300 text-xs font-medium px-2 py-1 rounded">Your Rating</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <div class="flex text-yellow-400 text-sm">
                                                <Star v-for="i in clinicRating.rating" :key="`filled-${i}`" class="h-4 w-4 fill-yellow-400" />
                                                <Star v-for="i in (5 - clinicRating.rating)" :key="`empty-${i}`" class="h-4 w-4" />
                                            </div>
                                            <span class="text-sm text-muted-foreground">{{ clinicRating.created_at }}</span>
                                        </div>
                                    </div>
                                </div>
                                <p v-if="clinicRating.comment" class="text-sm text-foreground">{{ clinicRating.comment }}</p>
                                <p v-else class="text-sm text-muted-foreground italic">No comment provided</p>
                            </div>

                            <!-- Rating Form -->
                            <div v-if="showRatingForm" class="bg-card border rounded-lg p-6">
                                <div class="flex justify-between items-center mb-4">
                                    <h4 class="text-lg font-semibold">Rate Your Experience</h4>
                                    <button @click="closeRatingForm" class="text-muted-foreground hover:text-foreground">
                                        <X class="h-5 w-5" />
                                    </button>
                                </div>

                                <form @submit.prevent="submitRating">
                                    <!-- Rating Stars -->
                                    <div class="mb-4">
                                        <label class="block text-sm font-medium mb-2">Rating</label>
                                        <div class="flex gap-2">
                                            <button v-for="star in 5" :key="star" 
                                                    type="button"
                                                    @click="ratingForm.rating = star"
                                                    class="text-3xl transition-colors focus:outline-none hover:scale-110">
                                                <Star :class="star <= ratingForm.rating ? 'text-yellow-400 fill-yellow-400' : 'text-gray-300 dark:text-gray-600'" />
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Comment -->
                                    <div class="mb-4">
                                        <label class="block text-sm font-medium mb-2">Comment (Optional)</label>
                                        <textarea v-model="ratingForm.comment" 
                                                  rows="4" 
                                                  maxlength="1000"
                                                  placeholder="Share your experience at {{ appointment.clinic.name }}..."
                                                  class="w-full border border-input bg-background rounded-lg px-4 py-2 text-sm placeholder-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 resize-none">
                                        </textarea>
                                        <div class="text-right text-xs text-muted-foreground mt-1">
                                            {{ ratingForm.comment?.length || 0 }} / 1000
                                        </div>
                                    </div>

                                    <!-- Submit Button -->
                                    <div class="flex gap-3">
                                        <button type="submit" 
                                                :disabled="ratingForm.processing"
                                                class="px-4 py-2 bg-primary text-primary-foreground rounded-md hover:bg-primary/90 text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                                            <Send class="h-4 w-4" />
                                            Submit Rating
                                        </button>
                                        <button type="button" @click="closeRatingForm" 
                                                class="px-4 py-2 border rounded-md hover:bg-muted text-sm font-medium">
                                            Cancel
                                        </button>
                                    </div>

                                    <!-- Error Display -->
                                    <div v-if="ratingForm.errors.rating || ratingForm.errors.comment" class="mt-4 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-500/50 rounded-lg">
                                        <p v-if="ratingForm.errors.rating" class="text-red-600 dark:text-red-400 text-sm">{{ ratingForm.errors.rating }}</p>
                                        <p v-if="ratingForm.errors.comment" class="text-red-600 dark:text-red-400 text-sm">{{ ratingForm.errors.comment }}</p>
                                    </div>
                                </form>
                            </div>
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
                                <h3 class="text-lg font-semibold">Medical Record Summary</h3>
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
                                        <p class="text-sm text-primary/90 font-medium mb-1">
                                            {{ isClinic ? 'Medical Record Management' : 'Medical records are managed by the clinic' }}
                                        </p>
                                        <p class="text-xs text-primary/70">
                                            {{ isClinic 
                                                ? (medicalRecord 
                                                    ? 'To update this medical record, go to Patient Records and use the Add/Edit Medical Record form. Only fields relevant to the selected record type will be saved.' 
                                                    : 'Medical records can be added through the Patient Records section using the Add Medical Record form after completing the appointment.')
                                                : (medicalRecord 
                                                    ? 'This is a summary view. For detailed medical history, clinics maintain complete patient records.' 
                                                    : 'Medical records will be added by the clinic after your appointment is completed.')
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

                            <!-- Dispute Window (if within window) - Pet Owners Only -->
                            <div v-if="isPetOwner && appointment.canBeDisputed && appointment.disputeHoursRemaining" 
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

        <!-- Medical Record Form Modal (Clinic) -->
        <div v-if="isClinic && showMedicalRecordModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4 overflow-y-auto">
            <div class="bg-card rounded-lg p-6 w-full max-w-3xl shadow-xl my-8">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <span class="text-3xl">📋</span>
                        <h2 class="text-xl font-semibold">Medical Record</h2>
                    </div>
                    <button @click="showMedicalRecordModal = false" class="text-muted-foreground hover:text-foreground">
                        <span class="text-2xl">&times;</span>
                    </button>
                </div>

                <div class="mb-6">
                    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 mb-6">
                        <p class="text-sm text-blue-900 dark:text-blue-100">
                            Complete this medical record to mark the appointment as completed.
                        </p>
                    </div>

                    <!-- Simplified 4-field form -->
                    <MedicalRecordFormFields 
                        :form="medicalForm" 
                    />

                    <p class="text-sm text-amber-600 dark:text-amber-400 mt-6 flex items-center gap-2">
                        <AlertCircle class="h-4 w-4" />
                        This record will be permanently saved and cannot be deleted, only edited within 24 hours.
                    </p>
                </div>

                <div class="flex gap-3">
                    <button 
                        @click="showMedicalRecordModal = false"
                        :disabled="medicalForm.processing"
                        class="btn btn-outline flex-1"
                    >
                        Cancel
                    </button>
                    <button 
                        @click="confirmComplete"
                        :disabled="medicalForm.processing || !isFormValid"
                        class="btn btn-primary flex-1 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Complete Appointment
                    </button>
                </div>
            </div>
        </div>

        <!-- Confirm Appointment Modal (Clinic) -->
        <div v-if="isClinic && showConfirmModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div class="bg-card rounded-lg p-6 w-full max-w-md shadow-xl">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold">Confirm Appointment</h2>
                    <button @click="showConfirmModal = false" class="text-muted-foreground hover:text-foreground">
                        <span class="text-2xl">&times;</span>
                    </button>
                </div>

                <div class="mb-6">
                    <p class="text-muted-foreground mb-4">
                        Confirm this pending appointment to move it to scheduled status?
                    </p>
                    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-3 text-sm">
                        <p class="text-blue-900 dark:text-blue-100 font-medium">{{ appointment.pet.name }}</p>
                        <p class="text-blue-700 dark:text-blue-300">{{ appointment.date }} at {{ appointment.time }}</p>
                        <p class="text-blue-600 dark:text-blue-400">Owner: {{ appointment.owner.name }}</p>
                    </div>
                </div>

                <div class="flex gap-3">
                    <button 
                        @click="showConfirmModal = false"
                        class="flex-1 px-4 py-2 border rounded-md hover:bg-muted text-sm font-medium"
                    >
                        Cancel
                    </button>
                    <button 
                        @click="submitConfirmAppointment"
                        class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm font-medium"
                    >
                        Confirm Appointment
                    </button>
                </div>
            </div>
        </div>

        <!-- Cancel Appointment Modal -->
        <div v-if="showCancelModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
            <div class="bg-card rounded-lg shadow-xl max-w-lg w-full border">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-semibold text-foreground flex items-center gap-2">
                            <AlertCircle class="h-5 w-5 text-red-500" />
                            Cancel Appointment
                        </h2>
                        <button @click="closeCancelModal" class="text-muted-foreground hover:text-foreground">
                            <X class="h-5 w-5" />
                        </button>
                    </div>

                    <div class="mb-6 space-y-4">
                        <p class="text-muted-foreground">
                            Are you sure you want to cancel this appointment? This action cannot be undone.
                        </p>

                        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                            <p class="text-sm font-medium text-yellow-900 dark:text-yellow-100 mb-2">Appointment Details:</p>
                            <div class="text-sm text-yellow-800 dark:text-yellow-200 space-y-1">
                                <p><strong>Pet:</strong> {{ appointment.pet.name }}</p>
                                <p><strong>Date & Time:</strong> {{ appointment.date }} at {{ appointment.time }}</p>
                                <p><strong>Clinic:</strong> {{ appointment.clinic.name }}</p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-foreground mb-2">
                                Reason for Cancellation (Optional)
                            </label>
                            <textarea 
                                v-model="cancelForm.cancellation_reason"
                                rows="3"
                                class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent resize-none"
                                placeholder="Please let us know why you're canceling..."
                            ></textarea>
                        </div>

                        <p class="text-xs text-muted-foreground">
                            Note: You may be able to rebook another appointment after cancellation.
                        </p>
                    </div>

                    <div class="flex gap-3">
                        <button 
                            @click="closeCancelModal"
                            :disabled="cancelForm.processing"
                            class="flex-1 px-4 py-2 border rounded-md hover:bg-muted text-sm font-medium disabled:opacity-50"
                        >
                            Keep Appointment
                        </button>
                        <button 
                            @click="confirmCancel"
                            :disabled="cancelForm.processing"
                            class="flex-1 px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 text-sm font-medium disabled:opacity-50 flex items-center justify-center gap-2"
                        >
                            <span v-if="cancelForm.processing">Canceling...</span>
                            <span v-else>Yes, Cancel Appointment</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reschedule Confirmation Modal -->
        <div v-if="showRescheduleConfirmModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
            <div class="bg-card rounded-lg shadow-xl max-w-lg w-full border">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-semibold text-foreground flex items-center gap-2">
                            <Calendar class="h-5 w-5 text-blue-500" />
                            Reschedule Appointment
                        </h2>
                        <button @click="cancelReschedule" class="text-muted-foreground hover:text-foreground">
                            <X class="h-5 w-5" />
                        </button>
                    </div>

                    <div class="mb-6 space-y-4">
                        <p class="text-muted-foreground">
                            You're about to reschedule this appointment. You'll be able to select a new date and time on the next page.
                        </p>

                        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                            <p class="text-sm font-medium text-blue-900 dark:text-blue-100 mb-2">Current Appointment:</p>
                            <div class="text-sm text-blue-800 dark:text-blue-200 space-y-1">
                                <p><strong>Pet:</strong> {{ appointment.pet.name }}</p>
                                <p><strong>Date & Time:</strong> {{ appointment.date }} at {{ appointment.time }}</p>
                                <p><strong>Clinic:</strong> {{ appointment.clinic.name }}</p>
                                <p v-if="appointment.service"><strong>Service:</strong> {{ appointment.service.name }}</p>
                            </div>
                        </div>

                        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-3">
                            <p class="text-xs text-yellow-800 dark:text-yellow-200">
                                💡 <strong>Tip:</strong> Make sure to choose a time that works best for both you and your pet!
                            </p>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <button 
                            @click="cancelReschedule"
                            class="flex-1 px-4 py-2 border rounded-md hover:bg-muted text-sm font-medium"
                        >
                            Cancel
                        </button>
                        <button 
                            @click="confirmReschedule"
                            class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm font-medium flex items-center justify-center gap-2"
                        >
                            <Calendar class="h-4 w-4" />
                            Continue to Reschedule
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- No Show Confirmation Modal (Clinic) -->
        <div v-if="isClinic && showNoShowModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div class="bg-card rounded-lg p-6 w-full max-w-md shadow-xl">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold">Mark as No Show</h2>
                    <button @click="showNoShowModal = false" class="text-muted-foreground hover:text-foreground">
                        <span class="text-2xl">&times;</span>
                    </button>
                </div>

                <div class="mb-6">
                    <p class="text-muted-foreground mb-4">
                        Mark this appointment as no-show because the patient did not arrive?
                    </p>
                    <div class="bg-gray-50 dark:bg-gray-900/20 rounded-lg p-3 text-sm mb-4">
                        <p class="text-gray-900 dark:text-gray-100 font-medium">{{ appointment.pet.name }}</p>
                        <p class="text-gray-700 dark:text-gray-300">{{ appointment.date }} at {{ appointment.time }}</p>
                        <p class="text-gray-600 dark:text-gray-400">Owner: {{ appointment.owner.name }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-2">Reason (Optional)</label>
                        <textarea 
                            v-model="noShowReason"
                            class="w-full px-3 py-2 border rounded-md bg-background text-foreground"
                            rows="3"
                            placeholder="Additional notes about the no-show..."
                        ></textarea>
                    </div>
                </div>

                <div class="flex gap-3">
                    <button 
                        @click="showNoShowModal = false"
                        class="flex-1 px-4 py-2 border rounded-md hover:bg-muted text-sm font-medium"
                    >
                        Cancel
                    </button>
                    <button 
                        @click="submitNoShow"
                        class="flex-1 px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 text-sm font-medium"
                    >
                        Mark as No Show
                    </button>
                </div>
            </div>
        </div>

        <!-- Follow-Up Appointment Modal -->
        <div v-if="showFollowUpModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div class="bg-card border rounded-lg shadow-xl max-w-md w-full p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold">Schedule Follow-Up Appointment</h2>
                    <button 
                        @click="closeFollowUpModal"
                        class="text-muted-foreground hover:text-foreground"
                    >
                        <X class="h-5 w-5" />
                    </button>
                </div>

                <div class="space-y-4">
                    <p class="text-sm text-muted-foreground">
                        Schedule a follow-up appointment for <strong>{{ appointment.pet.name }}</strong> with the same service and veterinarian.
                    </p>

                    <div>
                        <label class="block text-sm font-medium mb-2">Follow-Up Date <span class="text-destructive">*</span></label>
                        <input 
                            type="date"
                            v-model="followUpForm.follow_up_date"
                            :min="new Date().toISOString().split('T')[0]"
                            class="w-full px-3 py-2 border rounded-md bg-background text-foreground"
                            required
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">Follow-Up Time <span class="text-destructive">*</span></label>
                        <input 
                            type="time"
                            v-model="followUpForm.follow_up_time"
                            class="w-full px-3 py-2 border rounded-md bg-background text-foreground"
                            required
                        />
                        <p class="text-xs text-muted-foreground mt-1">Check clinic operating hours</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">Reason for Follow-Up</label>
                        <textarea 
                            v-model="followUpForm.reason"
                            class="w-full px-3 py-2 border rounded-md bg-background text-foreground"
                            rows="3"
                            placeholder="Why is this follow-up needed?"
                            maxlength="500"
                        ></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">Additional Notes</label>
                        <textarea 
                            v-model="followUpForm.notes"
                            class="w-full px-3 py-2 border rounded-md bg-background text-foreground"
                            rows="2"
                            placeholder="Any additional details..."
                            maxlength="1000"
                        ></textarea>
                    </div>
                </div>

                <div class="flex gap-3 mt-6">
                    <button 
                        type="button"
                        @click="closeFollowUpModal"
                        class="flex-1 px-4 py-2 border rounded-md hover:bg-muted text-sm font-medium"
                        :disabled="isSubmittingFollowUp"
                    >
                        Cancel
                    </button>
                    <button 
                        type="button"
                        @click="submitFollowUp"
                        class="flex-1 px-4 py-2 bg-primary text-primary-foreground rounded-md hover:bg-primary/90 text-sm font-medium disabled:opacity-50"
                        :disabled="isSubmittingFollowUp || !followUpForm.follow_up_date || !followUpForm.follow_up_time"
                    >
                        {{ isSubmittingFollowUp ? 'Scheduling...' : 'Schedule Follow-Up' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Clinic Reschedule Appointment Modal -->
        <div v-if="showClinicRescheduleModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div class="bg-card border rounded-lg shadow-xl max-w-md w-full p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold">Reschedule Appointment</h2>
                    <button 
                        @click="closeClinicRescheduleModal"
                        class="text-muted-foreground hover:text-foreground"
                    >
                        <X class="h-5 w-5" />
                    </button>
                </div>

                <div class="space-y-4">
                    <p class="text-sm text-muted-foreground">
                        Reschedule appointment for <strong>{{ appointment.pet.name }}</strong> with <strong>{{ appointment.owner.name }}</strong>.
                    </p>

                    <div class="bg-muted rounded-lg p-3 text-sm">
                        <p class="font-medium">Current Appointment</p>
                        <p class="text-muted-foreground">{{ appointment.date }} at {{ appointment.time }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">New Date <span class="text-destructive">*</span></label>
                        <input 
                            type="date"
                            v-model="clinicRescheduleForm.new_date"
                            :min="new Date().toISOString().split('T')[0]"
                            class="w-full px-3 py-2 border rounded-md bg-background text-foreground"
                            required
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">New Time <span class="text-destructive">*</span></label>
                        <input 
                            type="time"
                            v-model="clinicRescheduleForm.new_time"
                            class="w-full px-3 py-2 border rounded-md bg-background text-foreground"
                            required
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">Reason for Rescheduling <span class="text-destructive">*</span></label>
                        <textarea 
                            v-model="clinicRescheduleForm.reason"
                            class="w-full px-3 py-2 border rounded-md bg-background text-foreground"
                            rows="3"
                            placeholder="Explain why you are rescheduling this appointment..."
                            maxlength="500"
                            required
                        ></textarea>
                        <p class="text-xs text-muted-foreground mt-1">The owner will be notified of this change</p>
                    </div>
                </div>

                <div class="flex gap-3 mt-6">
                    <button 
                        @click="closeClinicRescheduleModal"
                        class="flex-1 px-4 py-2 border rounded-md hover:bg-muted text-sm font-medium"
                        :disabled="isSubmittingClinicReschedule"
                    >
                        Cancel
                    </button>
                    <button 
                        @click="submitClinicReschedule"
                        class="flex-1 px-4 py-2 bg-primary text-primary-foreground rounded-md hover:bg-primary/90 text-sm font-medium disabled:opacity-50"
                        :disabled="isSubmittingClinicReschedule || !clinicRescheduleForm.new_date || !clinicRescheduleForm.new_time || !clinicRescheduleForm.reason"
                    >
                        {{ isSubmittingClinicReschedule ? 'Rescheduling...' : 'Reschedule Appointment' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Clinic Cancel Appointment Modal -->
        <div v-if="showClinicCancelModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div class="bg-card border rounded-lg shadow-xl max-w-md w-full p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-destructive">Cancel Appointment</h2>
                    <button 
                        @click="closeClinicCancelModal"
                        class="text-muted-foreground hover:text-foreground"
                    >
                        <X class="h-5 w-5" />
                    </button>
                </div>

                <div class="space-y-4">
                    <p class="text-sm text-muted-foreground">
                        Are you sure you want to cancel the appointment for <strong>{{ appointment.pet.name }}</strong> with <strong>{{ appointment.owner.name }}</strong>?
                    </p>

                    <div class="bg-destructive/10 rounded-lg p-3 text-sm">
                        <p class="font-medium text-destructive">This action cannot be undone</p>
                        <p class="text-muted-foreground mt-1">{{ appointment.date }} at {{ appointment.time }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">Reason for Cancellation <span class="text-destructive">*</span></label>
                        <textarea 
                            v-model="clinicCancelForm.reason"
                            class="w-full px-3 py-2 border rounded-md bg-background text-foreground"
                            rows="4"
                            placeholder="Explain why you are canceling this appointment..."
                            maxlength="500"
                            required
                        ></textarea>
                        <p class="text-xs text-muted-foreground mt-1">The owner will be notified immediately</p>
                    </div>
                </div>

                <div class="flex gap-3 mt-6">
                    <button 
                        @click="closeClinicCancelModal"
                        class="flex-1 px-4 py-2 border rounded-md hover:bg-muted text-sm font-medium"
                        :disabled="isSubmittingClinicCancel"
                    >
                        Keep Appointment
                    </button>
                    <button 
                        @click="submitClinicCancel"
                        class="flex-1 px-4 py-2 bg-destructive text-destructive-foreground rounded-md hover:bg-destructive/90 text-sm font-medium disabled:opacity-50"
                        :disabled="isSubmittingClinicCancel || !clinicCancelForm.reason"
                    >
                        {{ isSubmittingClinicCancel ? 'Canceling...' : 'Cancel Appointment' }}
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Additional custom styles if needed */
</style>
