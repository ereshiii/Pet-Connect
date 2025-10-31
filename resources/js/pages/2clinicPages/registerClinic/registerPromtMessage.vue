<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { registerClinic } from '@/routes';
import { ref } from 'vue';
import { 
    Building2, 
    Clock, 
    CheckCircle, 
    XCircle, 
    AlertCircle,
    FileText,
    ArrowRight,
    Mail,
    RefreshCw
} from 'lucide-vue-next';

interface Props {
    user: {
        id: number;
        name: string;
        email: string;
        account_type: string;
    };
    clinicRegistration?: {
        id: number;
        status: 'incomplete' | 'pending' | 'approved' | 'rejected';
        clinic_name?: string;
        submitted_at?: string;
        approved_at?: string;
        rejection_reason?: string;
    } | null;
}

const props = defineProps<Props>();

// Determine registration status from clinicRegistration object
const registrationStatus = props.clinicRegistration?.status || 'unregistered';

// State for checking status
const isCheckingStatus = ref(false);
const statusMessage = ref('');
const lastChecked = ref('');

const getStatusInfo = () => {
    switch (registrationStatus) {
        case 'unregistered':
            return {
                title: 'Welcome to PetConnect Clinic Network!',
                subtitle: 'Start your clinic registration to join our network',
                icon: Building2,
                iconColor: 'text-blue-600',
                bgColor: 'bg-blue-50 dark:bg-blue-900/20',
                borderColor: 'border-blue-200 dark:border-blue-800',
                description: 'To access the clinic management features, you need to register your clinic with us. This helps us verify your credentials and provide you with the best service.',
                actionText: 'Start Registration',
                actionColor: 'bg-blue-600 hover:bg-blue-700',
            };
        case 'incomplete':
            return {
                title: 'Complete Your Clinic Registration',
                subtitle: 'You have an incomplete registration that needs to be finished',
                icon: AlertCircle,
                iconColor: 'text-yellow-600',
                bgColor: 'bg-yellow-50 dark:bg-yellow-900/20',
                borderColor: 'border-yellow-200 dark:border-yellow-800',
                description: 'You started the registration process but didn\'t complete it. You can continue from where you left off or start fresh.',
                actionText: 'Continue Registration',
                actionColor: 'bg-yellow-600 hover:bg-yellow-700',
            };
        case 'pending':
            return {
                title: 'Registration Submitted Successfully!',
                subtitle: 'Your clinic registration is under review',
                icon: Clock,
                iconColor: 'text-orange-600',
                bgColor: 'bg-orange-50 dark:bg-orange-900/20',
                borderColor: 'border-orange-200 dark:border-orange-800',
                description: 'Thank you for submitting your clinic registration. Our team is reviewing your application. You will receive an email notification once the review is complete.',
                actionText: 'Check for Updates',
                actionColor: 'bg-orange-600 hover:bg-orange-700',
            };
        case 'approved':
            return {
                title: 'Congratulations! Registration Approved',
                subtitle: 'Your clinic has been verified and approved',
                icon: CheckCircle,
                iconColor: 'text-green-600',
                bgColor: 'bg-green-50 dark:bg-green-900/20',
                borderColor: 'border-green-200 dark:border-green-800',
                description: 'Your clinic registration has been approved! You now have full access to all clinic management features.',
                actionText: 'Access Dashboard',
                actionColor: 'bg-green-600 hover:bg-green-700',
            };
        case 'rejected':
            return {
                title: 'Registration Requires Attention',
                subtitle: 'Your clinic registration was not approved',
                icon: XCircle,
                iconColor: 'text-red-600',
                bgColor: 'bg-red-50 dark:bg-red-900/20',
                borderColor: 'border-red-200 dark:border-red-800',
                description: 'Unfortunately, your clinic registration was not approved. Please review the feedback below and submit a new application.',
                actionText: 'Resubmit Application',
                actionColor: 'bg-red-600 hover:bg-red-700',
            };
        default:
            return {
                title: 'Clinic Registration',
                subtitle: 'Manage your clinic registration',
                icon: Building2,
                iconColor: 'text-gray-600',
                bgColor: 'bg-gray-50 dark:bg-gray-900/20',
                borderColor: 'border-gray-200 dark:border-gray-800',
                description: 'Manage your clinic registration status.',
                actionText: 'Continue',
                actionColor: 'bg-gray-600 hover:bg-gray-700',
            };
    }
};

const statusInfo = getStatusInfo();

const handleAction = () => {
    switch (registrationStatus) {
        case 'unregistered':
        case 'incomplete':
        case 'rejected':
            // Route to registration form
            router.visit(registerClinic().url);
            break;
        case 'pending':
            // Check status with loading animation
            checkRegistrationStatus();
            break;
        case 'approved':
            // Route to clinic dashboard
            router.visit('/clinic/dashboard');
            break;
        default:
            // Default to registration form
            router.visit(registerClinic().url);
            break;
    }
};

const checkRegistrationStatus = async () => {
    isCheckingStatus.value = true;
    statusMessage.value = 'Checking for updates...';
    
    try {
        // Fetch updated status from API
        const response = await fetch('/clinic/registration-status', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            credentials: 'same-origin'
        });
        
        if (!response.ok) {
            throw new Error('Failed to check status');
        }
        
        const data = await response.json();
        
        // Check if status has changed
        const currentStatus = props.clinicRegistration?.status || 'unregistered';
        const newStatus = data.status;
        
        if (newStatus !== currentStatus) {
            // Status has changed, refresh the page to update UI
            statusMessage.value = 'Status updated! Refreshing...';
            setTimeout(() => {
                router.reload();
            }, 1000);
        } else {
            // No change in status
            isCheckingStatus.value = false;
            lastChecked.value = new Date().toLocaleTimeString();
            if (newStatus === 'pending') {
                statusMessage.value = 'Your registration is still under review. Thank you for your patience!';
            } else {
                statusMessage.value = 'Status checked - no updates at this time.';
            }
            
            // Clear message after 4 seconds
            setTimeout(() => {
                statusMessage.value = '';
            }, 4000);
        }
    } catch (error) {
        isCheckingStatus.value = false;
        statusMessage.value = 'Error checking status. Please try again.';
        console.error('Status check error:', error);
        
        // Clear message after 3 seconds
        setTimeout(() => {
            statusMessage.value = '';
        }, 3000);
    }
};

const formatDate = (dateString?: string) => {
    if (!dateString) return '';
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const getProgressPercentage = () => {
    switch (registrationStatus) {
        case 'unregistered': return 0;
        case 'incomplete': return 25;
        case 'pending': return 75;
        case 'approved': return 100;
        case 'rejected': return 50;
        default: return 0;
    }
};
</script>

<template>
    <Head title="Clinic Registration" />

    <AppLayout :breadcrumbs="[]">
        <div class="min-h-screen flex items-center justify-center p-4">
            <div class="max-w-2xl w-full space-y-8">
                <!-- Logo/Header -->
                <div class="text-center">
                    <div class="mx-auto h-12 w-12 flex items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900/20">
                        <Building2 class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                    </div>
                    <h1 class="mt-4 text-3xl font-bold text-gray-900 dark:text-gray-100">
                        PetConnect Clinic Network
                    </h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">
                        Professional veterinary clinic management platform
                    </p>
                </div>

                <!-- Progress Bar -->
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                    <div 
                        :class="[
                            'h-2 rounded-full transition-all duration-500',
                            registrationStatus === 'approved' ? 'bg-green-600' :
                            registrationStatus === 'rejected' ? 'bg-red-600' :
                            registrationStatus === 'pending' ? 'bg-orange-600' :
                            'bg-blue-600'
                        ]"
                        :style="{ width: `${getProgressPercentage()}%` }"
                    ></div>
                </div>

                <!-- Main Status Card -->
                <div :class="[
                    'rounded-xl border-2 p-8 text-center',
                    statusInfo.bgColor,
                    statusInfo.borderColor
                ]">
                    <!-- Status Icon -->
                    <div class="mx-auto mb-6 h-16 w-16 flex items-center justify-center rounded-full bg-white dark:bg-gray-800 shadow-lg">
                        <component :is="statusInfo.icon" :class="['h-8 w-8', statusInfo.iconColor]" />
                    </div>

                    <!-- Status Title -->
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-2">
                        {{ statusInfo.title }}
                    </h2>
                    <p class="text-lg text-gray-600 dark:text-gray-400 mb-6">
                        {{ statusInfo.subtitle }}
                    </p>

                    <!-- Status Description -->
                    <p class="text-gray-700 dark:text-gray-300 mb-8 leading-relaxed">
                        {{ statusInfo.description }}
                    </p>

                    <!-- Registration Timeline (for pending/approved/rejected) -->
                    <div v-if="registrationStatus !== 'unregistered'" class="mb-8">
                        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 text-left">
                            <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                                <FileText class="h-5 w-5 mr-2" />
                                Registration Timeline
                            </h3>
                            <div class="space-y-3 text-sm">
                                <div v-if="props.clinicRegistration?.submitted_at" class="flex items-center text-gray-600 dark:text-gray-400">
                                    <div class="w-2 h-2 bg-blue-600 rounded-full mr-3"></div>
                                    <span>Submitted: {{ formatDate(props.clinicRegistration.submitted_at) }}</span>
                                </div>
                                <div v-if="props.clinicRegistration?.approved_at" class="flex items-center text-green-600">
                                    <div class="w-2 h-2 bg-green-600 rounded-full mr-3"></div>
                                    <span>Approved: {{ formatDate(props.clinicRegistration.approved_at) }}</span>
                                </div>
                                <div v-if="registrationStatus === 'pending'" class="flex items-center text-orange-600">
                                    <div class="w-2 h-2 bg-orange-600 rounded-full mr-3 animate-pulse"></div>
                                    <span>Under Review - Typically takes 1-2 business days</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Rejection Reason (if applicable) -->
                    <div v-if="registrationStatus === 'rejected' && props.clinicRegistration?.rejection_reason" class="mb-8">
                        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-6 text-left">
                            <h3 class="font-semibold text-red-800 dark:text-red-200 mb-2 flex items-center">
                                <AlertCircle class="h-5 w-5 mr-2" />
                                Feedback from Review Team
                            </h3>
                            <p class="text-red-700 dark:text-red-300 text-sm">
                                {{ props.clinicRegistration.rejection_reason }}
                            </p>
                        </div>
                    </div>

                    <!-- Action Button -->
                    <button 
                        @click="handleAction"
                        :disabled="isCheckingStatus"
                        :class="[
                            'inline-flex items-center px-8 py-3 text-white font-medium rounded-lg text-lg transition-colors shadow-lg',
                            statusInfo.actionColor,
                            isCheckingStatus ? 'opacity-75 cursor-not-allowed' : ''
                        ]"
                    >
                        <component 
                            :is="registrationStatus === 'pending' ? (isCheckingStatus ? RefreshCw : RefreshCw) : ArrowRight" 
                            :class="[
                                'h-5 w-5 mr-2', 
                                isCheckingStatus && registrationStatus === 'pending' ? 'animate-spin' : ''
                            ]"
                        />
                        {{ isCheckingStatus && registrationStatus === 'pending' ? 'Checking...' : statusInfo.actionText }}
                    </button>

                    <!-- Status Check Message -->
                    <div v-if="statusMessage" class="mt-4">
                        <div :class="[
                            'inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium',
                            statusMessage.includes('Error') ? 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-300' :
                            statusMessage.includes('successfully') ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-300' :
                            'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-300'
                        ]">
                            <component 
                                :is="statusMessage.includes('Checking') ? RefreshCw : statusMessage.includes('Error') ? XCircle : CheckCircle"
                                :class="[
                                    'h-4 w-4 mr-2',
                                    statusMessage.includes('Checking') ? 'animate-spin' : ''
                                ]"
                            />
                            {{ statusMessage }}
                        </div>
                    </div>

                    <!-- Additional Actions for Pending Status -->
                    <div v-if="registrationStatus === 'pending'" class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex flex-col sm:flex-row gap-4 justify-center items-center text-sm text-gray-600 dark:text-gray-400">
                            <div class="flex items-center">
                                <Mail class="h-4 w-4 mr-2" />
                                <span>Confirmation sent to {{ props.user.email }}</span>
                            </div>
                            <div class="flex items-center">
                                <Clock class="h-4 w-4 mr-2" />
                                <span>Expected response: 1-2 business days</span>
                            </div>
                            <div v-if="lastChecked" class="flex items-center">
                                <RefreshCw class="h-4 w-4 mr-2" />
                                <span>Last checked: {{ lastChecked }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Help Section -->
                <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-6 text-center">
                    <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-2">
                        Need Help?
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        If you have questions about the registration process or need assistance, our support team is here to help.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center text-sm">
                        <a href="mailto:support@petconnect.com" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                            📧 support@petconnect.com
                        </a>
                        <a href="tel:+1-555-123-4567" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                            📞 +1 (555) 123-4567
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
