<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard, petsIndex, clinics, history } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import PlaceholderPattern from '@/components/PlaceholderPattern.vue';
import { computed } from 'vue';

interface User {
    id: number;
    name: string;
    username?: string;
    email: string;
    phone?: string;
    address_line_1?: string;
    address_line_2?: string;
    city?: string;
    state?: string;
    postal_code?: string;
    country?: string;
    emergency_contact_name?: string;
    emergency_contact_relationship?: string;
    emergency_contact_phone?: string;
    date_of_birth?: string;
    gender?: string;
    bio?: string;
    email_verified_at?: string;
    created_at: string;
    initials: string;
    full_address?: string;
    has_complete_address: boolean;
    has_emergency_contact: boolean;
    membership_years: number;
    profile_completion_percentage: number;
}

interface Props {
    user: User;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

// Navigation functions
const navigateToAddPet = () => {
    router.visit(petsIndex().url);
};

const navigateToBookAppointment = () => {
    router.visit(clinics().url);
};

const navigateToHistory = () => {
    router.visit(history().url);
};

const navigateToProfile = () => {
    // Navigate to profile settings - using settings profile route
    router.visit('/settings/profile');
};

// Computed properties for formatted data
const memberSinceFormatted = computed(() => {
    const date = new Date(props.user.created_at);
    return date.getFullYear();
});

const profileSettingsLink = '/settings/profile';
const contactSettingsLink = '/settings/contact-information';
const addressSettingsLink = '/settings/address';
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <!-- Comprehensive Account Overview -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Account Overview</h2>
                    </div>
                    
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Profile Section -->
                        <div class="lg:col-span-1">
                            <div class="text-center">
                                <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <span class="text-white text-2xl font-bold">{{ user.initials }}</span>
                                </div>
                                <h3 class="font-semibold text-lg text-gray-900 dark:text-gray-100 mb-1">{{ user.name }}</h3>
                                <p v-if="user.username" class="text-sm text-gray-600 dark:text-gray-400 mb-1">@{{ user.username }}</p>
                                <p v-else class="text-sm text-gray-600 dark:text-gray-400 mb-1">
                                    <Link :href="profileSettingsLink" class="text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
                                        Set username
                                    </Link>
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-500 mb-4">{{ user.email }}</p>
                                
                                <!-- Account Stats -->
                                <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-600">
                                    <div class="grid grid-cols-3 gap-4 text-center">
                                        <div>
                                            <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">0</p>
                                            <p class="text-xs text-gray-600 dark:text-gray-400">Pets</p>
                                        </div>
                                        <div>
                                            <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">0</p>
                                            <p class="text-xs text-gray-600 dark:text-gray-400">Visits</p>
                                        </div>
                                        <div>
                                            <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ user.membership_years }}</p>
                                            <p class="text-xs text-gray-600 dark:text-gray-400">{{ user.membership_years === 1 ? 'Year' : 'Years' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Contact & Address Information -->
                        <div class="lg:col-span-2">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Contact Information -->
                                <div>
                                    <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-3">Contact Information</h4>
                                    <div class="space-y-3">
                                        <div class="flex items-center">
                                            <span class="text-gray-500 dark:text-gray-400 text-sm w-5">📧</span>
                                            <span class="text-sm text-gray-900 dark:text-gray-100 ml-3">{{ user.email }}</span>
                                        </div>
                                        <div class="flex items-center">
                                            <span class="text-gray-500 dark:text-gray-400 text-sm w-5">📱</span>
                                            <span v-if="user.phone" class="text-sm text-gray-900 dark:text-gray-100 ml-3">{{ user.phone }}</span>
                                            <Link v-else :href="contactSettingsLink" class="text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 ml-3">
                                                Add phone number
                                            </Link>
                                        </div>
                                        <div class="flex items-center">
                                            <span class="text-gray-500 dark:text-gray-400 text-sm w-5">📅</span>
                                            <span class="text-sm text-gray-900 dark:text-gray-100 ml-3">Member since {{ memberSinceFormatted }}</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Address Information -->
                                <div>
                                    <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-3">Address</h4>
                                    <div v-if="user.has_complete_address" class="flex items-start">
                                        <span class="text-gray-500 dark:text-gray-400 text-sm w-5 mt-0.5">🏠</span>
                                        <div class="ml-3">
                                            <p class="text-sm text-gray-900 dark:text-gray-100">{{ user.address_line_1 }}</p>
                                            <p v-if="user.address_line_2" class="text-sm text-gray-900 dark:text-gray-100">{{ user.address_line_2 }}</p>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ user.city }}, {{ user.state }} {{ user.postal_code }}</p>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ user.country }}</p>
                                        </div>
                                    </div>
                                    <div v-else class="flex items-center">
                                        <span class="text-gray-500 dark:text-gray-400 text-sm w-5">🏠</span>
                                        <Link :href="addressSettingsLink" class="text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 ml-3">
                                            Add address
                                        </Link>
                                    </div>
                                </div>
                                
                                <!-- Emergency Contact -->
                                <div>
                                    <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-3">Emergency Contact</h4>
                                    <div v-if="user.has_emergency_contact" class="space-y-2">
                                        <div class="flex items-center">
                                            <span class="text-gray-500 dark:text-gray-400 text-sm w-5">👤</span>
                                            <span class="text-sm text-gray-900 dark:text-gray-100 ml-3">
                                                {{ user.emergency_contact_name }}
                                                <span v-if="user.emergency_contact_relationship" class="text-gray-500 dark:text-gray-400">
                                                    ({{ user.emergency_contact_relationship }})
                                                </span>
                                            </span>
                                        </div>
                                        <div class="flex items-center">
                                            <span class="text-gray-500 dark:text-gray-400 text-sm w-5">📞</span>
                                            <span class="text-sm text-gray-900 dark:text-gray-100 ml-3">{{ user.emergency_contact_phone }}</span>
                                        </div>
                                    </div>
                                    <div v-else class="flex items-center">
                                        <span class="text-gray-500 dark:text-gray-400 text-sm w-5">👤</span>
                                        <Link :href="contactSettingsLink" class="text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 ml-3">
                                            Add emergency contact
                                        </Link>
                                    </div>
                                </div>
                                
                                <!-- Account Status -->
                                <div>
                                    <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-3">Account Status</h4>
                                    <div class="space-y-2">
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-gray-600 dark:text-gray-400">Email Verified</span>
                                            <span class="text-sm font-medium text-green-600 dark:text-green-400">
                                                {{ user.email_verified_at ? 'Yes' : 'No' }}
                                            </span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-gray-600 dark:text-gray-400">Account Type</span>
                                            <span class="text-sm text-gray-900 dark:text-gray-100">Member</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-gray-600 dark:text-gray-400">Profile Completion</span>
                                            <span class="text-sm text-gray-900 dark:text-gray-100">{{ user.profile_completion_percentage }}%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Quick Actions -->
                            <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-600">
                                <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-3">Quick Actions</h4>
                                <div class="flex flex-wrap gap-2">
                                    <button @click="navigateToProfile" class="px-3 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded-md hover:bg-blue-200 dark:bg-blue-900 dark:text-blue-200 dark:hover:bg-blue-800">
                                        Update Profile
                                    </button>
                                    <button @click="navigateToAddPet" class="px-3 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-md hover:bg-green-200 dark:bg-green-900 dark:text-green-200 dark:hover:bg-green-800">
                                        Add Pet
                                    </button>
                                    <button @click="navigateToBookAppointment" class="px-3 py-1 bg-purple-100 text-purple-800 text-xs font-medium rounded-md hover:bg-purple-200 dark:bg-purple-900 dark:text-purple-200 dark:hover:bg-purple-800">
                                        Book Appointment
                                    </button>
                                    <button @click="navigateToHistory" class="px-3 py-1 bg-gray-100 text-gray-800 text-xs font-medium rounded-md hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                                        View History
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Footer -->
                    <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-600 text-center">
                        <p class="text-xs text-gray-500 dark:text-gray-500">
                            Account created: {{ new Date(user.created_at).toLocaleDateString() }} • 
                            <span v-if="user.email_verified_at" class="text-green-600 dark:text-green-400">Email verified ✓</span>
                            <span v-else class="text-yellow-600 dark:text-yellow-400">Email not verified</span>
                        </p>
                    </div>
                </div>
            </div>
            <div
                class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border bg-white dark:bg-gray-800"
            >
                <!-- Notification Summary -->
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Notifications</h2>
                        <div class="flex gap-2">
                            <button class="text-blue-600 hover:text-blue-700 text-sm dark:text-blue-400 dark:hover:text-blue-300">
                                Mark All Read
                            </button>
                            <button class="text-gray-600 hover:text-gray-700 text-sm dark:text-gray-400 dark:hover:text-gray-300">
                                Settings
                            </button>
                        </div>
                    </div>
                    
                    <div class="space-y-4">
                        <!-- Appointment Reminder -->
                        <div class="flex items-start space-x-3 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border-l-4 border-blue-500">
                            <div class="flex-shrink-0">
                                <span class="text-blue-600 dark:text-blue-400 text-lg">📅</span>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <h4 class="font-medium text-gray-900 dark:text-gray-100">Appointment Reminder</h4>
                                    <span class="text-xs text-blue-600 dark:text-blue-400 font-medium">2 hours ago</span>
                                </div>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                    Bella's annual checkup is scheduled for today at 2:30 PM with Dr. Sarah Johnson
                                </p>
                                <div class="flex gap-2 mt-2">
                                    <button class="text-xs bg-blue-600 text-white px-3 py-1 rounded-md hover:bg-blue-700">
                                        View Details
                                    </button>
                                    <button class="text-xs text-blue-600 hover:text-blue-700 dark:text-blue-400">
                                        Dismiss
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Vaccination Due -->
                        <div class="flex items-start space-x-3 p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border-l-4 border-yellow-500">
                            <div class="flex-shrink-0">
                                <span class="text-yellow-600 dark:text-yellow-400 text-lg">💉</span>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <h4 class="font-medium text-gray-900 dark:text-gray-100">Vaccination Due</h4>
                                    <span class="text-xs text-yellow-600 dark:text-yellow-400 font-medium">1 day ago</span>
                                </div>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                    Max's rabies vaccination is due next week. Schedule an appointment to keep him protected.
                                </p>
                                <div class="flex gap-2 mt-2">
                                    <button class="text-xs bg-yellow-600 text-white px-3 py-1 rounded-md hover:bg-yellow-700">
                                        Schedule Now
                                    </button>
                                    <button class="text-xs text-yellow-600 hover:text-yellow-700 dark:text-yellow-400">
                                        Remind Later
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Treatment Complete -->
                        <div class="flex items-start space-x-3 p-4 bg-green-50 dark:bg-green-900/20 rounded-lg border-l-4 border-green-500">
                            <div class="flex-shrink-0">
                                <span class="text-green-600 dark:text-green-400 text-lg">✅</span>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <h4 class="font-medium text-gray-900 dark:text-gray-100">Treatment Complete</h4>
                                    <span class="text-xs text-green-600 dark:text-green-400 font-medium">3 days ago</span>
                                </div>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                    Luna's dental cleaning was completed successfully. Follow-up care instructions have been sent to your email.
                                </p>
                                <div class="flex gap-2 mt-2">
                                    <button class="text-xs text-green-600 hover:text-green-700 dark:text-green-400">
                                        View Report
                                    </button>
                                    <button class="text-xs text-green-600 hover:text-green-700 dark:text-green-400">
                                        Mark Read
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Prescription Refill -->
                        <div class="flex items-start space-x-3 p-4 bg-purple-50 dark:bg-purple-900/20 rounded-lg border-l-4 border-purple-500">
                            <div class="flex-shrink-0">
                                <span class="text-purple-600 dark:text-purple-400 text-lg">💊</span>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <h4 class="font-medium text-gray-900 dark:text-gray-100">Prescription Refill</h4>
                                    <span class="text-xs text-purple-600 dark:text-purple-400 font-medium">5 days ago</span>
                                </div>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                    Bella's arthritis medication is ready for pickup at Happy Paws Veterinary Clinic.
                                </p>
                                <div class="flex gap-2 mt-2">
                                    <button class="text-xs bg-purple-600 text-white px-3 py-1 rounded-md hover:bg-purple-700">
                                        Get Directions
                                    </button>
                                    <button class="text-xs text-purple-600 hover:text-purple-700 dark:text-purple-400">
                                        Mark Read
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- System Update -->
                        <div class="flex items-start space-x-3 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg border-l-4 border-gray-400">
                            <div class="flex-shrink-0">
                                <span class="text-gray-600 dark:text-gray-400 text-lg">🔔</span>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <h4 class="font-medium text-gray-900 dark:text-gray-100">System Update</h4>
                                    <span class="text-xs text-gray-600 dark:text-gray-400 font-medium">1 week ago</span>
                                </div>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                    PetConnect has been updated with new features including enhanced appointment scheduling and pet health tracking.
                                </p>
                                <div class="flex gap-2 mt-2">
                                    <button class="text-xs text-gray-600 hover:text-gray-700 dark:text-gray-400">
                                        Learn More
                                    </button>
                                    <button class="text-xs text-gray-600 hover:text-gray-700 dark:text-gray-400">
                                        Dismiss
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Load More -->
                    <div class="flex justify-center mt-6">
                        <button class="text-blue-600 hover:text-blue-700 text-sm font-medium dark:text-blue-400 dark:hover:text-blue-300">
                            Load More Notifications
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
